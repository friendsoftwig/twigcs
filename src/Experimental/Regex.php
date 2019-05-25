<?php

namespace Allocine\Twigcs\Experimental;

class Regex
{
    public $rule;
    public $regex;
    public $captureTypes;
    public $callback;

    public function __construct(string $rule, string $regex, array $captureTypes, callable $callback)
    {
        $this->rule = $rule;
        $this->regex = $regex;
        $this->captureTypes = $captureTypes;
        $this->callback = $callback;
    }

    public function match(string $text)
    {
        $captures = [];

        if (preg_match($this->regex, $text, $matches, \PREG_OFFSET_CAPTURE)) {
            $whole = array_shift($matches);
            $captures = [];
            foreach (array_values($matches) as $key => $match) {
                $captures[]= new Capture($this->captureTypes[$key], $match[0], $match[1], $this);
            }
        }

        return $captures;
    }
}
