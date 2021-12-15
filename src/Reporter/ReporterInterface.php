<?php

namespace FriendsOfTwig\Twigcs\Reporter;

use FriendsOfTwig\Twigcs\Validator\Violation;
use Symfony\Component\Console\Output\OutputInterface;

interface ReporterInterface
{
    /**
     * @param Violation[] $violations
     */
    public function report(OutputInterface $output, array $violations);
}
