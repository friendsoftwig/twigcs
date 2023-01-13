<?php

namespace FriendsOfTwig\Twigcs\Rule;

use FriendsOfTwig\Twigcs\Lexer;
use FriendsOfTwig\Twigcs\TwigPort\Token;
use FriendsOfTwig\Twigcs\TwigPort\TokenStream;

class LowerCaseVariable extends AbstractRule implements RuleInterface
{
    public function check(TokenStream $tokens)
    {
        $violations = [];

        while (!$tokens->isEOF()) {
            $token = $tokens->getCurrent();

            if (Token::NAME_TYPE === $token->getType() &&
                preg_match('/[A-Z]/', $token->getValue()) &&
                Token::WHITESPACE_TYPE === $tokens->look(Lexer::PREVIOUS_TOKEN)->getType()
            ) {
                $hasViolation = false;

                if (in_array($tokens->look(-2)->getValue(), ['set', 'for'], true)) {
                    $hasViolation = true;
                } elseif (',' === $tokens->look(-2)->getValue() &&
                    Token::WHITESPACE_TYPE === $tokens->look(-4)->getType() &&
                    'for' === $tokens->look(-5)->getValue()
                ) {
                    $hasViolation = true;
                }

                if ($hasViolation) {
                    $violations[] = $this->createViolation($tokens->getSourceContext()->getPath(), $token->getLine(), $token->getColumn(), sprintf('The "%s" variable should be in lower case (use _ as a separator).', $token->getValue()));
                }
            }

            $tokens->next();
        }

        return $violations;
    }
}
