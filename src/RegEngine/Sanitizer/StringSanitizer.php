<?php

namespace FriendsOfTwig\Twigcs\RegEngine\Sanitizer;

class StringSanitizer
{
    public const NEUTRAL_CHAR = 'A';

    public function sanitize(string $expr)
    {
        $insideString = false;
        $stringOpener = null;
        $result = '';
        $escaped = false;

        foreach (str_split($expr) as $char) {
            if ('\\' === $char) {
                $escaped = true;
                $result .= self::NEUTRAL_CHAR;

                continue;
            }

            if (!$insideString) {
                if (in_array($char, ['"', "'"], true) && !$escaped) {
                    $insideString = true;
                    $stringOpener = $char;
                    $result .= $char;

                    continue;
                }
            }

            if ($insideString && ($char === $stringOpener) && !$escaped) {
                $result .= $char;
                $insideString = false;

                continue;
            }

            $escaped = false;

            if ($insideString) {
                $result .= self::NEUTRAL_CHAR;
            } else {
                $result .= $char;
            }
        }

        return $result;
    }
}
