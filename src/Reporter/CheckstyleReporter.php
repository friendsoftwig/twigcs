<?php

namespace FriendsOfTwig\Twigcs\Reporter;

use Symfony\Component\Console\Output\OutputInterface;

class CheckstyleReporter implements ReporterInterface
{
    public function report(OutputInterface $output, array $violations)
    {
        $filename = null;
        $filenode = null;

        $checkstyle = new \SimpleXMLElement('<checkstyle version="1.0.0"/>');

        foreach ($violations as $violation) {
            if ($filename !== $violation->getFilename()) {
                $filename = $violation->getFilename();
                $filenode = $checkstyle->addChild('file');
                $filenode->addAttribute('name', $filename);
            }

            $error = $filenode->addChild('error');
            $error->addAttribute('column', $violation->getColumn());
            $error->addAttribute('line', $violation->getLine());
            $error->addAttribute('severity', strtolower($violation->getSeverityAsString()));
            $error->addAttribute('message', $violation->getReason());
            $error->addAttribute('source', $violation->getSource());
        }

        $output->writeln($checkstyle->asXML());
    }
}
