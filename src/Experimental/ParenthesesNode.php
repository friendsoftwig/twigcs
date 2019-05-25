<?php

namespace Allocine\Twigcs\Experimental;

class ParenthesesNode
{
    public $expr;
    public $children;
    public $offset;

    public function flatten()
    {
        $result = [$this->expr];

        foreach ($this->children as $children) {
            foreach ($children->flatten() as $expressions) {
                $result[]= $expressions;
            }
        }

        return $result;
    }
}
