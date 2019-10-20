<?php

namespace Allocine\Twigcs;

class Token
{
    const WHITESPACE_TYPE = 12;
    const NEWLINE_TYPE    = 13;
    const COMMENT_TYPE    = 14;

    /**
     * @param string  $type
     * @param boolean $short
     *
     * @return string
     */
    public static function typeToString($type, $short = false)
    {
        if (self::WHITESPACE_TYPE === $type) {
            return $short ? 'WHITESPACE_TYPE' : 'Twig\Token::WHITESPACE_TYPE';
        }

        if (self::NEWLINE_TYPE === $type) {
            return $short ? 'NEWLINE_TYPE' : 'Twig\Token::NEWLINE_TYPE';
        }

        if (self::COMMENT_TYPE === $type) {
            return $short ? 'COMMENT_TYPE' : 'Twig\Token::COMMENT_TYPE';
        }

        return \Twig\Token::typeToString($type, $short);
    }
}
