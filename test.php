<?php

require_once __DIR__.'/vendor/autoload.php';

use Allocine\Twigcs\Experimental\ParenthesesExtractor;

$p = new ParenthesesExtractor();

print_r($p->extract('(1 + 2) * 6')->flatten());
print_r($p->extract('(1 + 2) * (6 * 5)')->flatten());
print_r($p->extract('3 * (1 + 4 * (2 + 5))')->flatten());
print_r($p->extract('(1 + 2) * 6')->flatten());
