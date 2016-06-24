<?php

namespace Allocine\TwigLinter\Rule;

use Allocine\TwigLinter\Lexer;
use Allocine\TwigLinter\Validator\Violation;

/**
 * This rule enforces spacing inside ternaries.
 *
 * By default, the following are considered valids : condition ? "val1" : "val2"
 *
 * @author Tristan Maindron <tmaindron@gmail.com>
 */
class TernarySpacing extends AbstractSpacingRule implements RuleInterface
{
    /**
     * @var integer
     */
    private $spacing;

    /**
     * @param integer $severity
     * @param integer $spacing
     */
    public function __construct($severity, $spacing = 1)
    {
        parent::__construct($severity);

        $this->spacing = $spacing;
    }

    /**
     * {@inheritdoc}
     */
    public function check(\Twig_TokenStream $tokens)
    {
        $this->violations = [];
        $ternaryDepth = 0;

        $closingTokens = [];

        while (!$tokens->isEOF()) {
            $token = $tokens->getCurrent();

            if ($token->getValue() === '?' && $token->getType() === \Twig_Token::PUNCTUATION_TYPE) {
                // Memorize where is the closing ":" punctuation to validate spacing later.
                $closingTokens[] = $this->seekTernaryElse($tokens);

                $next = $tokens->look(Lexer::NEXT_TOKEN);

                if ($next->getValue() !== ':') {
                    $this->assertSpacing($tokens, Lexer::NEXT_TOKEN, $this->spacing);
                }

                $this->assertSpacing($tokens, Lexer::PREVIOUS_TOKEN, $this->spacing);
            }

            if (in_array($token, $closingTokens)) {
                $previous = $tokens->look(Lexer::PREVIOUS_TOKEN);

                if ($previous->getValue() !== '?') {
                    $this->assertSpacing($tokens, Lexer::PREVIOUS_TOKEN, $this->spacing);
                }

                $this->assertSpacing($tokens, Lexer::NEXT_TOKEN, $this->spacing);
            }

            $tokens->next();
        }

        return $this->violations;
    }

    /**
     * @param \Twig_TokenStream $tokens
     *
     * @return \Twig_Token
     */
    protected function seekTernaryElse(\Twig_TokenStream $tokens)
    {
        $i = 1;
        $depth = 0;
        $found = false;
        $token = null;

        while ($depth || !$found) {
            $token = $tokens->look($i);

            if ($token->getType() === \Twig_Token::VAR_END_TYPE || $token->getType() === \Twig_Token::INTERPOLATION_END_TYPE) {
                return;
            }

            // End of hash value means end of short ternary (eg. "foo ? bar" syntax)
            if ($token->getType() === \Twig_Token::PUNCTUATION_TYPE && $token->getValue() === ',') {
                return;
            }

            if ($token->getType() === \Twig_Token::PUNCTUATION_TYPE && in_array($token->getValue(), ['(', '[', '{'])) {
                $depth++;
            }

            if ($depth && $token->getType() === \Twig_Token::PUNCTUATION_TYPE && in_array($token->getValue(), [')', ']', '}'])) {
                $depth--;
            }

            $found = $token->getType() === \Twig_Token::PUNCTUATION_TYPE && $token->getValue() === ':';
            $i++;
        }

        return $token;
    }
}
