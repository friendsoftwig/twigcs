<?php

namespace Allocine\Twigcs\Rule;

use Allocine\Twigcs\Token;
use Allocine\Twigcs\Validator\Violation;

class UnusedMacro extends AbstractRule
{
    const KEY_GLOBAL = 'global';
    const KEY_LOCAL = 'local';
    const KEY_USED = 'used';

    /**
     * @var array
     */
    private $macroUsage = [
        self::KEY_GLOBAL => [],
        self::KEY_LOCAL  => [],
        self::KEY_USED   => [],
    ];

    /**
     * {@inheritdoc}
     */
    public function prepare(\Twig_TokenStream $tokens)
    {
        while (!$tokens->isEOF()) {
            $this->checkDeclaration($tokens);

            if ($tokens->isEOF()) {
                continue;
            }

            $tokens->next();
        }
    }

    /**
     * {@inheritdoc}
     */
    public function check(\Twig_TokenStream $tokens)
    {
        $path = $tokens->getSourceContext()->getPath();
        while (!$tokens->isEOF()) {
            $token = $tokens->getCurrent();

            if ($token->getType() === \Twig_Token::NAME_TYPE &&
                !empty($this->macroUsage[self::KEY_LOCAL][$path]) &&
                !$this->hasTagBefore($tokens, 'macro') &&
                !$this->hasTagBefore($tokens, 'import')
            ) {
                $filter = array_filter($this->macroUsage[self::KEY_LOCAL][$path], function (\stdClass $local) use ($token) {
                    return $local->alias === $token->getValue() || $local->value === $token->getValue();
                });

                if (!empty($filter)) {
                    $filter = array_pop($filter);
                    $this->setUsedLocal($filter->local_path ? $filter->local_path : $filter->macro_path, $filter);
                    $macroPath = $this->getGlobalPathKey($filter->macro_path);
                    if ($filter->with_from) {
                        $this->setUsedGlobal($macroPath, $filter->value);
                    } else {
                        $macro = $tokens->look(2);
                        if ($macro->getType() === \Twig_Token::NAME_TYPE) {
                            $this->setUsedGlobal($macroPath, $macro->getValue());
                        }
                    }
                }
            }

            $tokens->next();
        }
    }

    /**
     * @return Violation[]
     */
    public function getViolations()
    {
        foreach ($this->macroUsage[self::KEY_GLOBAL] as $file => $macros) {
            array_map(function (\stdClass $macro) use ($file) {
                if (!$macro->used) {
                    $this->addViolation($file, $macro->line, $macro->column, sprintf('Unused global macro "%s".', $macro->value));
                }
            }, $macros);
        }
        foreach ($this->macroUsage[self::KEY_LOCAL] as $file => $macros) {
            array_map(function (\stdClass $macro) use ($file) {
                if (!$macro->used) {
                    $reason = sprintf('Unused local macro "%s".', $macro->value);
                    $reason .= $macro->alias ? sprintf(' With alia "%s".', $macro->alias) : '';
                    $this->addViolation($file, $macro->line, $macro->column, $reason);
                }
            }, $macros);
        }

        return parent::getViolations();
    }

    /**
     * @param \stdClass $token
     * @param string    $key
     */
    private function setDeclaredMacro(\stdClass $token, string $key)
    {
        if ($token->type == \Twig_Token::NAME_TYPE) {
            $path = is_null($token->local_path) ? $token->macro_path : $token->local_path;
            if (!array_key_exists($path, $this->macroUsage[$key])) {
                $this->macroUsage[$key][$path] = [$token];
            } else {
                $this->macroUsage[$key][$path] = array_merge($this->macroUsage[$key][$path], [$token]);
            }
        }
    }

    /**
     * @param \Twig_TokenStream $tokens
     *
     * @throws \Twig_Error_Syntax
     */
    private function checkDeclaration(\Twig_TokenStream $tokens)
    {
        $token = $tokens->getCurrent();
        if ($token->getType() === \Twig_Token::NAME_TYPE && $token->getValue() === 'macro') {
            // Macro declaration => {% macro foo() %}{% endmacro %}. We logged global declaration
            $this->moveCursor($tokens, 2);
            $this->setDeclaredMacro(
                $this->getTokenRepresentation($tokens->getCurrent(), $tokens->getSourceContext()->getPath()),
                self::KEY_GLOBAL
            );
            $tokens->next();
        } elseif ($token->getType() === \Twig_Token::NAME_TYPE && $token->getValue() === 'import') {
            // Macro usage => {% from _self import foo as bar, baz %}{{ bar() }}{{ baz() }}
            //                {% import _self as foo %}{{ foo.bar() }} => with bar as a macro in the self file
            // _self can be changed by the file name in string. We logged local declaration
            $this->moveCursor($tokens, 2);

            $token = $tokens->getCurrent();
            $withFrom = false;
            $localPath = $tokens->getSourceContext()->getPath();
            if ($token->getType() === \Twig_Token::STRING_TYPE) {
                // import 'path/to/twig'
                $macroPath = $token->getValue();
            } elseif ($tokens->look(-4)->getType() === \Twig_Token::STRING_TYPE) {
                // from 'path/to/twig' import xxx
                $macroPath = $tokens->look(-4)->getValue();
                $withFrom = true;
            } else {
                // from or import _self
                if ($tokens->look(-4)->getType() === \Twig_Token::NAME_TYPE) {
                    $withFrom = true;
                }
                $macroPath = $localPath;
                $localPath = null;
            }

            if (!$withFrom) {
                $this->moveCursor($tokens, 4);
                $token = $tokens->getCurrent();
                $this->setDeclaredMacro(
                    $this->getTokenRepresentation($token, $macroPath, $localPath, $withFrom),
                    self::KEY_LOCAL
                );
            } else {
                while (in_array($tokens->getCurrent()->getType(), [\Twig_Token::NAME_TYPE, \Twig_Token::PUNCTUATION_TYPE, Token::WHITESPACE_TYPE])) {
                    $token = $tokens->getCurrent();
                    if ($token->getValue() === 'as') {
                        $alias = $tokens->look(2)->getValue();
                        $this->setDeclaredMacro(
                            $this->getTokenRepresentation($tokens->look(-2), $macroPath, $localPath, $withFrom, $alias),
                            self::KEY_LOCAL
                        );
                    } elseif ($token->getType() === \Twig_Token::NAME_TYPE &&
                        in_array($tokens->look(1)->getType(), [\Twig_Token::PUNCTUATION_TYPE, Token::WHITESPACE_TYPE]) &&
                        $tokens->look(-2)->getValue() !== 'as' &&
                        $tokens->look(2)->getValue() !== 'as'
                    ) {
                        $this->setDeclaredMacro(
                            $this->getTokenRepresentation($token, $macroPath, $localPath, $withFrom),
                            self::KEY_LOCAL
                        );
                    }

                    $tokens->next();
                }
            }
            $tokens->next();
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

    /**
     * @param \Twig_TokenStream $tokens
     * @param string            $tag
     *
     * @return bool
     * @throws \Twig_Error_Syntax
     */
    private function hasTagBefore(\Twig_TokenStream $tokens, string $tag)
    {
        $i = 1;
        while (!$tokens->isEOF()) {
            $token = $tokens->look(-$i);
            if ($token->getValue() === $tag) {
                return true;
            }

            if (in_array($token->getType(), [\Twig_Token::BLOCK_START_TYPE, \Twig_Token::VAR_START_TYPE])) {
                return false;
            }
            $i++;
        }

        return false;
    }

    /**
     * @param string    $path
     * @param \stdClass $declaration
     */
    private function setUsedLocal(string $path, \stdClass $declaration)
    {
        if (!empty($this->macroUsage[self::KEY_LOCAL][$path])) {
            foreach ($this->macroUsage[self::KEY_LOCAL][$path] as &$v) {
                if ($v->line === $declaration->line &&
                    $v->column === $declaration->column &&
                    $v->value === $declaration->value &&
                    $v->alias === $declaration->alias &&
                    $v->local_path === $declaration->local_path
                ) {
                    $v->used = true;
                }
            }
        }
    }

    /**
     * @param string $path
     * @param string $macroName
     */
    private function setUsedGlobal(string $path, string $macroName)
    {
        if (!empty($this->macroUsage[self::KEY_GLOBAL][$path])) {
            foreach ($this->macroUsage[self::KEY_GLOBAL][$path] as &$v) {
                if ($v->value === $macroName && $v->macro_path === $path) {
                    $v->used = true;
                }
            }
        }
    }

    /**
     * @param \Twig_Token $token
     * @param string      $macroPath
     * @param string      $localPath
     * @param bool        $withFrom
     * @param string|null $alias
     *
     * @return object
     */
    private function getTokenRepresentation(
        \Twig_Token $token,
        string $macroPath,
        string $localPath = null,
        bool $withFrom = false,
        string $alias = null
    ) {
        return (object)[
            'line'       => $token->getLine(),
            'column'     => $token->columnno,
            'value'      => $token->getValue(),
            'type'       => $token->getType(),
            'macro_path' => $macroPath,
            'local_path' => $localPath,
            'with_from'  => $withFrom,
            'alias'      => $alias,
            'used'       => false,
        ];
    }

    /**
     * @param string $path
     *
     * @return string
     */
    private function getGlobalPathKey(string $path)
    {
        foreach ($this->macroUsage[self::KEY_GLOBAL] as $globalPath => $macros) {
            if (strstr($globalPath, $path)) {
                return $globalPath;
            }
        }

        return $path;
    }
}
