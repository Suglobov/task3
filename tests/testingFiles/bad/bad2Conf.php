<?php

return [
    1 => '123', // faker
    2 => null, // set value to null
    3 => function ($value, $rowData, $rowIndex, $faker) {
        return 'фыва йцук';
    },
    4 => function ($value, $rowData, $rowIndex, $faker) {
        if ($value == 23) {
            return $rowIndex + 5;
        }
        if ($rowData[0] == 101) {
            return 0;
        }
        return $faker->randomDigit;
    },
    10 => null,
];

