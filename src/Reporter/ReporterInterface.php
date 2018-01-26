<?php

namespace Allocine\Twigcs\Reporter;

use Allocine\Twigcs\Validator\Violation;
use Symfony\Component\Console\Output\OutputInterface;

interface ReporterInterface
{
    /**
     * @param OutputInterface $output
     * @param Violation[]     $violations
     */
    public function report(OutputInterface $output, array $violations);
}
