<?php

namespace FriendsOfTwig\Twigcs\Ruleset;

use FriendsOfTwig\Twigcs\TemplateResolver\TemplateResolverInterface;

/**
 * @author Tristan Maindron <tmaindron@gmail.com>
 */
interface TemplateResolverAwareInterface
{
    public function setTemplateResolver(TemplateResolverInterface $resolver);
}
