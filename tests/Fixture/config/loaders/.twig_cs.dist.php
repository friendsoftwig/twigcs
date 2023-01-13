<?php

use FriendsOfTwig\Twigcs;

$finder = Twigcs\Finder\TemplateFinder::create()
    ->in(__DIR__.'/src')
    ->sortByName()
;

return Twigcs\Config\Config::create()
    ->setFinder($finder)
    ->setTemplateResolver(new Twigcs\TemplateResolver\ChainResolver([
        new Twigcs\TemplateResolver\FileResolver(__DIR__.'/src'),
        new Twigcs\TemplateResolver\NamespacedResolver([
            'acme' => new Twigcs\TemplateResolver\FileResolver(__DIR__.'/acme'),
        ]),
    ]))
    ->setSeverity('warning')
    ->setReporter('console')
    ->setName('my-config')
    ->setRuleSet(FriendsOfTwig\Twigcs\Ruleset\Official::class)
;
