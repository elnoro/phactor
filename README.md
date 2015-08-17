# Распараллеленное вычисление числа Pi методом Монте-Карло

## Описание структуры приложения ##

### Уровень генерации процессов и IPC ###

Запуск новых процессов:
1.  сериализируем функцию
2.  запускаем процесс PHP
3. в STDIN вписываем подключение autoload и десериализацию функции
4. запускаем функцию

IPC: 
Сделано через tcp-сокеты. Каждому процессу присваивается порт, явно определяемый при запуске в из родительского процессе. Запущенный процесс слушает соединения на этот порт.

Такой вариант выбран из-за кроссплатформенности. AF_UNIX-сокеты недоступны на Windows, pcntl_fork не работает на Windows.

На этом уровне использованы библиотеки для [корректной сериализации функций](https://github.com/jeremeamia/super_closure) и [запуска PHP процессов через proc_open](http://symfony.com/doc/current/components/process.html)
Можно переписать без их использования


### Уровень модели параллелизма ###

В проекте используется [модель акторов](https://en.wikipedia.org/wiki/Actor_model).
Каждый процесс - это Actor с адресом (номер порта). Может принимать и передавать сообщения, запускать новые Actor'ы, и определять свое поведение при получении следующего сообщения.

### Уровень приложения ###

Главный процесс запускает actor веб-агрегатора, затем actor'ы, рассчитывающие число Pi методом Монте-Карло.
Через случайные промежутки времени главный процесс посылает сообщения с просьбой об отчете actor'ам, занятым расчетом. При получении такого сообщения эти actor'ы посылают текущее значение числа Pi в веб-агрегатор, который вычисляет среднее и записывает результат в index.html.


## Пример использования actor'ов ##
Простое логированное. Создаем процесс лога, записываем две ошибки, просим сбросить лог на диск, останавливаем процесс.

```
#!php
<?php

require __DIR__ . '/vendor/autoload.php';

$logActorAddress = 4000;
$log = \Phactor\Phactor\Actor::createAndRun($logActorAddress, function ($message, $state) {
    if ($message === 'write') {
        file_put_contents('log.log', json_encode($state['log']));
    }
    else {
        $state['log'][] = $message;
    }
    return $state;
});

Phactor\Phactor\Actor::sendMessage($logActorAddress, 'error 1');
Phactor\Phactor\Actor::sendMessage($logActorAddress, 'error 2');
Phactor\Phactor\Actor::sendMessage($logActorAddress, 'write');

sleep(10);

if ($log->isRunning()) {
    echo 'Log is still running' . PHP_EOL;
}
$log->stop();

if (!$log->isRunning()) {
    echo 'Log is not running' . PHP_EOL;
}


```
**config.php и /vendors добавлены в git для того, чтобы можно было сразу запустить ** `php main.php`