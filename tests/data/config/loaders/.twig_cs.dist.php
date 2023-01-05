<?php

use FriendsOfTwig\Twigcs\TemplateResolver;

$finder = FriendsOfTwig\Twigcs\Finder\TemplateFinder::create()
    ->in(__DIR__.'/src')
    ->sortByName()
;

return \FriendsOfTwig\Twigcs\Config\Config::create()
    ->setFinder($finder)
    ->setTemplateResolver(new TemplateResolver\ChainResolver([
        new TemplateResolver\FileResolver(__DIR__.'/src'),
        new TemplateResolver\NamespacedResolver([
            'acme' => new TemplateResolver\FileResolver(__DIR__.'/acme'),
        ]),
    ]))
    ->setSeverity('warning')
    ->setReporter('console')
    ->setName('my-config')
    ->setRuleSet(FriendsOfTwig\Twigcs\Ruleset\Official::class)
;
