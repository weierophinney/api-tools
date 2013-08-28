<?php

namespace ZFTest\ApiFirst\Model;

use PHPUnit_Framework_TestCase as TestCase;
use ZF\ApiFirst\Model\MongoConnectedListener;

class MongoConnectedListenerTest extends TestCase
{
    static protected $mongoDb;

    static protected $lastId;

    public function setUp()
    {
        if (!extension_loaded('mongo')) {
            $this->markTestSkipped(
                'The MongoDB extension is not available.'
            );
        }

        $m  = new \MongoClient(); 
        static::$mongoDb = $m->selectDB("test_zf_apifirst_mongoconnected");
        $collection = new \MongoCollection(static::$mongoDb, 'test');

        $this->mongoListener = new MongoConnectedListener($collection);
    }

    static public function tearDownAfterClass()
    {
        static::$mongoDb->drop();
    }

    public function testCreate()
    {
        $data = array( 'foo' => 'bar' );
        $result = $this->mongoListener->create($data);
        $this->assertTrue(isset($result['_id']));
        static::$lastId = $result['_id'];
    }

    public function testPatch()
    {
        if (empty(static::$lastId)) {
            $this->markTestIncomplete(
                'This test cannot be executed.'
            );
        }
        $data = array ( 'foo' => 'baz' );
        $this->assertTrue($this->mongoListener->patch(static::$lastId, $data));
    }

    public function testFetch()
    {
        if (empty(static::$lastId)) {
            $this->markTestIncomplete(
                'This test cannot be executed.'
            );
        }
        $result = $this->mongoListener->fetch(static::$lastId);
        $this->assertTrue(!empty($result));
        $this->assertEquals(static::$lastId, $result['_id']); 
    }

    public function testFetchAll()
    {
        $num = 3;
        for ($i=0; $i < $num; $i++) {
            $this->mongoListener->create(array(
                'foo'   => 'bau',
                'count' => $i
            ));
        }
        $data = array( 'foo' => 'bau' );
        $result = $this->mongoListener->fetchAll($data);
        $this->assertTrue(!empty($result));
        $this->assertTrue(is_array($result));
        $this->assertEquals($num, count($result));
    }

    public function testDelete()
    {
        if (empty(static::$lastId)) {
            $this->markTestIncomplete(
                'This test cannot be executed.'
            );
        }
        $result = $this->mongoListener->delete(self::$lastId);
        $this->assertTrue($result);
    }
    
}
