<?php

namespace FriendsOfTwig\Twigcs;

use Symfony\Component\Finder\Finder as BaseFinder;

class Finder extends BaseFinder
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
