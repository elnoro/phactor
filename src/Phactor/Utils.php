<?php
namespace Phactor\Phactor;

/**
 * Class Utils
 * @package Phactor\Phactor
 */
class Utils
{
    /**
     * Provides path to composer's autoloader for a new process
     * @return string
     */
    public static function getAutoloadPath()
    {
        return __DIR__ . '/../../vendor/autoload.php';
    }
}