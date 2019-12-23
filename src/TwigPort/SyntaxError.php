<?php

/*
 * This file was taken and MODIFIED from Twig : https://github.com/twigphp/twig
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed in the same folder as this piece of code.
 */

namespace FriendsOfTwig\Twigcs\TwigPort;

class SyntaxError extends Error
{
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
