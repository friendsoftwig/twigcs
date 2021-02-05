<?php

namespace FriendsOfTwig\Twigcs\Rule;

use FriendsOfTwig\Twigcs\Scope\ScopeBuilder;
use FriendsOfTwig\Twigcs\TwigPort\TokenStream;

class UnusedVariable extends AbstractRule implements RuleInterface
{
    /**
     * {@inheritdoc}
     */
    public function check(TokenStream $tokens)
    {
        $builder = ScopeBuilder::createVariableScopeBuilder();

        $root = $builder->build($tokens);

        $violations = [];

        foreach ($root->flatten()->getUnusedDeclarations() as $declaration) {
            $token = $declaration->getToken();

            $violations[] = $this->createViolation(
                $tokens->getSourceContext()->getPath(),
                $token->getLine(),
                $token->getColumn(),
                sprintf('Unused variable "%s".', $token->getValue())
            );
        }

        return $violations;
    }
}
