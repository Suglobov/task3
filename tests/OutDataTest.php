<?php

namespace Test;

use PHPUnit\Framework\TestCase;

class OutDataTest extends TestCase
{
    private $filePath;

    protected function setUp()
    {
        include "bootstrap.php";
        $this->filePath = $scriptPath;
    }

    public function testOutData()
    {
        $fp = $this->filePath;
        $fileInput = __DIR__ . "/files/good1Input.csv";
        $fileConf = __DIR__ . "/files/good1Conf.php";
        $fileOunput = __DIR__ . "/files/tmpOutput.csv";

        $exec = exec(
            "php " . $fp . " -i $fileInput -c $fileConf -o $fileOunput",
            $output,
            $return_var
        );

        $handleInput = fopen($fileInput, "rb");
        $handleOutput = fopen($fileOunput, "rb");

        $this->assertEquals(true, $return_var == 0);

        if ($handleInput && $handleOutput) {
            try {
                $row = 0;
                while (($dataInput = fgetcsv($handleInput, 0, ',')) !== false
                    && ($dataOutput = fgetcsv($handleOutput, 0, ',')) !== false) {
                    switch ($row) {
                        case 0:
//                            echo PHP_EOL;
//                            echo print_r($dataOutput, 1) . PHP_EOL;
//                            echo print_r(gettype($dataOutput[2]), 1) . PHP_EOL;
                            $this->assertEquals(is_string($dataInput[0]), true);
                            $this->assertEquals(is_string($dataInput[1]), true);
                            $this->assertEquals(is_string($dataInput[2]), true);
                            $this->assertEquals(is_string($dataInput[3]), true);
                            $this->assertEquals(is_string($dataInput[4]), true);
                            $this->assertEquals(is_string($dataInput[5]), true);
                            $this->assertEquals(is_string($dataInput[6]), true);

                            $this->assertEquals(is_string($dataOutput[0]), true);
                            $this->assertEquals(is_string($dataOutput[1]), true);
                            $this->assertEquals($dataOutput[2] == '', true);
                            $this->assertEquals(is_string($dataOutput[3]), true);
                            $this->assertEquals(is_numeric($dataOutput[4]), true);
                            $this->assertEquals(is_string($dataOutput[5]), true);
                            $this->assertEquals(is_string($dataOutput[6]), true);
                            break;
                        default:
                            $this->assertEquals(is_numeric($dataInput[0]), true);
                            $this->assertEquals(is_string($dataInput[1]), true);
                            $this->assertEquals(is_string($dataInput[2]), true);
                            $this->assertEquals(is_numeric($dataInput[3]), true);
                            $this->assertEquals(is_string($dataInput[4]), true);
                            $this->assertEquals($dataInput[5] == '', true);
                            $this->assertEquals($dataInput[6] == '', true);

                            $this->assertEquals(is_string($dataOutput[0]), true);
                            $this->assertEquals(is_string($dataOutput[1]), true);
                            $this->assertEquals($dataOutput[2] == '', true);
                            $this->assertEquals(is_numeric($dataOutput[3]), true);
                            $this->assertEquals(is_numeric($dataOutput[4]), true);
                            $this->assertEquals($dataOutput[5] == '', true);
                            $this->assertEquals($dataOutput[6] == '', true);
                    }
                    $row++;
                }
            } finally {
                fclose($handleInput);
                fclose($handleOutput);
            }
        } else {
            $this->assertEquals(true, false);
        }
    }
}