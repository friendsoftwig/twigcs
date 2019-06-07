<?php

namespace Allocine\Twigcs\Rule;

use Allocine\Twigcs\RegEngine\Linter;
use Twig\Token;

class RegEngineRule extends AbstractRule implements RuleInterface
{
    /**
     * @var array
     */
    private $ruleset;

    public function __construct(int $severity, array $ruleset)
    {
        $this->ruleset = $ruleset;

        parent::__construct($severity);
    }

    /**
     * {@inheritdoc}
     */
    public function check(\Twig_TokenStream $tokens)
    {
        $this->reset();

        $currentExpression = ['value' => '', 'map' => [], 'offset' => 0];
        $expressions = [];

        while (!$tokens->isEOF()) {
            $token = $tokens->getCurrent();

            $toAppend = '';
            $clear = false;

            if (Token::BLOCK_START_TYPE === $token->getType()) {
                $currentExpression = ['value' => '', 'map' => [], 'offset' => $token->columnno];
                $toAppend = '{%';
            } elseif (Token::VAR_START_TYPE === $token->getType()) {
                $currentExpression = ['value' => '', 'map' => [], 'offset' => $token->columnno];
                $toAppend = '{{';
            } elseif (Token::BLOCK_END_TYPE === $token->getType()) {
                $toAppend = '%}';
                $clear = true;
            } elseif (Token::VAR_END_TYPE === $token->getType()) {
                $toAppend = '}}';
                $clear = true;
            } elseif (13 === $token->getType()) {
                $toAppend = "\n";
            } elseif (Token::STRING_TYPE === $token->getType()) {
                $toAppend = '"'.str_pad('', mb_strlen($token->getValue()), 'A').'"';
            } elseif (Token::TEXT_TYPE !== $token->getType()) {
                $toAppend = (string) $token->getValue();
            }

            if (null !== $toAppend) {
                $currentExpression['value'] .= $toAppend;

                $col = 0;
                foreach (str_split($toAppend) as $char) {
                    $currentExpression['map'][] = ['line' => $token->getLine(), 'column' => $token->columnno + $col];
                    ++$col;
                }
            }

            if ($clear) {
                $expressions[] = $currentExpression;
            }

            $tokens->next();
        }

        foreach ($expressions as $expression) {
            $linter = new Linter($this->ruleset);
            $linter->explain();
            $errors = $linter->lint($expression['value']);

            foreach ($errors as $error) {
                $this->addViolation(
                    $tokens->getSourceContext()->getPath(),
                    $expression['map'][$error->getColumn()]['line'] ?? 0,
                    $expression['map'][$error->getColumn()]['column'] ?? 0,
                    $error->getReason(),
                );
            }
        }

        return $this->violations;
    }
}
