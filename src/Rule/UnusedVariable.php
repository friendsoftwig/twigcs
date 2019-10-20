<?php

namespace FriendsOfTwig\Twigcs\Rule;

use FriendsOfTwig\Twigcs\Scope\Scope;
use FriendsOfTwig\Twigcs\Token;

class UnusedVariable extends AbstractRule implements RuleInterface
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
                    case 'embed':
                    case 'include':
                        if ('with' === $tokens->look(6)->getValue()) {
                            $this->skip($tokens, 8);
                        } else {
                            $this->skipTo($tokens, \Twig\Token::BLOCK_END_TYPE);
                        }
                        break;
                    case 'from':
                        $from = $tokens->look(4);

                        if (\Twig\Token::NAME_TYPE === $from->getType()) { // {% from varName import ... %}
                            $scope->use($from->getValue());
                        }
                        $this->skipTo($tokens, \Twig\Token::BLOCK_END_TYPE);
                        break;
                    case 'set':
                        $scope->declare($tokens->look(4)->getValue(), $tokens->look(4));
                        $this->skipToOneOf($tokens, [
                            ['type' => \Twig\Token::OPERATOR_TYPE, 'value' => '='],
                            ['type' => \Twig\Token::BLOCK_END_TYPE],
                        ]);
                        break;
                    case 'if':
                    case 'for':
                        $this->skip($tokens, 3);
                        break;
                    default:
                        $this->skipTo($tokens, \Twig\Token::BLOCK_END_TYPE);
                }
            } elseif (\Twig\Token::NAME_TYPE === $token->getType()) {
                $previous = $this->getPreviousSignificantToken($tokens);
                $next = $this->getNextSignificantToken($tokens);

                $isHashKey = in_array($previous->getValue(), [',', '{'], true) && ':' === $next->getValue();
                $isFilter = '|' === $previous->getValue();
                $isProperty = '.' === $previous->getValue();
                $isFunctionCall = '(' === $next->getValue();
                $isTest = ('is' === $previous->getValue()) || ('is not' === $previous->getValue());
                $isReserved = in_array($token->getValue(), ['null', 'true', 'false'], true);

                if (!$isHashKey && !$isFilter && !$isProperty && !$isFunctionCall && !$isTest && !$isReserved) {
                    $scope->use($token->getValue());
                }

                $tokens->next();
            } elseif (Token::COMMENT_TYPE === $token->getType()) {
                if (0 === strpos($token->getValue(), 'twigcs use-var ')) {
                    $names = explode(',', str_replace('twigcs use-var ', '', $token->getValue()));

                    foreach ($names as $name) {
                        $scope->use(trim($name));
                    }
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
                sprintf('Unused variable "%s".', $declarationToken->getValue())
            );
        }

        return $violations;
    }
}
