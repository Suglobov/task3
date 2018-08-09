<?php

namespace Test;

use PHPUnit\Framework\TestCase;

class InputParametersTest extends TestCase
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
        $fileGoodI = __DIR__ . "/files/good1Input.csv";
        $fileGoodC = __DIR__ . "/files/good1Conf.php";

        $fileO = __DIR__ . "/files/tmpOutput.csv";

        $fileGoodI2 = __DIR__ . "/files/good2Input.csv";

        $fileBadI = __DIR__ . "/files/bad1Input.csv";
        $fileBadC = __DIR__ . "/files/bad1Conf.php";

        return [
            // нет обязательных параметров
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
            // входные параметры - массивы
            [false, ["-i $fileGoodI", "-i $fileGoodI", "-c $fileGoodC", "-o $fileO"]],
            [false, ["-i $fileGoodI", "-c $fileGoodC", "-c $fileGoodC", "-o $fileO"]],
            [false, ["-i $fileGoodI", "-c $fileGoodC", "-o $fileO", "-o $fileO"]],
            [false, ["-i $fileGoodI", "-c $fileGoodC", "-o $fileO", "--strict", "--strict"]],
            [false, ["-i $fileGoodI", "-c $fileGoodC", "-o $fileO", "--skip-first", "--skip-first"]],
            [false, ["-i $fileGoodI", "-c $fileGoodC", "-o $fileO", '-d ","', '-d ","']],
            [false, ["-h", "-h"]],
            [false, ["--help", "--help"]],
            // неправильный делиметр
            [false, ["-i $fileGoodI", "-c $fileGoodC", "-o $fileO", '-d "asdf"']],
            // дублирование параметров
            [false, ["-h", "--help"]],
            [false, ["-i $fileGoodI", "--input $fileGoodI", "-c $fileGoodC", "-o $fileO"]],
            [false, ["-i $fileGoodI", "--config $fileGoodC", "-c $fileGoodC", "-o $fileO"]],
            [false, ["-i $fileGoodI", "--output $fileO", "-c $fileGoodC", "-o $fileO"]],
            // одинаковые значения обязательных параметров
            [false, ["-i $fileGoodI", "-c $fileGoodI", "-o $fileO"]],
            [false, ["-i $fileGoodI", "-c $fileO", "-o $fileO"]],
            [false, ["-i $fileGoodC", "-c $fileGoodC", "-o $fileO"]],
            [false, ["-i $fileO", "-c $fileGoodC", "-o $fileO"]],
            // неправильный конфиг
            [false, ["-i $fileGoodC", "-c $fileGoodI", "-o $fileO"]],
            // стрикт и плохой конфиг
            [false, ["-i $fileGoodI", "-c $fileBadC", "-o $fileO", "--strict"]],
            // плохие входные данные
            [false, ["-i $fileBadI", "-c $fileGoodC", "-o $fileO"]],
            // правильный делиметр и файл с ним
            [true, ["-i $fileGoodI", "-c $fileGoodC", "-o $fileO", '-d ","']],
            [true, ["-i $fileGoodI2", "-c $fileGoodC", "-o $fileO", '-d ";"']],
            // делиметр с пустым значением, игнорируется такой параметр
            [true, ["-i $fileGoodI", "-c $fileGoodC", "-o $fileO", '-d']],
            // пропуст 1 строки
            [true, ["-i $fileGoodI", "-c $fileGoodC", "-o $fileO", "--skip-first"]],
            // стрикт с правильным конфигом
            [true, ["-i $fileGoodI", "-c $fileGoodC", "-o $fileO", "--strict"]],
            // обычный вызов с корректными данными и конфигурацией
            [true, ["-i $fileGoodI", "-c $fileGoodC", "-o $fileO"]],
            // хелп
            [true, ["-h"]],
            [true, ["--help"]],
        ];
    }
}