<?php

namespace FriendsOfTwig\Twigcs\Rule;

use FriendsOfTwig\Twigcs\Lexer;
use FriendsOfTwig\Twigcs\TwigPort\Token;
use FriendsOfTwig\Twigcs\TwigPort\TokenStream;

class ForbiddenFunctions extends AbstractRule implements RuleInterface
{
    private $functions;

    public function __construct($severity, $functions = [])
    {
        parent::__construct($severity);

        $this->functions = $functions;
    }

    /**
     * @param array $functions
     *
     * @return $this
     */
    public function setFunctions($functions)
    {
        $this->functions = $functions;

        return $this;
    }

    /**
     * @param string $function
     *
     * @return $this
     */
    public function addFunction($function)
    {
        if (!in_array($function, $this->functions, true)) {
            $this->functions[] = $function;
        }

        return $this;
    }

    public function check(TokenStream $tokens)
    {
        if (empty($this->functions)) {
            return [];
        }

        $violations = [];

        while (!$tokens->isEOF()) {
            $token = $tokens->getCurrent();

            if (Token::NAME_TYPE === $token->getType() &&
                Token::WHITESPACE_TYPE === $tokens->look(Lexer::PREVIOUS_TOKEN)->getType() &&
                Token::PUNCTUATION_TYPE === $tokens->look(Lexer::NEXT_TOKEN)->getType() &&
                '(' === $tokens->look(Lexer::NEXT_TOKEN)->getValue() &&
                in_array($token->getValue(), $this->functions, true)
            ) {
                $violations[] = $this->createViolation(
                    $tokens->getSourceContext()->getPath(),
                    $token->getLine(),
                    $token->getColumn(),
                    sprintf('The function "%s" is forbidden.', $token->getValue())
                );
            }

            $tokens->next();
        }

        return $violations;
    }
}
