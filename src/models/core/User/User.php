<?php

namespace models\core\User;

use components\core\SaveError\SaveError;
use components\layout\Grid\description\Grid;
use components\layout\Grid\description\GridColumn;
use core\App;
use core\collections\dictionary\Session;
use core\communication\Request;
use core\database\sql\Column;
use core\database\sql\Database;
use core\database\sql\DatabaseAction;
use core\database\sql\Model;
use core\database\sql\query\Parameter;
use core\database\sql\query\Query;
use core\database\sql\SideEffect;
use core\database\sql\Sql;
use core\database\sql\Table;
use core\view\View;
use Exception;
use models\core\Group\Group;
use models\core\Privilege\Privilege;
use models\core\UserResource;

#[Grid(proxy: new UserProxy)]
#[Table('core_user')]
#[Database(App::DATABASE)]
class User extends Model {
    public const TAG_ROOT = 'root';
    public const TAG_ANONYMOUS = 'anonymous';
    public const TABLE_USERS_X_GROUPS = 'core_users_x_groups';

    public static function fromTag(string $tag): ?static {
        return static::first(
            where: Query::infer('tag = ?', $tag)
        );
    }

    public static function fromSession(Session $session): ?static {
        $id = $session->get(App::KEY_LOGGED_IN_USER);
        if (is_null($id)) {
            return User::fromTag(User::TAG_ANONYMOUS);
        }

        return static::fromId($id) ?? User::fromTag(User::TAG_ANONYMOUS);
    }

    public static function fromRequest(Request $request): ?static {
        if ($request->exists(App::KEY_LOGGED_IN_USER)) {
            return $request->get(App::KEY_LOGGED_IN_USER);
        }

        $request->set(App::KEY_LOGGED_IN_USER, $user = self::fromSession($request->getSession()));
        return $user;
    }

    public static function fromSessionId(Session $session): ?int {
        $id = $session->get(App::KEY_LOGGED_IN_USER);
        if (is_null($id)) {
            return null;
        }

        return intval($id);
    }

    public static function logout(): void {
        App::getInstance()
            ->getRequest()
            ->getSession()
            ->remove(App::KEY_LOGGED_IN_USER);
    }



    #[Column('id_user', Column::TYPE_INTEGER, true)]
    public int $id;

    #[GridColumn]
    #[Column(type: Column::TYPE_STRING)]
    public string $username;

    #[Column(type: Column::TYPE_STRING)]
    public string $password;

    #[GridColumn]
    #[Column(type: Column::TYPE_STRING)]
    public string $tag;



    private array $groups;



    // Model
    public function getHumanIdentifier(): string {
        return '@'. $this->tag;
    }

    public function save(): DatabaseAction|View {
        if ($this->isNewRecord()) {
            $user = self::fromTag($this->tag);

            if (!is_null($user)) {
                return new SaveError('tag', 'Tag is already taken by another user');
            }
        }

        return parent::save();
    }

    public function delete(): DatabaseAction {
        if (self::fromSessionId(App::getInstance()->getRequest()->getSession()) === $this->id) {
            self::logout();
        }

        return parent::delete();
    }



    public function login(): void {
        App::getInstance()
            ->getRequest()
            ->getSession()
            ->set(App::KEY_LOGGED_IN_USER, $this->id);
    }

    /**
     * @return array<Group>
     */
    public function getGroups(): array {
        if (isset($this->groups)) {
            return $this->groups;
        }

        $group = Group::getDescription();
        $driver = $group->getConnection()->getDriver();
        $groupTable = $group->getEscapedTable();
        $ug = $driver->escapeTable('ug');

        $sql = Sql::select($groupTable)
            ->join(
                $driver->escapeTable('core_users_x_groups') ." AS $ug",
                Query::infer("$ug.id_group = $groupTable.id_group AND $ug.id_user = ?", $this->id)
            );

        $group->projection($sql);

        return $this->groups = Group::fromRecords(
            $sql->fetchAll($group->getConnection())
        );
    }

    public function inGroup(Group $group): bool {
        return $this->inGroupRaw($group->id);
    }

    public function inGroupRaw(int $groupId): bool {
        $connection = static::getDescription()->getConnection();
        $driver = $connection->getDriver();
        $ug = $driver->escapeTable(self::TABLE_USERS_X_GROUPS);

        $sql = Sql::select($ug)
            ->projection("$ug.id_group")
            ->where(Query::infer(
                "$ug.id_group = ? AND $ug.id_user = ?",
                $groupId,
                $this->id
            ));

        return !is_null($sql->fetch($connection));
    }

    public function leaveAllGroups(): SideEffect {
        $ug = self::TABLE_USERS_X_GROUPS;

        return Sql::delete(self::TABLE_USERS_X_GROUPS)
            ->where(Query::infer("$ug.id_user = ?", $this->id))
            ->run(static::getDescription()->getConnection());
    }

    /**
     * @param array<Group> $groups
     * @return DatabaseAction
     * @throws Exception
     */
    public function assign(array $groups): DatabaseAction {
        return $this->assignIds(array_map(fn($x) => $x->id, $groups));
    }

    /**
     * @param array<int> $groupIds
     * @return DatabaseAction
     */
    public function assignIds(array $groupIds): DatabaseAction {
        $this->leaveAllGroups();

        if (empty($groupIds)) {
            return DatabaseAction::NONE;
        }

        $connection = static::getDescription()->getConnection();
        $driver = $connection->getDriver();

        $sql = Sql::insert(self::TABLE_USERS_X_GROUPS)
            ->columns(['id_group', 'id_user']);

        $userId = new Parameter($this->id, Parameter::TYPE_INTEGER);

        $memberOf = [];
        foreach ($this->getGroups() as $group) {
            $memberOf[] = $group->id;
        }

        foreach ($groupIds as $groupId) {
            if (in_array($groupId, $memberOf)) {
                continue;
            }

            $sql->value([
                new Parameter($groupId, Parameter::TYPE_INTEGER),
                $userId
            ]);
        }

        if ($sql->empty()) {
            return DatabaseAction::NONE;
        }

        $sideEffect = $sql->run($connection);

        return $sideEffect->getRowsAffected() > 0
            ? DatabaseAction::INSERT
            : DatabaseAction::NONE;
    }

    public function is(string $groupName): bool {
        foreach ($this->getGroups() as $group) {
            if ($group->name === $groupName) {
                return true;
            }
        }

        return false;
    }

    public function isAdmin(): bool {
        return $this->is(Group::NAME_ADMIN) || $this->isRoot();
    }

    public function isRoot(): bool {
        return $this->is(Group::NAME_ROOT);
    }

    public function isAnonymous(): bool {
        return $this->tag === self::TAG_ANONYMOUS;
    }

    public function hasAccess(UserResource $resource, Privilege $privilege): bool {
        return $this->hasAccessRaw($resource->id, $privilege->id);
    }

    private array $accessCache = [];

    public function hasAccessRaw(int $resourceId, int $privilegeId): bool {
        if ($this->isRoot()) {
            return true;
        }

        $key = $resourceId .'-'. $privilegeId;
        $hit = $this->accessCache[$key] ?? null;
        if (!is_null($hit)) {
            return $hit;
        }

        $connection = static::getDescription()->getConnection();
        $driver = $connection->getDriver();

        $ug = $driver->escapeTable(self::TABLE_USERS_X_GROUPS);
        $gr = $driver->escapeTable(Group::TABLE_GROUPS_X_RESOURCES);

        $sql = Sql::select($ug)
            ->projection("$gr.id_privilege")
            ->join(
                $gr,
                Query::infer(
                    "$ug.id_group = $gr.id_group AND $ug.id_user = ? AND $gr.id_resource = ? AND $gr.id_privilege = ?",
                    $this->id,
                    $resourceId,
                    $privilegeId
                )
            );

        return $this->accessCache[$key] = !is_null($sql->fetch($connection));
    }
}