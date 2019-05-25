<?php

namespace Allocine\Twigcs\Experimental;

class RuleChecker
{
    public $rules;

    public $errors;

    private $explain;

    public function __construct(array $rulesets)
    {
        $this->rules = [];
        $this->errors = [];
        $this->explain = false;

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

    private function compute(array $vars, string $rule, callable $callback)
    {
        $regex = '';
        $types = [];

        foreach (str_split($rule) as $char) {
            if ($vars[$char] ?? false) {
                $regex .= '('.$vars[$char].')';
                $types[]= $char;
            } else {
                $regex.=$char;
            }
        }

        return new Regex($rule, '#^'.$regex.'$#', $types, $callback);
    }

    public function collectError(string $error, $matcher)
    {
        $this->errors[]= new RuleError($error, $matcher->offset, $matcher->source);
    }

    public function subCheck(string $ruleset, Capture $capture)
    {
        return $this->check($ruleset, $capture->text, $capture->offset);
    }

    public function check(string $ruleset, string $text, int $offset = 0)
    {
        foreach ($this->rules[$ruleset] as $rule) {
            if ($matches = $rule->match($text)) {
                $grouped = [];
                foreach ($matches as $match) {
                    $match->offset += $offset;
                    $grouped[$match->type][] = $match;
                }

                if ($this->explain) {
                    echo "$text matched by #$rule->rule#.\n";
                }

                return call_user_func($rule->callback, $this, $grouped);
            }
        }

        if ($this->explain) {
            echo "$text did not match.\n";
        }
    }
}
