<?php

namespace Allocine\Twigcs\Experimental;

class RuleError
{
    /**
     * @var int
     */
    public $column;

    /**
     * @var string
     */
    public $reason;

    /**
     * @var Regex
     */
    public $source;

    public function __construct(string $reason, int $column, Regex $source)
    {
        $this->column = $column;
        $this->reason = $reason;
        $this->source = $source;
    }
}
