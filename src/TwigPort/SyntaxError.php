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
    private $lineno;
    private $source;

    /**
     * Constructor.
     *
     * @param string      $message The error message
     * @param int         $lineno  The template line where the error occurred
     * @param Source|null $source  The source context where the error occurred
     */
    public function __construct(string $message, int $lineno = -1, Source $source = null, \Exception $previous = null)
    {
        parent::__construct($message, null, $previous);

        $this->lineno = $lineno;
        $this->source = $source;
    }

    public function getLineNo(): int
    {
        return $this->lineno;
    }

    public function getSource(): ?Source
    {
        return $this->source;
    }

    public function getSourceName(): ?string {
        return $this->source ? $this->source->getName() : null;
    }

    public function getSourceCode(): ?string
    {
        return $this->source ? $this->source->getCode() : null;
    }

    public function getSourcePath(): ?string
    {
        return $this->source ? $this->source->getPath() : null;
    }

    public function addSuggestions($name, array $items)
    {
        $alternatives = [];
        foreach ($items as $item) {
            $lev = levenshtein($name, $item);
            if ($lev <= \strlen($name) / 3 || false !== strpos($item, $name)) {
                $alternatives[$item] = $lev;
            }
        }

        if (!$alternatives) {
            return;
        }

        asort($alternatives);

        $this->appendMessage(sprintf(' Did you mean "%s"?', implode('", "', array_keys($alternatives))));
    }
}
