<?php

namespace FriendsOfTwig\Twigcs\Rule;

use FriendsOfTwig\Twigcs\Lexer;
use FriendsOfTwig\Twigcs\Token;

class LowerCaseVariable extends AbstractRule implements RuleInterface
{
    /**
     * {@inheritdoc}
     */
    public function check(\Twig_TokenStream $tokens)
    {
        $this->reset();

        while (!$tokens->isEOF()) {
            $token = $tokens->getCurrent();

            if (\Twig_Token::NAME_TYPE === $token->getType() && preg_match('/[A-Z]/', $token->getValue())) {
                if (Token::WHITESPACE_TYPE === $tokens->look(Lexer::PREVIOUS_TOKEN)->getType() && 'set' === $tokens->look(-2)->getValue()) {
                    $this->addViolation($tokens->getSourceContext()->getPath(), $token->getLine(), $token->columnno, sprintf('The "%s" variable should be in lower case (use _ as a separator).', $token->getValue()));
                }
            }

            $tokens->next();
        }

        return $this->violations;
    }
}
