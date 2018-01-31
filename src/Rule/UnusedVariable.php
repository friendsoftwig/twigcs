<?php

namespace Allocine\Twigcs\Rule;

use Allocine\Twigcs\Lexer;
use Allocine\Twigcs\Token;

class UnusedVariable extends AbstractRule
{
    /**
     * {@inheritdoc}
     */
    public function check(\Twig_TokenStream $tokens)
    {
        $variables = [];

        while (!$tokens->isEOF()) {
            $token = $tokens->getCurrent();

            if ($token->getType() === \Twig_Token::NAME_TYPE) {
                if ($tokens->look(Lexer::PREVIOUS_TOKEN)->getType() === Token::WHITESPACE_TYPE && $tokens->look(-2)->getValue() === 'set') {
                    $variables[$token->getValue()] = $token;
                } else {
                    unset($variables[$token->getValue()]);
                }
            }

            $tokens->next();
        }

        foreach ($variables as $name => $originalToken) {
            $this->addViolation(
                $tokens->getSourceContext()->getPath(),
                $originalToken->getLine(),
                $originalToken->columnno,
                sprintf('Unused variable "%s".', $name)
            );
        }
    }
}
