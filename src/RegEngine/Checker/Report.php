<?php

namespace FriendsOfTwig\Twigcs\RegEngine\Checker;

class Report
{
    private array $errors;

    private array $unrecognizedExpressions;

    public function __construct()
    {
        $this->unrecognizedExpressions = [];
        $this->errors = [];
    }

    public function addError(RuleError $error): self
    {
        $this->errors[] = $error;

        return $this;
    }

    public function getErrors(): array
    {
        usort($this->errors, function ($a, $b) {
            return $a->getColumn() <=> $b->getColumn();
        });

        return $this->errors;
    }

    public function addUnrecognizedExpression(string $expression): self
    {
        $this->unrecognizedExpressions[] = $expression;

        return $this;
    }

    public function getUnrecognizedExpressions(): array
    {
        return $this->unrecognizedExpressions;
    }
}
