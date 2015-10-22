<?php

namespace Allocine\TwigLinter\Console;

use Allocine\TwigLinter\Container;
use Symfony\Component\Console\Application as BaseApplication;
use Symfony\Component\Console\Command\Command;

class Application extends BaseApplication
{
    /**
     * @var Container
     */
    private $container;

    /**
     * @param string $name
     * @param string $version
     */
    public function __construct($name = 'UNKNOWN', $version = 'UNKNOWN')
    {
        parent::__construct($name, $version);

        $this->container = new Container();
        $this->add(new LintCommand());
    }

    /**
     * @param Command $command
     */
    public function add(Command $command)
    {
        parent::add($command);

        if ($command instanceof ContainerAwareCommand) {
            $command->setContainer($this->container);
        }
    }
}
