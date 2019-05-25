<?php

use Allocine\Twigcs\Experimental\DefaultRuleset;
use Allocine\Twigcs\Experimental\Handler;
use Allocine\Twigcs\Experimental\Linter;
use Allocine\Twigcs\Experimental\RuleChecker;
use Allocine\Twigcs\Experimental\StringSanitizer;

require_once __DIR__.'/vendor/autoload.php';

/*$linter = new Linter($rules);
$linter->lint('root', '{% for lel in foo if foo +  1 %}');
print_r($linter->errors);

$linter = new Linter($rules);
$linter->lint('root', '{% set  foo = [ "a",  "b"] %}');
print_r($linter->errors);

$linter = new Linter($rules);
$linter->lint('root', '{% set foo = [ ] %}');
print_r($linter->errors);

$linter = new Linter($rules);
$linter->lint('root', '{% set foo = [ "lel" ] %}');
print_r($linter->errors);

$linter = new Linter($rules);
$linter->lint('root', '{% set foo = { } %}');
print_r($linter->errors);

$linter = new Linter($rules);
$linter->lint('root', '{% set foo = { baz:  true } %}');
print_r($linter->errors);
*/

$linter = new Linter(DefaultRuleset::get());
print_r($linter->lint('{% set foo = { "foo":  "bar", baz:  true } %}'));

$linter = new Linter(DefaultRuleset::get());
print_r($linter->lint('{% set foo = { foo:  "bar", "baz": true,  tata: 1 } %}'));

$linter = new Linter(DefaultRuleset::get());
print_r($linter->lint('{% set foo = toto(1 +  (2 * 5)) %}'));

$linter = new Linter(DefaultRuleset::get());
print_r($linter->lint('{% set foo = {  toto : { tata:  1 } } %}'));

$linter = new Linter(DefaultRuleset::get());
print_r($linter->lint('{% set foo = (1 + (2 * foo(3, baz(  5)))) %}'));
