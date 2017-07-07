<?php

namespace Allocine\Twigcs\Rule;

use Allocine\Twigcs\Lexer;
use Allocine\Twigcs\Validator\Violation;

class SliceShorthandSpacing extends AbstractSpacingRule implements RuleInterface
{
    /**
     * @var integer
     */
    private $spaces;

    /**
     * @param integer $spaces
     */
    public function __construct($severity, $spaces = 0)
    {
        parent::__construct($severity);

        $this->spaces = $spaces;
    }

    /**
     * {@inheritdoc}
     */
    public function check(\Twig_TokenStream $tokens)
    {
        $this->violations = [];
        $sliceOpened = false;

        while (!$tokens->isEOF()) {
            $token = $tokens->getCurrent();

            if ($token->getValue() === '[' && $tokens->look(-1)->getType() === \Twig_Token::NAME_TYPE) {
                $sliceOpened = true;
            }

            if ($sliceOpened > 0 && $token->getValue() === ':') {
                $this->assertSpacing($tokens, Lexer::NEXT_TOKEN, $this->spaces);
                $this->assertSpacing($tokens, Lexer::PREVIOUS_TOKEN, $this->spaces, false);
            }

            if ($token->getValue() === ']') {
                $sliceOpened = false;
            }

            $tokens->next();
        }

        return $this->violations;
    }
}
