<?php

namespace FriendsOfTwig\Twigcs\Rule;

use FriendsOfTwig\Twigcs\Scope\ScopeBuilder;
use FriendsOfTwig\Twigcs\TemplateResolver\NullResolver;
use FriendsOfTwig\Twigcs\TemplateResolver\TemplateResolverInterface;
use FriendsOfTwig\Twigcs\TwigPort\TokenStream;

class UnusedMacro extends AbstractRule implements RuleInterface
{
    public TemplateResolverInterface $loader;

    public function __construct(int $severity, TemplateResolverInterface $loader = null)
    {
        $this->loader = $loader ?: new NullResolver();

        parent::__construct($severity);
    }

    public function check(TokenStream $tokens)
    {
        $builder = ScopeBuilder::createMacroScopeBuilder($this->loader);

        $root = $builder->build($tokens);

        $violations = [];

        foreach ($root->flatten()->getRootUnusedDeclarations() as $declaration) {
            $token = $declaration->getToken();

            $violations[] = $this->createViolation(
                $tokens->getSourceContext()->getPath(),
                $token->getLine(),
                $token->getColumn(),
                sprintf('Unused macro import "%s".', $token->getValue())
            );
        }

        return $violations;
    }
}
