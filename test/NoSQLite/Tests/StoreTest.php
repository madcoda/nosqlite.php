<?php

/**
 * NoSQLite Store Test
 *
 * PHP Version 5
 *
 * @category NoSQLite
 * @package  NoSQLite
 * @author   Maciej Winnicki <maciej.winnicki@gmail.com>
 * @license  https://github.com/mthenw/NoSQLite-for-PHP The MIT License
 * @link     https://github.com/mthenw/NoSQLite-for-PHP
 */

namespace NoSQLite\Tests;

use PHPUnit_Framework_TestCase;
use NoSQLite\NoSQLite;
use NoSQLite\Store;

/**
 * Class StoreTest
 *
 * @category NoSQLite
 * @package  NoSQLite
 * @author   Maciej Winnicki <maciej.winnicki@gmail.com>
 * @license  https://github.com/mthenw/NoSQLite-for-PHP The MIT License
 * @link     https://github.com/mthenw/NoSQLite-for-PHP
 */
class StoreTest extends \PHPUnit_Framework_TestCase
{
    const DB_FILE = 'storeTest.db';

    /**
     * @var NoSQLite
     */
    protected $nsl;

    /**
     * @var Store
     */
    protected $store;

    /**
     * Setup test
     *
     * @return void
     */
    public function setUp()
    {
        $this->nsl = new NoSQLite(self::DB_FILE);
        $this->store = $this->nsl->getStore('test');
    }

    /**
     * Test first get
     *
     * @return void
     */
    public function testFirstGet()
    {
        $key = uniqid();
        $value = 'value';

        $this->store->set($key, $value);
        $this->setUp();
        $this->assertEquals($this->store->get($key), $value);
    }

    /**
     * Test getting all values
     *
     * @return void
     */
    public function testGetAll()
    {
        $data = array(
            '_1' => 'value1',
            '_2' => 'value2'
        );

        $this->store->deleteAll();

        foreach ($data as $key => $value) {
            $this->store->set($key, $value);
        }

        $this->assertEquals($data, $this->store->getAll());
    }

    /**
     * Test getting and setting value
     *
     * @param string $key   key
     * @param mixed $value value
     *
     * @dataProvider validData
     * @return void
     */
    public function testSetGetValue($key, $value)
    {
        $this->store->set($key, $value);
        $this->assertEquals($this->store->get($key), $value);
    }

    /**
     * Test updating earlier set value
     *
     * @return void
     */
    public function testUpdateValue()
    {
        $key = uniqid();
        $value1 = uniqid();
        $value2 = uniqid();
        $this->store->set($key, $value1);
        $this->store->set($key, $value2);
        $this->assertEquals($value2, $this->store->get($key));
    }

    /**
     * Test set method exception
     *
     * @param string $key   key
     * @param string $value value
     *
     * @dataProvider invalidSetData
     * @expectedException \InvalidArgumentException
     * @return void
     */
    public function testSetExceptions($key, $value)
    {
        $this->store->set($key, $value);
        $this->store->get($key);
    }

    /**
     * Test get method exception
     *
     * @param string $key key
     *
     * @dataProvider invalidGetData
     * @expectedException \InvalidArgumentException
     * @return void
     */
    public function testGetExceptions($key)
    {
        $this->store->get($key);
    }


    /**
     * Test getting and setting Int
     *
     * @param string $key   key
     * @param string $value value
     *
     * @dataProvider validIntData
     * @return void
     */
    public function testSetGetInt($key, $value){
        $this->store->setInt($key, $value);
        $this->assertEquals($this->store->get($key), $value);
    }

    /**
     * Test getting and setting Boolean
     *
     * @param string $key   key
     * @param string $value value
     *
     * @dataProvider validBooleanData
     * @return void
     */
    public function testSetGetBoolean($key, $value){
        $this->store->setBoolean($key, $value);
        $this->assertEquals($this->store->getBoolean($key), $value);
    }

    /**
     * Test getting and setting Date
     *
     * @param string $key   key
     * @param string $value value
     *
     * @dataProvider validDateData
     * @return void
     */
    public function testSetGetDate($key, $value){
        $this->store->setDate($key, $value);
        $this->assertEquals($this->store->get($key), $value);
    }

    /**
     * 
     * @return void
     */
    public function testIncrement(){
        $times = rand(2, 10); //use randome number to prevent system error
        $total = 0;
        for($i=0;$i<$times;$i++){
            $amount = rand(1, 10);
            $total += $amount;
            $this->store->increment('count');
            $this->store->increment('total', $amount);
        }
        $this->assertEquals($this->store->get('count'), $times);
        $this->assertEquals($this->store->get('total'), $total);
    }


    /**
     * Test delete value
     *
     * @return void
     */
    public function testDelete()
    {
        $key = uniqid();
        $this->store->set($key, 'value');
        $this->store->delete($key);
        $this->assertEquals(null, $this->store->get($key));
    }

    /**
     * Test all values
     *
     * @return void
     */
    public function testDeleteAll()
    {
        $this->store->set(uniqid(), 'value');
        $this->store->deleteAll();
        $this->assertEquals(array(), $this->store->getAll());
    }

    /**
     * Test Countable interface
     *
     * @return void
     */
    public function testCount()
    {
        $count = rand(1, 100);
        for ($i = 0; $i < $count; $i++) {
            $this->store->set(uniqid(), uniqid());
        }
        $this->assertEquals($count, count($this->store));
    }

    /**
     * Test Iterator interface
     *
     * @return void
     */
    public function testIteration()
    {
        $this->store->set('key1', 'value1');

        foreach ($this->store as $key => $value) {
            $this->assertSame($key, 'key1');
            $this->assertSame($value, 'value1');
        }
    }

    /**
     * Data provider - valid data
     *
     * @static
     * @return array
     */
    public static function validData()
    {
        return array(
            array('key', 'value'),
            array('0', 'value'),
            array('is_first', true),
            array('number', PHP_INT_MAX),
            array('price', 123.45),
            array('price2', 123.451231237987623748212391293801283901823908102983098190390890),
            array('array', json_encode(array(1,2,3))),
        );
    }

    /**
     * Data provider - valid int
     *
     * @static
     * @return array
     */
    public static function validIntData()
    {
        return array(
            array('key', 100),
            array('0', 0),
            array('abc', PHP_INT_MAX),
        );
    }

    /**
     * Data provider - valid data
     *
     * @static
     * @return array
     */
    public static function validFloatData()
    {
        return array(
            array('price', 123.45),
            array('0', 123.45),
            array('price2', 123.451231237987623748212391293801283901823908102983098190390890),
        );
    }

    /**
     * Data provider - valid data
     *
     * @static
     * @return array
     */
    public static function validBooleanData()
    {
        return array(
            array('flag1', true),
            array('flag2', false),
            array('0', true),
        );
    }

    /**
     * Data provider - valid data
     *
     * @static
     * @return array
     */
    public static function validDateData()
    {
        return array(
            array('last_visit', '2012-06-12 15:00:03'),
            array('last_login', '2012-06-30'),
            array('created_date', '1970-01-01'),
        );
    }

    /**
     * Data provider - invalid set data
     *
     * @static
     * @return array
     */
    public static function invalidSetData()
    {
        return array(
            array(0, 'value'),
            array('array', array())
        );
    }

    /**
     * Data provider - invalid get data
     *
     * @static
     * @return array
     */
    public static function invalidGetData()
    {
        return array(
            array(10),
        );
    }

    /**
     * Tear down
     *
     * @return void
     */
    public function tearDown()
    {
        unset($this->nsl);
        unset($this->store);
        unlink(self::DB_FILE);
    }
}
