<?php

namespace FriendsOfTwig\Twigcs\Tests\Unit\Reporter;

use FriendsOfTwig\Twigcs\Reporter\CsvReporter;
use FriendsOfTwig\Twigcs\Validator\Violation;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console;

/**
 * @internal
 */
final class CsvReporterTest extends TestCase
{
    public function testReport(): void
    {
        $output = $this->createMock(Console\Output\OutputInterface::class);

        $output
            ->expects(self::once())
            ->method('writeln')
            ->with('template.twig;10;20;error - You are not allowed to do that.')
        ;

        $reporter = new CsvReporter();

        $reporter->report(
            $output,
            [
                new Violation('template.twig', 10, 20, 'You are not allowed to do that.'),
            ]
        );
    }
}
