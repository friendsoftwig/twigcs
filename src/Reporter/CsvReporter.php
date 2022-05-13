<?php

namespace FriendsOfTwig\Twigcs\Reporter;

use Symfony\Component\Console\Output\OutputInterface;

class CsvReporter implements ReporterInterface
{
    public function report(OutputInterface $output, array $violations)
    {
        foreach ($violations as $violation) {
            $output->writeln(sprintf(
                '%s;%d;%d;%s - %s',
                $violation->getFilename(),
                $violation->getLine(),
                $violation->getColumn(),
                strtolower($violation->getSeverityAsString()),
                $violation->getReason()
            ));
        }
    }
}
