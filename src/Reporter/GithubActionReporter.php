<?php

namespace FriendsOfTwig\Twigcs\Reporter;

use Symfony\Component\Console\Output\OutputInterface;

class GithubActionReporter implements ReporterInterface
{
    /**
     * {@inheritdoc}
     */
    public function report(OutputInterface $output, array $violations)
    {
        foreach ($violations as $violation) {
            $output->writeln(sprintf(
                '::%s file=%s, line=%d, col=%d::%s',
                strtolower($violation->getSeverityAsString()),
                $violation->getFilename(),
                $violation->getLine(),
                $violation->getColumn(),
                $violation->getReason()
            ));
        }
    }
}
