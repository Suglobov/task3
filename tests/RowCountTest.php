<?php

namespace Test;

use PHPUnit\Framework\TestCase;

class RowCountTest extends TestCase
{
    private $filePath;

    protected function setUp()
    {
        include __DIR__ . "/bootstrap.php";
        $this->filePath = $scriptPath;
    }

    /**
     * @dataProvider additionProvider
     */
    public function testRowCount($fileInput)
    {
        $fp = $this->filePath;
        $fileConf = __DIR__ . "/testingFiles/good/good1Conf.php";
        $fileOunput = __DIR__ . "/testingFiles/tmpOutput.csv";

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
            [__DIR__ . "/testingFiles/good/good3Input.csv"],
            [__DIR__ . "/testingFiles/good/good1Input.csv"],
        ];
    }
}
