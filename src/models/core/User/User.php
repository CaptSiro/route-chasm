<?php

namespace models\core\User;

use components\core\SaveError\SaveError;
use components\layout\Grid\description\Grid;
use components\layout\Grid\description\GridColumn;
use core\App;
use core\collection\Session;
use core\database\sql\Action;
use core\database\sql\Column;
use core\database\sql\Database;
use core\database\sql\Model;
use core\database\sql\query\Query;
use core\database\sql\Sql;
use core\database\sql\Table;
use core\view\View;
use models\core\Group\Group;
use models\core\Privilege\Privilege;
use models\core\Resource;

/**
 * @property int $id
 * @property string $username
 * @property string $password
 * @property string $tag
 */

#[Grid(new UserProxy)]
#[Table('core_user')]
#[Database(App::DATABASE)]
class User extends Model {
    public const TAG_ROOT = 'root';
    public const TABLE_USERS_X_GROUPS = 'core_users_x_groups';

    public static function fromTag(string $tag): ?static {
        return static::first(
            where: Query::infer('tag = ?', [$tag])
        );
    }

    public static function fromSession(Session $session): ?static {
        $id = $session->get(App::KEY_USER);
        if (is_null($id)) {
            return null;
        }

        return static::fromId($id);
    }



    #[Column('id_user', Column::TYPE_INTEGER, true)]
    protected int $id;

    #[GridColumn]
    #[Column(type: Column::TYPE_STRING)]
    protected string $username;

    #[Column(type: Column::TYPE_STRING)]
    protected string $password;

    #[GridColumn]
    #[Column(type: Column::TYPE_STRING)]
    protected string $tag;



    private array $groups;



    public function save(): Action|View {
        if ($this->isNewRecord()) {
            $user = self::fromTag($this->tag);

            if (!is_null($user)) {
                return new SaveError('tag', 'Tag is already taken by another user');
            }
        }

        return parent::save();
    }

    /**
     * @return array<Group>
     */
    public function getGroups(): array {
        if (isset($this->groups)) {
            return $this->groups;
        }

        $group = Group::getDescription();
        $driver = $group->connection->getDriver();
        $groupTable = $group->getEscapedTable();
        $ug = $driver->escapeTable('ug');

        $sql = Sql::select($groupTable)
            ->join(
                $driver->escapeTable('core_users_x_groups') ." AS $ug",
                Query::infer("$ug.id_group = $groupTable.id_group AND $ug.id_user = ?", [$this->id])
            );

        $group->projection($sql);

        $records = $group->connection->fetchAll(
            $sql->toQuery($group->connection)
        );

        return $this->groups = Group::fromRecords($records);
    }

    public function inGroup(Group $group): bool {
        $connection = static::getDescription()->connection;
        $driver = $connection->getDriver();
        $ug = $driver->escapeTable(self::TABLE_USERS_X_GROUPS);

        $sql = Sql::select($ug)
            ->projection("$ug.id_group")
            ->where(Query::infer(
                "$ug.id_group = ? AND $ug.id_user = ?",
                [$group->id, $this->id]
            ));

        return !is_null($connection->fetch(
            $sql->toQuery($connection))
        );
    }

    public function isAdmin(): bool {
        foreach ($this->getGroups() as $group) {
            if ($group->name === Group::ADMIN || $group->name === Group::ROOT) {
                return true;
            }
        }

        return false;
    }

    public function hasAccess(Resource $resource, Privilege $privilege): bool {
        $connection = static::getDescription()->connection;
        $driver = $connection->getDriver();

        $ug = $driver->escapeTable(self::TABLE_USERS_X_GROUPS);
        $gr = $driver->escapeTable(Group::TABLE_GROUPS_X_RESOURCES);

        $sql = Sql::select($ug)
            ->projection("$gr.id_privilege")
            ->join(
                $gr .' AS gr',
                Query::infer(
                    "$ug.id_group = $gr.id_group AND $ug.id_user = ? AND $gr.id_resource = ? AND $gr.id_privilege = ?",
                    [$this->id, $resource->id, $privilege->id]
                )
            );

        return !is_null($connection->fetch(
            $sql->toQuery($connection)
        ));
    }
}