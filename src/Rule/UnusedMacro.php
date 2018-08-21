<?php

namespace Allocine\Twigcs\Rule;

use Allocine\Twigcs\Scope\Scope;
use Allocine\Twigcs\Token;

class UnusedMacro extends AbstractRule implements RuleInterface
{
    /**
     * {@inheritdoc}
     */
    public function check(\Twig_TokenStream $tokens)
    {
        $scope = new Scope('file');
        $root = $scope;

        $this->reset();

        while (!$tokens->isEOF()) {
            $token = $tokens->getCurrent();

            if ($token->getType() === \Twig_Token::BLOCK_START_TYPE) {
                $blockType = $tokens->look(2)->getValue();

                if (in_array($blockType, ['block', 'for', 'embed', 'macro'])) {
                    $scope = $scope->spawn($blockType);
                    if ($blockType === 'macro') {
                        $scope->isolate();
                    }
                }

                if (in_array($blockType, ['endblock', 'endfor', 'endembed', 'endmacro'])) {
                    $scope = $scope->leave();
                }
            }

            if ($token->getType() === \Twig_Token::BLOCK_START_TYPE) {
                $blockType = $tokens->look(2)->getValue();

                switch ($blockType) {
                    case 'from':
                        if ($tokens->look(10)->getValue() == 'as') {
                            $forward = 12; // Extracts token position from block of form {% import foo as bar %}
                        } else {
                            $forward = 8; // Extracts token position from block of form {% import foo %}
                        }

                        $this->skip($tokens, $forward);

                        // Handles single or multiple imports ( {% from "file.twig" import foo as bar, baz %} )
                        while (in_array($tokens->getCurrent()->getType(), [\Twig_Token::NAME_TYPE, \Twig_Token::PUNCTUATION_TYPE, Token::WHITESPACE_TYPE])) {
                            $next = $tokens->getCurrent();
                            if ($next->getType() === \Twig_Token::NAME_TYPE) {
                                $scope->declare($next->getValue(), $next);
                            }
                            $tokens->next();
                        }
                        break;
                    case 'import':
                        $this->skipTo($tokens, \Twig_Token::NAME_TYPE, 'as');
                        $this->skip($tokens, 2);

                        // Handles single or multiple imports ( {% import foo as bar, baz %} )
                        while (in_array($tokens->getCurrent()->getType(), [\Twig_Token::NAME_TYPE, \Twig_Token::PUNCTUATION_TYPE, Token::WHITESPACE_TYPE])) {
                            $next = $tokens->getCurrent();
                            if ($next->getType() === \Twig_Token::NAME_TYPE) {
                                $scope->declare($next->getValue(), $next);
                            }
                            $tokens->next();
                        }
                        break;
                    case 'if':
                    case 'for':
                        $this->skip($tokens, 3);
                        break;
                    default:
                        $this->skipTo($tokens, \Twig_Token::BLOCK_END_TYPE);
                }
            } elseif ($token->getType() === \Twig_Token::NAME_TYPE) {
                $previous = $this->getPreviousSignicantToken($tokens);
                $next = $this->getNextSignicantToken($tokens);

                if (!in_array($previous->getValue(), ['.', '|']) && in_array($next->getValue(), ['('])) {
                    $scope->use($token->getValue());
                }

                $tokens->next();
            } else {
                $tokens->next();
            }
        }

        foreach ($root->getUnused() as $declarationToken) {
            $this->addViolation(
                $tokens->getSourceContext()->getPath(),
                $declarationToken->getLine(),
                $declarationToken->columnno,
                sprintf('Unused macro import "%s".', $declarationToken->getValue())
            );
        }

        return $this->violations;
    }
}
