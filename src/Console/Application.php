<?php

namespace Allocine\Twigcs\Console;

use Allocine\Twigcs\Container;
use Symfony\Component\Console\Application as BaseApplication;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\ArgvInput;

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
        $command = new LintCommand();
        $this->add($command);
        // Support old way to execute linter (`twigcs lint <path>`) to preserve
        // backward compatibility.
        if ((new ArgvInput())->getFirstArgument() == 'lint') {
            @trigger_error("Calling 'lint' command is deprecated. Run `twigs <path>` instead.", E_USER_DEPRECATED);
        }
        else {
            $this->setDefaultCommand($command->getName(), true);
        }

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
