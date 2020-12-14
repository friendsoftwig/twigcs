<?php

namespace FriendsOfTwig\Twigcs\Console;

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
    const DISPLAY_BLOCKING = 'blocking';
    const DISPLAY_ALL = 'all';

    public function configure()
    {
        $this
            ->setName('lint')
            ->addArgument('paths', InputArgument::IS_ARRAY | InputArgument::OPTIONAL, 'The path to scan for twig files.', null)
            ->addOption('twig-version', 't', InputOption::VALUE_REQUIRED, 'The major version of twig to use.', 3)
            ->addOption('exclude', null, InputOption::VALUE_IS_ARRAY | InputOption::VALUE_REQUIRED, 'Excluded folder of path.', [])
            ->addOption('severity', 's', InputOption::VALUE_REQUIRED, 'The maximum allowed error level.', 'warning')
            ->addOption('reporter', 'r', InputOption::VALUE_REQUIRED, 'The reporter to use.')
            ->addOption('display', 'd', InputOption::VALUE_REQUIRED, 'The violations to display, "'.self::DISPLAY_ALL.'" or "'.self::DISPLAY_BLOCKING.'".', self::DISPLAY_ALL)
            ->addOption('throw-syntax-error', 'e', InputOption::VALUE_NONE, 'Throw syntax error when a template contains an invalid token.', false)
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
        ]);

        $finders = $resolver->getFinders();

        $lexer = $container->get('lexer');
        $validator = $container->get('validator');

        $files = [];
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

        $violations = $this->filterDisplayViolations($input, $violations);

        $resolver->getReporter()->report($output, $violations);

        return $this->determineExitCode($input, $violations);
    }

    private function determineExitCode(InputInterface $input, array $violations): int
    {
        $limit = $this->getSeverityLimit($input);

        foreach ($violations as $violation) {
            if ($violation->getSeverity() > $limit) {
                return 1;
            }
        }

        return 0;
    }

    private function filterDisplayViolations(InputInterface $input, array $violations): array
    {
        if (self::DISPLAY_ALL === $input->getOption('display')) {
            return $violations;
        }

        $limit = $this->getSeverityLimit($input);

        return array_filter($violations, function (Violation $violation) use ($limit) {
            return $violation->getSeverity() > $limit;
        });
    }

    private function getSeverityLimit(InputInterface $input): int
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
