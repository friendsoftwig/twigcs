<?php

namespace FriendsOfTwig\Twigcs\Reporter;

use Symfony\Component\Console\Output\OutputInterface;

class JUnitReporter implements ReporterInterface
{
    public function report(OutputInterface $output, array $violations)
    {
        $filename = null;
        $testsuite = null;
        $totalFailures = 0;

        $testsuites = new \SimpleXMLElement('<testsuites name="Twigcs"/>');

        foreach ($violations as $violation) {
            if ($filename !== $violation->getFilename()) {
                $filename = $violation->getFilename();
                $testsuite = $testsuites->addChild('testsuite');
                $testsuite->addAttribute('name', $filename);
                $totalFailures = 0;
            }
            $testsuite['failures'] = ++$totalFailures;
            $testcase = $testsuite->addChild('testcase');
            $testcase->addAttribute('name', $violation->getSource());
            $failure = $testcase->addChild('failure');
            $failure[0] = $violation->getReason().' at ('.$violation->getLine().':'.$violation->getColumn().')';
            $failure->addAttribute('type', strtolower($violation->getSeverityAsString()));
            $failure->addAttribute('message', $failure[0]);
        }

        $output->writeln($testsuites->asXML());
    }
}
