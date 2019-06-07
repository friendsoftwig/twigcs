<?php

namespace Allocine\Twigcs\RegEngine;

use Allocine\Twigcs\RegEngine\Checker\Report;
use Allocine\Twigcs\RegEngine\Checker\RuleChecker;
use Allocine\Twigcs\RegEngine\Extractor\ArrayExtractor;
use Allocine\Twigcs\RegEngine\Extractor\HashExtractor;
use Allocine\Twigcs\RegEngine\Extractor\ParenthesesExtractor;
use Allocine\Twigcs\RegEngine\Extractor\TernaryExtractor;
use Allocine\Twigcs\RegEngine\Sanitizer\StringSanitizer;

class Linter
{
    public function __construct(array $ruleset)
    {
        $this->ruleChecker = new RuleChecker($ruleset);
        $this->stringSanitizer = new StringSanitizer();
        $this->parenthesesExtractor = new ParenthesesExtractor();
        $this->hashExtractor = new HashExtractor();
        $this->arrayExtractor = new ArrayExtractor();
        $this->ternaryExtractor = new TernaryExtractor();
    }

    public function explain()
    {
        $this->ruleChecker->explain();
    }

    public function lint(string $expr): Report
    {
        $report = new Report();

        $expr = $this->stringSanitizer->sanitize($expr);

        $rootNode = new ExpressionNode($expr, 0);
        $this->parenthesesExtractor->extract($rootNode);
        $this->hashExtractor->extract($rootNode);
        $this->arrayExtractor->extract($rootNode);
        $this->ternaryExtractor->extract($rootNode);

        $nodes = $rootNode->flatten();

        foreach ($nodes as $node) {
            $this->ruleChecker->check($report, $node->getType(), $node->getExpr(), $node->getOffset());
        }

        return $report;
    }
}
