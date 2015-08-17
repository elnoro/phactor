<?php

namespace Phactor\App;

/**
 * Class Calculator
 * @package Phactor\App
 * Calculating Pi via Monte-Carlo method
 * @see http://www.eveandersson.com/pi/monte-carlo-circle
 */
class Calculator
{
    protected $r;
    protected $rSquare;
    protected $in    = 0;
    protected $total = 0;

    /**
     * Constructor
     * @param integer $r radius
     */
    public function __construct($r)
    {
        $this->r       = $r;
        $this->rSquare = $r * $r;
    }

    /**
     * Generates random point and check if it inside the circle
     */
    public function step()
    {
        $x = rand(0, $this->r);
        $y = rand(0, $this->r);
        if ((($x * $x) + ($y * $y)) < $this->rSquare) $this->in++;
        $this->total++;
    }

    /**
     * Calculates Pi
     * @return float
     */
    public function getResult()
    {
        return 4 * ($this->in / $this->total);
    }

    /**
     * Returns number of generated points
     * @return integer[]
     */
    public function getCounters()
    {
        return [$this->in, $this->total];
    }
}