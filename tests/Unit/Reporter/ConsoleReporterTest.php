<?php

namespace FriendsOfTwig\Twigcs\Tests\Unit\Reporter;

use FriendsOfTwig\Twigcs\Reporter\ConsoleReporter;
use FriendsOfTwig\Twigcs\Validator\Violation;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console;

/**
 * @internal
 *
 * @covers \FriendsOfTwig\Twigcs\Reporter\ConsoleReporter
 */
final class ConsoleReporterTest extends TestCase
{
    public function testReport(): void
    {
        $output = $this->createMock(Console\Output\OutputInterface::class);

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

        $reporter = new ConsoleReporter();

        $reporter->report(
            $output,
            [
                new Violation('template.twig', 10, 20, 'You are not allowed to do that.'),
            ]
        );
    }
}
