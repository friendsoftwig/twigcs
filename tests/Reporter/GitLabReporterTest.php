<?php

declare(strict_types=1);

namespace FriendsOfTwig\Twigcs\Tests\Reporter;

use FriendsOfTwig\Twigcs\Reporter\GitLabReporter;
use FriendsOfTwig\Twigcs\Validator\Violation;
use JsonException;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Output\ConsoleOutput;

final class GitLabReporterTest extends TestCase
{
    public const EXPECTED_REPORT = <<<EOF
[
    {
        "description": "You are not allowed to do that.",
        "fingerprint": "f72519b40b7ced7c6475d20ae6a0f4d26014891d98a29750dde4931e22745796",
        "severity": "major",
        "location": {
            "path": "template.twig",
            "lines": {
                "begin": 10
            }
        }
    },
    {
        "description": "You should not do that.",
        "fingerprint": "3068464e6cfb4c459076c2b5e910b8d99a80bbbadb1ff1e3e1551a71352ca3e2",
        "severity": "minor",
        "location": {
            "path": "template.twig",
            "lines": {
                "begin": 10
            }
        }
    },
    {
        "description": "You might not want to do that.",
        "fingerprint": "b2b519c15c4819fe59ccff92544b35c1dd331a41d0ec138dc3f6b966d0362187",
        "severity": "info",
        "location": {
            "path": "template.twig",
            "lines": {
                "begin": 10
            }
        }
    }
]
EOF;

    public function testReport(): void
    {
        $reporter = new GitLabReporter();
        $output = $this->createMock(ConsoleOutput::class);

        $output
            ->expects($this->once())
            ->method('writeln')
            ->with(self::EXPECTED_REPORT);

        $reporter->report($output, [
            new Violation('template.twig', 10, 20, 'You are not allowed to do that.'),
            new Violation('template.twig', 10, 20, 'You should not do that.', Violation::SEVERITY_WARNING),
            new Violation('template.twig', 10, 20, 'You might not want to do that.', Violation::SEVERITY_INFO),
        ]);
    }

    public function testReportWithJsonException(): void
    {
        $reporter = new GitLabReporter();
        $output = $this->createMock(ConsoleOutput::class);

        $this->expectException(JsonException::class);
        $reporter->report($output, [new Violation('template.twig', 10, 20, "Error message with latin1 character \xE7")]);
    }
}
