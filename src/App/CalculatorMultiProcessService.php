<?php

namespace Phactor\App;

use Phactor\Phactor\Actor;

/**
 * Class CalculatorMultiProcessService
 * @package Phactor\App
 * Runs actors calculating Pi via Monte-Carlo method
 */
class CalculatorMultiProcessService
{
    protected $numberOfProcesses;
    protected $addressToReport;
    protected $addresses = [];
    protected $processes = [];

    /**
     * @param integer $numberOfProcesses
     * @param integer $addressToReport
     */
    public function __construct($numberOfProcesses, $addressToReport)
    {
        $this->numberOfProcesses = $numberOfProcesses;
        $this->addressToReport   = $addressToReport;
    }

    /**
     * @return integer[]
     */
    public function getAddresses()
    {
        return $this->addresses;
    }

    /**
     * @return \Symfony\Component\Process\PhpProcess[]
     */
    public function getProcesses()
    {
        return $this->processes;
    }

    /**
     * Runs calculating actors
     */
    public function start()
    {
        $initialAddress = 22343;
        for ($i = 0; $i < $this->numberOfProcesses; ++$i) {
            $address           = $initialAddress + $i;
            $iterations        = rand(100, 200); // according to task should be set by random
            $this->addresses[] = $address;
            $this->processes[] = Actor::createAndRun(
                $address,
                $this->getCalculatorHandler($iterations, $this->addressToReport)
            );
            Actor::sendMessage($address, 'start');
        }
    }

    /**
     * @param integer $iterations how many
     * @param integer $addressToReport address to send reports about calculation
     * @return \Closure
     */
    public function getCalculatorHandler($iterations, $addressToReport)
    {
        return function ($message, $state) use ($iterations, $addressToReport) {
            if (!isset($state['calculator'])) $state['calculator'] = new Calculator(1000);
            if (!isset($state['sendReport'])) {
                $state['sendReport'] = function ($state) use ($addressToReport) {
                    \Phactor\Phactor\Actor::sendMessage(
                        $addressToReport,
                        serialize(\Phactor\App\Report::factory(
                            $state['mailbox']->getAddress(),
                            $state['calculator']->getCounters(),
                            $state['calculator']->getResult()
                        ))
                    );
                };
            }

            if ($message === 'start') {
                for ($i = 0; $i < $iterations; $i += 1000) {
                    for ($j = 0; $j < 1000; ++$j) {
                        $state['calculator']->step();
                    }
                    if ($connection = $state['mailbox']->fetchMessage()) {
                        if (stream_get_contents($connection) === 'report') $state['sendReport']($state);
                    }
                }
                $state['sendReport']($state);
            }
            elseif ($message === 'report') $state['sendReport']($state);
            return $state;
        };
    }
}