<?php

namespace Test;

use PHPUnit\Framework\TestCase;

class RowCountTest extends TestCase
{
    private $filePath;

    protected function setUp()
    {
        include "bootstrap.php";
        $this->filePath = $scriptPath;
    }

    /**
     * @dataProvider additionProvider
     */
    public function testRowCount($fileInput)
    {
        $fp = $this->filePath;
        $fileConf = __DIR__ . "/files/good1Conf.php";
        $fileOunput = __DIR__ . "/files/tmpOutput.csv";

        $exec = exec(
            "php " . $fp . " -i $fileInput -c $fileConf -o $fileOunput"
        );

        $file1 = file($fileInput);
        $file2 = file($fileOunput);

        $this->assertEquals(count($file1), count($file2));
    }

    public function additionProvider()
    {
        return [
            [__DIR__ . "/files/good1Input.csv"],
            [__DIR__ . "/files/good3Input.csv"],
        ];
    }
}