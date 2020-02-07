<?php

namespace FriendsOfTwig\Twigcs\Tests\Reporter;

use FriendsOfTwig\Twigcs\Reporter\JsonReporter;
use FriendsOfTwig\Twigcs\Validator\Violation;
use PHPUnit\Framework\TestCase;

class JsonReporterTest extends TestCase
{
const EXPECTED_REPORT = <<<EOF
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
EOF;

    public function testReport()
    {
        $reporter = new JsonReporter();
        $output = $this
            ->getMockBuilder('Symfony\Component\Console\Output\ConsoleOutput')
            ->disableOriginalConstructor()
            ->getMock()
        ;

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
