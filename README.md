## Самостоятельная работа - csv преобразователь
[Ссылка на полное описание задачи](https://docs.google.com/document/d/1ozgWBDF_-bpysEuwXfyPpyxsQ2Tze92JQs0WucmvM6s/)  
php программа, запускаемая из командной строки, основное назначение которой - преобразовать данные из csv-файла,  
в которых значения определенных полей заменяется по конфигурационному файлу.  
Программа принимает на вход 3 обязательных параметра.  
Первый - путь до исходного csv-файла с данными,  
второй - путь до конфигурационного файла, в котором определено, в каком столбце и по какой схеме заменять значения.  
Третий путь до файла для сохранения результата.  
Результат работы - csv-файл с тем же форматированием, что и исходный.

В конфигурационном файле определяется для поля тип в соответствии с типами,  
поддерживаемыми библиотекой генерации случайных данных  
**faker - https://github.com/fzaninotto/Faker**.  
Например, для столбца №3 можно указать ‘firstName’ и программа заменит все значения столбца №3 на разные значения,  
выдаваемые командой ```$faker->firstName```.  
Список поддерживаемых типов можно посмотреть в документации к Faker.  
Конфигурация задается на языке php и кроме типов из Faker, можно указать функцию,  
которая возвращает точное значение для этого поля.  
Аргументы функции:  
1) текущее значение,  
2) массив значений текущей строки,  
3) номер строки данных,  
4) экземпляр $faker.  
Кодировка исходного файла может быть **cp1251**, либо **utf-8**.  
Выходной файл по разделителям, экранированию, окончанию строк и кодировке аналогичен входному.  
Исходный файл может содержать строку-заголовок с названиями полей, есть возможность пропустить обработку первой строки.  

```
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
```

## Установка
Требуется **php 7**, установленный **composer**, а так же **make**
1. клонируйте репозиторий с гитхаба:
    ```
    git clone https://github.com/Suglobov/task3.git 
    ```
2. в папке с проектом запустите
    ```
    make install
    ```
3. для тестов в каталоге проекта используйте команду
    ```
    make test
    ```
4. вызов справки с возможными вариантами вызова и доступными опциями
    ```
    php action.php -h
    ```
    или
    ```
    php action.php --help
    ```
5. файлы с тестовыми данными лежат в каталоге проекта **./tests/files**

## Варианты вызова
завершится ли успешно | параметры после **php action.php**  
--------------------- | ----------------------------------  
true  | -i tests/files/good1Input.csv -c tests/files/good1Conf.php -o tests/files/tmpOutput.csv  
false | -i tests/files/good1Input.csv -c tests/files/good1Input.csv -o tests/files/tmpOutput.csv  
true  | -i tests/files/encodW1251.csv -c tests/files/good1Conf.php -o tests/files/tmpOutput.csv  
false | -i tests/files/tmpOutput.csv -c tests/files/good1Conf.php -o tests/files/tmpOutput.csv  
true  | -i tests/files/tmpOutput.csv -c tests/files/good1Conf.php -o tests/files/tmp2Output.csv  
true  | -i tests/files/eolInputCRNL.csv -c tests/files/good1Conf.php -o tests/files/tmpOutput.csv  
true  | -i tests/files/good4Input.csv -c tests/files/good1Conf.php -o tests/files/tmpOutput.csv -d $'\t'  