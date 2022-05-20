<?php

$finder = FriendsOfTwig\Twigcs\Finder\TemplateFinder::create()
    ->in(__DIR__.'/../../syntax_error')
;

return \FriendsOfTwig\Twigcs\Config\Config::create()
    ->addFinder($finder)
    ->setSeverity('error')
    ->setDisplay('blocking')
;
