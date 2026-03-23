<?php

namespace models\docs;

use components\layout\Grid\description\Grid;
use components\layout\Grid\description\GridColumn;
use core\App;
use core\data\DataItem;
use core\database\sql\Column;
use core\database\sql\Database;
use core\database\sql\Model;
use core\database\sql\query\Parameter;
use core\database\sql\query\Query;
use core\database\sql\SideEffect;
use core\database\sql\Sql;
use core\database\sql\Table;
use models\core\Language\Language;

#[Grid]
#[Database(App::DATABASE)]
#[Table('docs_content')]
class Document extends Model {
    public const DATA_NAMESPACE = 'docs';
    public const TABLE_CONTENTS_X_FRAGMENTS = 'docs_contents_x_fragments';



    public static function fromFile(string $file, bool $create = false): ?static {
        $document = static::first(
            where: Query::infer('file = ?', $file)
        );

        if (!is_null($document)) {
            return $document;
        }

        if ($create) {
            $document = new Document();

            $document->file = $file;
            $document->fileSize = filesize($file);
            $document->lastUpdated = filemtime($file);
            $document->hash = sha1_file($file);

            $document->save();
        }

        return $document;
    }



    #[Column('id_content', type: Column::TYPE_INTEGER, isPrimaryKey: true)]
    public int $id;

    #[GridColumn]
    #[Column(type: Column::TYPE_STRING)]
    public string $file;

    #[Column('file_size', type: Column::TYPE_INTEGER)]
    public int $fileSize;

    #[Column('last_updated', type: Column::TYPE_INTEGER)]
    public int $lastUpdated;

    #[Column(type: Column::TYPE_STRING)]
    public string $hash;



    /**
     * @var array<Fragment>
     */
    protected array $fragments;



    public function needsUpdate(): bool {
        if (!file_exists($this->file)) {
            return false;
        }

        if (!($this->lastUpdated !== filemtime($this->file) || $this->fileSize !== filesize($this->file))) {
            return false;
        }

        if ($this->hash === sha1_file($this->file)) {
            return false;
        }

        return true;
    }

    public function updateFileInfo(bool $save = false): void {
        $this->fileSize = filesize($this->file);
        $this->lastUpdated = filemtime($this->file);
        $this->hash = sha1_file($this->file);

        if ($save) {
            $this->save();
        }
    }

    public function getContent(Language $language): DataItem {
        return $this->getDataItem(self::DATA_NAMESPACE, $language->code .'_content.md');
    }

    /**
     * @return array<Fragment>
     */
    public function getFragments(): array {
        if (isset($this->fragments)) {
            return $this->fragments;
        }

        $connection = static::getDescription()->getConnection();
        $driver = $connection->getDriver();

        $gr = $driver->escapeTable(self::TABLE_CONTENTS_X_FRAGMENTS);
        $fragment = Fragment::getDescription();

        $sql = Sql::select($fragment->getEscapedTable())
            ->naturalJoin($gr)
            ->where(Query::infer("$gr.id_content = ?", $this->id));

        $factory = $fragment->getFactory();
        $factory->addProjection($sql);

        return $this->fragments = $fragment->getFactory()
            ->allExecute($sql);
    }

    public function clearFragments(): SideEffect {
        $connection = static::getDescription()->getConnection();

        $sql = Sql::delete(self::TABLE_CONTENTS_X_FRAGMENTS)
            ->where(Query::infer("id_content = ?", $this->id));

        return $sql->run($connection);
    }

    /**
     * @param array<Fragment> $fragments array of ['id_resource' => int, 'id_privilege' => int]
     * @return SideEffect
     */
    public function addFragments(array $fragments): SideEffect {
        if (count($fragments) === 0) {
            return SideEffect::none();
        }

        $connection = static::getDescription()->getConnection();

        $sql = Sql::insert(self::TABLE_CONTENTS_X_FRAGMENTS)
            ->columns(['id_content', 'id_fragment']);

        foreach ($fragments as $fragment) {
            $sql->value([
                Parameter::infer($this->id),
                Parameter::infer($fragment->id),
            ]);
        }

        return $sql->run($connection);
    }
}