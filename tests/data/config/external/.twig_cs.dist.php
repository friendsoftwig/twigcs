<?php

$finderA = FriendsOfTwig\Twigcs\Finder\TemplateFinder::create()
    ->in(__DIR__.'/../../basepaths/a')
    ->exclude('templates')
;

$finderB = FriendsOfTwig\Twigcs\Finder\TemplateFinder::create()
    ->in(__DIR__.'/../../basepaths/b')
    ->exclude('templates')
;

return \FriendsOfTwig\Twigcs\Config\Config::create()
    ->addFinder($finderA)
    ->addFinder($finderB)
    ->setSeverity('warning')
    ->setReporter('console')
    ->setName('my-config')
    ->setRuleSet(FriendsOfTwig\Twigcs\Ruleset\Official::class)
;
