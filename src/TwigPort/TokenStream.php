<?php

/*
 * This file was taken and MODIFIED from Twig : https://github.com/twigphp/twig
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed in the same folder as this piece of code.
 */

namespace FriendsOfTwig\Twigcs\TwigPort;

final class TokenStream
{
    private array $tokens;
    private int $current = 0;
    private Source $source;

    public function __construct(array $tokens, Source $source = null)
    {
        $this->tokens = $tokens;
        $this->source = $source ?: new Source('', '');
    }

    public function __toString()
    {
        return implode("\n", $this->tokens);
    }

    public function injectTokens(array $tokens)
    {
        $this->tokens = array_merge(\array_slice($this->tokens, 0, $this->current), $tokens, \array_slice($this->tokens, $this->current));
    }

    public function next(): Token
    {
        if (!isset($this->tokens[++$this->current])) {
            $token = $this->tokens[$this->current - 1];
            $line = $token->getLine();
            $column = $token->getColumn();

            throw new SyntaxError('Unexpected end of template.', $line, $column, $this->source);
        }

        return $this->tokens[$this->current - 1];
    }

    public function nextIf($primary, $secondary = null)
    {
        if ($this->tokens[$this->current]->test($primary, $secondary)) {
            return $this->next();
        }
    }

    public function expect($type, $value = null, string $message = null): Token
    {
        $token = $this->tokens[$this->current];

        if (!$token->test($type, $value)) {
            $line = $token->getLine();
            $column = $token->getColumn();

            throw new SyntaxError(sprintf('%sUnexpected token "%s"%s ("%s" expected%s).', $message ? $message.'. ' : '', Token::typeToEnglish($token->getType()), $token->getValue() ? sprintf(' of value "%s"', $token->getValue()) : '', Token::typeToEnglish($type), $value ? sprintf(' with value "%s"', $value) : ''), $line, $column, $this->source);
        }
        $this->next();

        return $token;
    }

    public function look(int $number = 1): Token
    {
        if (!isset($this->tokens[$this->current + $number])) {
            $token = $this->tokens[$this->current + $number - 1];
            $line = $token->getLine();
            $column = $token->getColumn();

            throw new SyntaxError('Unexpected end of template.', $line, $column, $this->source);
        }

        return $this->tokens[$this->current + $number];
    }

    public function test($primary, $secondary = null): bool
    {
        return $this->tokens[$this->current]->test($primary, $secondary);
    }

    public function isEOF(): bool
    {
        return /* Token::EOF_TYPE */ -1 === $this->tokens[$this->current]->getType();
    }

    public function getCurrent(): Token
    {
        return $this->tokens[$this->current];
    }

    public function getSourceContext(): Source
    {
        return $this->source;
    }
}
