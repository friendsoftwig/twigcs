<?php

namespace Allocine\Twigcs\Experimental;

class Linter
{
    public function __construct(array $ruleset)
    {
        $this->ruleChecker = new RuleChecker($ruleset);
        $this->stringSanitizer = new StringSanitizer();
        $this->parenthesesExtractor = new ParenthesesExtractor();
        $this->hashExtractor = new HashExtractor();
        $this->arrayExtractor = new ArrayExtractor();
    }

    public function explain()
    {
        $this->ruleChecker->explain();
    }

    public function lint(string $expr): array
    {
        $expr = $this->stringSanitizer->sanitize($expr);

        $rootNode = new ExpressionNode($expr, 0);
        $this->parenthesesExtractor->extract($rootNode);
        $this->hashExtractor->extract($rootNode);
        $this->arrayExtractor->extract($rootNode);

        $nodes = $rootNode->flatten();
        $errors = [];

        foreach ($nodes as $node) {
            $this->ruleChecker->check($node->type, $node->expr, $node->offset);
        }

        return $this->ruleChecker->errors;
    }
}
