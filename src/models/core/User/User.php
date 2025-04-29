<?php

namespace models\core\User;

use components\layout\Grid\description\Grid;
use components\layout\Grid\description\GridColumn;
use core\App;
use core\database\sql\Column;
use core\database\sql\Database;
use core\database\sql\Model;
use core\database\sql\Table;

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
    #[Column(type: Column::TYPE_INTEGER, primaryKey: true)]
    protected int $id;

    #[GridColumn]
    #[Column(type: Column::TYPE_STRING)]
    protected string $username;

    #[Column(type: Column::TYPE_STRING)]
    protected string $password;

    #[GridColumn]
    #[Column(type: Column::TYPE_STRING)]
    protected string $tag;
}