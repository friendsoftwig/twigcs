<?php

namespace FriendsOfTwig\Twigcs\Console;

use FriendsOfTwig\Twigcs\RegEngine\Checker\Report;
use FriendsOfTwig\Twigcs\RegEngine\Checker\RuleChecker;
use FriendsOfTwig\Twigcs\RegEngine\ExpressionNode;
use FriendsOfTwig\Twigcs\RegEngine\RulesetBuilder;
use FriendsOfTwig\Twigcs\RegEngine\RulesetConfigurator;
use FriendsOfTwig\Twigcs\RegEngine\Sanitizer\StringSanitizer;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class RegDebugCommand extends ContainerAwareCommand
{
    public function configure()
    {
        $this
            ->setName('reg:debug')
            ->addArgument('path', InputArgument::REQUIRED)
            ->addOption('twig-version', 't', InputOption::VALUE_REQUIRED, 'The major version of twig to use.', 3)
        ;
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);

        $path = $input->getArgument('path');

        $stringSanitizer = new StringSanitizer();

        $expr = trim(file_get_contents($path));

        $io->title('Expr');
        $io->writeln($expr);

        $expr = $stringSanitizer->sanitize($expr);

        $io->title('Sanitized expr');
        $io->writeln($expr);

        $rootNode = ExpressionNode::fromString($expr);

        $io->title('Extracted node tree');
        $io->writeln($rootNode->getTrace());

        $io->title('Regex matcher results');

        $nodes = $rootNode->flatten();

        $report = new Report();

        foreach ($nodes as $node) {
            $configurator = new RulesetConfigurator();
            $configurator->setTwigMajorVersion($input->getOption('twig-version'));
            $builder = new RulesetBuilder($configurator);

            $ruleChecker = new RuleChecker($builder->build());
            $ruleChecker->explain();
            $ruleChecker->check($report, $node->getType(), $node->getExpr(), $node->getOffsetsMap(), $node->getOffset());
            $io->writeln(sprintf("<info>EXPR : %s offset : %s</info>\n", $node->getExpr(), $node->getOffset()));
            $io->listing($ruleChecker->getLog());

            if (count($report->getErrors())) {
                $io->listing(array_map(function ($error) {
                    return sprintf('<error>%s at col %s</error>', $error->getReason(), $error->getColumn());
                }, $report->getErrors()));
            }
        }

        return 0;
    }
}
