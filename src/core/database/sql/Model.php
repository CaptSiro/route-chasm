<?php

namespace core\database\sql;

use components\core\Admin\Nexus\NexusProxyItem;
use components\core\SaveError\SaveError;
use core\data\DataItem;
use core\database\sql\query\Parameter;
use core\database\sql\query\Query;
use core\database\sql\query\SqlQuery;
use core\Identifier;
use core\RouteChasmEnvironment;
use core\utils\Strings;
use core\view\View;
use JsonSerializable;
use RuntimeException;

class Model implements JsonSerializable, Identifier, NexusProxyItem {
    public static function getDescription(): ModelDescription {
        return ModelDescription::extract(static::class);
    }

    public static function getTable(): string {
        return ModelDescription::extract(static::class)->getTable();
    }



    /**
     * @param array<string, mixed> $properties `[$phpPropertyName => $value]` Do not use column name as a key
     * @param bool $save
     * @return static
     */
    public static function create(array $properties, bool $save = true): static {
        $instance = new static();
        $instance->set($properties);

        if ($save) {
            $error = $instance->save();
            if ($error instanceof SaveError) { // todo make SaveError interface View + toThrowable()
                throw new RuntimeException($error->getMessage());
            }
        }

        return $instance;
    }

    /**
     * @param Model|null $instance
     * @param array<string, mixed> $properties
     * @param bool $create
     * @param bool $save
     * @return static|null
     */
    protected static function createConditionally(
        ?self $instance,
        array $properties,
        bool $create = false,
        bool $save = true
    ): ?static {
        if (!is_null($instance)) {
            return $instance;
        }

        if ($create) {
            return self::create($properties, $save);
        }

        return null;
    }

    public static function fromRecord(?array $record, Origin $origin = Origin::EXTERNAL): ?static {
        return ModelFactory::extract(static::class)
            ->fromRecord($record, $origin);
    }

    /**
     * @param array<array> $records
     * @param Origin $origin
     * @return array<static>
     */
    public static function fromRecords(array $records, Origin $origin = Origin::EXTERNAL): array {
        return ModelFactory::extract(static::class)
            ->fromRecords($records, $origin);
    }


    /**
     * @param ?array $projection Set of columns to select from database. If left null, all columns are selected
     * @param Query|string|null $where
     * @return ?static
     */
    public static function first(?array $projection = null, Query|string|null $where = null): ?static {
        return ModelFactory::extract(static::class)
            ->first($projection, $where);
    }

    /**
     * @param mixed $id
     * @param ?array $projection Set of columns to select from database. If left null, all columns are selected
     * @return static|null
     */
    public static function fromId(mixed $id, ?array $projection = null): ?static {
        return ModelFactory::extract(static::class)
            ->fromId($id, $projection);
    }

    /**
     * @param ?array $projection Set of columns to select from database. If left null, all columns are selected
     * @param Query|string|null $where
     * @return array<static>
     */
    public static function all(?array $projection = null, Query|string|null $where = null): array {
        return ModelFactory::extract(static::class)
            ->all($projection, $where);
    }

    public static function count(Query|string|null $where = null): int {
        return ModelFactory::extract(static::class)
            ->count($where);
    }

    public static function random(Query|string|null $where = null): static {
        return ModelFactory::extract(static::class)
            ->random($where);
    }



    private Origin $_origin;

    public function __construct() {
        $this->_origin = Origin::APPLICATION;
    }



    public function __get(string $name) {
        if (!isset($this->$name)) {
            return null;
        }

        return $this->$name;
    }

    public function __set(string $alias, $value): void {
        $this->$alias = $value;
    }

    /**
     * @param array $data `[$phpPropertyName => $value]` Do not use column name as a key
     * @return $this
     */
    public function set(array $data, bool $canSetIdColumn = false): static {
        $description = ModelDescription::extract(static::class);
        $idColumnName = $description->getIdColumn()->getName();

        foreach ($data as $property => $value) {
            if (!isset($description->getAlias()[$property])) {
                continue;
            }

            $column = $description->getAlias()[$property];
            if (!$canSetIdColumn && $column->getName() === $idColumnName) {
                continue;
            }

            $this->$property = $column->transform($value);
        }

        return $this;
    }

    public function getId(): mixed {
        $description = ModelDescription::extract(static::class);
        $id = $description->getIdColumn()->getAlias();
        if (!isset($this->{$id})) {
            return null;
        }

        return $this->{$id};
    }

    public function getModelType(): string {
        return ModelDescription::extract(static::class)
            ->getModelType();
    }

    /**
     * @param string|null $subtype kebab-case subtype name used for string identifiers: model-type_sub-type#ID
     * @return string
     */
    public function getMachineIdentifier(?string $subtype = null): string {
        $type = $this->getModelType();
        if (is_null($subtype)) {
            return $type .'#'. $this->id;
        }

        return $type .'_'. $subtype .'#'. $this->id;
    }

    public function getHumanIdentifier(): string {
        return $this->getMachineIdentifier();
    }

    public function getDataItem(string $namespace, string $item = ''): DataItem {
        $file = Strings::lpad('0', (string) $this->getId(), RouteChasmEnvironment::ID_DIGITS);
        if (!empty($item)) {
            $file .= '_'. $item;
        }

        return new DataItem($namespace, $file);
    }

    public function setOrigin(Origin $_origin): void {
        $this->_origin = $_origin;
    }

    public function notSavable(): void {
        $this->_origin = Origin::UNKNOWN;
    }

    public function isNewRecord(): bool {
        return $this->_origin === Origin::APPLICATION;
    }

    public function isSavable(): bool {
        return $this->_origin !== Origin::UNKNOWN;
    }

    public function isEditable(): bool {
        return true;
    }

    public function isDeletable(): bool {
        return true;
    }

    private function insertQuery(): SqlQuery {
        $description = ModelDescription::extract(static::class);
        $sql = Sql::insert($description->getTable());
        $idColumnName = $description->getIdColumn()->getName();

        $columns = [];
        $record = [];

        foreach ($description->getAlias() as $alias => $column) {
            if ($column->getName() === $idColumnName) {
                continue;
            }

            $columns[] = $column->getName();
            $record[] = new Parameter($this->{$column->getAlias()}, $column->getType());
        }

        $sql->columns($columns);
        $sql->value($record);

        return $sql;
    }

    private function updateQuery(): ?SqlQuery {
        $description = ModelDescription::extract(static::class);
        $sql = Sql::update($description->getTable());
        $idColumnName = $description->getIdColumn()->getName();

        $setClauses = 0;

        foreach ($description->getAlias() as $alias => $column) {
            if ($column->getName() === $idColumnName) {
                continue;
            }

            $sql->set($column->getName(), new Parameter(
                $this->{$column->getAlias()},
                $column->getType()
            ));
            $setClauses++;
        }

        if ($setClauses === 0) {
            return null;
        }

        $sql->where(new Query(
            $description->getEscapedIdColumnName() ." = ?",
            [
                new Parameter(
                    $this->{$description->getIdColumn()->getAlias()},
                    $description->getIdColumn()->getType()
                )
            ]
        ));

        return $sql;
    }

    public function deleteQuery(): SqlQuery {
        $description = ModelDescription::extract(static::class);
        $idColumnName = $description->getEscapedIdColumnName();

        return Sql::delete($description->getTable())
            ->where(new Query(
                "$idColumnName = ?",
                [new Parameter(
                    $this->{$description->getIdColumn()->getAlias()},
                    $description->getIdColumn()->getType()
                )]
            ))
            ->limit(1);
    }

    public function saveQuery(): ?SqlQuery {
        if (!$this->isSavable()) {
            return null;
        }

        if ($this->isNewRecord()) {
            return $this->insertQuery();
        }

        return $this->updateQuery();
    }

    private function insert(): DatabaseAction|View {
        $description = ModelDescription::extract(static::class);

        /** @var SideEffect $sideEffect */
        $sideEffect = $this->insertQuery()->run($description->getConnection());
        if ($sideEffect->getRowsAffected() === 0) {
            return DatabaseAction::NONE;
        }

        $this->{$description->getIdColumn()->getAlias()} = $sideEffect->getLastInsertedId();
        return DatabaseAction::INSERT;
    }

    public function save(): DatabaseAction|View {
        if (!$this->isSavable()) {
            return DatabaseAction::NONE;
        }

        if ($this->isNewRecord()) {
            return $this->insert();
        }

        $description = ModelDescription::extract(static::class);
        $sql = $this->updateQuery();
        if (is_null($sql)) {
            return DatabaseAction::NONE;
        }

        $sideEffect = $sql->run($description->getConnection());
        if ($sideEffect->getRowsAffected() === 0) {
            return DatabaseAction::NONE;
        }

        return DatabaseAction::UPDATE;
    }

    public function delete(): DatabaseAction {
        $description = ModelDescription::extract(static::class);
        /** @var SideEffect $sideEffect */
        $sideEffect = $this->deleteQuery()->run($description->getConnection());
        if ($sideEffect->getRowsAffected() === 0) {
            return DatabaseAction::NONE;
        }

        return DatabaseAction::DELETE;
    }

    public function getOrigin(): Origin {
        return $this->_origin;
    }

    public function jsonSerialize(): object {
        return $this;
    }

    public function getData(): array {
        $description = ModelDescription::extract(static::class);
        $data = [];

        foreach ($description->getAlias() as $alias => $ignored) {
            $data[$alias] = $this->$alias ?? null;
        }

        return $data;
    }
}