<?php

namespace FriendsOfTwig\Twigcs\Scope;

use FriendsOfTwig\Twigcs\Lexer;
use FriendsOfTwig\Twigcs\TemplateResolver\TemplateResolverInterface;
use FriendsOfTwig\Twigcs\TwigPort\SyntaxError;
use FriendsOfTwig\Twigcs\TwigPort\Token;
use FriendsOfTwig\Twigcs\TwigPort\TokenStream;
use FriendsOfTwig\Twigcs\Util\StreamNavigator;

/**
 * @author Tristan Maindron <tmaindron@gmail.com>
 */
class ScopeBuilder
{
    public const MODE_MACRO = 0;
    public const MODE_VARIABLE = 1;

    private int $mode;
    private int $maxDepth = 5;
    private TemplateResolverInterface $loader;

    public function __construct(TemplateResolverInterface $loader, int $mode = 0)
    {
        $this->mode = $mode;
        $this->loader = $loader;
    }

    public static function createVariableScopeBuilder(TemplateResolverInterface $loader)
    {
        return new self($loader, self::MODE_VARIABLE);
    }

    public static function createMacroScopeBuilder(TemplateResolverInterface $loader)
    {
        return new self($loader, self::MODE_MACRO);
    }

    public function build(TokenStream $tokens)
    {
        return $this->doBuild($tokens);
    }

    private function subScope(string $twigPath, int $depth): Scope
    {
        if ($depth > $this->maxDepth) {
            return new Scope('file', $twigPath);
        }

        $lexer = new Lexer();

        $source = $this->loader->load($twigPath);

        try {
            $tokens = $lexer->tokenize($source);
            $scope = $this->doBuild($tokens, $twigPath, $this->maxDepth + 1);

            return $scope;
        } catch (SyntaxError $e) {
            return new Scope('file', $twigPath);
        }
    }

    private function doBuild(TokenStream $tokens, $name = 'root', $depth = 0): Scope
    {
        $scope = new Scope('file', $name);
        $root = $scope;

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
                    case 'extends':
                        $templateName = $tokens->look(4);

                        if (Token::NAME_TYPE === $templateName->getType()) { // {% import varName ... %}
                            $scope->use($templateName->getValue());
                        }
                        $file = $tokens->look(4)->getValue();

                        $scope->extends($this->subScope($file, $depth));

                        StreamNavigator::skipToOneOf($tokens, [
                            ['type' => Token::BLOCK_END_TYPE],
                        ]);

                        break;

                    case 'embed':
                    case 'include':
                        $templateName = $tokens->look(4);

                        if (Token::NAME_TYPE === $templateName->getType()) { // {% import varName ... %}
                            $scope->use($templateName->getValue());
                        }
                        $file = $tokens->look(4)->getValue();

                        $scope->nest($this->subScope($file, $depth));

                        StreamNavigator::skipToOneOf($tokens, [
                            ['value' => 'with'],
                            ['type' => Token::BLOCK_END_TYPE],
                        ]);

                        break;

                    case 'from':
                        $from = $tokens->look(4);

                        if (Token::NAME_TYPE === $from->getType()) { // {% from varName import ... %}
                            if ($this->handleVariables()) {
                                $scope->use($from->getValue());
                            }
                        }

                        if ('as' === $tokens->look(10)->getValue()) {
                            $forward = 12; // Extracts token position from block of form {% import foo as bar %}
                        } else {
                            $forward = 8; // Extracts token position from block of form {% import foo %}
                        }

                        StreamNavigator::skip($tokens, $forward);

                        // Handles single or multiple imports ( {% from "file.twig" import foo as bar, baz %} )
                        while (in_array($tokens->getCurrent()->getType(), [Token::NAME_TYPE, Token::PUNCTUATION_TYPE, Token::WHITESPACE_TYPE], true)) {
                            $next = $tokens->getCurrent();

                            if (Token::NAME_TYPE === $next->getType() && $this->handleMacros()) {
                                $scope->declare($next->getValue(), $next);
                            }
                            $tokens->next();
                        }

                        StreamNavigator::skipTo($tokens, Token::BLOCK_END_TYPE);

                        break;

                    case 'import':
                        StreamNavigator::skipTo($tokens, Token::NAME_TYPE, 'as');
                        StreamNavigator::skip($tokens, 2);

                        // Handles single or multiple imports ( {% import foo as bar, baz %} )
                        while (in_array($tokens->getCurrent()->getType(), [Token::NAME_TYPE, Token::PUNCTUATION_TYPE, Token::WHITESPACE_TYPE], true)) {
                            $next = $tokens->getCurrent();

                            if (Token::NAME_TYPE === $next->getType() && $this->handleMacros()) {
                                $scope->declare($next->getValue(), $next);
                            }
                            $tokens->next();
                        }

                        break;

                    case 'set':
                        if ($this->handleVariables()) {
                            $scope->declare($tokens->look(4)->getValue(), $tokens->look(4));
                        }
                        StreamNavigator::skipToOneOf($tokens, [
                            ['type' => Token::OPERATOR_TYPE, 'value' => '='],
                            ['type' => Token::BLOCK_END_TYPE],
                        ]);

                        break;

                    case 'if':
                    case 'elseif':
                        StreamNavigator::skip($tokens, 3);

                        break;

                    case 'for':
                        if ($this->handleVariables()) {
                            $scope->declare($tokens->look(4)->getValue(), $tokens->look(4));
                        }
                        StreamNavigator::skip($tokens, 5);

                        break;

                    default:
                        StreamNavigator::skipTo($tokens, Token::BLOCK_END_TYPE);
                }
            } elseif (Token::NAME_TYPE === $token->getType()) {
                $previous = StreamNavigator::getPreviousSignificantToken($tokens);
                $next = StreamNavigator::getNextSignificantToken($tokens);

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

                if (!$isHashKey && !$isFilter && !$isProperty && !$isFunctionCall && !$isTest && !$isReserved && $this->handleVariables()) {
                    $scope->use($token->getValue());
                }

                $isSubProperty = in_array($previous->getValue(), ['.', '|'], true);
                $directUsage = in_array($next->getValue(), ['('], true);
                $dotUsage = (Token::NAME_TYPE === StreamNavigator::getNextSignificantToken($tokens, 1)->getType()) && in_array(StreamNavigator::getNextSignificantToken($tokens, 2)->getValue(), ['('], true);

                if (!$isSubProperty && ($directUsage || $dotUsage) && $this->handleMacros()) {
                    $scope->use($token->getValue());
                }

                $tokens->next();
            } elseif (Token::COMMENT_TYPE === $token->getType()) {
                if (0 === strpos($token->getValue(), 'twigcs use-var ')) {
                    $names = explode(',', str_replace('twigcs use-var ', '', $token->getValue()));

                    foreach ($names as $name) {
                        if ($this->handleVariables()) {
                            $scope->use(trim($name));
                        }
                    }
                }

                $tokens->next();
            } else {
                $tokens->next();
            }
        }

        return $root;
    }

    private function handleVariables()
    {
        return self::MODE_VARIABLE === $this->mode;
    }

    private function handleMacros()
    {
        return self::MODE_MACRO === $this->mode;
    }
}
