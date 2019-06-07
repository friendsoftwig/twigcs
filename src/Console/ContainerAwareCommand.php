<?php

namespace FriendsOfTwig\Twigcs\Console;

use Pimple\Container;
use Symfony\Component\Console\Command\Command;

class ContainerAwareCommand extends Command
{
    /**
     * @var Container
     */
    private $container;

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
