<?php
/*
true  -i tests/files/good1Input.csv -c tests/files/good1Conf.php -o tests/files/tmpOutput.csv
false -i tests/files/good1Input.csv -c tests/files/good1Input.csv -o tests/files/tmpOutput.csv
true  -i tests/files/encodW1251.csv -c tests/files/good1Conf.php -o tests/files/tmpOutput.csv
false -i tests/files/tmpOutput.csv -c tests/files/good1Conf.php -o tests/files/tmpOutput.csv
true  -i tests/files/tmpOutput.csv -c tests/files/good1Conf.php -o tests/files/tmp2Output.csv
true  -i tests/files/eolInputCRNL.csv -c tests/files/good1Conf.php -o tests/files/tmpOutput.csv
 */

$shortopts = "i:c:o:d:h";
$longopts = ["input:", "config:", "output:", "delimiter:", "skip-first", "strict", "help",];

$options = getopt($shortopts, $longopts, $optind);

if (!$options) {
    showErrorAndExit("`getopt` сработал с ошибкой");
}

// вывод введеных параметров
//echo "Введенные параметры: " . implode(", ", array_keys($options)) . PHP_EOL;
echo "Введенные параметры: " . PHP_EOL;
foreach ($options as $k => $v) {
    echo "\t$k: $v" . PHP_EOL;
}
// --

// проверка дублирование входных параметров
$equalParams = [
    ['i', 'input'],
    ['c', 'config'],
    ['o', 'output'],
    ['d', 'delimiter'],
    ['h', 'help'],
];
foreach ($equalParams as $k => $v) {
    $tmpArr = array_filter($v, function ($a) use ($options) {
        return isset($options[$a]);
    });
    if (count($tmpArr) > 1) {
        showErrorAndExit("вызываны дублирующие параметры: "
            . implode(', ', $v)
            . ". Выберете 1 из вариантов вызова"
            . PHP_EOL . getMotivationToHelp());
    }
}
// --

// переменные из входных параметров
$fileInput = (isset($options["i"]) ? $options["i"] : null);
$fileInput = (isset($options["input"]) ? $options["input"] : $fileInput);

$fileConfig = (isset($options["c"]) ? $options["c"] : null);
$fileConfig = (isset($options["config"]) ? $options["config"] : $fileConfig);

$fileOutput = (isset($options["o"]) ? $options["o"] : null);
$fileOutput = (isset($options["output"]) ? $options["output"] : $fileOutput);
//echo print_r($fileOutput, 1) . PHP_EOL;

$help = isset($options["h"]) ? true : null;
$help = isset($options["help"]) ? true : $help;

$delimiter = isset($options["d"]) ? $options["d"] : ",";
$delimiter = isset($options["delimiter"]) ? $options["delimiter"] : $delimiter;
//echo '$delimiter: ' . $delimiter . PHP_EOL;
//echo '$delimiter gettype: ' . gettype($delimiter) . PHP_EOL;

$strict = isset($options["strict"]) ? true : null;
$skipFirst = isset($options["skip-first"]) ? true : null;
// --

// проверка типов всех параметров
foreach ($options as $k => $v) {
    if (gettype($v) != "string" && gettype($v) != "boolean") {
        showErrorAndExit("$k недопустимый тип входного параметра" . PHP_EOL . getMotivationToHelp());
    }
}
// проверка типа параметра delimiter
if (!(gettype($delimiter) == "string" && strlen($delimiter) == 1)) {
    if ($delimiter == '\t') {
        $delimiter = "\t";
    } else {
        showErrorAndExit(
            "delimiter должен быть строкой из 1 символа ("
            . gettype($delimiter)
            . ")"
            . PHP_EOL . getMotivationToHelp()
        );
    }
}
//echo '$delimiter:' . $delimiter . "|||". PHP_EOL;
// --

// проверка на повторяемость параметров
$requiredParams = [$fileInput, $fileConfig, $fileOutput];
$requiredParamsName = ['input', 'config', 'output'];
for ($i = 0; $i < count($requiredParams) - 1; $i++) {
    for ($j = $i + 1; $j < count($requiredParams); $j++) {
        if (isset($requiredParams[$i])
            && isset($requiredParams[$j])
            && ($requiredParams[$i] == $requiredParams[$j])
        ) {
            showErrorAndExit(
                $requiredParamsName[$i] . " и " . $requiredParamsName[$j]
                . " параметры дублируют свои значения"
                . PHP_EOL . getMotivationToHelp()
            );
        }
    }
}
// --

// вывод подсказок
if (isset($help)) {
    showMessageAndExit(getHelp());
}
// --

// проверка на существование обязательных параметров
if (!(isset($fileInput) && isset($fileConfig) && isset($fileOutput))) {
    showErrorAndExit("Обязательные параметры не введены" . PHP_EOL . getHelp());
}
// --

// проверка на существование файлов для чтения
if (!is_readable($fileInput)) {
    showErrorAndExit("$fileInput не доступен для чтени или несуществует" . PHP_EOL . getMotivationToHelp());
}
if (!is_readable($fileConfig)) {
    showErrorAndExit("$fileConfig не доступен для чтени или несуществует" . PHP_EOL . getMotivationToHelp());
}
// --

// чтение файлов
$handleFileInput = fopen($fileInput, "rb");

ob_start();
$contentFileConfig = include $fileConfig;
ob_get_clean();
if (strtolower(gettype($contentFileConfig)) != 'array') {
    showErrorAndExit("Конфиграция не является массивом");
}
//echo print_r($contentFileConfig, 1) . PHP_EOL;
// --

// получаем данные о файле
$infoFileInput = inoFile($fileInput);
// проверка соответствия конфиграции входящему файлу
if ($strict !== null) {
    $isConfigAccordingInputs = isConfigAccordingInputs($fileInput, $contentFileConfig, $delimiter);
    if ($isConfigAccordingInputs['exit'] == 1) {
        showErrorAndExit($isConfigAccordingInputs['message']);
    }
}
// --

// открываем файл для записи
$handleFileOutput = fopen($fileOutput, "wb+");
// проверка на запись в файл
if (!is_writable($fileOutput)) {
    showErrorAndExit("$fileOutput не доступен для записи или несуществует" . PHP_EOL . getMotivationToHelp());
}
// --

// тут можно и автолоадер подключить
require_once __DIR__ . "/vendor/autoload.php";
// --

// включаем Faker
$faker = Faker\Factory::create();
// --

// пробежка по файлу
if ($handleFileInput && $handleFileOutput) {
    $row = 0;
    try {
        // получаем файл построчно
        while (($dataFileInput = fgetcsv($handleFileInput, 0, $delimiter)) !== false) {
            $row++;
            if ($row == 1) {
                // соличество столбцов первой строки
                $countDataFileInput = count($dataFileInput);
            }
            if ($countDataFileInput != count($dataFileInput)) {
                // если количество столбцов у других строк отличается, то данные неверные
                fclose($handleFileInput);
                fclose($handleFileOutput);
                showErrorAndExit("Кол-во полей первой строки отличается от кол-ва полей $row строки");
            }
            if (isset($skipFirst) && $row == 1) {
                $dataFileOutput = $dataFileInput;
            } else {
                $dataFileOutput = processingInputRow(
                    $dataFileInput,
                    $contentFileConfig,
                    $faker,
                    $row,
                    $infoFileInput
                );
                if (isset($dataFileOutput['exit'])) {
                    showErrorAndExit($dataFileOutput['message'], $dataFileOutput['exit']);
                }
            }
            $fputcsv = fputcsv($handleFileOutput, $dataFileOutput, $delimiter);
            if ($fputcsv === false) {
                showErrorAndExit("fputcsv отработал с ошибкой");
            }
            // перезаписываем конец строки. Так как скрипт могут запусть под windows,
            // надо проверить какой конец строки записывает fputcsv.
            // Хоть сейчас и не используется \r, все равно проверку сделаю универсальной.
            if (fseek($handleFileOutput, -1, SEEK_CUR) === 0) {
                if (fseek($handleFileOutput, -1, SEEK_CUR) === 0) {
                    readEOLAndWriteNew($handleFileOutput, 2, $infoFileInput);
                } else {
                    readEOLAndWriteNew($handleFileOutput, 1, $infoFileInput);
                }
            }
        }
    } finally {
        fclose($handleFileInput);
        fclose($handleFileOutput);
    }
} else {
    showErrorAndExit("Входной файл или выходной или оба не открылись");
}
showMessageAndExit("Успешно выполнено");

// ********** ********** Функции ********** ********** //
function readEOLAndWriteNew($handleFileOutput, $readByte, $infoFileInput)
{
    $linePartOut = fread($handleFileOutput, $readByte);
    $eolOut = findEOL($linePartOut);
    if ($eolOut != $infoFileInput['eol']) {
        $eolOutLength = strlen($eolOut);
        if (fseek($handleFileOutput, -$eolOutLength, SEEK_CUR) === 0) {
            fwrite($handleFileOutput, $infoFileInput['eol']);
        }
    }
}

function processingInputRow($dataFileInput, $contentFileConfig, $faker, $row, $infoFileInput)
{
    $dataFileOutput = [];
    $encodRead = $infoFileInput['encoding'];
    foreach ($dataFileInput as $k => $v) {
        if (!array_key_exists($k, $contentFileConfig)) {
            // если в конфиге нет такого ключа, менять поле не надо
            $tmpData = $v;
        } else {
            $fieldConfig = $contentFileConfig[$k];
            if (is_null($fieldConfig)) {
                $tmpData = "";
            } elseif (strtolower(gettype($fieldConfig)) == "string") {
                // так как в конфигурации строка, значит вызываем faker
                try {
                    $tmpData = $faker->$fieldConfig;
                    $encodSave = mb_check_encoding($tmpData, 'UTF-8') ? 'UTF-8' : 'Windows-1251';
                    // сравниваем кодировку файла и кодировку поля, если различаются, то меняем ее у поля
                    if ($infoFileInput['encoding'] != $encodSave) {
                        if ($encodRead == 'UTF-8') {
                            $tmpData = mb_convert_encoding($tmpData, 'UTF-8', 'Windows-1251');
                        } else {
                            $tmpData = mb_convert_encoding($tmpData, 'Windows-1251', 'UTF-8');
                        }
                    }
                } catch (Exception $e) {
                    return [
                        'exit' => 1,
                        'message' => "Метод из Faker отработал с ошибкой: " . $e->getMessage(),
                    ];
                }
            } elseif (strtolower(gettype($fieldConfig)) == "object") {
                // всего 3 варианта зименения данных,
                // так как не null и не строка, то значит функция, пробуем ее вызвать.
                try {
                    $tmpData = $fieldConfig($v, $dataFileInput, $row, $faker);
                    $encodSave = mb_check_encoding($tmpData, 'UTF-8') ? 'UTF-8' : 'Windows-1251';
                    // сравниваем кодировку файла и кодировку поля, если различаются, то меняем ее у поля
                    if ($encodRead != $encodSave) {
                        if ($encodRead == 'UTF-8') {
                            $tmpData = mb_convert_encoding($tmpData, 'UTF-8', 'Windows-1251');
                        } else {
                            $tmpData = mb_convert_encoding($tmpData, 'Windows-1251', 'UTF-8');
                        }
                    }
                } catch (Exception $e) {
                    return [
                        'exit' => 1,
                        'message' => "Функция из конфигурации отработала с ошибкой: " . $e->getMessage(),
                    ];
                }
            } else {
                $tmpData = $v;
            }
        }
        $dataFileOutput[$k] = $tmpData;
    }
    return $dataFileOutput;
}

function isConfigAccordingInputs($fileInput, $contentFileConfig, $delimiter)
{
    $handleFileInput = fopen($fileInput, "rb");
    if ($handleFileInput !== false) {
        try {
            $dataFileInput = fgetcsv($handleFileInput, 0, $delimiter);
            $countDataFileInput = count($dataFileInput);
            if ($countDataFileInput < count($contentFileConfig)) {
                return ['exit' => 1, 'message' => "Размер конфигураций больше чем количество полей входного файла",];
            }
            foreach ($contentFileConfig as $k => $v) {
                if (!isset($dataFileInput[$k])) {
                    return [
                        'exit' => 1,
                        'message' => "В конфигцрации есть правило для поля, которого нет в входном файле",
                    ];
                }
            }
        } finally {
            fclose($handleFileInput);
        }
        return ['exit' => 0, 'message' => "--stict Проверка пройдена успешно",];
    } else {
        return ['exit' => 1, 'message' => "Входной файл не открылся или пустой",];
    }
}

function inoFile($filePath)
{
    $handle = fopen($filePath, "rb");
    $result = [];
    if ($handle) {
        try {
            $buffer = fgets($handle);
            $result['eol'] = findEOL($buffer);
            $result['encoding'] = mb_check_encoding($buffer, 'UTF-8') ? 'UTF-8' : 'Windows-1251';
            $result['strlenEol'] = strlen($result['eol']);
//            echo 'mb_strlen:' . mb_strlen($result['eol'])
//                . '; strlen:' . $result['strlenEol'] . PHP_EOL;
        } finally {
            fclose($handle);
        }
    } else {
        return [
            'exit' => 1,
            'message' => "Не удалось открыть файл $filePath",
        ];
    }
    return $result;
}

function findEOL($line, $view = 0)
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

function getHelp()
{
    $help = <<<DOC
Usage:
    action.php (-i|--input) <filepath>
               (-c|--config) <filepath>
               (-o|--output) <filepath>
               [-d|--delimeter <delimeter>]
               [--skip-first]
               [--strict]
    action.php (-h|--help)
Options:
    -i|--input <filepath>                      путь до исходного файла (обязательный)
    -c|--config <filepath>                     путь до файла конфигурации (обязательный)
    -o|--output <filepath>                     путь до файла с результатом (обязательный)
    -d|--delimiter <delimiter> [default: ","]  задать разделитель, [default: “,”]
    --skip-first                               пропускать модификацию первой строки исходного csv
    --strict                                   проверяет, что исходный файл содержит необходимое
                                               количество описанных в конфигурационном файле столбцов.
    -h --help                                  вывести справку
DOC;
    return $help;
}

function getMotivationToHelp()
{
    return "-h|--help - вывести справку";
}

function showMessageAndExit($message, $exitCode = 0)
{
    echo "" . $message . PHP_EOL;
    exit($exitCode);

}

function showErrorAndExit($message, $exitCode = 1)
{
    echo "Ошибка: " . $message . PHP_EOL;
    exit($exitCode);

}