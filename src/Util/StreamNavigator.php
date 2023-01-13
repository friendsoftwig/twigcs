<?php

namespace FriendsOfTwig\Twigcs\Util;

use FriendsOfTwig\Twigcs\TwigPort\Token;
use FriendsOfTwig\Twigcs\TwigPort\TokenStream;

class StreamNavigator
{
    public static function getPreviousSignificantToken(TokenStream $tokens, int $skip = 0): ?Token
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

    public static function getNextSignificantToken(TokenStream $tokens, int $skip = 0): ?Token
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

    public static function skipTo(TokenStream $tokens, int $tokenType, string $tokenValue = null)
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

    public static function skipToOneOf(TokenStream $tokens, array $possibilities)
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

    public static function skip(TokenStream $tokens, int $amount)
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
