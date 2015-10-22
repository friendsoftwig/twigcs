<?php

namespace Allocine\TwigLinter\Validator;

class Violation
{
    const SEVERITY_NOTICE  = 1;
    const SEVERITY_WARNING = 2;
    const SEVERITY_FATAL   = 3;

    /**
     * @var integer
     */
    private $line;

    /**
     * @var string
     */
    private $reason;

    /**
     * @var string
     */
    private $filename;

    /**
     * @var integer
     */
    private $severity;

    /**
     * @param string  $filename
     * @param integer $line
     * @param string  $reason
     */
    public function __construct($filename, $line, $reason, $severity = Violation::SEVERITY_FATAL)
    {
        $this->filename = $filename;
        $this->line     = $line;
        $this->reason   = $reason;
        $this->severity = $severity;
    }

    /**
     * @return integer
     */
    public function getLine()
    {
        return $this->line;
    }

    /**
     * @return string
     */
    public function getReason()
    {
        return $this->reason;
    }

    /**
     * @return string
     */
    public function getFilename()
    {
        return $this->filename;
    }

    /**
     * @return integer
     */
    public function getSeverity()
    {
        return $this->severity;
    }

    /**
     * @return string
     */
    public function getSeverityAsString()
    {
        return ['NOTICE', 'WARNING', 'FATAL'][$this->severity - 1];
    }
}
