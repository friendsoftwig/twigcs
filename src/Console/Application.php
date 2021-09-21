<?php

namespace FriendsOfTwig\Twigcs\Console;

use FriendsOfTwig\Twigcs\Container;
use Symfony\Component\Console\Application as BaseApplication;
use Symfony\Component\Console\Command\Command;

class Application extends BaseApplication
{
    const NAME = 'twigcs';
    const VERSION = '@__VERSION__@';

    /**
     * @var Container
     */
    private $container;

    public function __construct(bool $singleCommand = true)
    {
        parent::__construct(self::NAME, self::VERSION);

        $this->container = new Container();
        $command = new LintCommand();
        $this->add($command);
        $this->add(new RegDebugCommand());

        $this->setDefaultCommand($command->getName(), $singleCommand);
    }

    public function add(Command $command)
    {
        parent::add($command);

        if ($command instanceof ContainerAwareCommand) {
            $command->setContainer($this->container);
        }
    }
}
