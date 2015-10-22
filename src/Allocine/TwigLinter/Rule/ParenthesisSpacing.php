<?php

namespace Allocine\TwigLinter\Rule;

use Allocine\TwigLinter\Lexer;
use Allocine\TwigLinter\Validator\Violation;

class ParenthesisSpacing extends AbstractSpacingRule implements RuleInterface
{
    /**
     * @var integer
     */
    private $spacing;

    /**
     * @param integer $severity
     * @param integer $spacing
     */
    public function __construct($severity, $spacing = 0)
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

        while (!$tokens->isEOF()) {
            $token = $tokens->getCurrent();

            if ($token->getValue() === '(') {
                $this->assertSpacing($tokens, Lexer::NEXT_TOKEN, $this->spacing);

                // Space allowed if previous token is not a function name.
                if ($tokens->look(-2)->getType() === \Twig_Token::NAME_TYPE) {
                    $this->assertSpacing($tokens, Lexer::PREVIOUS_TOKEN, $this->spacing);
                }
            }

            if ($token->getValue() === ')' && $tokens->look(Lexer::PREVIOUS_TOKEN)->getType() === Lexer::WHITESPACE_TYPE) {
                $this->assertSpacing($tokens, Lexer::PREVIOUS_TOKEN, $this->spacing);
            }

            $tokens->next();
        }

        return $this->violations;
    }
}
