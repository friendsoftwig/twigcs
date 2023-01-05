<?php

namespace FriendsOfTwig\Twigcs\RegEngine;

use FriendsOfTwig\Twigcs\RegEngine\Checker\Report;
use FriendsOfTwig\Twigcs\RegEngine\Checker\RuleChecker;
use FriendsOfTwig\Twigcs\RegEngine\Sanitizer\StringSanitizer;

class Linter
{
    private RuleChecker $ruleChecker;

    private StringSanitizer $stringSanitizer;

    public function __construct(array $ruleset)
    {
        $this->ruleChecker = new RuleChecker($ruleset);
        $this->stringSanitizer = new StringSanitizer();
    }

    public function explain()
    {
        $this->ruleChecker->explain();
    }

    public function lint(string $expr): Report
    {
        $report = new Report();

        $expr = $this->stringSanitizer->sanitize($expr);

        $nodes = ExpressionNode::fromString($expr)->flatten();

        foreach ($nodes as $node) {
            $this->ruleChecker->check($report, $node->getType(), $node->getExpr(), $node->getOffsetsMap());
        }

        return $report;
    }
}
