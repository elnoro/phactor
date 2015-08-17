<?php
namespace Phactor\App;

/**
 * Class Report
 * @package Phactor\App
 * Represents a message with results of calculations
 */
class Report
{
    protected $reporterAddress;
    protected $pointsInside;
    protected $pointsTotal;
    protected $pi;

    /**
     * Constructor
     * @param integer $reporterAddress
     * @param integer $pointsInside
     * @param integer $pointsTotal
     * @param float   $pi
     */
    public function __construct($reporterAddress, $pointsInside, $pointsTotal, $pi)
    {
        $this->reporterAddress = $reporterAddress;
        $this->pointsInside    = $pointsInside;
        $this->pointsTotal     = $pointsTotal;
        $this->pi              = $pi;
    }

    /**
     * @param integer $reporterAddress
     * @param array   $pointsData
     * @param float   $pi
     * @return static
     */
    public static function factory($reporterAddress, array $pointsData, $pi)
    {
        return new static($reporterAddress, $pointsData[0], $pointsData[1], $pi);
    }

    /**
     * @return integer
     */
    public function getReporterAddress()
    {
        return $this->reporterAddress;
    }

    /**
     * @return integer
     */
    public function getPointsInside()
    {
        return $this->pointsInside;
    }

    /**
     * @return integer
     */
    public function getPointsTotal()
    {
        return $this->pointsTotal;
    }

    /**
     * @return float
     */
    public function getPi()
    {
        return $this->pi;
    }
}