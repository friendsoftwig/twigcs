<?php

namespace Allocine\Twigcs\Experimental;

class ArrayExtractor
{
    public function extract(ExpressionNode $node)
    {
        $collectedExpr = '';
        $currentCapture = '';
        $captures = [];
        $capturesOffsets = [];
        $depth = 0;
        $counter = 0;

        foreach (str_split($node->expr) as $char) {
            $consumeChar = false;

            if ($char === '[') {
                $depth++;

                if ($depth === 1) {
                    $capturesOffsets[]= $counter + $node->offset + 1;
                    $consumeChar = true;
                }
            }

            if ($char === ']' && ($depth > 0)) {
                $depth--;

                if ($depth === 0) {
                    $captures[]= $currentCapture;
                    $currentCapture = '';
                    $collectedExpr .= '__ARRAY__';
                    $consumeChar = true;
                }
            }

            if (!$consumeChar) {
                if ($depth > 0) {
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
            $child = new ExpressionNode($capture, $capturesOffsets[$key], 'expr');
            $node->addChild($child);
            $this->extract($child);
            $child->replaceExpr('[' . $child->expr . ']');
        }

        return $node;
    }
}
