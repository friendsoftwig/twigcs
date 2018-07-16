<?php

namespace Allocine\Twigcs\Tests\Reporter;

use Allocine\Twigcs\Reporter\ConsoleReporter;
use Allocine\Twigcs\Validator\Violation;
use PHPUnit\Framework\TestCase;

class ConsoleReporterTest extends TestCase
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
            ->expects($this->at(1))
            ->method('writeln')
            ->with('<comment>l.10 c.20</comment> : ERROR You are not allowed to do that.')
        ;

        $output
            ->expects($this->at(2))
            ->method('writeln')
            ->with('<error>1 violation(s) found</error>')
        ;

        $reporter->report($output, [
            new Violation('template.twig', 10, 20, 'You are not allowed to do that.')
        ]);
    }
}
