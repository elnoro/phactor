<?php

require __DIR__ . '/vendor/autoload.php';

$startTime = time();
$config    = require __DIR__ . '/config.php';
\Phactor\Phactor\App::launch($config['appAddress'], function ($message, $state) use ($startTime) {
    if ($message === '') {
        \Phactor\Phactor\Actor::createAndRun($state['aggregatorAddress'], function ($message, $state) use ($startTime) {
            if (!isset($state['aggregator'])) {
                $state['aggregator'] = new \Phactor\App\WebAggregator(__DIR__ . '/index.html', $startTime);
            }
            $state['aggregator']->addReport(unserialize($message));
            $state['aggregator']->dump();
            return $state;
        });
        $service = new \Phactor\App\CalculatorMultiProcessService(
            $state['numberOfProcesses'],
            $state['aggregatorAddress']
        );
        $service->start();
        while (true) {
            sleep(rand(1, 100));
            echo 'Asking for report...' . PHP_EOL;
            foreach ($service->getAddresses() as $calculatorAddress) {
                \Phactor\Phactor\Actor::sendMessage($calculatorAddress, 'report');
            }
        }
    }
    return $state;
}, $config);
