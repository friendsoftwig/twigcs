<?php

namespace Allocine\TwigLinter\Rule;

use Allocine\TwigLinter\Token;
use Allocine\TwigLinter\Validator\Violation;
use Allocine\TwigLinter\Whitelist\WhitelistInterface;

class AbstractSpacingRule extends AbstractRule
{
    /**
     * @var WhitelistInterface
     */
    protected $whitelist;

    /**
     * @param integer                 $severity
     * @param WhitelistInterface|null $whitelist
     */
    public function __construct($severity, WhitelistInterface $whitelist = null)
    {
        parent::__construct($severity);

        $this->whitelist = $whitelist;
    }

    /**
     * @param \Twig_TokenStream $tokens
     * @param integer           $position
     * @param message           $target
     * @param boolean           $acceptNewLines
     */
    protected function assertSpacing(\Twig_TokenStream $tokens, $position, $spacing, $acceptNewLines = true, $allowIndentation = false)
    {
        $current = $tokens->getCurrent();
        $token = $tokens->look($position);
        $orientation = round($position/abs($position));
        $positionName = $orientation > 0 ? 'after' : 'before';

        if ($this->whitelist && !$this->whitelist->pass($tokens, $orientation)) {
            return;
        }

        if ($acceptNewLines && $token->getType() == Token::NEWLINE_TYPE) {
            return;
        }

        // special case of no spaces allowed.
        if ($spacing === 0) {
            if ($token->getType() === Token::WHITESPACE_TYPE) {
                if ($allowIndentation && $this->isPreviousWhitespaceTokenOnlyIndentation($tokens)) {
                    return;
                }

                $this->addViolation(
                    $tokens->getSourceContext()->getPath(),
                    $current->getLine(),
                    $current->columnno,
                    sprintf('There should be no space %s "%s".', $positionName, $current->getValue())
                );
            }

            if ($token->getType() === Token::NEWLINE_TYPE) {
                $this->addViolation(
                    $tokens->getSourceContext()->getPath(),
                    $current->getLine(),
                    $current->columnno,
                    sprintf('There should be no new line %s "%s".', $positionName, $current->getValue())
                );
            }

            return;
        }

        if ($token->getType() !== Token::WHITESPACE_TYPE || strlen($token->getValue()) < $spacing) {
            $this->addViolation(
                $tokens->getSourceContext()->getPath(),
                $current->getLine(),
                $current->columnno,
                sprintf('There should be %d space(s) %s "%s".', $spacing, $positionName, $current->getValue())
            );
        }

        if ($token->getType() === Token::WHITESPACE_TYPE && strlen($token->getValue()) > $spacing) {
            $this->addViolation(
                $tokens->getSourceContext()->getPath(),
                $current->getLine(),
                $current->columnno,
                sprintf('More than %d space(s) found %s "%s".', $spacing, $positionName, $current->getValue())
            );
        }
    }

    private function isPreviousWhitespaceTokenOnlyIndentation($tokens)
    {
        $lookBehind = 0;

        // look behind until you find anything non-whitespace-ish
        do {
            $whitespaceToken = $tokens->look(--$lookBehind);
        } while ($whitespaceToken->getType() === Token::WHITESPACE_TYPE);

        $firstNonWhitespaceTokenBehind = $tokens->look($lookBehind);
        if ($firstNonWhitespaceTokenBehind->getType() === Token::NEWLINE_TYPE) {
            return true;
        }

        return false;
    }
}
