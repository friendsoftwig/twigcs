<?php

namespace Allocine\TwigLinter\Rule;

use Allocine\TwigLinter\Lexer;
use Allocine\TwigLinter\Whitelist\WhitelistInterface;

class PunctuationSpacing extends AbstractSpacingRule implements RuleInterface
{
    /**
     * @var string[]
     */
    private $punctuations;

    /**
     * @var integer
     */
    private $spacing;

    /**
     * @param integer                 $severity
     * @param string[]                $punctuations
     * @param integer                 $spacing
     * @param WhitelistInterface|null $whitelist
     */
    public function __construct($severity, array $punctuations, $spacing, WhitelistInterface $whitelist = null)
    {
        parent::__construct($severity, $whitelist);

        $this->spacing   = $spacing;
        $this->punctuations = $punctuations;
    }

    /**
     * {@inheritdoc}
     */
    public function check(\Twig_TokenStream $tokens)
    {
        $this->violations = [];

        while (!$tokens->isEOF()) {
            $token = $tokens->getCurrent();

            if ($token->getType() === \Twig_Token::PUNCTUATION_TYPE && in_array($token->getValue(), $this->punctuations)) {
                $this->assertSpacing($tokens, Lexer::PREVIOUS_TOKEN, $this->spacing);
                $this->assertSpacing($tokens, Lexer::NEXT_TOKEN, $this->spacing);
            }

            $tokens->next();
        }

        return $this->violations;
    }
}
