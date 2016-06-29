<?php

namespace Allocine\TwigLinter\Console;

use Allocine\TwigLinter\Ruleset\Official;
use Allocine\TwigLinter\Validator\Violation;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Finder\Finder;

class LintCommand extends ContainerAwareCommand
{
    public function configure()
    {
        $this
            ->setName('lint')
            ->addArgument('path')
            ->addOption('severity', 's', InputOption::VALUE_REQUIRED, 'The maximum allowed error level.', 'warning')
            ->addOption('reporter', 'r', InputOption::VALUE_REQUIRED, 'The reporter to use.', 'console')
        ;
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $container = $this->getContainer();
        $limit = $this->getSeverityLimit($input);

        $finder = new Finder();
        $files = $finder->in($input->getArgument('path'))->name('*.twig');

        $violations = [];

        foreach ($files as $file) {
            $violations = array_merge($violations, $container['validator']->validate(new Official(), $container['twig']->tokenize(
                file_get_contents($file->getRealPath()),
                str_replace(realpath($input->getArgument('path')), $input->getArgument('path'), $file->getRealPath())
            )));
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
