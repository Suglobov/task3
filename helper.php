<?php

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
Use:
    Программа предназначена для преобразования одного csv файла в другой csv файл
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
