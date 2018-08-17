<?php

namespace Test;

use PHPUnit\Framework\TestCase;

class InputParametersTest extends TestCase
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
    public function testParam($testNumber, $expected, $arrayParams)
    {
        // $testNumber для удобства вычесления какой именно тест упал
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
        $fileGoodI = __DIR__ . "/testingFiles/good/good1Input.csv";
        $fileGoodI2 = __DIR__ . "/testingFiles/good/good2Input.csv";
        $fileGoodI4 = __DIR__ . "/testingFiles/good/good4Input.csv";
        $fileBadI = __DIR__ . "/testingFiles/bad/bad1Input.csv";

        $fileGoodC = __DIR__ . "/testingFiles/good/good1Conf.php";
        $fileBadC = __DIR__ . "/testingFiles/bad/bad1Conf.php";

        $fileO = __DIR__ . "/testingFiles/tmpOutput.csv";


        return [
            // нет обязательных параметров
            [1, false, []], // 1
            [2, false, ["-i"]],
            [3, false, ["-c"]],
            [4, false, ["-o"]],
            [5, false, ["--input"]],
            [6, false, ["--config"]],
            [7, false, ["--output"]],
            [8, false, ["-i fff", "-c zzz"]],
            [9, false, ["-c rrr", "-o ggg"]],
            [10, false, ["-i sss", "-o ggg"]],
            [11, false, ["-i fff", "-o sss", "-c eee"]],
            [12, false, ["-i $fileGoodI", "-c $fileGoodC", "--strict"]],
            // входные параметры - массивы
            //            [13, false, ["-i $fileGoodI", "-i $fileGoodI", "-c $fileGoodC", "-o $fileO"]],
            //            [14, false, ["-i $fileGoodI", "-c $fileGoodC", "-c $fileGoodC", "-o $fileO"]],
            //            [15, false, ["-i $fileGoodI", "-c $fileGoodC", "-o $fileO", "-o $fileO"]],
            //            [16, false, ["-i $fileGoodI", "-c $fileGoodC", "-o $fileO", "--strict", "--strict"]],
            //            [17, false, ["-i $fileGoodI", "-c $fileGoodC", "-o $fileO", "--skip-first", "--skip-first"]],
            //            [18, false, ["-i $fileGoodI", "-c $fileGoodC", "-o $fileO", '-d ","', '-d ","']],
            //            [19, false, ["-h", "-h"]],
            //            [20, false, ["--help", "--help"]],
            // неправильный делиметр
            [21, false, ["-i $fileGoodI", "-c $fileGoodC", "-o $fileO", '-d "asdf"']],
            // дублирование параметров
            //            [22, false, ["-h", "--help"]],
            //            [23, false, ["-i $fileGoodI", "--input $fileGoodI", "-c $fileGoodC", "-o $fileO"]],
            //            [24, false, ["-i $fileGoodI", "--config $fileGoodC", "-c $fileGoodC", "-o $fileO"]],
            //            [25, false, ["-i $fileGoodI", "--output $fileO", "-c $fileGoodC", "-o $fileO"]],
            // одинаковые значения обязательных параметров
            [26, false, ["-i $fileGoodI", "-c $fileGoodI", "-o $fileO"]],
            [27, false, ["-i $fileGoodI", "-c $fileO", "-o $fileO"]],
            [28, false, ["-i $fileGoodC", "-c $fileGoodC", "-o $fileO"]],
            [29, false, ["-i $fileO", "-c $fileGoodC", "-o $fileO"]],
            // неправильный конфиг
            [30, false, ["-i $fileGoodC", "-c $fileGoodI", "-o $fileO"]],
            // стрикт и плохой конфиг
            [31, false, ["-i $fileGoodI", "-c $fileBadC", "-o $fileO", "--strict"]],
            // плохие входные данные (не все строки имеют одинаковое количество столбцов)
            [32, false, ["-i $fileBadI", "-c $fileGoodC", "-o $fileO"]],
            // правильный делиметр и файл с ним
            [33, true, ["-i $fileGoodI", "-c $fileGoodC", "-o $fileO", '-d ","']],
            [34, true, ["-i $fileGoodI2", "-c $fileGoodC", "-o $fileO", '-d ";"']],
            // не получилось запустить этот тест так, чтоб строка $'\t' воспринялась как табуляция в exec
            //            [35, true, ["-i $fileGoodI4", "-c $fileGoodC", "-o $fileO", "-d $'" . '\t' . "'"]],
            // делиметр с пустым значением (игнорируется такой параметр)
            [36, true, ["-i $fileGoodI", "-c $fileGoodC", "-o $fileO", '-d']],
            // пропуст первой строки
            [37, true, ["-i $fileGoodI", "-c $fileGoodC", "-o $fileO", "--skip-first"]],
            // стрикт с правильным конфигом
            [38, true, ["-i $fileGoodI", "-c $fileGoodC", "-o $fileO", "--strict"]],
            // обычный вызов с корректными данными и конфигурацией
            [39, true, ["-i $fileGoodI", "-c $fileGoodC", "-o $fileO"]],
            // хелп
            [40, true, ["-h"]],
            [41, true, ["--help"]],
        ];
    }
}