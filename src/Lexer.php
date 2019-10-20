<?php

namespace FriendsOfTwig\Twigcs;

/**
 * Backward compatibility with Twig 1.X.
 */
$majorVersion = \Twig\Environment::MAJOR_VERSION;

// Detects this twig issue : https://github.com/twigphp/Twig/commit/cee29710248d27d881772a3a44517a97b7402c67
// that causes twig 1.38.2 to 1.38.4 to be flagged as a twig 2.x
if (in_array(\Twig\Environment::VERSION_ID, [13802, 13803, 13804], true)) {
    $majorVersion = 1;
}

if ($majorVersion > 1) {
    class_alias(\FriendsOfTwig\Twigcs\Compatibility\TwigLexer::class, 'FriendsOfTwig\Twigcs\BaseLexer');
} else {
    class_alias(\Twig\Lexer::class, 'FriendsOfTwig\Twigcs\BaseLexer');
}

/**
 * An override of Twig's Lexer to add whitespace and new line detection.
 * It also populates a column number property on tokens.
 *
 * @author Tristan Maindron <tmaindron@gmail.com>
 */
class Lexer extends BaseLexer
{
    const PREVIOUS_TOKEN = -1;
    const NEXT_TOKEN = 1;

    /**
     * @var int
     */
    protected $columnno = 0;

    protected function lexExpression()
    {
        // collect whitespaces and new lines
        if (preg_match('/\s+/A', $this->code, $match, null, $this->cursor)) {
            if ("\n" === $match[0]) {
                $emptyLines = [''];
            } else {
                $emptyLines = explode("\n", $match[0]);
            }

            foreach ($emptyLines as $line) {
                if (0 === strlen($line)) {
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

            $this->pushToken(\Twig\Token::BLOCK_END_TYPE);
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

            $this->pushToken(\Twig\Token::VAR_END_TYPE);
            $this->moveCursor($match[0]);
            $this->popState();
        } else {
            $this->lexExpression();
        }
    }

    protected function lexComment()
    {
        if (!preg_match($this->regexes['lex_comment'], $this->code, $match, PREG_OFFSET_CAPTURE, $this->cursor)) {
            throw new \Twig\Error\SyntaxError('Unclosed comment.', $this->lineno, $this->source);
        }

        $content = substr($this->code, $this->cursor, $match[0][1] - $this->cursor);

        $this->pushToken(Token::COMMENT_TYPE, trim($content));

        $this->moveCursor($content.$match[0][0]);
    }

    /**
     * @param int    $type
     * @param string $value
     */
    protected function pushToken($type, $value = '')
    {
        // do not push empty text tokens
        if (\Twig\Token::TEXT_TYPE === $type && '' === $value) {
            return;
        }

        // The base lexer doesn't call moveCursor when encountering punctuations. (See class \Twig\Lexer line 269)
        if (\Twig\Token::PUNCTUATION_TYPE === $type) {
            ++$this->columnno;
        }

        $token = new \Twig\Token($type, $value, $this->lineno);

        // Twig tokens cannot be extended anymore since 2.0, so a dynamic attribute
        // is the only way to store the column number.
        $token->columnno = $this->columnno;

        $this->tokens[] = $token;
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
