<?php

namespace Allocine\Twigcs\Rule;

use Allocine\Twigcs\Token;

class UnusedMacro extends AbstractRule implements RuleInterface
{
    /**
     * {@inheritdoc}
     */
    public function check(\Twig_TokenStream $tokens)
    {
        $this->reset();

        $macros = [];

        while (!$tokens->isEOF()) {
            $token = $tokens->getCurrent();

            if ($token->getType() === \Twig_Token::NAME_TYPE && $token->getValue() === 'import') {
                if ($tokens->look(4)->getValue() == 'as') {
                    $forward = 6; // Extracts token position from block of form {% import foo as bar %}
                } else {
                    $forward = 2; // Extracts token position from block of form {% import foo %}
                }

                $macroToken = $tokens->look($forward);

                for ($i=0; $i<$forward; $i++) {
                    $tokens->next();
                }

                // Handles single or multiple imports ( {% import foo as bar, baz %} )
                while (in_array($tokens->getCurrent()->getType(), [\Twig_Token::NAME_TYPE, \Twig_Token::PUNCTUATION_TYPE, Token::WHITESPACE_TYPE])) {
                    $next = $tokens->getCurrent();
                    if ($next->getType() === \Twig_Token::NAME_TYPE) {
                        $macros[$next->getValue()] = $next;
                    }
                    $tokens->next();
                }
            } elseif ($token->getType() === \Twig_Token::NAME_TYPE && array_key_exists($token->getValue(), $macros)) {
                unset($macros[$token->getValue()]);
            }

            $tokens->next();
        }


        foreach ($macros as $name => $originalToken) {
            $this->addViolation(
                $tokens->getSourceContext()->getPath(),
                $originalToken->getLine(),
                $originalToken->columnno,
                sprintf('Unused macro "%s".', $name)
            );
        }

        return $this->violations;
    }
}
