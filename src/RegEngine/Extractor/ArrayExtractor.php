<?php

namespace Allocine\Twigcs\RegEngine\Extractor;

use Allocine\Twigcs\RegEngine\ExpressionNode;

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

        foreach (str_split($node->getExpr()) as $char) {
            $consumeChar = false;

            if ('[' === $char) {
                ++$depth;

                if (1 === $depth) {
                    $capturesOffsets[] = $counter + $node->getOffset() + 1;
                    $consumeChar = true;
                }
            }

            if (']' === $char && ($depth > 0)) {
                --$depth;

                if (0 === $depth) {
                    $captures[] = $currentCapture;
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

            ++$counter;
        }

        $node->replaceExpr($collectedExpr);

        foreach ($node->getChildren() as $child) {
            $this->extract($child);
        }

        foreach ($captures as $key => $capture) {
            $child = new ExpressionNode($capture, $capturesOffsets[$key], 'expr');
            $node->addChild($child);
            $this->extract($child);
            $child->replaceExpr(sprintf('[%s]', $child->getExpr()));
        }

        return $node;
    }
}
