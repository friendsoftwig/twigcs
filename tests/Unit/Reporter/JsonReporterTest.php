<?php

namespace FriendsOfTwig\Twigcs\Tests\Unit\Reporter;

use FriendsOfTwig\Twigcs\Reporter\JsonReporter;
use FriendsOfTwig\Twigcs\Validator\Violation;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console;

/**
 * @internal
 *
 * @covers \FriendsOfTwig\Twigcs\Reporter\JsonReporter
 */
final class JsonReporterTest extends TestCase
{
    public function testReport(): void
    {
        $reporter = new JsonReporter();
        $output = $this
            ->getMockBuilder(Console\Output\ConsoleOutput::class)
            ->disableOriginalConstructor()
            ->getMock()
        ;

        $output
            ->expects(self::once())
            ->method('writeln')
            ->with(
                <<<EOF
{
    "failures": 1,
    "files": [
        {
            "file": "template.twig",
            "violations": [
                {
                    "line": 10,
                    "column": 20,
                    "severity": 3,
                    "type": "error",
                    "message": "You are not allowed to do that."
                }
            ]
        }
    ]
}
EOF
            )
        ;

        $reporter->report(
            $output,
            [
                new Violation('template.twig', 10, 20, 'You are not allowed to do that.'),
            ]
        );
    }

    public function testReportMultiple(): void
    {
        $reporter = new JsonReporter();
        $output = $this
            ->getMockBuilder(Console\Output\ConsoleOutput::class)
            ->disableOriginalConstructor()
            ->getMock()
        ;

        $output
            ->expects(self::once())
            ->method('writeln')
            ->with(
                <<<EOF
{
    "failures": 2,
    "files": [
        {
            "file": "first.twig",
            "violations": [
                {
                    "line": 1,
                    "column": 2,
                    "severity": 3,
                    "type": "error",
                    "message": "This is the first file."
                }
            ]
        },
        {
            "file": "second.twig",
            "violations": [
                {
                    "line": 10,
                    "column": 20,
                    "severity": 1,
                    "type": "info",
                    "message": "This is the second file."
                }
            ]
        }
    ]
}
EOF
            )
        ;

        $reporter->report(
            $output,
            [
                new Violation('first.twig', 1, 2, 'This is the first file.'),
                new Violation('second.twig', 10, 20, 'This is the second file.', Violation::SEVERITY_INFO),
            ]
        );
    }
}
