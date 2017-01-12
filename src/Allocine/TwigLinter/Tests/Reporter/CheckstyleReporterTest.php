<?php

namespace Allocine\TwigLinter\Tests\Reporter;

use Allocine\TwigLinter\Reporter\CheckstyleReporter;
use Allocine\TwigLinter\Validator\Violation;

class CheckstyleReporterTest extends \PHPUnit_Framework_TestCase
{
    const EXPECTED_REPORT = <<<EOF
<?xml version="1.0"?>
<checkstyle version="1.0.0"><file name="template.twig"><error column="20" line="10" severity="error" message="You are not allowed to do that." source="unknown"/></file></checkstyle>

EOF;

    public function testReport()
    {
        $reporter = new CheckstyleReporter();
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
            new Violation('template.twig', 10, 20, 'You are not allowed to do that.')
        ]);
    }
}
