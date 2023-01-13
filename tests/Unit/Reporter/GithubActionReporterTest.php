<?php

namespace FriendsOfTwig\Twigcs\Tests\Unit\Reporter;

use FriendsOfTwig\Twigcs\Reporter\GithubActionReporter;
use FriendsOfTwig\Twigcs\Reporter\ReporterInterface;
use FriendsOfTwig\Twigcs\Validator\Violation;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console;

/**
 * @internal
 *
 * @covers \FriendsOfTwig\Twigcs\Reporter\CsvReporter
 */
final class GithubActionReporterTest extends TestCase
{
    public function testReport(): void
    {
        $reporter = new GithubActionReporter($this->createStub(ReporterInterface::class));
        $output = $this
            ->getMockBuilder(Console\Output\ConsoleOutputInterface::class)
            ->disableOriginalConstructor()
            ->getMock()
        ;

        $output
            ->expects(self::once())
            ->method('writeln')
            ->with('::error file=template.twig,line=10,col=20::You are not allowed to do that.')
        ;

        $reporter->report(
            $output,
            [
                new Violation('template.twig', 10, 20, 'You are not allowed to do that.'),
            ]
        );
    }
}
