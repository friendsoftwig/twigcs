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

        foreach (str_split($expr) as $char) {
            if ($char === '(') {
                if ($parenthesesDepth === 0) {
                    $capturesOffsets[]= $counter;
                }
                if ($parenthesesDepth > 0) {
                    $currentCapture .= $char;
                }
                $parenthesesDepth++;
            } elseif ($char === ')') {
                $parenthesesDepth--;
                if ($parenthesesDepth > 0) {
                    $currentCapture .= $char;
                }
                if ($parenthesesDepth === 0) {
                    $captures[]= $currentCapture;
                    $currentCapture = '';
                    $collectedExpr .= 'PARENTHESES';
                }
            } else {
                if ($parenthesesDepth > 0) {
                    $currentCapture .= $char;
                } else {
                    $collectedExpr .= $char;
                }
            }

            $counter++;
        }

        $node = new ParenthesesNode();
        $node->expr = $collectedExpr;
        $node->offset = $offset;
        $node->children = [];

        foreach ($captures as $key => $capture) {
            $offset = $capturesOffsets[$key];
            $node->children[$key]= $this->extract($capture, $offset);
        }

        return $node;
    }
}
