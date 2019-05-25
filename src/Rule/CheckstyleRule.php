<?php

namespace Allocine\Twigcs\Rule;

use Allocine\Twigcs\Experimental\DefaultRuleset;
use Allocine\Twigcs\Experimental\Linter;
use Allocine\Twigcs\Lexer;
use Allocine\Twigcs\Rule\AbstractRule;
use Allocine\Twigcs\Rule\RuleInterface;
use Allocine\Twigcs\Validator\Violation;
use Twig\Token;

class CheckstyleRule extends AbstractRule implements RuleInterface
{
    /**
     * {@inheritdoc}
     */
    public function check(\Twig_TokenStream $tokens)
    {
        $this->reset();

        $currentExpression = ['value' => '', 'map' => [], 'offset'=> 0];
        $expressions = [];

        while (!$tokens->isEOF()) {
            $token = $tokens->getCurrent();

            $toAppend = '';
            $clear = false;

            if ($token->getType() === Token::BLOCK_START_TYPE) {
                $currentExpression = ['value' => '', 'map' => [], 'offset' => $token->columnno];
                $toAppend = '{%';
            } elseif ($token->getType() === Token::VAR_START_TYPE) {
                $currentExpression = ['value' => '', 'map' => [], 'offset' => $token->columnno];
                $toAppend = '{{';
            } elseif ($token->getType() === Token::BLOCK_END_TYPE) {
                $toAppend = '%}';
                $clear = true;
            } elseif ($token->getType() === Token::VAR_END_TYPE) {
                $toAppend = '}}';
                $clear = true;
            } elseif ($token->getType() === 13) {
                $toAppend = "\n";
            } elseif ($token->getType() !== Token::TEXT_TYPE) {
                $toAppend = (string)$token->getValue();
            }

            if ($toAppend !== null) {
                $currentExpression['value'] .= $toAppend;

                $col = 0;
                foreach (str_split($toAppend) as $char) {
                    $currentExpression['map'][]= ['line' => $token->getLine(), 'column' => $token->columnno + $col];
                    $col++;
                }
            }

            if ($clear) {
                $expressions[]= $currentExpression;
            }

            $tokens->next();
        }

        foreach ($expressions as $expression) {
            $linter = new Linter(DefaultRuleset::get());
            $linter->explain();
            $errors = $linter->lint($expression['value']);

            foreach ($errors as $error) {
                $this->addViolation(
                    $tokens->getSourceContext()->getPath(),
                    $expression['map'][$error->column]['line'] ?? 0,
                    $expression['map'][$error->column]['column'] ?? 0,
                    $error->reason,
                );
            }
        }


        return $this->violations;
    }
}
