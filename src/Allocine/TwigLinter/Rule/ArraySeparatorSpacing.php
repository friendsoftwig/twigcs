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
        $skip = false;

        while (!$tokens->isEOF()) {
            $token = $tokens->getCurrent();

            if ($token->getValue() === '(') {
                $skip = true; // Ignore function arguments or embedded expressions (eg. [ func(1, 2) ]  )
                              // This prevents this rule from having influence on arguments spacing.
            }

            if (in_array($token->getValue(), ['[', '{'])) {
                if ($tokens->look(-1)->getType() === \Twig_Token::NAME_TYPE) {
                    break; // This is not an array declaration, but an array access ( eg. : foo[1] )
                }

                $arrayDepth++;
                $skip = false; // We entered a new array or hash, from now on do not skip anything.
            }

            if (in_array($token->getValue(), [']', '}'])) {
                $arrayDepth--;
            }

            if (!$skip && $arrayDepth > 0 && $token->getType() === \Twig_Token::PUNCTUATION_TYPE && in_array($token->getValue(), [':', ','], true)) {
                $this->assertSpacing($tokens, Lexer::NEXT_TOKEN, $this->spaceAfter);
                $this->assertSpacing($tokens, Lexer::PREVIOUS_TOKEN, $this->spaceBefore, false);
            }

            $tokens->next();
        }

        return $this->violations;
    }
}
