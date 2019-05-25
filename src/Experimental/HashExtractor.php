<?php

namespace Allocine\Twigcs\Experimental;

class HashExtractor
{
    const VARIABLE_PATTERN = '/^[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*$/';

    public function extract(ExpressionNode $node)
    {
        $collectedExpr = '';
        $currentCapture = '';
        $captures = [];
        $capturesOffsets = [];
        $depth = 0;
        $counter = 0;

        foreach (str_split($node->expr) as $char) {
            $prevChar = $node->expr[$counter - 1] ?? null;
            $nextChar = $node->expr[$counter + 1] ?? null;
            $consumeChar = false;

            if ($char === '{' && $prevChar != '{' && $nextChar != '{') {
                $depth++;

                if ($depth === 1) {
                    $capturesOffsets[]= $counter + $node->offset + 1;
                    $consumeChar = true;
                }
            }

            if ($char === '}' && (($depth > 0) || ($prevChar != '}' && $nextChar != '}'))) {
                $depth--;

                if ($depth === 0) {
                    $captures[]= $currentCapture;
                    $currentCapture = '';
                    $collectedExpr .= 'EXPR';
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
            $child = new ExpressionNode($capture, $capturesOffsets[$key]);
            $node->addChild($child);
            $this->extract($child);
            $child->replaceExpr('{' . $child->expr . '}');
        }

        return $node;
    }
}
