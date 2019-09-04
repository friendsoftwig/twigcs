<?php

namespace FriendsOfTwig\Twigcs\Console;

use FriendsOfTwig\Twigcs\Ruleset\Official;
use FriendsOfTwig\Twigcs\Ruleset\RulesetInterface;
use FriendsOfTwig\Twigcs\Validator\Violation;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Finder\Finder;
use function class_exists;
use function sprintf;

class LintCommand extends ContainerAwareCommand
{
    public function configure()
    {
        $this
            ->setName('lint')
            ->addArgument('paths', InputArgument::IS_ARRAY | InputArgument::OPTIONAL, 'The path to scan for twig files.', ['.'])
            ->addOption('severity', 's', InputOption::VALUE_REQUIRED, 'The maximum allowed error level.', 'warning')
            ->addOption('reporter', 'r', InputOption::VALUE_REQUIRED, 'The reporter to use.', 'console')
            ->addOption('ruleset', null, InputOption::VALUE_REQUIRED, 'Ruleset class to use', Official::class)
        ;
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $container = $this->getContainer();
        $limit = $this->getSeverityLimit($input);

        $paths = $input->getArgument('paths');

        $files = [];
        foreach ($paths as $path) {
            if (is_file($path)) {
                $files[] = new \SplFileInfo($path);
            } else {
                $finder = new Finder();
                $found = iterator_to_array($finder->in($path)->name('*.twig'));
                if (!empty($found)) {
                    $files = array_merge($files, $found);
                }
            }
        }

        $violations = [];

        $ruleset = $input->getOption('ruleset');

        if (!class_exists($ruleset)) {
            throw new \InvalidArgumentException(sprintf('Ruleset class %s does not exist', $ruleset));
        }

        if (!is_subclass_of($ruleset, RulesetInterface::class)) {
            throw new \InvalidArgumentException('Ruleset class must implement '.RulesetInterface::class);
        }

        foreach ($files as $file) {
            $violations = array_merge($violations, $container['validator']->validate(new $ruleset(), $container['twig']->tokenize(new \Twig_Source(
                file_get_contents($file->getRealPath()),
                $file->getRealPath(),
                str_replace(realpath($path), rtrim($path, '/'), $file->getRealPath())
            ))));
        }

        $container[sprintf('reporter.%s', $input->getOption('reporter'))]->report($output, $violations);

        foreach ($violations as $violation) {
            if ($violation->getSeverity() > $limit) {
                return 1;
            }
        }

        return 0;
    }

    private function getSeverityLimit(InputInterface $input)
    {
        switch ($input->getOption('severity')) {
            case 'ignore':
                return Violation::SEVERITY_IGNORE - 1;
            case 'info':
                return Violation::SEVERITY_INFO - 1;
            case 'warning':
                return Violation::SEVERITY_WARNING - 1;
            case 'error':
                return Violation::SEVERITY_ERROR - 1;
            default:
                throw new \InvalidArgumentException('Invalid severity limit provided.');
        }
    }
}
