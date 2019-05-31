<?php

namespace Allocine\Twigcs\RegEngine\Checker;

class Handler
{
    /**
     * @var callable
     */
    private $callback;

    /**
     * @var Handler
     */
    private $parent;

    public static function create(): self
    {
        return new self();
    }

    public function __construct(self $parent = null)
    {
        $this->parent = $parent;
    }

    public function debug(): self
    {
        return $this->attach(function (RuleChecker $ruleChecker, array $captures) {
            var_dump($captures);
        });
    }

    public function enforceSpaceOrLineBreak(string $type, int $size, string $message): self
    {
        return $this->attach(function (RuleChecker $ruleChecker, array $captures) use ($type, $size, $message) {
            foreach ($captures[$type] as $capture) {
                $text = $capture->getText();

                if ("\n" !== ($text[0] ?? '') && strlen($text) !== $size) {
                    $ruleChecker->collectError($message, $capture);
                }
            }
        });
    }

    public function enforceSize(string $type, int $size, string $message): self
    {
        return $this->attach(function (RuleChecker $ruleChecker, array $captures) use ($type, $size, $message) {
            foreach ($captures[$type] as $capture) {
                if (strlen($capture->getText()) !== $size) {
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

    public function attach(callable $callback): self
    {
        if (!$this->callback) {
            $this->callback = $callback;

            return $this;
        }

        $child = new self($this);
        $child->attach($callback);

        return $child;
    }
}
