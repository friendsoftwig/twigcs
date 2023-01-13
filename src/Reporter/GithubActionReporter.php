<?php

namespace FriendsOfTwig\Twigcs\Reporter;

use Symfony\Component\Console\Output\OutputInterface;

class GithubActionReporter implements ReporterInterface
{
    /**
     * @see https://github.com/actions/toolkit/blob/5e5e1b7aacba68a53836a34db4a288c3c1c1585b/packages/core/src/command.ts#L80-L85
     */
    private const ESCAPED_DATA = [
        '%' => '%25',
        "\r" => '%0D',
        "\n" => '%0A',
    ];

    /**
     * @see https://github.com/actions/toolkit/blob/5e5e1b7aacba68a53836a34db4a288c3c1c1585b/packages/core/src/command.ts#L87-L94
     */
    private const ESCAPED_PROPERTIES = [
        '%' => '%25',
        "\r" => '%0D',
        "\n" => '%0A',
        ':' => '%3A',
        ',' => '%2C',
    ];

    private ReporterInterface $reporter;

    public function __construct(ReporterInterface $reporter)
    {
        $this->reporter = $reporter;
    }

    public function report(OutputInterface $output, array $violations)
    {
        foreach ($violations as $violation) {
            $output->writeln(sprintf(
                '::%s file=%s,line=%s,col=%s::%s',
                strtolower($violation->getSeverityAsString()),
                strtr($violation->getFilename(), self::ESCAPED_PROPERTIES),
                strtr($violation->getLine() ?? 1, self::ESCAPED_PROPERTIES),
                strtr($violation->getColumn() ?? 0, self::ESCAPED_PROPERTIES),
                strtr($violation->getReason(), self::ESCAPED_DATA)
            ));
        }

        $this->reporter->report($output, $violations);
    }
}
