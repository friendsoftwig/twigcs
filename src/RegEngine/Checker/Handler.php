<?php

namespace FriendsOfTwig\Twigcs\RegEngine\Checker;

class Handler
{
    /**
     * @var callable
     */
    private $callback;

    private ?Handler $parent;

    public function __construct(self $parent = null)
    {
        $this->parent = $parent;
    }

    public function __invoke(RuleChecker $ruleChecker, Report $report, array $captures)
    {
        $errors = call_user_func($this->callback, $ruleChecker, $report, $captures);

        if (!$this->parent) {
            return;
        }

        call_user_func($this->parent, $ruleChecker, $report, $captures);
    }

    public static function create(): self
    {
        return new self();
    }

    public function noop(): self
    {
        return $this->attach(function () {
        });
    }

    public function enforceSpaceOrLineBreak(string $type, int $size, string $message): self
    {
        return $this->attach(function (RuleChecker $ruleChecker, Report $report, array $captures) use ($type, $size, $message) {
            foreach ($captures[$type] ?? [] as $capture) {
                $text = $capture->getText();

                if ("\n" !== ($text[0] ?? '') && strlen($text) !== $size) {
                    $replacedMessage = str_replace('%quantity%', $size, $message);
                    $replacedMessage = str_replace('(s)', $size > 1 ? 's' : '', $replacedMessage);

                    $report->addError(new RuleError($replacedMessage, $capture->getRealOffset(), $capture->getSource()));
                }
            }
        });
    }

    public function enforceSize(string $type, int $size, string $message): self
    {
        return $this->attach(function (RuleChecker $ruleChecker, Report $report, array $captures) use ($type, $size, $message) {
            foreach ($captures[$type] ?? [] as $capture) {
                if (strlen($capture->getText()) !== $size) {
                    $replacedMessage = str_replace('%quantity%', $size, $message);
                    $replacedMessage = str_replace('(s)', $size > 1 ? 's' : '', $replacedMessage);

                    $report->addError(new RuleError($replacedMessage, $capture->getRealOffset(), $capture->getSource()));
                }
            }
        });
    }

    public function delegate(string $type, string $ruleset): self
    {
        return $this->attach(function (RuleChecker $ruleChecker, Report $report, array $captures) use ($type, $ruleset) {
            foreach ($captures[$type] ?? [] as $subExpr) {
                $ruleChecker->subCheck($report, $ruleset, $subExpr);
            }
        });
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
