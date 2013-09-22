<?php

/**
 * Store
 *
 * PHP Version 5
 *
 * @category NoSQLite
 * @package  NoSQLite
 * @author   Maciej Winnicki <maciej.winnicki@gmail.com>
 * @license  https://github.com/mthenw/nosqlite.php The MIT License
 * @link     https://github.com/mthenw/nosqlite.php
 */

namespace NoSQLite;
use PDO;
use PDOStatement;

/**
 * Class Store
 *
 * @category NoSQLite
 * @package  NoSQLite
 * @author   Maciej Winnicki <maciej.winnicki@gmail.com>
 * @license  https://github.com/mthenw/nosqlite.php The MIT License
 * @link     https://github.com/mthenw/nosqlite.php
 */
class Store implements \Iterator, \Countable
{
    /**
     * PDO instance
     * @var PDO
     */
    protected $db = null;

    /**
     * Store name
     * @var string
     */
    protected $name = null;

    /**
     * Key column name
     * @var string
     */
    protected $keyColumnName = 'key';

    /**
     * Value column name
     * @var string
     */
    protected $valueColumnName = 'value';

    /**
     * Values stored
     * @var array
     */
    protected $data = array();

    /**
     * Data were loaded from DB
     * @var bool
     */
    protected $loaded = false;

    /**
     * Store iterator statement
     * @var PDOStatement
     */
    protected $iterator;

    /**
     * Current value during iteration
     * @var array
     */
    protected $current = null;

    /**
     * Create store
     *
     * @param PDO    $db   PDO database instance
     * @param string $name store name
     *
     * @return void
     */
    public function __construct($db, $name)
    {
        $this->db = $db;
        $this->name = $name;
        $this->createTable();
    }

    /**
     * Create storage table in database if not exists
     *
     * @return void
     */
    protected function createTable()
    {
        $stmt = 'CREATE TABLE IF NOT EXISTS "' . $this->name;
        $stmt.= '" ("' . $this->keyColumnName . '" TEXT PRIMARY KEY, "';
        $stmt.= $this->valueColumnName . '" TEXT);';
        $this->db->exec($stmt);
    }

    /**
     * Get value for specified key
     *
     * @param string $key key
     *
     * @throws \InvalidArgumentException
     * @return string|null
     */
    public function get($key)
    {
        if (!is_string($key)) {
            throw new \InvalidArgumentException('Expected string as key');
        }

        if (isset($this->data[$key])) {
            return $this->data[$key];
        } else if (!$this->loaded) {
            $stmt = $this->db->prepare(
                'SELECT * FROM ' . $this->name . ' WHERE ' . $this->keyColumnName
                . ' = :key;'
            );
            $stmt->bindParam(':key', $key, \PDO::PARAM_STR);
            $stmt->execute();

            if ($row = $stmt->fetch(\PDO::FETCH_NUM)) {
                $this->data[$row[0]] = $row[1];
                return $this->data[$key];
            }
        }

        return null;
    }

    /**
     * Get all values as array with key => value structure
     *
     * @return array
     */
    public function getAll()
    {
        if (!$this->loaded) {
            $stmt = $this->db->prepare('SELECT * FROM ' . $this->name);
            $stmt->execute();

            while ($row = $stmt->fetch(\PDO::FETCH_NUM, \PDO::FETCH_ORI_NEXT)) {
                $this->data[$row[0]] = $row[1];
            }
        }

        return $this->data;
    }

    /**
     * Set value on specified key
     *
     * @param string $key   key
     * @param string $value value
     *
     * @return string value stored
     * @throws \InvalidArgumentException
     */
    public function set($key, $value)
    {
        if(is_array($value) || is_object($value)){
            throw new \InvalidArgumentException('Object and Array value is not allowed');
        }
        return $this->_set($key, (string) $value);
    }


    public function setString($key, $value){
        if (!is_string($value)) {
            throw new \InvalidArgumentException('Expected string as value');
        }

        return $this->_set($key, $value);
    }

    public function setInt($key, $value){
        if (!is_int($value)) {
            throw new \InvalidArgumentException('Expected integer as value');
        }
        return $this->_set($key, (string) $value);
    }

    public function setFloat($key, $value){
        if (!is_float($value)) {
            throw new \InvalidArgumentException('Expected float as value');
        }
        //return $this->_set($key, sprintf('%f', $value));
        return $this->_set($key, (string) $value);
    }

    public function setDouble($key, $value){
        return $this->setDouble();
    }

    public function setBoolean($key, $value){
        if (!is_bool($value)) {
            throw new \InvalidArgumentException('Expected float as value');
        }
        return $this->setInt($key, ($value)?1:0);
    }

    public function getBoolean($key){
        return (bool) $this->get($key);
    }

    public function setDate($key, $value){
        $ts = strtotime($value);
        return $this->_set($key, date('Y-m-d H:i:s', $ts));
    }

    /**
     * Increment an integer
     * @param  [type] $key [description]
     * @return [type]      [description]
     */
    public function increment($key, $amount=1){
        if (!is_int($amount)) {
            throw new \InvalidArgumentException('Expected integer as amount');
        }
        $val = $this->get($key);
        if(!empty($val) && is_numeric($val)){
            $val = intval($val, 10);
            $this->setInt($key, ($val+$amount));
        }else{
            $this->setInt($key, $amount);
        }
    }


    private function _set($key, $value)
    {
        if (!is_string($key)) {
            throw new \InvalidArgumentException('Expected string as key');
        }

        if (isset($this->data[$key])) {
            $queryString ='UPDATE ' . $this->name . ' SET ';
            $queryString.= $this->valueColumnName . ' = :value WHERE ';
            $queryString.= $this->keyColumnName . ' = :key;';
        } else {
            $queryString = 'INSERT INTO ' . $this->name . ' VALUES (:key, :value);';
        }

        $stmt = $this->db->prepare($queryString);
        $stmt->bindParam(':key', $key, \PDO::PARAM_STR);
        $stmt->bindParam(':value', $value, \PDO::PARAM_STR);
        $stmt->execute();
        $this->data[(string)$key] = $value;

        return $this->data[$key];
    }


    /**
     * Delete value from store
     *
     * @param string $key key
     *
     * @return void
     */
    public function delete($key)
    {
        $stmt = $this->db->prepare(
            'DELETE FROM ' . $this->name . ' WHERE ' . $this->keyColumnName
            . ' = :key;'
        );
        $stmt->bindParam(':key', $key, \PDO::PARAM_STR);
        $stmt->execute();

        unset($this->data[$key]);
    }

    /**
     * Delete all values from store
     *
     * @return void
     */
    public function deleteAll()
    {
        $stmt = $this->db->prepare('DELETE FROM ' . $this->name);
        $stmt->execute();
        $this->data = array();
    }

    /**
     * Rewind the store to the first value
     *
     * @return void
     */
    public function rewind()
    {
        $this->iterator = $this->db->query('SELECT * FROM ' . $this->name);
        $this->current = $this->iterator->fetch(\PDO::FETCH_NUM, \PDO::FETCH_ORI_NEXT);
    }

    /**
     * Move forward to next value
     *
     * @return void
     */
    public function next()
    {
        $this->current = $this->iterator->fetch(\PDO::FETCH_NUM, \PDO::FETCH_ORI_NEXT);
    }

    /**
     * Check if current position is valid
     *
     * @return bool
     */
    public function valid()
    {
        return $this->current !== false;
    }

    /**
     * Return the current value
     *
     * @return string|null
     */
    public function current()
    {
        return isset($this->current[1]) ? $this->current[1] : null;
    }

    /**
     * Return the key of the current value
     *
     * @return string|null
     */
    public function key()
    {
        return isset($this->current[0]) ? $this->current[0] : null;
    }

    /**
     * Get number of values in store
     *
     * @return int
     */
    public function count()
    {
        return (int) $this->db->query('SELECT COUNT(*) FROM ' . $this->name)->fetchColumn();
    }
}
