<?php

namespace FriendsOfTwig\Twigcs\Tests\Unit\Reporter;

use FriendsOfTwig\Twigcs\Reporter\CheckstyleReporter;
use FriendsOfTwig\Twigcs\Validator\Violation;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console;

/**
 * @internal
 *
 * @covers \FriendsOfTwig\Twigcs\Reporter\CheckstyleReporter
 */
final class CheckstyleReporterTest extends TestCase
{
    public function testReport(): void
    {
        $output = $this->createMock(Console\Output\OutputInterface::class);

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

        $reporter = new CheckstyleReporter();

        $reporter->report(
            $output,
            [
                new Violation('template.twig', 10, 20, 'You are not allowed to do that.'),
            ]
        );
    }
}
