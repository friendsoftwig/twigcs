<?php

namespace Allocine\TwigLinter\Rule;

use Allocine\TwigLinter\Token;

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
    public function check(\Twig_TokenStream $tokens)
    {
        $this->reset();

        while (!$tokens->isEOF()) {
            $token = $tokens->getCurrent();

            if ($token->getType() === Token::NEWLINE_TYPE && $tokens->look(-1)->getType() === Token::WHITESPACE_TYPE ||
                $token->getType() === Token::TEXT_TYPE
            ) {
                if (preg_match("/[[:blank:]]+\n/", $token->getValue())) {
                    $this->addViolation(
                        $tokens->getFilename(),
                        $token->getLine(),
                        $token->getColumn(),
                        'A line should not end with blank space(s).'
                    );
                }
            }

            $tokens->next();
        }

        return $this->violations;
    }
}
