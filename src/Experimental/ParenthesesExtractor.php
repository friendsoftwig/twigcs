<?php

namespace Allocine\Twigcs\Experimental;

class ParenthesesExtractor
{
    public function extract(string $expr, int $offset = 0)
    {
        $parenthesesDepth = 0;
        $collectedExpr = '';
        $currentCapture = '';
        $captures = [];
        $capturesOffsets = [];
        $counter = 0;
        $lastSymbolIsName = false;
        $previousWord = '';
        $resetWord = false;

        $stack = [];

        foreach (str_split($expr) as $char) {
            $lastSymbolIsName = preg_match('/^[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*$/', $previousWord);
            $consumeChar = false;

            if ($char === '(') {
                $type = $lastSymbolIsName ? 'f' : 'p';

                if ($type === 'p') {
                    $parenthesesDepth++;
                }

                if ($parenthesesDepth === 1 && $type === 'p') {
                    $capturesOffsets[]= $counter + $offset + 1;
                    $consumeChar = true;
                }

                $stack[]= $type;
            } elseif ($char === ')') {
                $type = array_pop($stack);

                if ($type === 'p') {
                    $parenthesesDepth--;

                    if ($parenthesesDepth === 0) {
                        $captures[]= $currentCapture;
                        $currentCapture = '';
                        $collectedExpr .= 'EXPR';
                        $consumeChar = true;
                    }
                }
            }

            if (!$consumeChar) {
                if ($parenthesesDepth > 0) {
                    $currentCapture .= $char;
                } else {
                    $collectedExpr .= $char;
                }
            }

            if ($char != ' ') {
                if ($resetWord) {
                    $resetWord = false;
                    $previousWord = '';
                }
                $previousWord .= $char;
            } else {
                $resetWord = true;
            }

            $counter++;
        }

        $node = new ParenthesesNode();
        $node->expr = $collectedExpr;
        $node->offset = $offset;
        $node->children = [];

        foreach ($captures as $key => $capture) {
            $node->children[$key]= $this->extract($capture, $capturesOffsets[$key]);
        }

        return $node;
    }
}
