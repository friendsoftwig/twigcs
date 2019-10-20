<?php

namespace FriendsOfTwig\Twigcs\Rule;

use FriendsOfTwig\Twigcs\Validator\Violation;

interface RuleInterface
{
    /**
     * @return Violation[]
     */
    public function check(\Twig\TokenStream $tokens);

    public function collect(): array;
}
