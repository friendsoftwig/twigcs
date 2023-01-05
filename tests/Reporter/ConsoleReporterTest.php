<?php

namespace FriendsOfTwig\Twigcs\Tests\Reporter;

use FriendsOfTwig\Twigcs\Reporter\ConsoleReporter;
use FriendsOfTwig\Twigcs\Validator\Violation;
use PHPUnit\Framework\TestCase;

final class ConsoleReporterTest extends TestCase
{
    public function testReport()
    {
        $reporter = new ConsoleReporter();
        $output = $this
            ->getMockBuilder('Symfony\Component\Console\Output\ConsoleOutput')
            ->disableOriginalConstructor()
            ->getMock()
        ;

        $output
            ->expects($this->exactly(3))
            ->method('writeln')
            ->withConsecutive(
                ['<comment>template.twig</comment>'],
                ['<comment>l.10 c.20</comment> : ERROR You are not allowed to do that.'],
                ['<error>1 violation(s) found</error>']
            )
        ;

        $reporter->report($output, [
            new Violation('template.twig', 10, 20, 'You are not allowed to do that.'),
        ]);
    }
}
