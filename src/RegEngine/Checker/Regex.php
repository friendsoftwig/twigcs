<?php

namespace FriendsOfTwig\Twigcs\RegEngine\Checker;

class Regex
{
    private string $rule;

    private string $regex;

    private array $captureTypes;

    /**
     * @var callable
     */
    private $callback;

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
                $captures[] = new Capture($this->captureTypes[$key], $match[0], $match[1], $this);
            }
        }

        return $captures;
    }

    public function getRule(): string
    {
        return $this->rule;
    }

    public function setRule(string $rule): self
    {
        $this->rule = $rule;

        return $this;
    }

    public function getRegex(): string
    {
        return $this->regex;
    }

    public function setRegex(string $regex): self
    {
        $this->regex = $regex;

        return $this;
    }

    public function getCaptureTypes(): array
    {
        return $this->captureTypes;
    }

    public function setCaptureTypes(array $captureTypes): self
    {
        $this->captureTypes = $captureTypes;

        return $this;
    }

    public function getCallback(): callable
    {
        return $this->callback;
    }

    public function setCallback(callable $callback): self
    {
        $this->callback = $callback;

        return $this;
    }
}
