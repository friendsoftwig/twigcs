<?php

namespace Allocine\Twigcs\Rule;

use Allocine\Twigcs\Lexer;
use Allocine\Twigcs\Token;
use Allocine\Twigcs\Validator\Violation;

class DelimiterSpacing extends AbstractRule implements RuleInterface
{
    const CHECKS = [
        \Twig_Token::BLOCK_START_TYPE => [Lexer::NEXT_TOKEN,     'after opening a block'],
        \Twig_Token::BLOCK_END_TYPE   => [Lexer::PREVIOUS_TOKEN, 'before closing a block'],
        \Twig_Token::VAR_START_TYPE   => [Lexer::NEXT_TOKEN,     'after opening a variable'],
        \Twig_Token::VAR_END_TYPE     => [Lexer::PREVIOUS_TOKEN, 'before closing a variable'],
    ];

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

        $watchList = array_keys(self::CHECKS);

        while (!$tokens->isEOF()) {
            $token = $tokens->getCurrent();

            if (in_array($token->getType(), $watchList)) {
                $checks = self::CHECKS[$token->getType()];
                $this->assertSpacing($tokens, $checks[0], $checks[1]);
            }

            $tokens->next();
        }

        return $this->violations;
    }

    /**
     * @param \Twig_TokenStream $tokens
     * @param integer           $position
     * @param message           $target
     */
    private function assertSpacing(\Twig_TokenStream $tokens, $position, $target)
    {
        $token = $tokens->look($position);

        if ($token->getType() !== Token::WHITESPACE_TYPE || strlen($token->getValue()) < $this->spacing) {
            $this->addViolation(
                $tokens->getSourceContext()->getPath(),
                $token->getLine(),
                $token->columnno,
                sprintf('There should be %d space(s) %s.', $this->spacing, $target)
            );
        }

        if ($token->getType() === Token::WHITESPACE_TYPE && strlen($token->getValue()) > $this->spacing) {
            $this->addViolation(
                $tokens->getSourceContext()->getPath(),
                $token->getLine(),
                $token->columnno,
                sprintf('More than %d space(s) found %s.', $this->spacing, $target)
            );
        }
    }
}
