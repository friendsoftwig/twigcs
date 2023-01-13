<?php

namespace FriendsOfTwig\Twigcs\Console;

use FriendsOfTwig\Twigcs\Config\ConfigInterface;
use FriendsOfTwig\Twigcs\Config\ConfigResolver;
use FriendsOfTwig\Twigcs\TwigPort\Source;
use FriendsOfTwig\Twigcs\TwigPort\SyntaxError;
use FriendsOfTwig\Twigcs\Validator\Violation;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class LintCommand extends ContainerAwareCommand
{
    /**
     * @deprecated use ConfigInterface::DISPLAY_BLOCKING instead
     */
    public const DISPLAY_BLOCKING = 'blocking';

    /**
     * @deprecated use ConfigInterface::DISPLAY_ALL instead
     */
    public const DISPLAY_ALL = 'all';

    public function configure()
    {
        $this
            ->setName('lint')
            ->addArgument('paths', InputArgument::IS_ARRAY | InputArgument::OPTIONAL, 'The path to scan for twig files.', null)
            ->addOption('twig-version', 't', InputOption::VALUE_REQUIRED, 'The major version of twig to use.', 3)
            ->addOption('exclude', null, InputOption::VALUE_IS_ARRAY | InputOption::VALUE_REQUIRED, 'Excluded folder of path.', [])
            ->addOption('severity', 's', InputOption::VALUE_REQUIRED, 'The maximum allowed error level.')
            ->addOption('reporter', 'r', InputOption::VALUE_REQUIRED, 'The reporter to use.')
            ->addOption('display', 'd', InputOption::VALUE_REQUIRED, 'The violations to display, "'.ConfigInterface::DISPLAY_ALL.'" or "'.ConfigInterface::DISPLAY_BLOCKING.'".')
            ->addOption('throw-syntax-error', 'e', InputOption::VALUE_NONE, 'Throw syntax error when a template contains an invalid token.')
            ->addOption('ruleset', null, InputOption::VALUE_REQUIRED, 'Ruleset class to use')
            ->addOption('config', null, InputOption::VALUE_REQUIRED, 'Config file to use', null)
        ;
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $container = $this->getContainer();

        $resolver = new ConfigResolver($container, getcwd(), [
            'path' => $input->getArgument('paths'),
            'reporter-service-name' => $input->getOption('reporter'),
            'exclude' => $input->getOption('exclude'),
            'severity' => $input->getOption('severity'),
            'ruleset-class-name' => $input->getOption('ruleset'),
            'twig-version' => $input->getOption('twig-version'),
            'config' => $input->getOption('config'),
            'display' => $input->getOption('display'),
        ]);

        $finders = $resolver->getFinders();

        $lexer = $container->get('lexer');
        $validator = $container->get('validator');

        $violations = [];

        foreach ($finders as $finder) {
            $files = iterator_to_array($finder);

            foreach ($files as $file) {
                $ruleset = $resolver->getRuleset($file);

                $realPath = $file->getRealPath();
                $source = new Source(file_get_contents($realPath), $realPath, ltrim(str_replace(getcwd(), '', $realPath), '/'));

                try {
                    $tokens = $lexer->tokenize($source);
                    $violations = array_merge($violations, $validator->validate($ruleset, $tokens));
                } catch (SyntaxError $e) {
                    if (false !== $input->getOption('throw-syntax-error')) {
                        throw $e;
                    }
                    $violations[] = new Violation($e->getSourcePath(), $e->getLineNo(), $e->getColumnNo(), $e->getMessage());
                }
            }
        }

        $severityLimit = $resolver->getSeverityLimit();

        $violations = $this->filterDisplayViolations($resolver->getDisplay(), $severityLimit, $violations);

        $resolver->getReporter()->report($output, $violations);

        return $this->determineExitCode($severityLimit, $violations);
    }

    private function determineExitCode(int $severityLevel, array $violations): int
    {
        foreach ($violations as $violation) {
            if ($violation->getSeverity() > $severityLevel) {
                return 1;
            }
        }

        return 0;
    }

    private function filterDisplayViolations(string $display, int $severityLevel, array $violations): array
    {
        if (ConfigInterface::DISPLAY_ALL === $display) {
            return $violations;
        }

        return array_filter($violations, function (Violation $violation) use ($severityLevel) {
            return $violation->getSeverity() > $severityLevel;
        });
    }
}
