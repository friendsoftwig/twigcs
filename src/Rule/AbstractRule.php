<?php

namespace FriendsOfTwig\Twigcs\Rule;

use FriendsOfTwig\Twigcs\TwigPort\Token;
use FriendsOfTwig\Twigcs\TwigPort\TokenStream;
use FriendsOfTwig\Twigcs\Util\StreamNavigator;
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

    public function __construct(int $severity)
    {
        $this->severity = $severity;
    }

    public function collect(): array
    {
        return [];
    }

    public function createViolation(string $filename, int $line, int $column, string $reason): Violation
    {
        return new Violation($filename, $line, $column, $reason, $this->severity, static::class);
    }

    protected function getPreviousSignificantToken(TokenStream $tokens, int $skip = 0): ?Token
    {
        return StreamNavigator::getPreviousSignificantToken($tokens, $skip);
    }

    protected function getNextSignificantToken(TokenStream $tokens, int $skip = 0): ?Token
    {
        return StreamNavigator::getNextSignificantToken($tokens, $skip);
    }

    protected function skipTo(TokenStream $tokens, int $tokenType, string $tokenValue = null)
    {
        return StreamNavigator::skipTo($tokens, $tokenType, $tokenValue);
    }

    protected function skipToOneOf(TokenStream $tokens, array $possibilities)
    {
        return StreamNavigator::skipToOneOf($tokens, $possibilities);
    }

    protected function skip(TokenStream $tokens, int $amount)
    {
        return StreamNavigator::skip($tokens, $amount);
    }
}
