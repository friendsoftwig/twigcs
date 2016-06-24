<?php

namespace Allocine\TwigLinter;

class Lexer extends \Twig_Lexer
{
    const PREVIOUS_TOKEN = -1;
    const NEXT_TOKEN     = 1;

    /**
     * @var integer
     */
    protected $columnno = 0;

    protected function lexExpression()
    {
        // collect whitespaces and new lines
        if (preg_match('/\s+/A', $this->code, $match, null, $this->cursor)) {
            $emptyLines = explode("\n", $match[0]);

            foreach ($emptyLines as $line) {
                if (strlen($line) == 0) {
                    $this->pushToken(Token::NEWLINE_TYPE);
                } else {
                    $this->pushToken(Token::WHITESPACE_TYPE, $line);
                }
            }

        }

        parent::lexExpression();
    }

    protected function lexBlock()
    {
        if (empty($this->brackets) && preg_match($this->regexes['lex_block'], $this->code, $match, null, $this->cursor)) {
            // Collect whitespaces in end blocks
            if (preg_match('/^(\s+)/', $match[0], $spaces)) {
                $this->pushToken(Token::WHITESPACE_TYPE, $spaces[0]);
            }

            $this->pushToken(\Twig_Token::BLOCK_END_TYPE);
            $this->moveCursor($match[0]);
            $this->popState();
        } else {
            $this->lexExpression();
        }
    }

    protected function lexVar()
    {
        if (empty($this->brackets) && preg_match($this->regexes['lex_var'], $this->code, $match, null, $this->cursor)) {
            // Collect whitespaces in end blocks
            if (preg_match('/^(\s+)/', $match[0], $spaces)) {
                $this->pushToken(Token::WHITESPACE_TYPE, $spaces[0]);
            }

            $this->pushToken(\Twig_Token::VAR_END_TYPE);
            $this->moveCursor($match[0]);
            $this->popState();
        } else {
            $this->lexExpression();
        }
    }

    /**
     * @param int    $type
     * @param string $value
     */
    protected function pushToken($type, $value = '')
    {
        // do not push empty text tokens
        if (Token::TEXT_TYPE === $type && '' === $value) {
            return;
        }

        // The base lexer doesn't call moveCursor when encountering punctuations. (See class \Twig_Lexer line 269)
        if ($type == \Twig_Token::PUNCTUATION_TYPE) {
            $this->columnno++;
        }

        $this->tokens[] = new Token($type, $value, $this->lineno, $this->columnno);
    }

    /**
     * @param string $text
     */
    protected function moveCursor($text)
    {
        $lineCount = substr_count($text, "\n");
        $movement = strlen($text);

        $this->cursor += $movement;
        $this->lineno += $lineCount;
        $this->columnno = $lineCount ? strlen(substr($text, strrpos($text, "\n") + 1)) : ($this->columnno + $movement);
    }
}
