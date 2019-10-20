<?php

namespace FriendsOfTwig\Twigcs\Rule;

use FriendsOfTwig\Twigcs\Scope\Scope;
use FriendsOfTwig\Twigcs\Token;

class UnusedMacro extends AbstractRule implements RuleInterface
{
    /**
     * {@inheritdoc}
     */
    public function check(\Twig\TokenStream $tokens)
    {
        $scope = new Scope('file');
        $root = $scope;

        $violations = [];

        while (!$tokens->isEOF()) {
            $token = $tokens->getCurrent();

            if (\Twig\Token::BLOCK_START_TYPE === $token->getType()) {
                $blockType = $tokens->look(2)->getValue();

                if (in_array($blockType, ['block', 'for', 'embed', 'macro'], true)) {
                    $scope = $scope->spawn($blockType);
                    if ('macro' === $blockType) {
                        $scope->isolate();
                    }
                }

                if (in_array($blockType, ['endblock', 'endfor', 'endembed', 'endmacro'], true)) {
                    $scope = $scope->leave();
                }
            }

            if (\Twig\Token::BLOCK_START_TYPE === $token->getType()) {
                $blockType = $tokens->look(2)->getValue();

                switch ($blockType) {
                    case 'from':
                        if ('as' === $tokens->look(10)->getValue()) {
                            $forward = 12; // Extracts token position from block of form {% import foo as bar %}
                        } else {
                            $forward = 8; // Extracts token position from block of form {% import foo %}
                        }

                        $this->skip($tokens, $forward);

                        // Handles single or multiple imports ( {% from "file.twig" import foo as bar, baz %} )
                        while (in_array($tokens->getCurrent()->getType(), [\Twig\Token::NAME_TYPE, \Twig\Token::PUNCTUATION_TYPE, Token::WHITESPACE_TYPE], true)) {
                            $next = $tokens->getCurrent();
                            if (\Twig\Token::NAME_TYPE === $next->getType()) {
                                $scope->declare($next->getValue(), $next);
                            }
                            $tokens->next();
                        }
                        break;
                    case 'import':
                        $this->skipTo($tokens, \Twig\Token::NAME_TYPE, 'as');
                        $this->skip($tokens, 2);

                        // Handles single or multiple imports ( {% import foo as bar, baz %} )
                        while (in_array($tokens->getCurrent()->getType(), [\Twig\Token::NAME_TYPE, \Twig\Token::PUNCTUATION_TYPE, Token::WHITESPACE_TYPE], true)) {
                            $next = $tokens->getCurrent();
                            if (\Twig\Token::NAME_TYPE === $next->getType()) {
                                $scope->declare($next->getValue(), $next);
                            }
                            $tokens->next();
                        }
                        break;
                    case 'if':
                    case 'for':
                        $this->skip($tokens, 3);
                        break;
                    case 'set':
                        $this->skipToOneOf($tokens, [
                            ['type' => \Twig\Token::OPERATOR_TYPE, 'value' => '='],
                            ['type' => \Twig\Token::BLOCK_END_TYPE],
                        ]);
                        break;
                    default:
                        $this->skipTo($tokens, \Twig\Token::BLOCK_END_TYPE);
                }
            } elseif (\Twig\Token::NAME_TYPE === $token->getType()) {
                $previous = $this->getPreviousSignificantToken($tokens);
                $next = $this->getNextSignificantToken($tokens);

                $isSubProperty = in_array($previous->getValue(), ['.', '|'], true);
                $directUsage = in_array($next->getValue(), ['('], true);
                $dotUsage = (\Twig\Token::NAME_TYPE === $this->getNextSignificantToken($tokens, 1)->getType()) && in_array($this->getNextSignificantToken($tokens, 2)->getValue(), ['('], true);

                if (!$isSubProperty && ($directUsage || $dotUsage)) {
                    $scope->use($token->getValue());
                }

                $tokens->next();
            } else {
                $tokens->next();
            }
        }

        foreach ($root->getUnused() as $declarationToken) {
            $violations[] = $this->createViolation(
                $tokens->getSourceContext()->getPath(),
                $declarationToken->getLine(),
                $declarationToken->columnno,
                sprintf('Unused macro import "%s".', $declarationToken->getValue())
            );
        }

        return $violations;
    }
}
