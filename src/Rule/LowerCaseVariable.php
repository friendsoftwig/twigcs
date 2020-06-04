<?php

namespace FriendsOfTwig\Twigcs\Rule;

use FriendsOfTwig\Twigcs\Lexer;
use FriendsOfTwig\Twigcs\TwigPort\Token;
use FriendsOfTwig\Twigcs\TwigPort\TokenStream;

class LowerCaseVariable extends AbstractRule implements RuleInterface
{
    /**
     * {@inheritdoc}
     */
    public function check(TokenStream $tokens)
    {
        $violations = [];

        while (!$tokens->isEOF()) {
            $token = $tokens->getCurrent();

            if (Token::NAME_TYPE === $token->getType() && preg_match('/[A-Z]/', $token->getValue())) {
                if (Token::WHITESPACE_TYPE === $tokens->look(Lexer::PREVIOUS_TOKEN)->getType() && 'set' === $tokens->look(-2)->getValue()) {
                    $violations[] = $this->createViolation($tokens->getSourceContext()->getPath(), $token->getLine(), $token->getColumn(), sprintf('The "%s" variable should be in lower case (use _ as a separator).', $token->getValue()));
                }
            }

            $tokens->next();
        }

        return $violations;
    }
}
