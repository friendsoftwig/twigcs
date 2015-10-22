<?php

namespace Allocine\TwigLinter\Rule;

use Allocine\TwigLinter\Lexer;
use Allocine\TwigLinter\Validator\Violation;

class LowerCaseVariable extends AbstractRule implements RuleInterface
{
    /**
     * {@inheritdoc}
     */
    public function check(\Twig_TokenStream $tokens)
    {
        $this->reset();

        while (!$tokens->isEOF()) {
            $token = $tokens->getCurrent();

            if ($token->getType() === \Twig_Token::NAME_TYPE && !ctype_lower($token->getValue())) {
                if ($tokens->look(Lexer::PREVIOUS_TOKEN)->getType() === Lexer::WHITESPACE_TYPE && $tokens->look(-2)->getValue() === 'set') {
                    $this->addViolation($tokens->getFilename(), $token->getLine(), sprintf('The "%s" variable should be in lower case (use _ as a separator).', $token->getValue()));
                }
            }

            $tokens->next();
        }

        return $this->violations;
    }
}
