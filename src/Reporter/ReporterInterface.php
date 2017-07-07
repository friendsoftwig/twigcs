<?php

namespace Allocine\Twigcs\Reporter;

use Symfony\Component\Console\Output\OutputInterface;

interface ReporterInterface
{
    /**
     * @param OutputInterface $output
     * @param Violation[]     $violations
     */
    public function report(OutputInterface $output, array $violations);
}