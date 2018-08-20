<?php

$fileList = [
    [
        "path" => __DIR__ . "/../testingFiles/eol/eolInputCRNL.csv",
        "eol" => "\r\n"
    ],
    [
        "path" => __DIR__ . "/../testingFiles/eol/eolInputNL.csv",
        "eol" => "\n"
    ],
    [
        "path" => __DIR__ . "/../testingFiles/eol/eolInputCR.csv",
        "eol" => "\r"
    ],
];

array_walk($fileList, function ($v, $k) {
    $fgc = file($v['path']);
    $view = 0;
    if (strpos($fgc[0], "\r\n") !== false) {
        $eol = $view ? '\r\n' : "\r\n";
    } elseif (strpos($fgc[0], "\n") !== false) {
        $eol = $view ? '\n' : "\n";
    } elseif (strpos($fgc[0], "\r") !== false) {
        $eol = $view ? '\r' : "\r";
    } else {
        $eol = "?";
    }
    $fileArr = explode($eol, $fgc);
    echo file_put_contents($v['path'], implode($v["eol"], $fileArr)) . PHP_EOL;
});
