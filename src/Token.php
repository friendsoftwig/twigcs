<?php

namespace Allocine\Twigcs;

class Token
{
    const WHITESPACE_TYPE = 12;
    const NEWLINE_TYPE    = 13;

    /**
     * @param string  $type
     * @param boolean $short
     *
     * @return string
     */
    public static function typeToString($type, $short = false)
    {
        if ($type === self::WHITESPACE_TYPE) {
            return $short ? 'WHITESPACE_TYPE' : 'Twig_Token::WHITESPACE_TYPE';
        }

        if ($type === self::NEWLINE_TYPE) {
            return $short ? 'NEWLINE_TYPE' : 'Twig_Token::NEWLINE_TYPE';
        }

        return \Twig_Token::typeToString($type, $short);
    }
}
