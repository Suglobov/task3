<?php

/**
 * Получает входные параметры, проверяет их и возвращает переменные,
 * нужные для дальнейшей работы скрипта
 *
 * @param $options array массив входных параметров
 *
 * @return array если проверка прошла успешно, то вернет массив массивов переменных
 */
function testingOfInputParameters($options)
{
    // набор функций
    require_once __DIR__ . "/helper.php";
    // --

    if (!$options) {
        showErrorAndExit("Отсутствуют нужные параметры" . PHP_EOL . getHelp());
    }

    // вывод введеных параметров
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

    $help = isset($options["h"]) ? true : null;
    $help = isset($options["help"]) ? true : $help;

    $delimiter = isset($options["d"]) ? $options["d"] : ",";
    $delimiter = isset($options["delimiter"]) ? $options["delimiter"] : $delimiter;

    $strict = isset($options["strict"]) ? true : null;
    $skipFirst = isset($options["skip-first"]) ? true : null;
    // --

    // проверка типов всех параметров
    foreach ($options as $k => $v) {
        if (gettype($v) != "string" && gettype($v) != "boolean") {
            showErrorAndExit("$k недопустимый тип входного параметра"
                . PHP_EOL . getMotivationToHelp());
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

    // проверка файлов для чтения
    if (!is_readable($fileInput)) {
        showErrorAndExit("$fileInput не доступен для чтени или несуществует"
            . PHP_EOL . getMotivationToHelp());
    }
    if (!is_readable($fileConfig)) {
        showErrorAndExit("$fileConfig не доступен для чтени или несуществует"
            . PHP_EOL . getMotivationToHelp());
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
        showErrorAndExit("$fileOutput не доступен для записи или несуществует"
            . PHP_EOL . getMotivationToHelp());
    }
    // --

    $prepareParametrs['input'] = [
        $fileInput,
        $fileConfig,
        $fileOutput,
        $delimiter,
        $strict,
        $skipFirst
    ];

    $prepareParametrs['file'] = [
        $handleFileInput,
        $handleFileOutput,
        $contentFileConfig,
        $infoFileInput
    ];

    return $prepareParametrs;
}
