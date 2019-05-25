<?php

namespace Allocine\Twigcs\Experimental;

class Capture
{
    public $type;
    public $text;
    public $offset;
    public $source;

    public function __construct(string $type, string $text, int $offset, Regex $source)
    {
        $this->type = $type;
        $this->text = $text;
        $this->offset = $offset;
        $this->source = $source;
    }
}
