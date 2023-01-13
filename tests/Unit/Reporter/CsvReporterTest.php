<?php

namespace FriendsOfTwig\Twigcs\Tests\Unit\Reporter;

use FriendsOfTwig\Twigcs\Reporter\CsvReporter;
use FriendsOfTwig\Twigcs\Validator\Violation;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
final class CsvReporterTest extends TestCase
{
    public function testReport(): void
    {
        $reporter = new CsvReporter();
        $output = $this
            ->getMockBuilder('Symfony\Component\Console\Output\ConsoleOutput')
            ->disableOriginalConstructor()
            ->getMock()
        ;

        $output
            ->expects($this->once())
            ->method('writeln')
            ->with('template.twig;10;20;error - You are not allowed to do that.')
        ;

        $reporter->report($output, [
            new Violation('template.twig', 10, 20, 'You are not allowed to do that.'),
        ]);
    }
}
