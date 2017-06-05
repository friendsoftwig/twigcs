<?php

namespace Allocine\TwigLinter\Ruleset;

use Allocine\TwigLinter\Rule;
use Allocine\TwigLinter\Validator\Violation;
use Allocine\TwigLinter\Whitelist\TokenWhitelist;

/**
 * The official twigcs ruleset, based on http://twig.sensiolabs.org/doc/coding_standards.html
 *
 * @author Tristan Maindron <tmaindron@gmail.com>
 */
class Official implements RulesetInterface
{
    /**
     * {@inheritdoc}
     */
    public function getRules()
    {
        return [
            new Rule\DelimiterSpacing(Violation::SEVERITY_ERROR, 1),
            new Rule\ParenthesisSpacing(Violation::SEVERITY_ERROR, 0, 1),
            new Rule\ArraySeparatorSpacing(Violation::SEVERITY_ERROR, 0, 1),
            new Rule\HashSeparatorSpacing(Violation::SEVERITY_ERROR, 0, 1),
            new Rule\OperatorSpacing(Violation::SEVERITY_ERROR, [
                '==', '!=', '<', '>', '>=', '<=',
                '+', '-', '/', '*', '%', '//', '**',
                'not', 'and', 'or',
                '~',
                'is', 'in'
            ], 1),
            new Rule\PunctuationSpacing(
                Violation::SEVERITY_ERROR,
                ['|', '.', '..', '[', ']'],
                0,
                new TokenWhitelist([
                    ')',
                    \Twig_Token::NAME_TYPE,
                    \Twig_Token::NUMBER_TYPE,
                    \Twig_Token::STRING_TYPE
                ], [2])
            ),
            new Rule\TernarySpacing(Violation::SEVERITY_ERROR, 1),
            new Rule\LowerCaseVariable(Violation::SEVERITY_ERROR),
            new Rule\UnusedVariable(Violation::SEVERITY_WARNING),
            new Rule\UnusedMacro(Violation::SEVERITY_WARNING),
            new Rule\SliceShorthandSpacing(Violation::SEVERITY_ERROR),
            new Rule\TrailingSpace(Violation::SEVERITY_ERROR),
        ];
    }
}
