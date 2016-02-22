<?php

namespace Allocine\TwigLinter\Rule;

use Allocine\TwigLinter\Lexer;
use Allocine\TwigLinter\Validator\Violation;

class ArraySeparatorSpacing extends AbstractSpacingRule implements RuleInterface
{
    /**
     * @var integer
     */
    private $spaceBefore;

    /**
     * @var integer
     */
    private $spaceAfter;

    /**
     * @param integer $spaceBefore
     * @param integer $spaceAfter
     */
    public function __construct($severity, $spaceBefore = 0, $spaceAfter = 1)
    {
        parent::__construct($severity);

        $this->spaceBefore = $spaceBefore;
        $this->spaceAfter  = $spaceAfter;
    }

    /**
     * {@inheritdoc}
     */
    public function check(\Twig_TokenStream $tokens)
    {
        $this->violations = [];
        $arrayDepth = 0;

        while (!$tokens->isEOF()) {
            $token = $tokens->getCurrent();

            if (in_array($token->getValue(), ['[', '{'])) {
                $arrayDepth++;
            }

            if (in_array($token->getValue(), [']', '}'])) {
                $arrayDepth--;
            }

            if ($arrayDepth > 0 && in_array($token->getValue(), [':', ','], true)) {
                $this->assertSpacing($tokens, Lexer::NEXT_TOKEN, $this->spaceAfter);
                $this->assertSpacing($tokens, Lexer::PREVIOUS_TOKEN, $this->spaceBefore, false);
            }

            $tokens->next();
        }

        return $this->violations;
    }
}
