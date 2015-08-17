<?php
namespace Phactor\App;

/**
 * Class WebAggregator
 * @package Phactor\App
 * Aggregates information about Pi calculations
 */
class WebAggregator
{
    /**
     * @var string
     */
    protected $pathToHtml;
    /**
     * @var integer
     */
    protected $startTime;

    /**
     * @var Report[]
     */
    protected $reports = [];

    /**
     * Constructor
     * @param string  $pathToHtml path to html file to dump information into
     * @param integer $startTime time when application was started
     */
    public function __construct($pathToHtml, $startTime = 0)
    {
        $this->pathToHtml = $pathToHtml;
        $this->startTime  = $startTime ?: time();
    }

    /**
     * Creates or updates html file with information about Pi
     */
    public function dump()
    {
        file_put_contents($this->pathToHtml, <<<HTML
        <strong>Current PI:</strong> {$this->calculateCurrentPi()}
        <strong>Time spent:</strong> {$this->calculateRunningTime()}
HTML
        );
    }

    /**
     * Calculates an average Pi based on received reports
     * @return float
     */
    public function calculateCurrentPi()
    {
        $pi = 0;
        foreach ($this->reports as $report) {
            $pi += $report->getPi();
        }
        return $pi / count($this->reports);
    }

    /**
     * Calculates running time
     * @return int
     */
    public function calculateRunningTime()
    {
        return (time() - $this->startTime);
    }

    /**
     * Adds of updates report from certain Actor
     * @param Report $report
     */
    public function addReport(Report $report)
    {
        $this->reports[$report->getReporterAddress()] = $report;
    }
}
