<?php

namespace FriendsOfTwig\Twigcs\Console;

use FriendsOfTwig\Twigcs\Container;
use Symfony\Component\Console\Application as BaseApplication;
use Symfony\Component\Console\Command\Command;

class Application extends BaseApplication
{
    public const NAME = 'twigcs';
    public const VERSION = '@__VERSION__@';

    private Container $container;

    public function __construct(bool $singleCommand = true)
    {
        parent::__construct(self::NAME, self::VERSION);

        $this->container = new Container();
        $command = new LintCommand();
        $this->addCommand($command);
        $this->addCommand(new RegDebugCommand());

        $this->setDefaultCommand($command->getName(), $singleCommand);
    }

    /**
     * @param callable|Command $command
     */
    public function addCommand($command): ?Command
    {
        if ($command instanceof ContainerAwareCommand) {
            $command->setContainer($this->container);
        }

        // Symfony Console 8.0+ uses addCommand(), earlier versions use add()
        if (method_exists(BaseApplication::class, 'addCommand')) {
            // @phpstan-ignore-next-line staticMethod.notFound - addCommand method exists only on Symfony Console 8.0+
            return parent::addCommand($command);
        }

        // For Symfony Console < 8.0, ensure we only pass Command instances
        if (!$command instanceof Command) {
            throw new \InvalidArgumentException('Command must be an instance of '.Command::class.' for Symfony Console < 8.0');
        }

        return parent::add($command);
    }
}
