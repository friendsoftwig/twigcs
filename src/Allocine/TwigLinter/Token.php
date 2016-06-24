<?php

namespace Allocine\TwigLinter;

class Token extends \Twig_Token
{
    const WHITESPACE_TYPE = 12;
    const NEWLINE_TYPE    = 13;

    /**
     * @var int
     */
    private $columnno;

    /**
     * @param int    $type
     * @param string $value
     * @param int    $lineno
     * @param int    $columnno
     */
    public function __construct($type, $value, $lineno, $columnno)
    {
        parent::__construct($type, $value, $lineno);

        $this->columnno = $columnno;
    }

    /**
     * @return int
     */
    public function getColumn()
    {
        return $this->columnno;
    }

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
