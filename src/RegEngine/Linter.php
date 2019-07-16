<?php

namespace FriendsOfTwig\Twigcs\RegEngine;

use FriendsOfTwig\Twigcs\RegEngine\Checker\Report;
use FriendsOfTwig\Twigcs\RegEngine\Checker\RuleChecker;
use FriendsOfTwig\Twigcs\RegEngine\Extractor\ArrayExtractor;
use FriendsOfTwig\Twigcs\RegEngine\Extractor\HashExtractor;
use FriendsOfTwig\Twigcs\RegEngine\Extractor\ParenthesesExtractor;
use FriendsOfTwig\Twigcs\RegEngine\Extractor\TernaryExtractor;
use FriendsOfTwig\Twigcs\RegEngine\Sanitizer\StringSanitizer;

class Linter
{
    /**
     * @var ArrayExtractor
     */
    private $arrayExtractor;

    /**
     * @var HashExtractor
     */
    private $hashExtractor;

    /**
     * @var ParenthesesExtractor
     */
    private $parenthesesExtractor;

    /**
     * @var RuleChecker
     */
    private $ruleChecker;

    /**
     * @var StringSanitizer
     */
    private $stringSanitizer;

    /**
     * @var TernaryExtractor
     */
    private $ternaryExtractor;

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
