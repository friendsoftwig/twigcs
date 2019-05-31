<?php

namespace Allocine\Twigcs\Console;

use Allocine\Twigcs\RegEngine\Checker\RuleChecker;
use Allocine\Twigcs\RegEngine\DefaultRuleset;
use Allocine\Twigcs\RegEngine\ExpressionNode;
use Allocine\Twigcs\RegEngine\Extractor\ArrayExtractor;
use Allocine\Twigcs\RegEngine\Extractor\HashExtractor;
use Allocine\Twigcs\RegEngine\Extractor\ParenthesesExtractor;
use Allocine\Twigcs\RegEngine\Extractor\TernaryExtractor;
use Allocine\Twigcs\RegEngine\Sanitizer\StringSanitizer;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class RegDebugCommand extends ContainerAwareCommand
{
    public function configure()
    {
        $this
            ->setName('reg:debug')
            ->addArgument('path', InputArgument::REQUIRED)
        ;
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);

        $path = $input->getArgument('path');

        $stringSanitizer = new StringSanitizer();

        $parenthesesExtractor = new ParenthesesExtractor();
        $hashExtractor = new HashExtractor();
        $arrayExtractor = new ArrayExtractor();
        $ternaryExtractor = new TernaryExtractor();

        $expr = trim(file_get_contents($path));

        $io->title('Expr');
        $io->writeln($expr);

        $expr = $stringSanitizer->sanitize($expr);

        $io->title('Sanitized expr');
        $io->writeln($expr);

        $rootNode = new ExpressionNode($expr, 0);

        $parenthesesExtractor->extract($rootNode);
        $hashExtractor->extract($rootNode);
        $arrayExtractor->extract($rootNode);
        $ternaryExtractor->extract($rootNode);

        $io->title('Extracted node tree');
        $io->writeln($rootNode->getTrace());

        $io->title('Regex matcher results');

        $nodes = $rootNode->flatten();

        foreach ($nodes as $node) {
            $ruleChecker = new RuleChecker(DefaultRuleset::get());
            $ruleChecker->explain();
            $ruleChecker->check($node->getType(), $node->getExpr(), $node->getOffset());
            $io->writeln(sprintf("<info>EXPR : %s offset : %s</info>\n", $node->getExpr(), $node->getOffset()));
            $io->listing($ruleChecker->getLog());

            if (count($ruleChecker->getErrors())) {
                $io->listing(array_map(function ($error) {
                    return sprintf('<error>%s at col %s</error>', $error->getReason(), $error->getColumn());
                }, $ruleChecker->getErrors()));
            }
        }

        return 0;
    }
}
