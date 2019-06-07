<?php

namespace FriendsOfTwig\Twigcs;

class Token
{
    const WHITESPACE_TYPE = 12;
    const NEWLINE_TYPE = 13;
    const COMMENT_TYPE = 14;

    /**
     * @param string $type
     * @param bool   $short
     *
     * @return string
     */
    public static function typeToString($type, $short = false)
    {
        if (self::WHITESPACE_TYPE === $type) {
            return $short ? 'WHITESPACE_TYPE' : 'Twig_Token::WHITESPACE_TYPE';
        }

        if (self::NEWLINE_TYPE === $type) {
            return $short ? 'NEWLINE_TYPE' : 'Twig_Token::NEWLINE_TYPE';
        }

        if (self::COMMENT_TYPE === $type) {
            return $short ? 'COMMENT_TYPE' : 'Twig_Token::COMMENT_TYPE';
        }

        return \Twig_Token::typeToString($type, $short);
    }
}
