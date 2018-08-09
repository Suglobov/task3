<?php

namespace Test;

use PHPUnit\Framework\TestCase;

class EndOfLineTest extends TestCase
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
    public function testEndOfLine($fileInput)
    {
        $fp = $this->filePath;
        $fileConf = __DIR__ . "/files/good1Conf.php";
        $fileOunput = __DIR__ . "/files/tmpOutput.csv";

        $exec = exec(
            "php " . $fp . " -i $fileInput -c $fileConf -o $fileOunput"
        );

        $fgc1 = file_get_contents($fileInput);
        $eol1 = $this->findEOL($fgc1);

        $fgc2 = file_get_contents($fileOunput);
        $eol2 = $this->findEOL($fgc2);

        $this->assertEquals($eol1, $eol2);
    }

    public function additionProvider()
    {
        return [
            [__DIR__ . "/files/eolInputCRNL.csv"],
//            [__DIR__."/files/eolInputCR.csv"],
            [__DIR__ . "/files/eolInputNL.csv"],
        ];
    }

    private function findEOL($line, $view = 0)
    {
        if (strpos($line, "\r\n") !== false) {
            return $view ? '\r\n' : "\r\n";
        } elseif (strpos($line, "\n") !== false) {
            return $view ? '\n' : "\n";
        } elseif (strpos($line, "\r") !== false) {
            return $view ? '\r' : "\r";
        } else {
            return "?";
        }
    }
}