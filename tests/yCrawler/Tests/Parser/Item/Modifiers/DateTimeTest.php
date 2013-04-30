<?php
namespace yCrawler\Tests\Parse\Item\Modifiers;
use yCrawler\Parser\Item\Modifiers\DateTime;

class DateTimeTest extends  \PHPUnit_Framework_TestCase { 
    function testStrToTime() {
        $closure = DateTime::strtotime();
        $result = array(
            array('value' => '12 December 2010 10:00')
        );

        $closure($result);
        $this->assertSame('2010-12-12 10:00:00', date('Y-m-d H:i:s', $result[0]['value']));
    }

    function testDiff() {
        $closure = DateTime::diff();
        $result = array(
            array('value' => 100000)
        );

        $closure($result);

        $time = time();
        $this->assertSame($time + 100000, $result[0]['value']);
    }

    function testDiffMilliseconds() {
        $closure = DateTime::diff(true);
        $result = array(
            array('value' => 100000)
        );

        $closure($result);

        $time = time();
        $this->assertSame($time + 100, $result[0]['value']);
    }
}