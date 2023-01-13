<?php

declare(strict_types=1);

namespace FriendsOfTwig\Twigcs\Reporter;

use FriendsOfTwig\Twigcs\Validator\Violation;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Code Quality report format supported by GitLab.
 *
 * @see https://docs.gitlab.com/ee/user/project/merge_requests/code_quality.html#implementing-a-custom-tool
 */
final class GitLabReporter implements ReporterInterface
{
    /**
     * {@inheritdoc}
     *
     * @throws \JsonException
     */
    public function report(OutputInterface $output, array $violations): void
    {
        $errors = [];

        foreach ($violations as $violation) {
            switch ($violation->getSeverity()) {
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
                'fingerprint' => \hash(
                    'sha256',
                    \implode(
                        '',
                        [
                            $violation->getFilename(),
                            $violation->getLine(),
                            $violation->getColumn(),
                            $violation->getReason(),
                        ]
                    )
                ),
                'severity' => $severity,
                'location' => [
                    'path' => $violation->getFilename(),
                    'lines' => [
                        'begin' => $violation->getLine(),
                    ],
                ],
            ];
        }

        $output->writeln(\json_encode($errors, \JSON_THROW_ON_ERROR | \JSON_PRETTY_PRINT));
    }
}
