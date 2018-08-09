<?php

use PHPUnit\Framework\TestCase;

class InputParametersTest extends TestCase
{
    private $filePath;

    protected function setUp()
    {
        $this->filePath = __DIR__ . '/../action.php';
    }

    /**
     * @dataProvider additionProvider
     */
    public function testEncoding($encod, $fileInput)
    {
        $fp = $this->filePath;
        $fileConf = "files/good1Conf.php";
        $fileOunput = "files/tmpOutput.csv";

        $exec = exec(
            "php " . $fp . " -i $fileInput -c $fileConf -o $fileOunput",
            $output,
            $return_var
        );

//        echo '$return_var: ' . $return_var . PHP_EOL;
//        echo "php " . $fp . " -i$fileInput -c$fileConf -o$fileOunput" . PHP_EOL;

        $fileContent1 = file_get_contents($fileInput);
        $fileEn1 = mb_check_encoding($fileContent1, 'UTF-8') ? 'UTF-8' : 'Windows-1251';
//        echo PHP_EOL;
//        echo "1:" . (mb_check_encoding($fileContent1, 'UTF-8') ? '1' : '2') . PHP_EOL;
//        echo "2:" . (mb_check_encoding($fileContent1, 'Windows-1251') ? '1' : '2') . PHP_EOL;

        $fileContent2 = file_get_contents($fileOunput);
        $fileEn2 = mb_check_encoding($fileContent2, 'UTF-8') ? 'UTF-8' : 'Windows-1251';

        $this->assertEquals($fileEn1, $fileEn2);
    }

    public function additionProvider()
    {
        return [
            ['UTF-8', "files/encodUtf8.csv"],
            ['Windows-1251', "files/encodW1251.csv"],
        ];
    }
}