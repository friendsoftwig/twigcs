<?php

namespace Allocine\Twigcs\Rule;

use Allocine\Twigcs\Lexer;
use Allocine\Twigcs\Token;
use Allocine\Twigcs\Validator\Violation;

/**
 * This rule enforces spacing in hashes. Concerned spaces are the ones surrounding
 * the ":" and the "," punctuations.
 *
 * By default, the following is considered valid : {prop1: value1, prop2: value2}
 *
 * @author Tristan Maindron <tmaindron@gmail.com>
 */
class HashSeparatorSpacing extends AbstractSpacingRule implements RuleInterface
{
    /**
     * @var int
     */
    private $spaceBefore;

    /**
     * @var int
     */
    private $spaceAfter;

    /**
     * @param int $severity
     * @param int $spaceBefore
     * @param int $spaceAfter
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

            if ($token->getValue() === '{' && $token->getType() === \Twig\Token::PUNCTUATION_TYPE) {
                if ($tokens->look(-1)->getType() === \Twig\Token::NAME_TYPE) {
                    break; // This is not an array declaration, but an array access ( eg. : foo[1] )
                }

                $arrayDepth++;
                $skip = false; // We entered a new array or hash, from now on do not skip anything.
            }

            if ($token->getValue() === '}' && $token->getType() === \Twig\Token::PUNCTUATION_TYPE) {
                $arrayDepth--;
            }

            if (!$skip && $arrayDepth > 0 && $token->getType() === \Twig\Token::PUNCTUATION_TYPE && $token->getValue() === ',') {
                $this->assertSpacing($tokens, Lexer::NEXT_TOKEN, $this->spaceAfter);
                $this->assertSpacing($tokens, Lexer::PREVIOUS_TOKEN, $this->spaceBefore, false);
            }

            if ($arrayDepth > 0 && in_array($this->getPreviousSignificantToken($tokens)->getType(), [\Twig\Token::NAME_TYPE, \Twig\Token::STRING_TYPE])) {
                if (!$skip && $token->getType() === \Twig\Token::PUNCTUATION_TYPE && $token->getValue() === ':') {
                    $this->assertSpacing($tokens, Lexer::NEXT_TOKEN, $this->spaceAfter);
                    $this->assertSpacing($tokens, Lexer::PREVIOUS_TOKEN, $this->spaceBefore, false);
                }
            }

            $tokens->next();
        }

        return $this->violations;
    }
}
