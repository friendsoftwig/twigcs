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

        var_dump($rootNode);

        $nodes = $rootNode->flatten();
        $errors = [];

        foreach ($nodes as $node) {
            $this->ruleChecker->check('expr', $node->expr, $node->offset);
        }

        return $this->ruleChecker->errors;
    }
}
