<?php

namespace Allocine\Twigcs\RegEngine\Extractor;

use Allocine\Twigcs\RegEngine\ExpressionNode;

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

        foreach (str_split($node->getExpr()) as $char) {
            $consumeChar = false;

            if ('(' === $char) {
                ++$parenthesesDepth;

                if (1 === $parenthesesDepth) {
                    $capturesOffsets[] = $counter + $node->getOffset() + 1;
                    $consumeChar = true;
                }
            } elseif (')' === $char) {
                --$parenthesesDepth;

                if (0 === $parenthesesDepth) {
                    $captures[] = $currentCapture;
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
            $child->replaceExpr(sprintf('(%s)', $child->getExpr()));
        }

        return $node;
    }
}
