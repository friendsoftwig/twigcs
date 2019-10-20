<?php

namespace FriendsOfTwig\Twigcs\Rule;

use FriendsOfTwig\Twigcs\Token;

/**
 * Checks for trailing spaces and triggers a violation when one
 * or more are found.
 *
 * @author Tristan Maindron <tmaindron@gmail.com>
 */
class TrailingSpace extends AbstractRule implements RuleInterface
{
    /**
     * {@inheritdoc}
     */
    public function check(\Twig\TokenStream $tokens)
    {
        $violations = [];

        while (!$tokens->isEOF()) {
            $token = $tokens->getCurrent();

            if (Token::NEWLINE_TYPE === $token->getType() && Token::WHITESPACE_TYPE === $tokens->look(-1)->getType() ||
                \Twig\Token::TEXT_TYPE === $token->getType()
            ) {
                if (preg_match("/[[:blank:]]+\n/", $token->getValue())) {
                    $violations[] = $this->createViolation(
                        $tokens->getSourceContext()->getPath(),
                        $token->getLine(),
                        $token->columnno,
                        'A line should not end with blank space(s).'
                    );
                }
            }

            $tokens->next();
        }

        return $violations;
    }
}
