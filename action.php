<?php

$shortopts = "i:c:o:d::h";

$longopts = [
    "input:",
    "config:",
    "output:",
    "delimiter::",
    "skip-first",
    "strict",
    "help",
];

$options = getopt($shortopts, $longopts, $optind);

if (!$options) {
    showErrorAndExit("`getopt` сработал с ошибкой");
}

// переменные из входных параметров
$fileInput = (isset($options["i"]) ? $options["i"] : null);
$fileInput = (isset($options["input"]) ? $options["input"] : $fileInput);

$fileConfig = (isset($options["c"]) ? $options["c"] : null);
$fileConfig = (isset($options["config"]) ? $options["config"] : $fileConfig);

$fileOutput = (isset($options["o"]) ? $options["o"] : null);
$fileOutput = (isset($options["output"]) ? $options["output"] : $fileOutput);

$help = isset($options["h"]) ? $options["h"] : null;
$help = isset($options["help"]) ? $options["help"] : $help;

$strict = isset($options["strict"]) ? $options["strict"] : null;

$delimiter = isset($options["delimiter"]) ? $options["delimiter"] : ",";
$skipFirst = isset($options["skip-first"]) ? $options["skip-first"] : null;
// --
// вывод подсказок
if (isset($help)) {
    showMessageAndExit(showHelp());
}
// --
// проверка на существование обязательных параметров
if (!(
    isset($fileInput)
    && isset($fileConfig)
    && (isset($fileOutput) || isset($strict))
)) {
    showErrorAndExit("Обязательные параметры не введены" . PHP_EOL . showHelp());
}
// --
// проверка на существование файлов для чтения
if (!is_readable($fileInput)) {
    showErrorAndExit("$fileInput не доступен для чтени или несуществует" . PHP_EOL . showMotivationToHelp());
}
if (!is_readable($fileConfig)) {
    showErrorAndExit("$fileConfig не доступен для чтени или несуществует" . PHP_EOL . showMotivationToHelp());
}
// --
// чтение файлов
$handleFileInput = fopen($fileInput, "r");
$contentFileConfig = include $fileConfig;
if (strtolower(gettype($contentFileConfig)) != 'array') {
    showErrorAndExit("Конфиграция не является массивом");
}
//echo print_r($contentFileConfig, 1) . PHP_EOL;
// --
// ветвление есть ли strict или нет
if ($strict !== null) {
    if ($handleFileInput !== false) {
        try {
            $dataFileInput = fgetcsv($handleFileInput, 0, $delimiter);
            $countDataFileInput = count($dataFileInput);
            if ($countDataFileInput < count($contentFileConfig)) {
                showErrorAndExit("Размер конфигураций больше чем количество полей входного файла");
            }
            foreach ($contentFileConfig as $k => $v) {
                if (!isset($dataFileInput[$k])) {
                    showErrorAndExit("В конфигцрации есть правило для поля, которого нет в входном файле");
                }
            }
        } finally {
            fclose($handleFileInput);
        }
        showMessageAndExit("--stict Проверка пройдена успешно");
    } else {
        showErrorAndExit("Входной файл не открылся или пустой");
    }
}
// --
// strict опция не включена, значит должен быть параметр output
if (!is_writable($fileOutput)) {
    showErrorAndExit("$fileOutput не доступен для записи или несуществует" . PHP_EOL . showMotivationToHelp());
}

// открываем файл для записи
$handleFileOutput = fopen($fileOutput, "w");

// проверка на запись в файл
if (!is_writable($fileOutput)) {
    showErrorAndExit("$fileOutput не доступен для записи или несуществует" . PHP_EOL . showMotivationToHelp());
}
// --
// тут можно и автолоадер подключить
require_once __DIR__ . "/./vendor/autoload.php";
// --
// включаем Faker
$faker = Faker\Factory::create();
// --
// пробежка по файлу
if ($handleFileInput && $handleFileOutput) {
    $row = 0;
    try {
        while (($dataFileInput = fgetcsv($handleFileInput, 0, $delimiter)) !== false) {
            $row++;
            if ($row == 1) {
                $countDataFileInput = count($dataFileInput);
            }
            if ($countDataFileInput != count($dataFileInput)) {
                fclose($handleFileInput);
                fclose($handleFileOutput);
                showErrorAndExit("Кол-во полей первой строки отличается от кол-ва полей $row строки");
            }
            if (isset($skipFirst) && $row == 1) {
                $dataFileOutput = $dataFileInput;
            } else {
//                echo print_r($contentFileConfig, 1) . PHP_EOL;
                foreach ($dataFileInput as $k => $v) {
                    if (!array_key_exists($k, $contentFileConfig)) {
                        $dataFileOutput[$k] = $v;
                    } else {
                        $fieldConfig = $contentFileConfig[$k];
                        if (is_null($fieldConfig)) {
                            $dataFileOutput[$k] = "";
                        } elseif (strtolower(gettype($fieldConfig)) == "string") {
                            try {
                                $dataFileOutput[$k] = $faker->$fieldConfig;
                            } catch (Exception $e) {
                                showErrorAndExit("Метод из Faker отработал с ошибкой: " . $e->getMessage());
                            }
                        } elseif (strtolower(gettype($fieldConfig)) == "object") {
                            try {
                                $dataFileOutput[$k] = $fieldConfig($v, $dataFileInput, $row, $faker);
                            } catch (Exception $e) {
                                showErrorAndExit("Функция из конфигурации отработала с ошибкой: " . $e->getMessage());
                            }
                        } else {
                            $dataFileOutput[$k] = $v;
                        }
                    }
                }
            }
            $fputcsv = fputcsv($handleFileOutput, $dataFileOutput, $delimiter);
            if ($fputcsv === false) {
                showErrorAndExit("fputcsv отработал с ошибкой");
            }
        }
    } finally {
        fclose($handleFileInput);
        fclose($handleFileOutput);
    }
} else {
    showErrorAndExit("Входной файл или выходной или оба не открыл(ся/ись) или пуст(ой/ые)");
}
showMessageAndExit("Успешно выполнено");

// ********** ********** Функции ********** ********** //
//function showErrorAndExit($message, $errorNumber = 1) {
//
//}
function showHelp()
{
    $help = <<<DOC
Usage:
    action.php (-i|--input) <filepath>
               (-c|--config) <filepath>
               (-o|--output) <filepath>
               [-d|--delimeter <delimeter>]
               [--skip-first]
    action.php (-i|--input) <filepath>
               (-c|--config) <filepath>
               --strict
               [-d|--delimeter <delimeter>]
    action.php (-h|--help)
Options:
    -i|--input <filepath>                      путь до исходного файла (обязательный)
    -c|--config <filepath>                     путь до файла конфигурации (обязательный)
    -o|--output <filepath>                     путь до файла с результатом (обязательный)
    -d|--delimiter <delimiter> [default: ","]  задать разделитель, [default: “,”]
    --skip-first                               пропускать модификацию первой строки исходного csv
    --strict                                   проверяет, что исходный файл содержит необходимое
                                               количество описанных в конфигурационном файле столбцов.
    -h|--help                                  вывести справку
DOC;
    return $help;
}

function showMotivationToHelp()
{
    return "-h|--help - вывести справку";
}

function showMessageAndExit($message, $exitCode = 0)
{
    echo "" . $message . PHP_EOL;
    exit($exitCode);

}

function showErrorAndExit($message, $errorNumber = 1)
{
    echo "Ошибка: " . $message . PHP_EOL;
    exit($errorNumber);

}