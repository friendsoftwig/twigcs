<?php

namespace FriendsOfTwig\Twigcs\Rule;

use FriendsOfTwig\Twigcs\Token;
use FriendsOfTwig\Twigcs\Validator\Violation;

/**
 * This is an utility class that provides some common functionnalities
 * for rule creation.
 *
 * @author Tristan Maindron <tmaindron@gmail.com>
 */
abstract class AbstractRule
{
    /**
     * @var int
     */
    protected $severity;

    /**
     * @param int $severity
     */
    public function __construct($severity)
    {
        $this->severity = $severity;
    }

    public function collect(): array
    {
        return [];
    }

    public function createViolation(string $filename, int $line, int $column, string $reason): Violation
    {
        return new Violation($filename, $line, $column, $reason, $this->severity, get_called_class());
    }

    /**
     * @param int $skip
     *
     * @return Token|null
     */
    protected function getPreviousSignificantToken(\Twig_TokenStream $tokens, $skip = 0)
    {
        $i = 1;
        $token = null;

        while ($token = $tokens->look(-$i)) {
            if (!in_array($token->getType(), [Token::WHITESPACE_TYPE, Token::NEWLINE_TYPE], true)) {
                if (0 === $skip) {
                    return $token;
                }

                --$skip;
            }

            ++$i;
        }

        return null;
    }

    /**
     * @param int $skip
     *
     * @return Token|null
     */
    protected function getNextSignificantToken(\Twig_TokenStream $tokens, $skip = 0)
    {
        $i = 1;
        $token = null;

        while ($token = $tokens->look($i)) {
            if (!in_array($token->getType(), [Token::WHITESPACE_TYPE, Token::NEWLINE_TYPE], true)) {
                if (0 === $skip) {
                    return $token;
                }

                --$skip;
            }

            ++$i;
        }

        return null;
    }

    protected function skipTo(\Twig_TokenStream $tokens, int $tokenType, string $tokenValue = null)
    {
        while (!$tokens->isEOF()) {
            $continue = $tokens->getCurrent()->getType() !== $tokenType;

            if (null !== $tokenValue) {
                $continue |= $tokens->getCurrent()->getValue() !== $tokenValue;
            }

            if (!$continue) {
                return;
            }

            $tokens->next();
        }
    }

    protected function skipToOneOf(\Twig_TokenStream $tokens, array $possibilities)
    {
        while (!$tokens->isEOF()) {
            foreach ($possibilities as $possibility) {
                $tokenValue = $possibility['value'] ?? null;
                $tokenType = $possibility['type'] ?? null;
                $found = true;

                if ($tokenType) {
                    $found &= $tokenType === $tokens->getCurrent()->getType();
                }

                if ($tokenValue) {
                    $found &= $tokenValue === $tokens->getCurrent()->getValue();
                }

                if ($found) {
                    return;
                }
            }

            $tokens->next();
        }
    }

    protected function skip(\Twig_TokenStream $tokens, int $amount)
    {
        while (!$tokens->isEOF()) {
            --$amount;
            $tokens->next();
            if (0 === $amount) {
                return;
            }
        }
    }
}
