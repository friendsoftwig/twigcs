<?php

namespace Allocine\Twigcs\Experimental;

class ParenthesesExtractor
{
    public function extract(ExpressionNode $node)
    {
        $parenthesesDepth = 0;
        $collectedExpr = '';
        $currentCapture = '';
        $captures = [];
        $capturesOffsets = [];
        $counter = 0;

        $stack = [];

        foreach (str_split($node->expr) as $char) {
            $consumeChar = false;

            if ($char === '(') {
                $parenthesesDepth++;

                if ($parenthesesDepth === 1) {
                    $capturesOffsets[]= $counter + $node->offset + 1;
                    $consumeChar = true;
                }

                $stack[]= $type;
            } elseif ($char === ')') {
                $type = array_pop($stack);

                $parenthesesDepth--;

                if ($parenthesesDepth === 0) {
                    $captures[]= $currentCapture;
                    $currentCapture = '';
                    $collectedExpr .= '__PARENTHESES__';
                    $consumeChar = true;
                }
            }

            if (!$consumeChar) {
                if ($parenthesesDepth > 0) {
                    $currentCapture .= $char;
                } else {
                    $collectedExpr .= $char;
                }
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
