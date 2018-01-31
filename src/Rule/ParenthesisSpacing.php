<?php

namespace Allocine\Twigcs\Rule;

use Allocine\Twigcs\Lexer;
use Allocine\Twigcs\Token;

/**
 * This rule enforces spacing around parenthesis. The expected spacing
 * can be tweaked in case of control structure.
 *
 * By default, the following are considered valids :
 * - {{ (1 + 2) }}
 * - {{ func(1) }}
 * - {% if (1 > 2) %}
 *
 * @author Tristan Maindron <tmaindron@gmail.com>
 */
class ParenthesisSpacing extends AbstractSpacingRule
{
    /**
     * @var integer
     */
    private $spacing;

    /**
     * @var integer
     */
    private $controlStructureSpacing;

    /**
     * @param integer $severity
     * @param integer $spacing
     */
    public function __construct($severity, $spacing = 0, $controlStructureSpacing = 1)
    {
        parent::__construct($severity);

        $this->spacing = $spacing;
        $this->controlStructureSpacing = $controlStructureSpacing;
    }

    /**
     * {@inheritdoc}
     */
    public function check(\Twig_TokenStream $tokens)
    {
        $this->violations = [];

        while (!$tokens->isEOF()) {
            $token = $tokens->getCurrent();

            if ($token->getValue() === '(' && $token->getType() === \Twig_Token::PUNCTUATION_TYPE) {
                $this->assertSpacing($tokens, Lexer::NEXT_TOKEN, $this->spacing);

                // Space allowed if previous token is not a function name.
                // Space also allowed in case of control structure
                if ($tokens->look(-2)->getType() === \Twig_Token::NAME_TYPE) {
                    $value = $tokens->look(-2)->getValue();
                    $spacing = in_array($value, ['if', 'elseif', 'in']) ? $this->controlStructureSpacing : $this->spacing;
                    $this->assertSpacing($tokens, Lexer::PREVIOUS_TOKEN, $spacing);
                }
            }

            if ($token->getValue() === ')' && $token->getType() === \Twig_Token::PUNCTUATION_TYPE && $tokens->look(Lexer::PREVIOUS_TOKEN)->getType() === Token::WHITESPACE_TYPE) {
                $this->assertSpacing($tokens, Lexer::PREVIOUS_TOKEN, $this->spacing, true, true);
            }

            $tokens->next();
        }
    }
}
