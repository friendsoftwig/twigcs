<?php

namespace Allocine\TwigLinter;

class Lexer extends \Twig_Lexer
{
    const WHITESPACE_TYPE = 12;
    const NEWLINE_TYPE    = 13;

    const PREVIOUS_TOKEN = -1;
    const NEXT_TOKEN     = 1;


    protected function lexExpression()
    {
        // collect whitespaces and new lines
        if (preg_match('/\s+/A', $this->code, $match, null, $this->cursor)) {
            $emptyLines = explode("\n", $match[0]);

            foreach ($emptyLines as $line) {
                if (strlen($line) == 0) {
                    $this->pushToken(self::NEWLINE_TYPE);
                } else {
                    $this->pushToken(self::WHITESPACE_TYPE, $line);
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
                $this->pushToken(self::WHITESPACE_TYPE, $spaces[0]);
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
                $this->pushToken(self::WHITESPACE_TYPE, $spaces[0]);
            }

            $this->pushToken(\Twig_Token::VAR_END_TYPE);
            $this->moveCursor($match[0]);
            $this->popState();
        } else {
            $this->lexExpression();
        }
    }
}
