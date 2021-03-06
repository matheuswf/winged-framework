<?php

namespace Winged\Database\Drivers;

use Winged\Database\CurrentDB;
use Winged\Database\Database;
use Winged\Date\Date;
use Winged\Model\Model;

/**
 * Class Eloquent
 *
 * @package Winged\Database\Drivers
 */
abstract class Eloquent
{
    abstract public function prepare();

    const COMMAND_DELETE = 'delete';

    const COMMAND_UPDATE = 'update';

    const COMMAND_SELECT = 'select';

    const COMMAND_INSERT = 'insert';

    const COMMAND_COUNT = 'count';

    public $prepared = false;

    public $builded = false;

    public $queryAlias = false;

    public $currentQueryString = '';

    public $tablesUsed = [];

    public $aliasUsed = [];

    public $queryTables = [];

    public $queryFields = [];

    public $queryTablesAlias = [];

    public $queryFieldsAlias = [];

    public $queryTablesInfo = [];

    public $queryFieldsInfo = [];

    public $mysqliDataType = [];

    public $pdoDataType = [];

    public $queryValues = [];

    public $initialDelete = 'DELETE';

    public $initialUpdate = 'UPDATE';

    public $initialSelect = 'SELECT';

    public $initialInsert = 'INSERT';

    public $modifiersConditions = [
        ELOQUENT_DIFFERENT => '<>',
        ELOQUENT_SMALLER => '<',
        ELOQUENT_LARGER => '>',
        ELOQUENT_SMALLER_OR_EQUAL => '<=',
        ELOQUENT_LARGER_OR_EQUAL => '>=',
        ELOQUENT_EQUAL => '=',
        ELOQUENT_BETWEEN => 'BETWEEN',
        ELOQUENT_DESC => 'DESC',
        ELOQUENT_ASC => 'ASC',
        ELOQUENT_IN => 'IN',
        ELOQUENT_NOTIN => 'NOT IN',
        ELOQUENT_LIKE => 'LIKE',
        ELOQUENT_NOTLIKE => 'NOT LIKE',
        ELOQUENT_IS_NULL => 'IS NULL',
        ELOQUENT_IS_NOT_NULL => 'IS NOT NULL'
    ];

    /**
     * @var $database null | Database
     */
    public $database = null;

    /**
     * store an model, if an models is stored here, the intire behavior of this class change
     *
     * @var $model null | Model
     */
    public $model = null;

    /**
     * store fields for select statement
     *
     * @var $select array
     */
    protected $select = [];

    /**
     * store main table name and main alias for select statement
     *
     * @var $from array
     */
    protected $from = [];

    /**
     * store configs for inner join
     *
     * @var $innerJoin array
     */
    protected $joins = [];

    /**
     * store having values
     *
     * @var $having array
     */
    protected $having = [];

    /**
     * store grouped fields
     *
     * @var $groupBy array
     */
    protected $groupBy = [];

    /**
     * set if query fetch data has distinct
     *
     * @var $distinct array
     */
    protected $distinct = false;

    /**
     * set order by field and direction
     *
     * @var $orderBy array
     */
    protected $orderBy = [];

    /**
     * set limit when query fetch data
     *
     * @var $limit array
     */
    protected $limit = [];

    /**
     * store alias for tables
     *
     * @var $alias array
     */
    protected $alias = [];

    /**
     * store fields and values for insert statement
     *
     * @var $into array
     */
    protected $into = [];

    /**
     * store tables names for update statement
     *
     * @var $update array
     */
    protected $update = [];

    /**
     * store tables names for insert statement
     *
     * @var $insert array
     */
    protected $insert = [];

    /**
     * store fields and values for delete statement
     *
     * @var $delete array
     */
    protected $delete = [];

    /**
     * store fields and values for update statement
     *
     * @var $set array
     */
    protected $set = [];

    /**
     * store other eloquents objects, when main eloquent calls build, the others eloquents are buld so
     *
     * @var $eloquents Eloquent[] | Sqlite[] | SQLServer[] | PostgreSQL[] | MySQL[] | Cubrid[]
     */
    protected $eloquents = [];

    /**
     * store alias for tables
     *
     * @var $alias array
     */
    protected $values = [];

    /**
     * store where statement for query
     *
     * @var $where array
     */
    protected $where = [];

    protected $command = false;

    /**
     * Eloquent constructor.
     *
     * @param Database     $database
     * @param Model | null $model
     */
    public function __construct($database = null, $model = null)
    {
        if (!$database) {
            $this->database = CurrentDB::$current;
        } else {
            $this->database = $database;
        }
        $this->model = &$model;
    }

    /**
     * Example: ['table_name.field' => 'new_name', 'table.name.field']
     *
     * @param array $fields
     *
     * @return $this
     */
    public function select($fields = [])
    {
        $this->command = Eloquent::COMMAND_SELECT;
        $this->storeArray($fields, 'select');
        return $this;
    }

    /**
     * Adds distinct clause in query
     *
     * @param bool $boolean
     *
     * @return $this
     */
    public function distinct($boolean = true)
    {
        $this->distinct = $boolean;
        return $this;
    }

    /**
     * Example: ['alias' => 'table_name', 'alias' => 'table_name', 'table_name', 'table_name']
     *
     * @param array $tables
     *
     * @return $this
     */
    public function from($tables = [])
    {
        $this->storeArray($tables, 'from');
        return $this;
    }

    /**
     * Example: ['alias' => 'table_name', 'alias' => 'table_name', 'table_name', 'table_name']
     *
     * @param array $tables
     *
     * @return $this
     */
    public function update($tables = [])
    {
        $this->command = Eloquent::COMMAND_UPDATE;
        $this->storeArray($tables, 'update');
        return $this;
    }

    /**
     * Example: ['table_name.field' => 'new_name', 'table.name.field']
     *
     * @param array $fields
     *
     * @return $this
     */
    public function set($fields = [])
    {
        $this->storeArray($fields, 'set');
        return $this;
    }

    /**
     * Example: ['alias' => 'table_name', 'alias' => 'table_name', 'table_name', 'table_name']
     *
     * @param array $tables
     *
     * @return $this
     */
    public function delete($tables = [])
    {
        $this->command = Eloquent::COMMAND_DELETE;
        $this->storeArray($tables, 'delete');
        return $this;
    }

    /**
     * @return $this
     */
    public function insert()
    {
        $this->command = Eloquent::COMMAND_INSERT;
        return $this;
    }

    /**
     * @param string $table
     *
     * @return $this
     */
    public function into($table)
    {
        $this->storeArray([$table], 'into');
        return $this;
    }

    /**
     * Example: ['table_name.field' => 'new_name', 'table.name.field']
     *
     * @param array $fields
     *
     * @return $this
     */
    public function values($fields = [])
    {
        $this->storeArray($fields, 'values');
        return $this;
    }

    /**
     * Exemple: Eloquent::EQUALS, ['table_name.field' => value or Eloquent]
     *
     * @param $condition string
     * @param $values    array
     *
     * @return $this
     * @throws \Exception
     *
     */
    public function where($condition = '', $values = [])
    {
        try {
            $this->parse($condition, $values, 'begin', 'where');
        } catch (Exception $exception) {
            return $this;
        }
        return $this;
    }

    /**
     * Exemple: Eloquent::EQUALS, ['table_name.field' => value or Eloquent]
     *
     * @param $condition string
     * @param $values    array
     *
     * @return $this
     * @throws \Exception
     *
     */
    public function andWhere($condition = '', $values = [])
    {
        try {
            $this->parse($condition, $values, 'and', 'where');
        } catch (Exception $exception) {
            return $this;
        }
        return $this;
    }

    /**
     * Exemple: Eloquent::EQUALS, ['table_name.field' => value or Eloquent]
     *
     * @param $condition string
     * @param $values    array
     *
     * @return $this
     * @throws \Exception
     *
     */
    public function orWhere($condition = '', $values = [])
    {
        try {
            $this->parse($condition, $values, 'or', 'where');
        } catch (Exception $exception) {
            return $this;
        }
        return $this;
    }

    /**
     * Adds limit clause to query
     *
     * @param int $initial_or_count
     * @param int $final
     *
     * @return $this
     */
    public function limit($initial_or_count, $final = 0)
    {
        $initial_or_count = intval($initial_or_count);
        $final = intval($final);

        if (is_int($initial_or_count) && is_int($final)) {
            $this->limit = [
                'init' => $initial_or_count,
                'final' => $final
            ];
        }

        return $this;
    }

    /**
     * Exemple: Eloquent::EQUALS, ['alias' => 'table_name', 'alias.field' => 'other_alias.field' or Eloquent]
     *
     * @param string $condition
     * @param array  $inner
     *
     * @return $this
     * @throws \Exception
     *
     */
    public function leftJoin($condition = '', $inner = [])
    {
        if ($condition != '' && count7($inner) > 0) {
            try {
                $this->parse($condition, $inner, 'left', 'joins');
            } catch (Exception $exception) {
                return $this;
            }
        }
        return $this;
    }

    /**
     * Exemple: Eloquent::EQUALS, ['alias' => 'table_name', 'alias.field' => 'other_alias.field' or Eloquent]
     *
     * @param string $condition
     * @param array  $inner
     *
     * @return $this
     * @throws \Exception
     *
     */
    public function rightJoin($condition = '', $inner = [])
    {

        if ($condition != '' && count7($inner) > 0) {
            try {
                $this->parse($condition, $inner, 'right', 'joins');
            } catch (Exception $exception) {
                return $this;
            }
        }
        return $this;
    }

    /**
     * Exemple: Eloquent::EQUALS, ['alias' => 'table_name', 'alias.field' => 'other_alias.field' or Eloquent]
     *
     * @param string $condition
     * @param array  $inner
     *
     * @return $this
     * @throws \Exception
     *
     */
    public function innerJoin($condition = '', $inner = [])
    {
        if ($condition != '' && count7($inner) > 0) {
            try {
                $this->parse($condition, $inner, 'inner', 'joins');
            } catch (Exception $exception) {
                return $this;
            }
        }
        return $this;
    }

    /**
     * Adds order by clause in query
     *
     * @param string $direction
     * @param string $field
     *
     * @return $this
     */
    public function orderBy($direction = '', $field = '')
    {
        $direction = trim($direction);
        if ($direction == ELOQUENT_ASC || $direction == ELOQUENT_DESC) {
            $this->orderBy[] = [
                $direction,
                $field
            ];
        }
        return $this;
    }

    /**
     * set groupBy clause in query with fields
     *
     * @param array $fields
     *
     * @return $this
     */
    public function groupBy($fields = [])
    {
        $this->storeArray($fields, 'groupBy');
        return $this;
    }

    /**
     * Exemple: Eloquent::EQUALS, ['table_name.field' => value or Eloquent]
     *
     * @param $condition string
     * @param $values    array
     *
     * @return $this
     * @throws \Exception
     *
     */
    public function having($condition = '', $values = [])
    {
        try {
            $this->parse($condition, $values, 'begin', 'having');
        } catch (Exception $exception) {
            return $this;
        }
        return $this;
    }

    /**
     * Exemple: Eloquent::EQUALS, ['table_name.field' => value or Eloquent]
     *
     * @param $condition string
     * @param $values    array
     *
     * @return $this
     * @throws \Exception
     *
     */
    public function andHaving($condition = '', $values = [])
    {
        try {
            $this->parse($condition, $values, 'and', 'having');
        } catch (Exception $exception) {
            return $this;
        }
        return $this;
    }

    /**
     * Exemple: Eloquent::EQUALS, ['table_name.field' => value or Eloquent]
     *
     * @param $condition string
     * @param $values    array
     *
     * @return $this
     * @throws \Exception
     *
     */
    public function orHaving($condition = '', $values = [])
    {
        try {
            $this->parse($condition, $values, 'or', 'having');
        } catch (Exception $exception) {
            return $this;
        }
        return $this;
    }

    /**
     * set tables names into property name if property exists in self object
     *
     * @param $args
     * @param $propertyName
     */
    protected function storeArray($args, $propertyName)
    {
        if (property_exists($this, $propertyName)) {
            $this->{$propertyName} = $args;
        }
    }

    /**
     * add where, having and join clause into query
     *
     * @param string $condition
     * @param array  $args
     * @param string $command
     * @param string $propertyName
     *
     * @return false | $this
     * @throws \Exception
     *
     */
    protected function parse($condition = '', $args = [], $command = 'begin', $propertyName = 'where')
    {
        if (!property_exists($this, $propertyName)) {
            throw new \Exception('property ' . $propertyName . ' do not exists in $this object');
        }

        $countArguments = count7($args);
        if (in_array($command, ['inner', 'left', 'right'])) {
            if ($countArguments < 2) {
                throw new \Exception('args inside $args expected exactly two parameters, given ' . (is_bool($countArguments) ? 'boolean value' : $countArguments));
            }
        } else {
            if ($condition === ELOQUENT_BETWEEN) {
                $keys = array_keys($args);
                if (!is_array($args[$keys[0]])) {
                    throw new \Exception('args when you use ELOQUENT_BETWEEN expected an key with value equals array');
                } else {
                    if (count7($args[$keys[0]]) != 2) {
                        throw new \Exception('args inside $args expected exactly three parameter, given ' . (is_bool($args[$keys[0]]) ? 'boolean value' : count7($args[$keys[0]])));
                    }
                }
            } else {
                if ($countArguments > 1) {
                    throw new \Exception('args inside $args expected exactly one parameter, given ' . (is_bool($countArguments) ? 'boolean value' : $countArguments));
                }
            }
        }

        if (($command === 'or' || $command === 'and') && empty($this->{$propertyName})) {
            $command = 'begin';
        }

        if (!empty($this->{$propertyName}) && $command === 'begin') {
            $command = 'and';
        }

        if (!in_array($command, ['begin', 'and', 'or', 'inner', 'left', 'right'])) {
            $command = 'begin';
        }

        $this->{$propertyName}[] = [
            'type' => $command,
            'condition' => $condition,
            'args' => $args,
        ];

        return $this;
    }

    /**
     * check if field exists inside an table
     *
     * @param string $string
     * @param bool   $tableName
     *
     * @return bool
     * @throws \Exception
     */
    protected function fieldExists($string = '', $tableName = false)
    {
        if (is_string($tableName) && $tableName != '') {
            if (array_key_exists($tableName, $this->database->db_tables)) {
                if (array_key_exists($string, $this->database->db_tables[$tableName]['fields'])) {
                    return true;
                }
            }
            return false;
        }
        if ($this->model) {
            return in_array($string, $this->model->tableFields());
        }
        return true;
    }

    /**
     * check if table exists in database
     *
     * @param string $string
     *
     * @return bool
     */
    protected function tableExists($string = '')
    {
        return array_key_exists($string, $this->database->db_tables);
    }

    /**
     * check if alias has registred in eloquent object
     *
     * @param string $string
     *
     * @return string
     */
    protected function aliasExists($string = '')
    {
        foreach ($this->queryTablesAlias as $key => $value) {
            if (in_array($key, ['from', 'into', 'delete', 'update', 'joins'])) {
                if (in_array($string, $value)) {
                    $aliasIndex = array_search($string, $value);
                    if (array_key_exists($aliasIndex, $this->queryTables[$key])) {
                        return $this->queryTables[$key][$aliasIndex];
                    }
                }
            }
        }
        return false;
    }

    /**
     * @param Eloquent | Model | string | int | boolean | float | double $value
     *
     * @return array
     * @throws \Exception
     *
     */
    protected function getInformation($value)
    {
        $info = [
            'type' => false,
            'alias' => false,
            'table' => false,
            'field' => false,
            'value' => false,
            'eloquent' => false,
        ];
        if (is_string($value)) {
            preg_match('#\((.*?)\)#', $value, $matchs);
            if (empty($matchs)) {
                $exp = explode('.', trim($value));
            } else {
                $exp = explode('.', trim($matchs[1]));
            }
            if (count($exp) > 1) {
                $possibleTableOrAlias = $exp[0];
                $possibleFieldName = $exp[1];
                $tableName = $this->aliasExists($possibleTableOrAlias);
                if ($this->tableExists($possibleTableOrAlias) && !$this->aliasExists($possibleTableOrAlias)) {
                    $info['type'] = 'table';
                    $info['table'] = $possibleTableOrAlias;
                    if (!$this->fieldExists($possibleFieldName, $possibleTableOrAlias)) {
                        throw new \Exception('field ' . $possibleFieldName . ' not exists in table ' . $possibleTableOrAlias);
                    }
                    $info['field'] = $possibleFieldName;
                } else if ($tableName) {
                    $info['type'] = 'alias';
                    $info['alias'] = $possibleTableOrAlias;
                    $info['table'] = $tableName;
                    if (!$this->fieldExists($possibleFieldName, $tableName) && $possibleFieldName != "*") {
                        throw new \Exception('field ' . $possibleFieldName . ' not exists in table ' . $tableName);
                    }
                    $info['field'] = $possibleFieldName;
                } else {
                    throw new \Exception('table name or alias not registred in query. name is: ' . $possibleTableOrAlias);
                }
            } else {
                $stack = debug_backtrace();
                $columnArgs = array_column(array_column($stack, 'args'), 0);
                if (in_array('joins', $columnArgs)) {
                    $search = array_search('joins', $columnArgs);
                    if ($search) {
                        if ($columnArgs[$search] === 'joins') {
                            throw new \Exception('format alias.field_name is required in join clause');
                        }
                    }
                }
                $possibleAny = $exp[0];
                if ($possibleAny === '*') {
                    $info['type'] = 'field';
                    $info['alias'] = false;
                    $info['table'] = false;
                    $info['field'] = $possibleAny;
                } else if (!in_array($possibleAny, $this->aliasUsed) && !in_array($possibleAny, $this->tablesUsed)) {
                    $found = false;
                    $foundIn = false;
                    foreach ($this->tablesUsed as $key => $tableName) {
                        if ($this->fieldExists($possibleAny, $tableName)) {
                            $found = $tableName;
                            $foundIn = $key;
                            break;
                        }
                    }
                    if (!$found) {
                        throw new \Exception('field name ' . $possibleAny . ' not found in any tables names registred in query');
                    }
                    $info['type'] = 'field';
                    $info['alias'] = $this->aliasUsed[$foundIn];
                    $info['table'] = $found;
                    $info['field'] = $possibleAny;
                } else {
                    pre_clear_buffer_die('to doooooooooooooooooooooo');
                }
            }
        }
        return $info;
    }

    /**
     * return type of value, as date, string, float or int
     *
     * @param $tableName
     * @param $fieldName
     *
     * @return array
     * @throws \Exception
     *
     */
    protected function getTypeOfField($tableName, $fieldName)
    {
        if (!$tableName) {
            throw new \Exception('table name must be integer or string');
        }
        if (array_key_exists($tableName, $this->database->db_tables)) {
            if (array_key_exists($fieldName, $this->database->db_tables[$tableName]['fields'])) {
                $field = $this->database->db_tables[$tableName]['fields'][$fieldName];
                if (array_key_exists($field['type'], $this->database->normalTypes)) {
                    return [
                        'type' => $this->database->normalTypes[$field['type']],
                        'field' => $field['type']
                    ];
                } else {
                    return [
                        'type' => 's',
                        'field' => $field['type']
                    ];
                }
            } else {
                throw new \Exception('field ' . $fieldName . ' no exists in table ' . $tableName . ' on database ' . $this->database->dbname);
            }
        } else {
            throw new \Exception('table ' . $tableName . ' no exists in database ' . $this->database->dbname);
        }
    }

    /**
     * normalize value for register after in query
     *
     * @param Model | Eloquent | int | float | array | double | string $value
     * @param string                                                   $tableName
     * @param string                                                   $fieldName
     *
     * @return array
     * @throws \Exception
     *
     */
    protected function normalizeValue($value, $tableName, $fieldName)
    {
        $normalized = [
            'value' => null
        ];
        $fieldType = $this->getTypeOfField($tableName, $fieldName);
        $normalized = array_merge($normalized, $fieldType);

        if (is_object($value)) {
            if (in_array($fieldType['field'], ['date', 'time', 'year', 'timestamp', 'datetime', 'timestamptz'])) {
                if (get_class($value) !== 'Winged\Date\Date') {
                    switch ($fieldType['field']) {
                        case 'date':
                            if (Date::valid($value)) {
                                $value = new Date($value);
                            }
                            break;
                        case 'time':
                            if (Date::valid('1994-09-15 ' . $value)) {
                                $value = new Date('1994-09-15 ' . $value);
                            }
                            break;
                        case 'year':
                            if (strlen($value) === 4 && intval($value) > 0) {
                                $value = new Date($value . '-09-15');
                            }
                            break;
                        case 'timestamp':
                            if (Date::valid($value)) {
                                $value = new Date($value);
                            }
                            break;
                        case 'timestamptz':
                            if (Date::valid($value)) {
                                $value = new Date($value);
                            }
                            break;
                        case 'datetime':
                            if (Date::valid($value)) {
                                $value = new Date($value);
                            }
                            break;
                        default:
                            break;
                    }
                } else {
                    throw new \Exception('field type is: ' . $fieldType['field'] . ', but the value founded in clause is:' . get_class($value));
                }
            } else {
                if (is_subclass_of($value, 'Winged\Model\Model')
                    || is_subclass_of($value, 'Winged\Database\Drivers\Eloquent')
                    || get_class($value) === 'Winged\Model\Model'
                    || get_class($value) === 'Winged\Database\Drivers\Eloquent'
                ) {
                    $value = $value->build();
                    //@todo
                }
            }
        } else {
            if (!is_array($value)) {
                if ($fieldType['field'] === 'i') {
                    $value = intval($value);
                }
                if ($fieldType['field'] === 'd') {
                    $value = doubleval($value);
                }
                if ($fieldType['field'] === 's') {
                    $value = '' . $value . '';
                }
            }
        }
        $normalized['value'] = $value;
        return $normalized;
    }

    public function pushQueryInformation($left, $right)
    {
        if (is_array($right['value'])) {
            foreach ($right['value'] as $value) {
                $this->mysqliDataType[] = $right['type'];
                $this->pdoDataType[] = ':' . $left['field'] . uniqid('_');
                $this->queryValues[] = $value;
            }
        } else {
            $this->mysqliDataType[] = $right['type'];
            $this->pdoDataType[] = ':' . $left['field'] . uniqid('_');
            $this->queryValues[] = $right['value'];
        }
    }

    /**
     * @param string $propertyName
     *
     * @return $this
     * @throws \Exception
     *
     */
    public function parseFields($propertyName = '')
    {
        if (!property_exists($this, $propertyName)) {
            throw new \Exception('property ' . $propertyName . ' do not exists in $this object');
        }
        foreach ($this->{$propertyName} as $key => $property) {

            if (!array_key_exists($propertyName, $this->queryFields)) {
                $this->queryFields[$propertyName] = [];
                $this->queryFieldsAlias[$propertyName] = [];
            }

            if (is_array($property)) {
                if ($propertyName === 'where') {
                    $keys = array_keys($property['args']);
                    $information = $this->getInformation($keys[0]);
                    $theValue = $this->normalizeValue($property['args'][$keys[0]], $information['table'], $information['field']);
                    if (!is_string($keys[0])) {
                        throw new \Exception('error in where clause, check if sintax are correctly. first params is condition, and second is an associative array where key is an string and any value after');
                    }
                    if (($property['condition'] === ELOQUENT_IN || $property['condition'] === ELOQUENT_NOTIN) &&
                        !is_array($theValue)) {
                        throw new \Exception('error in where clause, in condition requires value has array');
                    }
                    if ($property['condition'] === ELOQUENT_BETWEEN &&
                        !is_array($theValue) &&
                        count7($theValue) != 2) {
                        throw new \Exception('error in where clause, between condition requires value has array and array count exactly equals two');
                    }
                    $info = [
                        'condition' => $property['condition'],
                        'original' => $property,
                        'left' => $information,
                        'right' => $theValue,
                        'type' => 'where',
                    ];
                    $this->queryTablesInfo[$propertyName][] = $info;
                }
                if ($propertyName === 'having') {
                    $keys = array_keys($property['args']);
                    $information = $this->getInformation($keys[0]);
                    $theValue = $this->normalizeValue($property['args'][$keys[0]], $information['table'], $information['field']);
                    if (!is_string($keys[0])) {
                        throw new \Exception('error in having clause, check if sintax are correctly. first params is condition, and second is an associative array where key is an string and any value after');
                    }
                    $info = [
                        'condition' => $property['condition'],
                        'original' => $property,
                        'left' => $information,
                        'right' => $theValue,
                        'type' => 'where',
                    ];
                    $this->queryTablesInfo[$propertyName][] = $info;
                }
                if ($propertyName === 'orderBy') {
                    $information = $this->getInformation($property[1]);
                    $info = [
                        'condition' => $property[0],
                        'original' => $property,
                        'left' => false,
                        'right' => $information,
                        'type' => 'orderBy',
                    ];
                    $this->queryTablesInfo[$propertyName][] = $info;
                }
            } else {
                if (is_string($key)) {
                    $realName = $this->getInformation($key);
                    $this->queryFieldsAlias[$propertyName][] = $property;
                    $this->queryFields[$propertyName][] = $realName['field'];
                    $this->queryTables[$propertyName][] = $realName['table'];
                    $this->queryTablesAlias[$propertyName][] = $realName['alias'];
                    if (in_array($propertyName, ['set', 'values'])) {
                        $this->pushQueryInformation($realName, $this->normalizeValue($property, $realName['table'], $realName['field']));
                    }
                } else {
                    $realName = $this->getInformation($property);
                    $this->queryFieldsAlias[$propertyName][] = false;
                    $this->queryTables[$propertyName][] = $realName['table'];
                    $this->queryTablesAlias[$propertyName][] = $realName['alias'];
                    $this->queryFields[$propertyName][] = $realName['field'];
                    if (in_array($propertyName, ['set', 'values'])) {
                        $this->pushQueryInformation($realName, $this->normalizeValue($property, $realName['table'], $realName['field']));
                    }
                }
            }
        }
        return $this;
    }

    /**
     * @param string $propertyName
     *
     * @return $this;
     * @throws \Exception
     *
     */
    public function parseTables($propertyName = '')
    {
        if (!property_exists($this, $propertyName)) {
            throw new \Exception('property ' . $propertyName . ' do not exists in $this object');
        }
        foreach ($this->{$propertyName} as $key => $property) {

            if (!array_key_exists($propertyName, $this->queryTables)) {
                $this->queryTables[$propertyName] = [];
                $this->queryTablesAlias[$propertyName] = [];
                $this->queryTablesInfo[$propertyName] = [];
            }
            $info = false;
            $alias = false;
            if (is_array($property)) {
                switch ($propertyName) {
                    case 'joins':
                        $condition = str_replace(' ', '', $property['condition']);
                        $keys = array_keys($property['args']);
                        if (is_string($keys[0])) {
                            $alias = $keys[0];
                        }
                        $tableName = $property['args'][$keys[0]];
                        $this->tablesUsed[] = $tableName;
                        $this->aliasUsed[] = $alias;
                        $this->queryTables[$propertyName][] = $tableName;
                        $this->queryTablesAlias[$propertyName][] = $alias;
                        if (!is_string($keys[1])) {
                            throw new \Exception('in join clause is required an string key with field or table.field or alias.field');
                        }
                        $leftInfo = $this->getInformation($keys[1]);
                        $rightInfo = $this->getInformation($property['args'][$keys[1]]);
                        $info = [
                            'condition' => $condition,
                            'original' => $property,
                            'left' => $leftInfo,
                            'right' => $rightInfo,
                            'type' => 'joins',
                        ];
                        $this->queryTablesInfo[$propertyName][] = $info;
                        break;
                    default:
                        break;
                }
            } else {
                if (is_string($key)) {
                    $alias = $key;
                    if (in_array($alias, $this->queryTablesAlias[$propertyName])) {
                        throw new \Exception('can\'t use an table alias twice. the duplicated alias is: ' . $alias);
                    }
                }
                $tableName = $property;
                if (!$this->tableExists($tableName)) {
                    throw new \Exception('table ' . $tableName . ' do not exists in database ' . $this->database->dbname);
                }

                if (in_array($tableName, $this->queryTables[$propertyName])) {
                    throw new \Exception('can\'t use name of table twice. the name of table is: ' . $tableName);
                }
                $this->tablesUsed[] = $tableName;
                $this->aliasUsed[] = $alias;
                $this->queryTables[$propertyName][] = $tableName;
                $this->queryTablesAlias[$propertyName][] = $alias;
                $this->queryTablesInfo[$propertyName][] = $info;
            }
        }
        return $this;
    }

    /**
     * get after clause relative to $current position in $this->queryTablesInfo with $propertyName
     * util to get information for parsing where and having clause for example
     *
     * @param int    $current
     * @param string $propertyName
     *
     * @return bool | array
     * @throws \Exception
     *
     */
    public function afterClause($current = 0, $propertyName = '')
    {
        if (!array_key_exists($propertyName, $this->queryTablesInfo)) {
            throw new \Exception('key ' . $propertyName . ' do not exists in $this->queryTablesInfo');
        }
        if (($current + 1) > count7($this->queryTablesInfo[$propertyName])) return false;
        if (($current + 1) === count7($this->queryTablesInfo[$propertyName])) return $this->queryTablesInfo[$propertyName][$current];
        return $this->queryTablesInfo[$propertyName][$current + 1];
    }

    /**
     * get before clause relative to $current position in $this->queryTablesInfo with $propertyName
     * util to get information for parsing where and having clause for example
     *
     * @param int    $current
     * @param string $propertyName
     *
     * @return bool | array
     * @throws \Exception
     *
     */
    public function beforeClause($current = 0, $propertyName = '')
    {
        if (!array_key_exists($propertyName, $this->queryTablesInfo)) {
            throw new \Exception('key ' . $propertyName . ' do not exists in $this->queryTablesInfo');
        }
        if ($current === 0) return $this->queryTablesInfo[$propertyName][$current];
        return $this->queryTablesInfo[$propertyName][$current - 1];
    }

    /**
     * same as beforeClause(), but this return only type of operation like OR, BEGIN or AND.
     *
     * @param int    $current
     * @param string $propertyName
     *
     * @return bool | string
     * @throws \Exception
     *
     */
    public function beforeClauseOperation($current = 0, $propertyName = '')
    {
        $before = $this->beforeClause($current, $propertyName);
        if ($before) {
            return $before['original']['type'];
        }
        return false;
    }

    /**
     * same as afterClause(), but this return only type of operation like OR, BEGIN or AND.
     *
     * @param int    $current
     * @param string $propertyName
     *
     * @return bool | string
     * @throws \Exception
     *
     */
    public function afterClauseOperation($current = 0, $propertyName = '')
    {
        $after = $this->afterClause($current, $propertyName);
        if ($after) {
            return $after['original']['type'];
        }
        return false;
    }

    /**
     * reset only prepared infos
     *
     * @return $this
     */
    public function reset()
    {
        $this->prepared = false;

        $this->queryAlias = false;
        $this->aliasUsed = [];
        $this->tablesUsed = [];
        $this->currentQueryString = '';
        $this->queryTables = [];
        $this->queryFields = [];
        $this->queryTablesAlias = [];
        $this->queryFieldsAlias = [];
        $this->queryTablesInfo = [];
        $this->queryFieldsInfo = [];
        $this->mysqliDataType = [];
        $this->pdoDataType = [];
        $this->queryValues = [];
        return $this;
    }

    /**
     * reset prepared infos and all registred commands in query
     *
     * @return $this
     */
    public function resetAll()
    {
        $this->builded = false;

        $this->reset();
        $this->select = [];
        $this->from = [];
        $this->joins = [];
        $this->having = [];
        $this->groupBy = [];
        $this->distinct = false;
        $this->orderBy = [];
        $this->limit = [];
        $this->alias = [];
        $this->into = [];
        $this->update = [];
        $this->insert = [];
        $this->delete = [];
        $this->set = [];
        $this->eloquents = [];
        $this->values = [];
        $this->where = [];
        $this->command = false;
        return $this;
    }

    /**
     * finally build query, return the final query and all values registred in eloquent
     *
     * @param bool $noReset
     *
     * @return $this|EloquentInterface|array
     * @throws \Exception
     *
     */
    public function build($noReset = false)
    {
        if (in_array(get_class($this), [
            'Winged\Database\Drivers\Cubrid',
            'Winged\Database\Drivers\MySQL',
            'Winged\Database\Drivers\PostgreSQL',
            'Winged\Database\Drivers\Sqlite',
            'Winged\Database\Drivers\SQLServer'
        ])) {
            /**
             * @var $this Cubrid|MySQL|PostgreSQL|Sqlite|SQLServer
             */
            $return = [];
            if (!$this->prepared || $noReset) {
                $this->prepare();
            }
            $this->prepared = false;
            //build prepared mysqli query type
            $return['mysqli_query'] = str_replace('%s', '?', $this->currentQueryString);
            $return['mysqli'] = [];
            if (!empty($this->mysqliDataType)) {
                $return['mysqli'] = array_merge([
                    join('', $this->mysqliDataType)
                ], $this->queryValues);
            }
            //build prepared pdo query type
            $return['pdo_query'] = call_user_func_array('sprintf', array_merge([$this->currentQueryString], $this->pdoDataType));
            $pdoParamsAndValues = [];
            foreach ($this->pdoDataType as $key => $field) {
                $pdoParamsAndValues[$field] = $this->queryValues[$key];
            }
            $return['pdo'] = $pdoParamsAndValues;
            //build normal query type
            $normalValues = [];
            foreach ($this->mysqliDataType as $key => $field) {
                if (in_array($field, ['s', 'b'])) {
                    $normalValues[] = '"' . $this->queryValues[$key] . '"';
                } else {
                    $normalValues[] = $this->queryValues[$key];
                }
            }
            $return['query'] = call_user_func_array('sprintf', array_merge([$this->currentQueryString], $normalValues));
            $return['command'] = $this->command;
            if (!$noReset) {
                $this->resetAll();
            } else {
                $this->reset();
            }
            $this->builded = $return;
        }
        return $this;
    }

    /**
     * after execute the final query, this object is entire reseted
     *
     * @return $this
     */
    public function unbuild()
    {
        $this->builded = false;
        return $this;
    }

    /**
     * @param bool $selectAsArray
     *
     * @return array|bool|false|int|mixed|string
     * @throws \Exception
     */
    public function execute($selectAsArray = false)
    {
        $returnedValue = false;
        if (!$this->builded) {
            try {
                $this->build();
            } catch (\Exception $exception) {
                return false;
            }
        }

        switch ($this->builded['command']) {
            case Eloquent::COMMAND_COUNT:
                if ($this->database->isPdo() && \WingedConfig::$config->db()->USE_PREPARED_STMT === USE_PREPARED_STMT) {
                    $returnedValue = $this->database->count($this->builded['pdo_query'], $this->builded['pdo']);
                } else if ($this->database->isMysqli() && \WingedConfig::$config->db()->USE_PREPARED_STMT === USE_PREPARED_STMT) {
                    $returnedValue = $this->database->count($this->builded['mysqli_query'], $this->builded['mysqli']);
                } else {
                    $returnedValue = $this->database->count($this->builded['query']);
                }
                if ($this->model) {
                    $this->model->primaryKey($returnedValue);
                }
                break;
            case Eloquent::COMMAND_INSERT:
                if ($this->database->isPdo() && \WingedConfig::$config->db()->USE_PREPARED_STMT === USE_PREPARED_STMT) {
                    $returnedValue = $this->database->insert($this->builded['pdo_query'], $this->builded['pdo']);
                } else if ($this->database->isMysqli() && \WingedConfig::$config->db()->USE_PREPARED_STMT === USE_PREPARED_STMT) {
                    $returnedValue = $this->database->insert($this->builded['mysqli_query'], $this->builded['mysqli']);
                } else {
                    $returnedValue = $this->database->insert($this->builded['query']);
                }
                if ($this->model) {
                    $this->model->primaryKey($returnedValue);
                }
                break;
            case Eloquent::COMMAND_DELETE:
                if ($this->database->isPdo() && \WingedConfig::$config->db()->USE_PREPARED_STMT === USE_PREPARED_STMT) {
                    $returnedValue = $this->database->execute($this->builded['pdo_query'], $this->builded['pdo']);
                } else if ($this->database->isMysqli() && \WingedConfig::$config->db()->USE_PREPARED_STMT === USE_PREPARED_STMT) {
                    $returnedValue = $this->database->execute($this->builded['mysqli_query'], $this->builded['mysqli']);
                } else {
                    $returnedValue = $this->database->execute($this->builded['query']);
                }
                break;
            case Eloquent::COMMAND_UPDATE:
                if ($this->database->isPdo() && \WingedConfig::$config->db()->USE_PREPARED_STMT === USE_PREPARED_STMT) {
                    $returnedValue = $this->database->execute($this->builded['pdo_query'], $this->builded['pdo']);
                } else if ($this->database->isMysqli() && \WingedConfig::$config->db()->USE_PREPARED_STMT === USE_PREPARED_STMT) {
                    $returnedValue = $this->database->execute($this->builded['mysqli_query'], $this->builded['mysqli']);
                } else {
                    $returnedValue = $this->database->execute($this->builded['query']);
                }
                break;
            case Eloquent::COMMAND_SELECT:
                if ($this->database->isPdo() && \WingedConfig::$config->db()->USE_PREPARED_STMT === USE_PREPARED_STMT) {
                    $returnedValue = $this->database->fetch($this->builded['pdo_query'], $this->builded['pdo']);
                } else if ($this->database->isMysqli() && \WingedConfig::$config->db()->USE_PREPARED_STMT === USE_PREPARED_STMT) {
                    $returnedValue = $this->database->fetch($this->builded['mysqli_query'], $this->builded['mysqli']);
                } else {
                    $returnedValue = $this->database->fetch($this->builded['query']);
                }
                if (!$selectAsArray && $this->model && !empty($returnedValue)) {
                    $models = [];
                    try {
                        $reflection = new \ReflectionClass(get_class($this->model));
                    } catch (\Exception $exception) {
                        $reflection = false;
                    }

                    foreach ($returnedValue as $result) {
                        if ($reflection) {
                            /**
                             * @var $model Model
                             */
                            $model = $reflection->newInstance();
                            foreach ($result as $property => $value) {
                                if (property_exists($model, $property)) {
                                    if(Date::valid($value)){
                                        $model->{$property} = new Date($value);
                                    }else{
                                        $model->{$property} = $value;
                                    }
                                } else {
                                    if(Date::valid($value)){
                                        $model->extras->{$property} = new Date($value);
                                    }else{
                                        $model->extras->{$property} = $value;
                                    }
                                }
                            }
                            $model->_reverse();
                            $models[] = $model;
                        }
                    }
                    $returnedValue = $models;
                }
                break;
            default:
                return false;
                break;
        }
        $this->unbuild();
        return $returnedValue;
    }



}