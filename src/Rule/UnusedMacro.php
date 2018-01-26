<?php

namespace Allocine\Twigcs\Rule;

use Allocine\Twigcs\Token;

class UnusedMacro extends AbstractRule
{
    const WITH_FROM = 'withFrom';
    const WITHOUT_FROM = 'withoutFrom';

    /**
     * @var array
     */
    private $macros = [
        'declared' => [],
        'used' => []
    ];

    /**
     * {@inheritdoc}
     */
    public function check(\Twig_TokenStream $tokens)
    {
        $this->reset();

        $macros = [
            self::WITH_FROM => [],
            self::WITHOUT_FROM => []
        ];

        while (!$tokens->isEOF()) {
            $token = $tokens->getCurrent();
            if ($token->getType() === \Twig_Token::NAME_TYPE && $token->getValue() === 'macro') {
                // Macro declaration => {% macro foo() %}{% endmacro %}
                $this->moveCursor($tokens, 2);
                $this->setDeclaredMacro($tokens);
            } elseif ($token->getType() === \Twig_Token::NAME_TYPE && $token->getValue() === 'import') {
                // Macro usage => {% from _self import foo as bar, baz %}{{ bar() }}{{ baz() }}
                //                {% import _self as foo %}{{ foo.bar() }} => with bar as a macro in the self file
                $this->moveCursor($tokens, 2);

                $next = $tokens->getCurrent();
                $key = self::WITHOUT_FROM;
                if ($next->getType() === \Twig_Token::STRING_TYPE) {
                    $path = $next->getValue();
                } elseif ($tokens->look(-4)->getType() === \Twig_Token::STRING_TYPE) {
                    $path = $tokens->look(-4)->getValue();
                    $key = self::WITH_FROM;
                } else {
                    if ($tokens->look(-4)->getType() === \Twig_Token::NAME_TYPE) {
                        $key = self::WITH_FROM;
                    }
                    $path = $tokens->getSourceContext()->getPath();
                }

                $this->moveCursor($tokens, 2);

                while (in_array($tokens->getCurrent()->getType(), [\Twig_Token::NAME_TYPE, \Twig_Token::PUNCTUATION_TYPE, Token::WHITESPACE_TYPE])) {
                    $next = $tokens->getCurrent();
                    if ($key === self::WITH_FROM) {
                        if ($next->getValue() === 'as') {
                            $macro = $tokens->look(2)->getValue();
                            $macros[$key][$path] = array_merge($macros[$key][$path], [$macro]);
                        }
                    } else {

                    }
                    if ($next->getType() === \Twig_Token::NAME_TYPE) {
                        $macros[$key][$next->getValue()] = $next;
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
    }

    /**
     * @param \Twig_TokenStream $tokens
     */
    private function setDeclaredMacro(\Twig_TokenStream $tokens)
    {
        $next = $tokens->getCurrent();
        if ($next->getType() == \Twig_Token::NAME_TYPE) {
            $path = $tokens->getSourceContext()->getPath();
            if (!array_key_exists($path, $this->macros['declared'])) {
                $this->macros['declared'][$path] = [$next];
            } else {
                $this->macros['declared'][$path] = array_merge($this->macros['declared'][$path], [$next]);
            }
        }
    }

    /**
     * @param \Twig_TokenStream $tokens
     * @param int               $number
     *
     * @throws \Twig_Error_Syntax
     */
    private function moveCursor(\Twig_TokenStream $tokens, int $number)
    {
        for ($i = 0; $i < $number; $i++) {
            $tokens->next();
        }
    }
}
