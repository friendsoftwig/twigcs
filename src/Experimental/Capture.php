<?php

namespace Allocine\Twigcs\Experimental;

class Capture
{
    public $type;
    public $text;
    public $offset;

    public function __construct(string $type, string $text, int $offset)
    {
        $this->type = $type;
        $this->text = $text;
        $this->offset = $offset;
    }
}
