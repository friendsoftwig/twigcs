<?php

namespace FriendsOfTwig\Twigcs\Reporter;

use Symfony\Component\Console\Output\OutputInterface;

class JsonReporter implements ReporterInterface
{
    public function report(OutputInterface $output, array $violations)
    {
        $filename = null;

        $result = new \stdClass();
        $result->failures = count($violations);

        foreach ($violations as $violation) {
            if ($filename !== $violation->getFilename()) {
                $filename = $violation->getFilename();
                $entry = new \stdClass();
                $entry->file = $filename;
                $result->files[] = $entry;
            }

            $entry->violations[] = [
                'line' => $violation->getLine(),
                'column' => $violation->getColumn(),
                'severity' => $violation->getSeverity(),
                'type' => strtolower($violation->getSeverityAsString()),
                'message' => $violation->getReason(),
            ];
        }

        $output->writeln(json_encode($result, \JSON_PRETTY_PRINT));
    }
}
