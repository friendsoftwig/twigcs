<?php

namespace Allocine\TwigLinter\Rule;

use Allocine\TwigLinter\Lexer;

class UnusedVariable extends AbstractRule implements RuleInterface
{
    /**
     * {@inheritdoc}
     */
    public function check(\Twig_TokenStream $tokens)
    {
        $this->reset();

        $variables = [];

        while (!$tokens->isEOF()) {
            $token = $tokens->getCurrent();

            if ($token->getType() === \Twig_Token::NAME_TYPE) {
                if ($tokens->look(Lexer::PREVIOUS_TOKEN)->getType() === Lexer::WHITESPACE_TYPE && $tokens->look(-2)->getValue() === 'set') {
                    $variables[$token->getValue()] = $token->getLine();
                } else {
                    unset($variables[$token->getValue()]);
                }
            }

            $tokens->next();
        }

        foreach ($variables as $name => $line) {
            $this->addViolation(
                $tokens->getFilename(),
                $token->getLine(),
                sprintf('Unused variable "%s".', $name)
            );
        }

        return $this->violations;
    }
}
