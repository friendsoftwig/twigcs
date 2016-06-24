<?php

namespace Allocine\TwigLinter\Rule;

use Allocine\TwigLinter\Validator\Violation;

class AbstractRule
{
    /**
     * @var integer
     */
    protected $severity;

    /**
     * @var Violations[]
     */
    protected $violations;

    /**
     * @param integer $severity
     */
    public function __construct($severity)
    {
        $this->severity = $severity;
    }

    public function reset()
    {
        $this->violations = [];
    }

    /**
     * @param string $filename
     * @param integer $line
     * @param string $reason
     */
    public function addViolation($filename, $line, $column, $reason)
    {
        $this->violations[] = new Violation($filename, $line, $column, $reason, $this->severity, get_called_class());
    }
}
