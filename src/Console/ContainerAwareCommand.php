<?php

namespace FriendsOfTwig\Twigcs\Console;

use FriendsOfTwig\Twigcs\Container;
use Symfony\Component\Console\Command\Command;

class ContainerAwareCommand extends Command
{
    private ?Container $container = null;

    public function setContainer(Container $container)
    {
        $this->container = $container;
    }

    /**
     * @return Container
     */
    public function getContainer()
    {
        return $this->container;
    }
}
