<?php

namespace Allocine\Twigcs\RegEngine\Extractor;

use Allocine\Twigcs\RegEngine\ExpressionNode;

class HashExtractor
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
            $expr = $node->getExpr();
            $prevChar = $expr[$counter - 1] ?? null;
            $nextChar = $expr[$counter + 1] ?? null;
            $consumeChar = false;

            if ('{' === $char && '{' !== $prevChar && '{' !== $nextChar) {
                ++$depth;

                if (1 === $depth) {
                    $capturesOffsets[] = $counter + $node->getOffset() + 1;
                    $consumeChar = true;
                }
            }

            if ('}' === $char && (($depth > 0) || ('}' !== $prevChar && '}' !== $nextChar))) {
                --$depth;

                if (0 === $depth) {
                    $captures[] = $currentCapture;
                    $currentCapture = '';
                    $collectedExpr .= '__HASH__';
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
            $child->replaceExpr(sprintf('{%s}', $child->getExpr()));
        }

        return $node;
    }
}
