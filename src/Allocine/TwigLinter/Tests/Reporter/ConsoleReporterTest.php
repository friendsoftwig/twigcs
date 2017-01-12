<?php

namespace Allocine\TwigLinter\Tests\Reporter;

use Allocine\TwigLinter\Reporter\ConsoleReporter;
use Allocine\TwigLinter\Validator\Violation;

class ConsoleReporterTest extends \PHPUnit_Framework_TestCase
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
