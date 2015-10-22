<?php

namespace Allocine\TwigLinter\Rule;

use Allocine\TwigLinter\Lexer;
use Allocine\TwigLinter\Whistelist\WhitelistInterface;

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
    public function check(\Twig_TokenStream $tokens)
    {
        $this->violations = [];

        while (!$tokens->isEOF()) {
            $token = $tokens->getCurrent();

            if ($token->getType() === \Twig_Token::OPERATOR_TYPE && in_array($token->getValue(), $this->operators)) {
                $this->assertSpacing($tokens, Lexer::PREVIOUS_TOKEN, $this->spacing);
                $this->assertSpacing($tokens, Lexer::NEXT_TOKEN, $this->spacing);
            }

            $tokens->next();
        }

        return $this->violations;
    }
}
