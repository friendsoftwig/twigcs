<?php

namespace Allocine\Twigcs\Rule;

use Allocine\Twigcs\Lexer;
use Allocine\Twigcs\Token;
use Allocine\Twigcs\Whitelist\WhitelistInterface;

class OperatorSpacing extends AbstractSpacingRule implements RuleInterface
{
    /**
     * @var string[]
     */
    private $operators;

    /**
     * @var integer
     */
    private $spacing;

    /**
     * @param integer                 $severity
     * @param string[]                $operators
     * @param integer                 $spacing
     * @param WhitelistInterface|null $whitelist
     */
    public function __construct($severity, array $operators, $spacing, WhitelistInterface $whitelist = null)
    {
        parent::__construct($severity, $whitelist);

        $this->spacing   = $spacing;
        $this->operators = $operators;
    }

    /**
     * {@inheritdoc}
     */
    public function check(\Twig\TokenStream $tokens)
    {
        $this->violations = [];

        while (!$tokens->isEOF()) {
            $token = $tokens->getCurrent();

            if ($token->getType() === \Twig\Token::OPERATOR_TYPE && in_array($token->getValue(), $this->operators)) {
                // allows unary operators to be next to an opening parenthesis.
                if (!($this->isUnary($token->getValue()) && $tokens->look(-1)->getValue() == '(')) {
                    $this->assertSpacing($tokens, Lexer::PREVIOUS_TOKEN, $this->spacing);
                }

                // Don't mark values like "-1" as a violation.
                if (!$this->canBeCloseOnRight($token, $tokens)) {
                    $this->assertSpacing($tokens, Lexer::NEXT_TOKEN, $this->spacing);
                }
            }

            $tokens->next();
        }

        return $this->violations;
    }

    /**
     * Allows the "-" unary operator to be close to its value on particular expressions
     * like: "{{ -1 }}" or "{{ var == -1 }}"
     *
     * @param \Twig\Token       $token
     * @param \Twig\TokenStream $tokens
     *
     * @return bool
     */
    private function canBeCloseOnRight(\Twig\Token $token, \Twig\TokenStream $tokens)
    {
        $forbiddenTokens = [\Twig\Token::NAME_TYPE, \Twig\Token::NUMBER_TYPE, \Twig\Token::STRING_TYPE ];

        return
            ($token->getValue() === '-') &&
            !in_array($tokens->look(-1)->getType(), $forbiddenTokens) &&
            !in_array($tokens->look(-2)->getType(), $forbiddenTokens)
        ;
    }

    /**
     * @param int $operator
     *
     * @return bool
     */
    private function isUnary($operator)
    {
        return in_array($operator, ['-', 'not']);
    }
}
