<?php

/*
 * This file was taken and MODIFIED from Twig : https://github.com/twigphp/twig
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed in the same folder as this piece of code.
 */

namespace FriendsOfTwig\Twigcs\TwigPort;

class SyntaxError extends \Exception
{
    private int $lineno;
    private int $columnno;
    private ?Source $source;

    /**
     * @param string      $message The error message
     * @param int         $lineno  The template line where the error occurred
     * @param Source|null $source  The source context where the error occurred
     */
    public function __construct(string $message, int $lineno = -1, int $columnno = -1, Source $source = null)
    {
        parent::__construct($message);

        $this->lineno = $lineno;
        $this->columnno = $columnno;
        $this->source = $source;
    }

    public function getLineNo(): int
    {
        return $this->lineno;
    }

    /**
     * @return Source|null
     */
    public function getSource()
    {
        return $this->source;
    }

    /**
     * @return string|null
     */
    public function getSourceName()
    {
        return $this->source ? $this->source->getName() : null;
    }

    /**
     * @return string|null
     */
    public function getSourceCode()
    {
        return $this->source ? $this->source->getCode() : null;
    }

    /**
     * @return string|null
     */
    public function getSourcePath()
    {
        return $this->source ? $this->source->getPath() : null;
    }

    public function getColumnNo(): int
    {
        return $this->columnno;
    }
}
