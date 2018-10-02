<?php

namespace Allocine\Twigcs\Reporter;

use Symfony\Component\Console\Output\OutputInterface;

class JUnitReporter implements ReporterInterface
{
    /**
     * {@inheritdoc}
     */
    public function report(OutputInterface $output, array $violations)
    {
        $testsuites = new \SimpleXMLElement('<testsuites name="Twigcs"/>');

        foreach ($violations as $violation) {
            $testsuite = $testsuites->addChild('testsuite');
            $testsuite->addAttribute('name', $violation->getFilename());
            $testcase = $testsuites->addChild('testcase');
            $testcase->addAttribute('name', $violation->getSource());
            $failure = $testcase->addChild('failure');
            $position = '(' . $violation->getLine() . ':' . $violation->getColumn() . ')';
            $failure->addAttribute('type', 'error');
            $failure->addAttribute('message', $violation->getReason()  . ' at ' . $position);
        }

        $output->writeln($testsuites->asXML());
    }
}
