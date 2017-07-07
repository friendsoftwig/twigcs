<?php

namespace Allocine\Twigcs\Rule;

use Allocine\Twigcs\Token;
use Allocine\Twigcs\Validator\Violation;

/**
 * This is an utility class that provides some common functionnalities
 * for rule creation.
 *
 * @author Tristan Maindron <tmaindron@gmail.com>
 */
abstract class AbstractRule
{
    /**
     * @var integer
     */
    protected $severity;

    /**
     * @var Violations[]
     */
    protected $violations;

    /**
     * @param integer $severity
     */
    public function __construct($severity)
    {
        $this->severity = $severity;
    }

    public function reset()
    {
        $this->violations = [];
    }

    /**
     * @param string $filename
     * @param integer $line
     * @param string $reason
     */
    public function addViolation($filename, $line, $column, $reason)
    {
        $this->violations[] = new Violation($filename, $line, $column, $reason, $this->severity, get_called_class());
    }

    /**
     * @param \Twig_TokenStream $tokens
     * @param integer           $skip
     *
     * @return null|Token
     */
    protected function getPreviousSignicantToken(\Twig_TokenStream $tokens, $skip = 0)
    {
        $i = 1;
        $token = null;

        while ($token = $tokens->look(-$i)) {
            if (!in_array($token->getType(), [Token::WHITESPACE_TYPE, Token::NEWLINE_TYPE])) {
                if ($skip === 0) {
                    return $token;
                }

                $skip--;
            }

            $i++;
        }

        return null;
    }
}
