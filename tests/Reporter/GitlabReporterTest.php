<?php
declare(strict_types=1);

namespace FriendsOfTwig\Twigcs\Tests\Reporter;

use FriendsOfTwig\Twigcs\Reporter\GitlabReporter;
use FriendsOfTwig\Twigcs\Validator\Violation;
use JsonException;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Output\ConsoleOutput;

class GitlabReporterTest extends TestCase
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
    }
]
EOF;

    /**
     * @throws JsonException
     */
    public function testReport(): void
    {
        $reporter = new GitlabReporter();
        $output = $this->createMock(ConsoleOutput::class);

        $output
            ->expects($this->once())
            ->method('writeln')
            ->with(self::EXPECTED_REPORT)
        ;

        $reporter->report($output, [
            new Violation('template.twig', 10, 20, 'You are not allowed to do that.'),
        ]);
    }
}
