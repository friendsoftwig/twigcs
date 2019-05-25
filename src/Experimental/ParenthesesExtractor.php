<?php

namespace Allocine\Twigcs\Experimental;

class ParenthesesExtractor
{
    const PARENTHESES = 0;
    const FUNCTION_CALL = 1;

    const VARIABLE_PATTERN = '/^[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*$/';

    public function extract(ExpressionNode $node)
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

        foreach (str_split($node->expr) as $char) {
            $lastSymbolIsName = preg_match(self::VARIABLE_PATTERN, $previousWord);
            $consumeChar = false;

            if ($char === '(') {
                $type = $lastSymbolIsName ? self::FUNCTION_CALL : self::PARENTHESES;

                if ($type === self::PARENTHESES) {
                    $parenthesesDepth++;
                }

                if ($parenthesesDepth === 1 && $type === self::PARENTHESES) {
                    $capturesOffsets[]= $counter + $node->offset + 1;
                    $consumeChar = true;
                }

                $stack[]= $type;
            } elseif ($char === ')') {
                $type = array_pop($stack);

                if ($type === self::PARENTHESES) {
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

            if (!in_array($char, [' ', '.', '|'])) {
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

        $node->replaceExpr($collectedExpr);

        foreach ($node->children as $child) {
            $this->extract($child);
        }

        foreach ($captures as $key => $capture) {
            $child = new ExpressionNode($capture, $capturesOffsets[$key]);
            $node->addChild($child);
            $this->extract($child);
            $child->replaceExpr('(' . $child->expr . ')');
        }

        return $node;
    }
}
