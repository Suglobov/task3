<?php

//require "../action.php";

use PHPUnit\Framework\TestCase;

class InputParametrsTest extends TestCase
{
    private $filePath;

    protected function setUp()
    {
        $this->filePath = __DIR__ . '/../action.php';
    }

//    protected function tearDown()
//    {
//        $this->calculator = NULL;
//    }

    public function testWrongParam()
    {
        print_r(exec($this->filePath . " -i"));
//        $this->assertEquals("Ошибка: `getopt` сработал с ошибкой", exec($this->filePath . " -i"));
//        $result = $this->calculator->add(1, 2);
//        $this->assertEquals(3, $result);
    }

}