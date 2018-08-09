<?php

prepareFileEndOfLine("files/eolInputCRNL.csv", "\r\n");
prepareFileEndOfLine("files/eolInputNL.csv", "\n");
prepareFileEndOfLine("files/eolInputCR.csv", "\r");
function prepareFileEndOfLine($filepath, $endOfLine)
{
    $fgc = file_get_contents($filepath);
//    echo "mb_strlen: " . mb_strlen($fgc) . PHP_EOL;
    $eol = findEOL($fgc);
    $fileArr = explode($eol, $fgc);
    echo file_put_contents($filepath, implode($endOfLine, $fileArr)) . PHP_EOL;
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