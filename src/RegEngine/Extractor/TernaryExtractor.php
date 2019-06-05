<?php

namespace Allocine\Twigcs\RegEngine\Extractor;

use Allocine\Twigcs\RegEngine\ExpressionNode;

class TernaryExtractor
{
    public function extract(ExpressionNode $node)
    {
        $collectedExpr = '';
        $currentCapture = '';
        $captures = [];
        $capturesOffsets = [];
        $depth = 0;
        $counter = 0;
        $i = 0;

        $expr = str_split($node->getExpr());

        foreach ($expr as $char) {
            $consumeChar = false;
            $nextChar = $expr[$i + 1] ?? null;
            ++$i;

            if ('?' === $char) {
                ++$depth;

                if (1 === $depth) {
                    $capturesOffsets[] = $counter + $node->getOffset() + 1;
                    $consumeChar = true;
                }
            }

            // If a comma is encountered, interrupt
            // Useful for cases like {foo: 1 ? 2, bar: 3}
            if (',' === $char && ($depth > 0)) {
                array_pop($capturesOffsets);
                $depth = 0;
                $collectedExpr .= '?'.$currentCapture;
            }

            if ((':' === $char) && ($depth > 0)) {
                --$depth;

                if (0 === $depth) {
                    $captures[] = $currentCapture;
                    $currentCapture = '';
                    $collectedExpr .= '__TERNARY__';
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

        if ($depth > 0) {
            $collectedExpr .= '?'.$currentCapture;
            $currentCapture = '';
            $consumeChar = false;
        }

        $node->replaceExpr($collectedExpr);

        foreach ($node->getChildren() as $child) {
            $this->extract($child);
        }

        foreach ($captures as $key => $capture) {
            $child = new ExpressionNode($capture, $capturesOffsets[$key], 'expr');
            $node->addChild($child);
            $this->extract($child);
            $child->replaceExpr(sprintf('?%s:', $child->getExpr()));
        }

        return $node;
    }
}
