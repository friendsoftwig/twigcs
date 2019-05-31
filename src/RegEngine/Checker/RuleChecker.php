<?php

namespace Allocine\Twigcs\RegEngine\Checker;

class RuleChecker
{
    /**
     * @var array
     */
    private $rules;

    /**
     * @var array
     */
    private $errors;

    /**
     * @var bool
     */
    private $explain;

    /**
     * @var array
     */
    private $log;

    public function __construct(array $rulesets)
    {
        $this->rules = [];
        $this->errors = [];
        $this->explain = false;
        $this->log = [];

        foreach ($rulesets as $key => $ruleset) {
            foreach ($ruleset as $rule) {
                $this->rules[$key][] = $this->compute($rule[0], $rule[1], $rule[2]);
            }
        }
    }

    public function explain()
    {
        $this->explain = true;
    }

    public function getLog()
    {
        return $this->log;
    }

    private function compute(array $vars, string $rule, callable $callback)
    {
        $regex = '';
        $types = [];

        foreach (str_split($rule) as $char) {
            if ($vars[$char] ?? false) {
                $regex .= '('.$vars[$char].')';
                $types[] = $char;
            } else {
                $regex .= $char;
            }
        }

        return new Regex($rule, '#^'.$regex.'$#', $types, $callback);
    }

    public function collectError(string $error, $matcher)
    {
        $this->errors[] = new RuleError($error, $matcher->getOffset(), $matcher->getSource());
    }

    public function subCheck(string $ruleset, Capture $capture)
    {
        return $this->check($ruleset, $capture->getText(), $capture->getOffset());
    }

    public function check(string $ruleset, string $text, int $offset = 0)
    {
        foreach ($this->rules[$ruleset] as $rule) {
            if ($matches = $rule->match($text)) {
                $grouped = [];
                foreach ($matches as $match) {
                    $match->increaseOffset($offset);
                    $grouped[$match->getType()][] = $match;
                }

                if ($this->explain) {
                    $this->log[] = sprintf("%s matched by #%s#.\n", $text, $rule->getRule());
                }

                return call_user_func($rule->getCallback(), $this, $grouped);
            }
        }

        if ($this->explain) {
            $this->log[] = sprintf("%s did not match.\n", $text);
        }
    }

    public function getRules(): array
    {
        return $this->rules;
    }

    public function setRules(array $rules): self
    {
        $this->rules = $rules;

        return $this;
    }

    public function getErrors(): array
    {
        return $this->errors;
    }

    public function setErrors(array $errors): self
    {
        $this->errors = $errors;

        return $this;
    }

    public function isExplain(): bool
    {
        return $this->explain;
    }

    public function setExplain(bool $explain): self
    {
        $this->explain = $explain;

        return $this;
    }

    public function setLog(array $log): self
    {
        $this->log = $log;

        return $this;
    }
}
