<?php

namespace Allocine\Twigcs\Rule;

use Allocine\Twigcs\Lexer;
use Allocine\Twigcs\Token;

class LowerCaseVariable extends AbstractRule
{
    /**
     * {@inheritdoc}
     */
    public function check(\Twig_TokenStream $tokens)
    {
        while (!$tokens->isEOF()) {
            $token = $tokens->getCurrent();

            if ($token->getType() === \Twig_Token::NAME_TYPE && preg_match('/[A-Z]/', $token->getValue())) {
                if ($tokens->look(Lexer::PREVIOUS_TOKEN)->getType() === Token::WHITESPACE_TYPE && $tokens->look(-2)->getValue() === 'set') {
                    $this->addViolation($tokens->getSourceContext()->getPath(), $token->getLine(), $token->columnno, sprintf('The "%s" variable should be in lower case (use _ as a separator).', $token->getValue()));
                }
            }

            $tokens->next();
        }
    }
}
