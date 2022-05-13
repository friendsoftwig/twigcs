<?php

namespace FriendsOfTwig\Twigcs\Reporter;

use Symfony\Component\Console\Output\OutputInterface;

class ConsoleReporter implements ReporterInterface
{
    public function report(OutputInterface $output, array $violations)
    {
        $filename = null;

        foreach ($violations as $violation) {
            if ($filename !== $violation->getFilename()) {
                $filename = $violation->getFilename();
                $output->writeln(sprintf('<comment>%s</comment>', $filename));
            }

            $output->writeln(sprintf(
                '<comment>l.%d c.%d</comment> : %s %s',
                $violation->getLine(),
                $violation->getColumn(),
                $violation->getSeverityAsString(),
                $violation->getReason()
            ));
        }

        if ($count = count($violations)) {
            $output->writeln(sprintf('<error>%d violation(s) found</error>', $count));
        } else {
            $output->writeln('<info>No violation found.</info>');
        }
    }
}
