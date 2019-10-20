<?php

namespace Allocine\Twigcs\Rule;

use Allocine\Twigcs\Lexer;
use Allocine\Twigcs\Validator\Violation;

/**
 * This rule enforces spacing in arrays. Concerned spaces are the ones surrounding
 * the "," punctuations.
 *
 * By default, the following is considered valid : [1, 2, 3]
 *
 * @author Tristan Maindron <tmaindron@gmail.com>
 */
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
    public function check(\Twig\TokenStream $tokens)
    {
        $this->violations = [];
        $arrayDepth = 0;
        $skip = false;

        while (!$tokens->isEOF()) {
            $token = $tokens->getCurrent();

            if ($token->getType() === \Twig\Token::PUNCTUATION_TYPE && $token->getValue() === '(') {
                $skip = true; // Ignore function arguments or embedded expressions (eg. [ func(1, 2) ]  )
                              // This prevents this rule from having influence on arguments spacing.
            }

            if ($token->getType() === \Twig\Token::PUNCTUATION_TYPE && $token->getValue() === '?') {
                $skip = true;
            }

            if ($token->getValue() === '[' && $token->getType() === \Twig\Token::PUNCTUATION_TYPE) {
                if ($tokens->look(-1)->getType() === \Twig\Token::NAME_TYPE) {
                    break; // This is not an array declaration, but an array access ( eg. : foo[1] )
                }

                $arrayDepth++;
                $skip = false; // We entered a new array or hash, from now on do not skip anything.
            }

            if ($token->getValue() === ']' && $token->getType() === \Twig\Token::PUNCTUATION_TYPE) {
                $arrayDepth--;
            }

            if (!$skip && $arrayDepth > 0 && $token->getType() === \Twig\Token::PUNCTUATION_TYPE && $token->getValue() === ',') {
                $this->assertSpacing($tokens, Lexer::NEXT_TOKEN, $this->spaceAfter);
                $this->assertSpacing($tokens, Lexer::PREVIOUS_TOKEN, $this->spaceBefore, false);
            }

            $tokens->next();
        }

        return $this->violations;
    }
}
