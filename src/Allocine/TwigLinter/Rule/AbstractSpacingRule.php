<?php

namespace Allocine\TwigLinter\Rule;

use Allocine\TwigLinter\Lexer;
use Allocine\TwigLinter\Validator\Violation;
use Allocine\TwigLinter\Whistelist\WhitelistInterface;

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
     */
    protected function assertSpacing(\Twig_TokenStream $tokens, $position, $spacing)
    {
        $current = $tokens->getCurrent();
        $token = $tokens->look($position);
        $orientation = round($position/abs($position));
        $positionName = $orientation > 0 ? 'after' : 'before';

        if ($this->whitelist && !$this->whitelist->pass($tokens, $orientation)) {
            return;
        }

        // special case of no spaces allowed.
        if ($spacing === 0) {
            if ($token->getType() === Lexer::WHITESPACE_TYPE) {
                $this->addViolation(
                    $tokens->getFilename(),
                    $token->getLine(),
                    sprintf('There should be no space %s "%s".', $positionName, $current->getValue())
                );
            }

            return;
        }

        if ($token->getType() !== Lexer::WHITESPACE_TYPE || strlen($token->getValue()) < $spacing) {
            $this->addViolation(
                $tokens->getFilename(),
                $token->getLine(),
                sprintf('There should be %d space(s) %s "%s".', $spacing, $positionName, $current->getValue())
            );
        }

        if ($token->getType() === Lexer::WHITESPACE_TYPE && strlen($token->getValue()) > $spacing) {
            $this->addViolation(
                $tokens->getFilename(),
                $token->getLine(),
                sprintf('More than %d space(s) found %s "%s".', $spacing, $positionName, $current->getValue())
            );
        }
    }
}
