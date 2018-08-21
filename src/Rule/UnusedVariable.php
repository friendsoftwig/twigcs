<?php

namespace Allocine\Twigcs\Rule;

use Allocine\Twigcs\Lexer;
use Allocine\Twigcs\Scope\Scope;
use Allocine\Twigcs\Token;

class UnusedVariable extends AbstractRule implements RuleInterface
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
                        $from = $tokens->look(4);

                        if ($from->getType() === \Twig_Token::NAME_TYPE) { // {% from varName import ... %}
                            $scope->use($from->getValue());
                        }
                        $this->skipTo($tokens, \Twig_Token::BLOCK_END_TYPE);
                        break;
                    case 'set':
                        $scope->declare($tokens->look(4)->getValue(), $tokens->look(4));
                        $this->skipTo($tokens, \Twig_Token::OPERATOR_TYPE, '=');
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

                $isHashKey = in_array($previous->getValue(), [',', '{']) && $next->getValue() === ':';
                $isFilter = $previous->getValue() === '|';
                $isProperty = $previous->getValue() === '.';
                $isFunctionCall = $next->getValue() === '(';
                $isTest = ($previous->getValue() === 'is') || ($previous->getValue() === 'is not');
                $isReserved = in_array($token->getValue(), ['null', 'true', 'false']);

                if (!$isHashKey && !$isFilter && !$isProperty && !$isFunctionCall && !$isTest && !$isReserved) {
                    $scope->use($token->getValue());
                }

                $tokens->next();
            } elseif ($token->getType() === Token::COMMENT_TYPE) {
                if (strpos($token->getValue(), 'twigcs use-var ') === 0) {
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
            $this->addViolation(
                $tokens->getSourceContext()->getPath(),
                $declarationToken->getLine(),
                $declarationToken->columnno,
                sprintf('Unused variable "%s".', $declarationToken->getValue())
            );
        }

        return $this->violations;
    }
}
