<?php

namespace Allocine\TwigLinter\Rule;

use Allocine\TwigLinter\Lexer;
use Allocine\TwigLinter\Validator\Violation;

class TernarySpacing extends AbstractSpacingRule implements RuleInterface
{
    const TERNARY_PUNCTUATION = ['if' => '?', 'else' => ':'];

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

        while (!$tokens->isEOF()) {
            $token = $tokens->getCurrent();

            if ($token->getValue() === self::TERNARY_PUNCTUATION['if']) {
                $ternaryDepth++;
            }

            if ($ternaryDepth > 0 && $token->getType() === \Twig_Token::PUNCTUATION_TYPE && in_array($token->getValue(), self::TERNARY_PUNCTUATION)) {
                $previous = $tokens->look(Lexer::PREVIOUS_TOKEN);
                $next = $tokens->look(Lexer::NEXT_TOKEN);

                if ($previous->getValue() !== '?') {
                    $this->assertSpacing($tokens, Lexer::PREVIOUS_TOKEN, $this->spacing);
                }
                if ($next->getValue() !== ':') {
                    $this->assertSpacing($tokens, Lexer::NEXT_TOKEN, $this->spacing);
                }
            }

            if ($token->getValue() === self::TERNARY_PUNCTUATION['else']) {
                $ternaryDepth = max(0, $ternaryDepth - 1);
            }

            $tokens->next();
        }

        return $this->violations;
    }
}
