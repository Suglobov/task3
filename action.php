<?php

// автолоадер
require_once __DIR__ . "/vendor/autoload.php";
// набор функций
require_once __DIR__ . "/helper.php";
// функция проверки входных параметров
require_once __DIR__ . "/testingOfInputParameters.php";
// --

$shortopts = "i:c:o:d:h";
$longopts = ["input:", "config:", "output:", "delimiter:", "skip-first", "strict", "help",];

$options = getopt($shortopts, $longopts, $optind);

$prepareParametrs = testingOfInputParameters($options);

list($fileInput,
    $fileConfig,
    $fileOutput,
    $delimiter,
    $strict,
    $skipFirst) = $prepareParametrs['input'];

list(
    $handleFileInput,
    $handleFileOutput,
    $contentFileConfig,
    $infoFileInput) = $prepareParametrs['file'];


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
