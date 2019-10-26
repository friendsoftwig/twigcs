<?php

namespace Allocine\Twigcs\Rule;

use Allocine\Twigcs\Token;

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
        $this->reset();

        while (!$tokens->isEOF()) {
            $token = $tokens->getCurrent();

            if ($token->getType() === Token::NEWLINE_TYPE && $tokens->look(-1)->getType() === Token::WHITESPACE_TYPE ||
                $token->getType() === \Twig\Token::TEXT_TYPE
            ) {
                if (preg_match("/[[:blank:]]+\n/", $token->getValue())) {
                    $line = $token->getLine();
                    $column = $token->columnno;
                    $values = explode("\n", $token->getValue());
                    $counter = 0;

                    foreach ($values as $value) {
                        if (preg_match('/[[:blank:]]+$/', $value)) {
                            $this->addViolation(
                                $tokens->getSourceContext()->getPath(),
                                $line + $counter,
                                0 === $counter ? $column + strlen($value) : strlen($value),
                                'A line should not end with blank space(s).'
                            );
                        }

                        ++$counter;
                    }
                }
            }

            $tokens->next();
        }

        return $this->violations;
    }
}
