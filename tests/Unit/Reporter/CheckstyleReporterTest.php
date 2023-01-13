<?php

namespace FriendsOfTwig\Twigcs\Tests\Unit\Reporter;

use FriendsOfTwig\Twigcs\Reporter\CheckstyleReporter;
use FriendsOfTwig\Twigcs\Validator\Violation;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 *
 * @covers \FriendsOfTwig\Twigcs\Reporter\CheckstyleReporter
 */
final class CheckstyleReporterTest extends TestCase
{
    public function testReport(): void
    {
        $reporter = new CheckstyleReporter();
        $output = $this
            ->getMockBuilder('Symfony\Component\Console\Output\ConsoleOutput')
            ->disableOriginalConstructor()
            ->getMock()
        ;

        $output
            ->expects(self::once())
            ->method('writeln')
            ->with(
                <<<EOF
<?xml version="1.0"?>
<checkstyle version="1.0.0"><file name="template.twig"><error column="20" line="10" severity="error" message="You are not allowed to do that." source="unknown"/></file></checkstyle>

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
}
