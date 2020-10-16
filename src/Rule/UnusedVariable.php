<?php

namespace FriendsOfTwig\Twigcs\Rule;

use FriendsOfTwig\Twigcs\Scope\Scope;
use FriendsOfTwig\Twigcs\TwigPort\Token;
use FriendsOfTwig\Twigcs\TwigPort\TokenStream;

class UnusedVariable extends AbstractRule implements RuleInterface
{
    /**
     * {@inheritdoc}
     */
    public function check(TokenStream $tokens)
    {
        $scope = new Scope('file', 'root');
        $root = $scope;

        $violations = [];

        while (!$tokens->isEOF()) {
            $token = $tokens->getCurrent();

            if (Token::BLOCK_START_TYPE === $token->getType()) {
                $blockType = $tokens->look(2)->getValue();

                if (in_array($blockType, ['block', 'for', 'embed', 'macro'], true)) {
                    if ('block' === $blockType) {
                        $scope = $scope->spawn($blockType, $tokens->look(4)->getValue());
                    } else {
                        $scope = $scope->spawn($blockType, 'noname');
                    }
                    if ('macro' === $blockType) {
                        $scope->isolate();
                    }
                }

                if (in_array($blockType, ['endblock', 'endfor', 'endembed', 'endmacro'], true)) {
                    $scope = $scope->leave();
                }
            }

            if (Token::BLOCK_START_TYPE === $token->getType()) {
                $blockType = $tokens->look(2)->getValue();

                switch ($blockType) {
                    case 'embed':
                    case 'include':
                        if ('with' === $tokens->look(6)->getValue()) {
                            $this->skip($tokens, 8);
                        } else {
                            $this->skipTo($tokens, Token::BLOCK_END_TYPE);
                        }
                        break;
                    case 'from':
                        $from = $tokens->look(4);

                        if (Token::NAME_TYPE === $from->getType()) { // {% from varName import ... %}
                            $scope->use($from->getValue());
                        }
                        $this->skipTo($tokens, Token::BLOCK_END_TYPE);
                        break;
                    case 'set':
                        $scope->declare($tokens->look(4)->getValue(), $tokens->look(4));
                        $this->skipToOneOf($tokens, [
                            ['type' => Token::OPERATOR_TYPE, 'value' => '='],
                            ['type' => Token::BLOCK_END_TYPE],
                        ]);
                        break;
                    case 'if':
                    case 'elseif':
                        $this->skip($tokens, 3);
                        break;
                    case 'for':
                        $scope->declare($tokens->look(4)->getValue(), $tokens->look(4));
                        $this->skip($tokens, 5);
                        break;
                    default:
                        $this->skipTo($tokens, Token::BLOCK_END_TYPE);
                }
            } elseif (Token::NAME_TYPE === $token->getType()) {
                $previous = $this->getPreviousSignificantToken($tokens);
                $next = $this->getNextSignificantToken($tokens);

                $isHashKey = in_array($previous->getValue(), [',', '{'], true) && ':' === $next->getValue();
                $isFilter = '|' === $previous->getValue();
                $isProperty = '.' === $previous->getValue();
                $isFunctionCall = '(' === $next->getValue();
                $isTest = ('is' === $previous->getValue()) || ('is not' === $previous->getValue());
                $isReserved = in_array($token->getValue(), ['null', 'true', 'false'], true);

                if ($isFunctionCall && 'block' === $token->getValue()) {
                    $i = 0;
                    $blockNameToken = $tokens->look($i);
                    // Scans for the name of the nested block.
                    while (Token::BLOCK_END_TYPE !== $blockNameToken->getType() && Token::STRING_TYPE !== $blockNameToken->getType()) {
                        $blockNameToken = $tokens->look($i);
                        ++$i;
                    }
                    $scope->referenceBlock($blockNameToken->getValue());
                }

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

        foreach ($root->flatten()->getUnusedDeclarations() as $declaration) {
            $token = $declaration->getToken();

            $violations[] = $this->createViolation(
                $tokens->getSourceContext()->getPath(),
                $token->getLine(),
                $token->getColumn(),
                sprintf('Unused variable "%s".', $token->getValue())
            );
        }

        return $violations;
    }
}
