<?php

namespace FriendsOfTwig\Twigcs\Console;

use FriendsOfTwig\Twigcs\Config\ConfigurationResolver;
use FriendsOfTwig\Twigcs\Ruleset\Official;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Finder\SplFileInfo;
use Symplify\SmartFileSystem\Finder\FinderSanitizer;
use Symplify\SmartFileSystem\SmartFileInfo;

class LintCommand extends ContainerAwareCommand
{
    public function configure()
    {
        $this
            ->setName('lint')
            ->addArgument('path', InputArgument::IS_ARRAY, 'The path to scan for twig files.')
            ->addOption('path-mode', '', InputOption::VALUE_REQUIRED, 'Specify path mode (can be override or intersection).', 'override')
            ->addOption('exclude', null, InputOption::VALUE_IS_ARRAY | InputOption::VALUE_OPTIONAL, 'Excluded folder of path.', [])
            ->addOption('severity', 's', InputOption::VALUE_REQUIRED, 'The maximum allowed error level.', 'warning')
            ->addOption('reporter', 'r', InputOption::VALUE_REQUIRED, 'The reporter to use.', 'console')
            ->addOption('ruleset', null, InputOption::VALUE_REQUIRED, 'Ruleset class to use', Official::class)
            ->addOption('config', null, InputOption::VALUE_REQUIRED, 'Config file to use', null)
        ;
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int|null
     * @throws \Exception
     */
    public function execute(InputInterface $input, OutputInterface $output)
    {
        $container = $this->getContainer();
        $twig = $container['twig'];
        $validator = $container['validator'];
        $resolver = new ConfigurationResolver($container, getcwd(), [
            'path' => $input->getArgument('path'),
            'path-mode' => $input->getOption('path-mode'),
            'reporter-service-name' => $input->getOption('reporter'),
            'exclude' => $input->getOption('exclude'),
            'severity' => $input->getOption('severity'),
            'ruleset-class-name' => $input->getOption('ruleset'),
            'config' => $input->getOption('config'),
        ]);
        $severityLimit = $resolver->getSeverityLimit();
        $finder = $resolver->getFinder();
        $reporter = $resolver->getReporter();

        $violations = [];

        $finderSanitizer = new FinderSanitizer();
        $files = $finderSanitizer->sanitize($finder);
//        $files = $finder;

        // TODO: should be refactored to a Runner class
        foreach ($files as $file) {
            /** @var SmartFileInfo $file */
//            var_dump(
//                'getRelativeFilePath',
//                $file->getRelativeFilePath(),
//                $file->getRelativePathname(),
//                ''
//                'getRelativeFilePathFromCwd',
//                $file->getRelativeFilePathFromCwd(),
//                'getRelativePathname',
//                $file->getRelativePathname(),
//                'getRelativeFilePathFromDirectory',
//                $file->getRelativeFilePathFromDirectory(getcwd())
//            );


            $ruleset = $resolver->getRuleset($file->getRelativePathname());
            $currentViolations = $validator->validate($ruleset, $twig->tokenize(new \Twig\Source(
                file_get_contents($file->getRealPath()),
                $file->getRealPath(),
                // TODO: relative path is without base dir if multiple in's are given.
//                $file->getRelativePathname(),
                $file->getRelativeFilePathFromCwd()
            )));

            // TODO: change array_merge to something more efficient
            $violations = array_merge($violations, $currentViolations);
        }

        $reporter->report($output, $violations);

        foreach ($violations as $violation) {
            if ($violation->getSeverity() > $severityLimit) {
                return 1;
            }
        }

        return 0;
    }
}
