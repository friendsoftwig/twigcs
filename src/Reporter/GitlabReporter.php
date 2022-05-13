<?php
declare(strict_types=1);

namespace FriendsOfTwig\Twigcs\Reporter;

use FriendsOfTwig\Twigcs\Validator\Violation;
use JsonException;
use Symfony\Component\Console\Output\OutputInterface;
use function hash;
use function implode;
use function json_encode;

class GitlabReporter implements ReporterInterface
{
    /**
     * {@inheritdoc}
     * @param Violation[] $violations
     *
     * @throws JsonException
     */
    public function report(OutputInterface $output, array $violations): void
    {
        $errors = [];

        foreach ($violations as $violation) {
            switch($violation->getSeverity()) {
                case Violation::SEVERITY_INFO:
                    $severity = 'info';
                    break;
                case Violation::SEVERITY_WARNING:
                    $severity = 'minor';
                    break;
                default:
                    $severity = 'major';
                    break;
            }

            $errors[] = [
                'description' => $violation->getReason(),
                'fingerprint' => hash(
                    'sha256',
                    implode(
                        [
                            $violation->getFilename(),
                            $violation->getLine(),
                            $violation->getColumn(),
                            $violation->getReason(),
                        ]
                    )
                ),
                'severity'    => $severity,
                'location'    => [
                    'path'  => $violation->getFilename(),
                    'lines' => [
                        'begin' => $violation->getLine(),
                    ],
                ],
            ];
        }

        $output->writeln(json_encode($errors, JSON_THROW_ON_ERROR | JSON_PRETTY_PRINT));
    }
}
