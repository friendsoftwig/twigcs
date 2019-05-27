<?php

namespace Allocine\Twigcs\Experimental;

class Handler
{
    private $callback;

    private $parent;

    public static function create()
    {
        return new Handler();
    }

    public function __construct(Handler $parent = null)
    {
        $this->parent = $parent;
    }

    public function debug()
    {
        return $this->attach(function (RuleChecker $ruleChecker, array $captures) {
            var_dump($captures);
        });
    }

    public function enforceSpaceOrLineBreak(string $type, int $size, string $message)
    {
        return $this->attach(function (RuleChecker $ruleChecker, array $captures) use ($type, $size, $message) {
            foreach ($captures[$type] as $capture) {
                if ("\n" !== ($capture->text[0] ?? '') && strlen($capture->text) != $size) {
                    $ruleChecker->collectError($message, $capture);
                }
            }
        });
    }

    public function enforceSize(string $type, int $size, string $message): self
    {
        return $this->attach(function (RuleChecker $ruleChecker, array $captures) use ($type, $size, $message) {
            foreach ($captures[$type] as $capture) {
                if (strlen($capture->text) != $size) {
                    $ruleChecker->collectError($message, $capture);
                }
            }
        });
    }

    public function delegate(string $type, string $ruleset): self
    {
        return $this->attach(function (RuleChecker $ruleChecker, array $captures) use ($type, $ruleset) {
            foreach ($captures[$type] as $subExpr) {
                $ruleChecker->subCheck($ruleset, $subExpr);
            }
        });
    }

    public function __invoke(RuleChecker $ruleChecker, array $captures)
    {
        call_user_func($this->callback, $ruleChecker, $captures);

        if ($this->parent) {
            call_user_func($this->parent, $ruleChecker, $captures);
        }
    }

    public function attach(callable $callback)
    {
        if (!$this->callback) {
            $this->callback = $callback;

            return $this;
        }

        $child = new Handler($this);
        $child->attach($callback);

        return $child;
    }
}
