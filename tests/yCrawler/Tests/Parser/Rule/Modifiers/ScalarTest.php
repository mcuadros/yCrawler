<?php

namespace yCrawler\Tests\Parse\Rule\Modifiers;

use yCrawler\Parser\Rule\Modifiers\Scalar;

class ScalarTest extends  \PHPUnit_Framework_TestCase
{
    public function testBooleanPositive()
    {
        $closure = Scalar::boolean(Scalar::BOOLEAN_POSITIVE);
        $result = array(
            array('value' => false),
            array('value' => null),
            array('value' => ''),
            array('value' => array()),
            array('value' => 1),
            array('value' => 'string'),
            array('value' => array('array'))
        );

        $closure($result);
        $this->assertFalse($result[0]);
        $this->assertFalse($result[1]);
        $this->assertFalse($result[2]);
        $this->assertFalse($result[3]);
        $this->assertTrue($result[4]);
        $this->assertTrue($result[5]);
        $this->assertTrue($result[6]);
    }

    public function testBooleanNegative()
    {
        $closure = Scalar::boolean(Scalar::BOOLEAN_NEGATIVE);
        $result = array(
            array('value' => false),
            array('value' => null),
            array('value' => ''),
            array('value' => array()),
            array('value' => 1),
            array('value' => 'string'),
            array('value' => array('array'))
        );

        $closure($result);
        $this->assertTrue($result[0]);
        $this->assertTrue($result[1]);
        $this->assertTrue($result[2]);
        $this->assertTrue($result[3]);
        $this->assertFalse($result[4]);
        $this->assertFalse($result[5]);
        $this->assertFalse($result[6]);
    }

    public function testIntDefaultRegExp()
    {
        $closure = Scalar::int();
        $result = array(
            array('value' => false),
            array('value' => null),
            array('value' => ''),
            array('value' => array()),
            array('value' => 1),
            array('value' => 'string'),
            array('value' => '121 string'),
            array('value' => '121,12'),
            array('value' => '121.12'),
            array('value' => array('array', 'asas'))
        );

        $closure($result);
        $this->assertSame(0, $result[0]['value']);
        $this->assertSame(0, $result[1]['value']);
        $this->assertSame(0, $result[2]['value']);
        $this->assertSame(0, $result[3]['value']);
        $this->assertSame(1, $result[4]['value']);
        $this->assertSame(0, $result[5]['value']);
        $this->assertSame(121, $result[6]['value']);
        $this->assertSame(121, $result[7]['value']);
        $this->assertSame(121, $result[8]['value']);
        $this->assertSame(1, $result[9]['value']);
    }

    public function testIntCustomRegExp()
    {
        $closure = Scalar::int('/[^0-9]/');
        $result = array(
            array('value' => '122 string'),
            array('value' => '121,12'),
        );

        $closure($result);
        $this->assertSame(122, $result[0]['value']);
        $this->assertSame(12112, $result[1]['value']);
    }

    public function testFloatDefaultRegExp()
    {
        $closure = Scalar::float();
        $result = array(
            array('value' => false),
            array('value' => null),
            array('value' => ''),
            array('value' => array()),
            array('value' => 1),
            array('value' => 'string'),
            array('value' => '121 string'),
            array('value' => '121,12'),
            array('value' => '121.12'),
            array('value' => array('array', 'asas'))
        );

        $closure($result);
        $this->assertSame(0.0, $result[0]['value']);
        $this->assertSame(0.0, $result[1]['value']);
        $this->assertSame(0.0, $result[2]['value']);
        $this->assertSame(0.0, $result[3]['value']);
        $this->assertSame(1.0, $result[4]['value']);
        $this->assertSame(0.0, $result[5]['value']);
        $this->assertSame(121.0, $result[6]['value']);
        $this->assertSame(121.12, $result[7]['value']);
        $this->assertSame(121.12, $result[8]['value']);
        $this->assertSame(1.0, $result[9]['value']);
    }

    public function testFloatCustomRegExp()
    {
        $closure = Scalar::float('/[^0-9]/');
        $result = array(
            array('value' => '122 string'),
            array('value' => '121,12'),
        );

        $closure($result);
        $this->assertSame(122.0, $result[0]['value']);
        $this->assertSame(12112.0, $result[1]['value']);
    }

    public function testJoin()
    {
        $closure = Scalar::join();
        $result = array(
            array('value' => 'A'),
            array('value' => 'B'),
        );

        $closure($result);
        $this->assertSame('AB', $result[0]['value']);
    }
}
