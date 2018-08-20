<?php

namespace Test;

use PHPUnit\Framework\TestCase;

class EncodingTest extends TestCase
{
    private $filePath;

    protected function setUp()
    {
        include __DIR__ . "/bootstrap.php";
        $this->filePath = $scriptPath;
    }

    /**
     *
     * @dataProvider additionProvider
     */
    public function testEncoding($fileInput)
    {
        $fp = $this->filePath;
        $fileConf = __DIR__ . "/testingFiles/good/good1Conf.php";
        $fileOunput = __DIR__ . "/testingFiles/good/tmpOutput.csv";

        $exec = exec(
            "php " . $fp . " -i $fileInput -c $fileConf -o $fileOunput",
            $output,
            $return_var
        );

        $fileContent1 = file_get_contents($fileInput);
        $fileEn1 = mb_check_encoding($fileContent1, 'UTF-8') ? 'UTF-8' : 'Windows-1251';

        $fileContent2 = file_get_contents($fileOunput);
        $fileEn2 = mb_check_encoding($fileContent2, 'UTF-8') ? 'UTF-8' : 'Windows-1251';

        $this->assertEquals($fileEn1, $fileEn2);
    }

    public function additionProvider()
    {
        return [
            [__DIR__ . "/testingFiles/encode/encodUtf8.csv"],
            [__DIR__ . "/testingFiles/encode/encodW1251.csv"],
        ];
    }
}
