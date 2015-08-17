<?php
namespace Phactor\Phactor;

/**
 * Class App
 * @package Phactor\Phactor
 * Everything is an actor and the app itself
 * Provides initial handler run
 */
class App extends Actor
{
    /**
     * The initialization flag
     * @var bool
     */
    protected $initialized = false;

    /**
     * Runs handler with empty message
     * @throws \BadMethodCallException
     */
    public function init()
    {
        if ($this->initialized) {
            throw new \BadMethodCallException('App has been already initialized');
        }
        $this->handle('');
    }

    /**
     * Launches an app actor
     * @param integer  $id
     * @param callable $handler
     * @param array    $config
     */
    public static function launch($id, Callable $handler, array $config = [])
    {
        $app = new static($id, $handler, $config);
        $app->init();
        $app->run();
    }
}