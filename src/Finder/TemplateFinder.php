<?php

namespace FriendsOfTwig\Twigcs\Finder;

use Symfony\Component\Finder\Finder as BaseFinder;

/**
 * Special thanks to https://github.com/c33s/twigcs/ which this feature was inspired from.
 */
class TemplateFinder extends BaseFinder
{
    public function __construct()
    {
        parent::__construct();

        $this
            ->files()
            ->name('*.twig')
            ->ignoreDotFiles(true)
            ->ignoreVCS(true)
        ;
    }
}
