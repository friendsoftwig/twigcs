<?php

namespace Allocine\Twigcs\Console;

use Allocine\Twigcs\Experimental\ArrayExtractor;
use Allocine\Twigcs\Experimental\DefaultRuleset;
use Allocine\Twigcs\Experimental\ExpressionNode;
use Allocine\Twigcs\Experimental\HashExtractor;
use Allocine\Twigcs\Experimental\ParenthesesExtractor;
use Allocine\Twigcs\Experimental\RuleChecker;
use Allocine\Twigcs\Experimental\StringSanitizer;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class RegDebugCommand extends AbstractCommand
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

        $io->title('Extracted node tree');
        $io->writeln($rootNode->getTrace());

        $io->title('Regex matcher results');

        $nodes = $rootNode->flatten();

        foreach ($nodes as $node) {
            $ruleChecker = new RuleChecker(DefaultRuleset::get());
            $ruleChecker->explain();
            $ruleChecker->check($node->type, $node->expr, $node->offset);
            $io->writeln('<info>EXPR : '.$node->expr.' offset : '.$node->offset."</info>\n");
            $io->listing($ruleChecker->getLog());

            if (count($ruleChecker->errors)) {
                $io->listing(array_map(function ($error) {
                    return '<error>'.$error->reason.' at col '.$error->column.'</error>';
                }, $ruleChecker->errors));
            }
        }

        return 0;
    }
}
