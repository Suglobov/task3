<?php

$filepath = __DIR__ . "/../testingFiles/encode/encodUtf8.csv";
$fileContent = file_get_contents($filepath);
$fileContent2 = mb_convert_encoding($fileContent, 'Windows-1251', 'UTF-8');
echo file_put_contents(__DIR__ . "/../testingFiles/encode/encodW1251.csv", $fileContent2) . PHP_EOL;
