<?php

use FriendsOfTwig\Twigcs;

$finder = Twigcs\Finder\TemplateFinder::create()
    ->in(__DIR__.'/../../syntax_error')
;

return Twigcs\Config\Config::create()
    ->addFinder($finder)
    ->setSeverity('error')
    ->setDisplay('blocking')
;
