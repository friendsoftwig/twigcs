<?php

namespace Allocine\Twigcs\Rule;

use Allocine\Twigcs\Token;

/**
 * Checks for trailing spaces and triggers a violation when one
 * or more are found.
 *
 * @author Tristan Maindron <tmaindron@gmail.com>
 */
class TrailingSpace extends AbstractRule
{
    /**
     * {@inheritdoc}
     */
    public function check(\Twig_TokenStream $tokens)
    {
        while (!$tokens->isEOF()) {
            $token = $tokens->getCurrent();

            if ($token->getType() === Token::NEWLINE_TYPE && $tokens->look(-1)->getType() === Token::WHITESPACE_TYPE ||
                $token->getType() === \Twig_Token::TEXT_TYPE
            ) {
                if (preg_match("/[[:blank:]]+\n/", $token->getValue())) {
                    $this->addViolation(
                        $tokens->getSourceContext()->getPath(),
                        $token->getLine(),
                        $token->columnno,
                        'A line should not end with blank space(s).'
                    );
                }
            }

            $tokens->next();
        }
    }
}
