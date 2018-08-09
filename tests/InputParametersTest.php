<?php

use PHPUnit\Framework\TestCase;

class InputParametersTest extends TestCase
{
    private $filePath;

    protected function setUp()
    {
        $this->filePath = __DIR__ . "/../action.php";
    }

    /**
     * @dataProvider additionProvider
     */
    public function testParam($expected, $arrayParams)
    {
        $fp = $this->filePath;
        $output = [];
        $return_var = "";
        $exec = exec(
            "php " . $fp . " " . implode(" ", $arrayParams),
            $output,
            $return_var
        );
        $this->assertEquals($expected, $return_var == 0);
    }

    public function additionProvider()
    {
        $fileGoodI = "files/good1Input.csv";
        $fileGoodC = "files/good1Conf.php";
        $fileGoodO = "files/tmpOutput.csv";
//        $fileBadI = "files/bad1Input.csv";
//        $fileBadC = "files/bad1Conf.php";
//        $fileBadO = "files/bad1Output.csv";

        return [
            [false, []],
            [false, ["-i"]],
            [false, ["-c"]],
            [false, ["-o"]],
            [false, ["--input"]],
            [false, ["--config"]],
            [false, ["--output"]],
            [false, ["-i fff", "-c zzz"]],
            [false, ["-c rrr", "-o ggg"]],
            [false, ["-i sss", "-o ggg"]],
            [false, ["-i fff", "-o sss", "-c eee"]],
            [false, ["-i $fileGoodI", "-c $fileGoodC", "--strict"]],
            [true, ["-i $fileGoodI", "-c $fileGoodC", "-o $fileGoodO", "--strict"]],
            [true, ["-i $fileGoodI", "-c $fileGoodC", "-o $fileGoodO"]],
            [true,
                ["-i $fileGoodI", "-c $fileGoodC", "-o $fileGoodO", "--skip-first"]
            ],
            [true,
                ["-i $fileGoodI", "-c $fileGoodC", "-o $fileGoodO", "-d ,"]
            ],
            [true,
                ["-i $fileGoodI", "-c $fileGoodC", "--strict"]
            ],
            [true, ["-h"]],
            [true, ["--help"]],
        ];
    }
}