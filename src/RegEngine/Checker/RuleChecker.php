<?php

namespace FriendsOfTwig\Twigcs\RegEngine\Checker;

function split($str, $len = 1)
{
    $arr = [];
    $length = mb_strlen($str, 'UTF-8');

    for ($i = 0; $i < $length; $i += $len) {
        $arr[] = mb_substr($str, $i, $len, 'UTF-8');
    }

    return $arr;
}

class RuleChecker
{
    private array $rules;

    private bool $explain;

    private array $unrecognizedExpressions;

    private array $log;

    public function __construct(array $rulesets)
    {
        $this->rules = [];
        $this->explain = false;
        $this->log = [];
        $this->unrecognizedExpressions = [];

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

    public function subCheck(Report $report, string $ruleset, Capture $capture)
    {
        $this->check($report, $ruleset, $capture->getText(), $capture->getOffsetsMap(), $capture->getOffset());
    }

    public function check(Report $report, string $ruleset, string $text, array $offsetsMap, int $offset = 0)
    {
        foreach ($this->rules[$ruleset] as $rule) {
            if ($matches = $rule->match($text)) {
                $grouped = [];

                foreach ($matches as $match) {
                    $match->increaseOffset($offset);
                    $match->setOffsetsMap($offsetsMap);
                    $grouped[$match->getType()][] = $match;
                }

                if ($this->explain) {
                    $this->log[] = sprintf("%s matched by #%s#.\n", $text, $rule->getRule());
                }

                return call_user_func($rule->getCallback(), $this, $report, $grouped);
            }
        }

        $report->addUnrecognizedExpression($text);

        if ($this->explain) {
            $this->log[] = sprintf("%s did not match in ruleset \"%s\".\n", $text, $ruleset);
        }

        return [];
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

    public function getUnrecognizedExpressions(): array
    {
        return $this->unrecognizedExpressions;
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

    private function compute(array $vars, string $rule, callable $callback)
    {
        $regex = '';
        $types = [];

        foreach (split($rule) as $char) {
            if ($vars[$char] ?? false) {
                $regex .= '('.$vars[$char].')';
                $types[] = $char;
            } else {
                $regex .= $char;
            }
        }

        return new Regex($rule, '#^'.$regex.'$#', $types, $callback);
    }
}
