<?php

namespace FriendsOfTwig\Twigcs\Validator;

class Violation
{
    public const SEVERITY_INFO = 1;
    public const SEVERITY_WARNING = 2;
    public const SEVERITY_ERROR = 3;
    public const SEVERITY_IGNORE = 4;

    /**
     * @var int
     */
    private $line;

    /**
     * @var int
     */
    private $column;

    /**
     * @var string
     */
    private $reason;

    /**
     * @var string
     */
    private $filename;

    /**
     * @var int
     */
    private $severity;

    /**
     * @var string
     */
    private $source;

    /**
     * @param int    $severity
     * @param string $source
     */
    public function __construct($filename, $line, $column, $reason, $severity = self::SEVERITY_ERROR, $source = 'unknown')
    {
        $this->filename = $filename;
        $this->line = $line;
        $this->column = $column;
        $this->reason = $reason;
        $this->severity = $severity;
        $this->source = $source;
    }

    /**
     * @return int
     */
    public function getLine()
    {
        return $this->line;
    }

    /**
     * @return int
     */
    public function getColumn()
    {
        return $this->column;
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
     * @return int
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
        return ['IGNORE', 'INFO', 'WARNING', 'ERROR'][$this->severity];
    }

    /**
     * @return string
     */
    public function getSource()
    {
        return $this->source;
    }
}
