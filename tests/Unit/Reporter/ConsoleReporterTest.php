<?php

namespace FriendsOfTwig\Twigcs\Tests\Unit\Reporter;

use FriendsOfTwig\Twigcs\Reporter\ConsoleReporter;
use FriendsOfTwig\Twigcs\Validator\Violation;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 *
 * @covers \FriendsOfTwig\Twigcs\Reporter\ConsoleReporter
 */
final class ConsoleReporterTest extends TestCase
{
    public function testReport(): void
    {
        $reporter = new ConsoleReporter();
        $output = $this
            ->getMockBuilder('Symfony\Component\Console\Output\ConsoleOutput')
            ->disableOriginalConstructor()
            ->getMock()
        ;

        $output
            ->expects(self::exactly(3))
            ->method('writeln')
            ->withConsecutive(
                [
                    '<comment>template.twig</comment>',
                ],
                [
                    '<comment>l.10 c.20</comment> : ERROR You are not allowed to do that.',
                ],
                [
                    '<error>1 violation(s) found</error>',
                ]
            )
        ;

        $reporter->report(
            $output,
            [
                new Violation('template.twig', 10, 20, 'You are not allowed to do that.'),
            ]
        );
    }
}
