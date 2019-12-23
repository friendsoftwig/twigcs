<?php

namespace FriendsOfTwig\Twigcs\Rule;

use FriendsOfTwig\Twigcs\TwigPort\TokenStream;
use FriendsOfTwig\Twigcs\Validator\Violation;

interface RuleInterface
{
    /**
     * @return Violation[]
     */
    public function check(TokenStream $tokens);

    public function collect(): array;
}
