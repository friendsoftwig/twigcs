<?php

namespace Allocine\Twigcs\Console;

use Symfony\Component\Finder\Finder;

abstract class AbstractCommand extends ContainerAwareCommand
{
    protected function getFiles(array $paths): array
    {
        $files = [];
        foreach ($paths as $path) {
            if (is_file($path)) {
                $files[] = new \SplFileInfo($path);
            } else {
                $finder = new Finder();
                $found = iterator_to_array($finder->in($path)->name('*.twig'));
                if (!empty($found)) {
                    $files = array_merge($files, $found);
                }
            }
        }

        return $files;
    }
}
