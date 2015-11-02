<?php

namespace Allocine\TwigLinter\Ruleset;

use Allocine\TwigLinter\Rule;
use Allocine\TwigLinter\Validator\Violation;
use Allocine\TwigLinter\Whistelist\TokenWhitelist;

class Official implements RulesetInterface
{
    /**
     * {@inheritdoc}
     */
    public function getRules()
    {
        return [
            new Rule\DelimiterSpacing(Violation::SEVERITY_WARNING, 1),
            new Rule\ParenthesisSpacing(Violation::SEVERITY_WARNING, 0),
            new Rule\ArraySeparatorSpacing(Violation::SEVERITY_WARNING, 0, 1),
            new Rule\OperatorSpacing(Violation::SEVERITY_WARNING, [
                '==', '!=', '<', '>', '>=', '<=',
                '+', '-', '/', '*', '%', '//', '**',
                'not', 'and', 'or',
                '~',
                'is', 'in'
            ], 1),
            new Rule\PunctuationSpacing(
                Violation::SEVERITY_WARNING,
                ['|', '.', '..', '[', ']'],
                0,
                new TokenWhitelist([
                    ')',
                    \Twig_Token::NAME_TYPE,
                    \Twig_Token::NUMBER_TYPE,
                    \Twig_Token::STRING_TYPE
                ], [2])
            ),
            new Rule\TernarySpacing(Violation::SEVERITY_WARNING, 1),
            new Rule\LowerCaseVariable(Violation::SEVERITY_WARNING),
            new Rule\UnusedVariable(Violation::SEVERITY_NOTICE),
            new Rule\UnusedMacro(Violation::SEVERITY_NOTICE)
        ];
    }
}
