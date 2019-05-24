<?php

use Allocine\Twigcs\Experimental\Handler;
use Allocine\Twigcs\Experimental\Linter;

require_once __DIR__.'/vendor/autoload.php';

const OP_VARS = [
    ' ' => ' *',
    '$' => '.+?',
];

const BLOCK_VARS = [
    ' ' => ' +',
    '_' => ' *',
    '@' => '[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*',
    '$' => '.+?',
];

const LIST_VARS = [
    ' ' => ' *',
    '_' => ' *',
    '@' => '[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*',
    '$' => '.+?',
    '%' => '.+?',
];

$rules = [];
$rules[] = [BLOCK_VARS, '{% for @ in $ if $ %}', Handler::create()->delegate('$', 'expr')->enforceSize(' ', 1, 'More than one space used')];
$rules[] = [BLOCK_VARS, '{% set @ = $ %}', Handler::create()->delegate('$', 'expr')->enforceSize(' ', 1, 'More than one space used')];

$expr = [];
$expr[] = [OP_VARS, '\[ $ \]', Handler::create()->delegate('$', 'list')->enforceSize(' ', 0, 'No space should be used')];
$expr[] = [OP_VARS, '$ \.\. $', Handler::create()->delegate('$', 'expr')->enforceSize(' ', 1, 'More than one space used')];
$expr[] = [OP_VARS, '$ \?\? $', Handler::create()->delegate('$', 'expr')->enforceSize(' ', 1, 'More than one space used')];
$expr[] = [OP_VARS, '$ \*\* $', Handler::create()->delegate('$', 'expr')->enforceSize(' ', 1, 'More than one space used')];
$expr[] = [OP_VARS, '$ % $', Handler::create()->delegate('$', 'expr')->enforceSize(' ', 1, 'More than one space used')];
$expr[] = [OP_VARS, '$ // $', Handler::create()->delegate('$', 'expr')->enforceSize(' ', 1, 'More than one space used')];
$expr[] = [OP_VARS, '$ / $', Handler::create()->delegate('$', 'expr')->enforceSize(' ', 1, 'More than one space used')];
$expr[] = [OP_VARS, '$ \* $', Handler::create()->delegate('$', 'expr')->enforceSize(' ', 1, 'More than one space used')];
$expr[] = [OP_VARS, '$ ~ $', Handler::create()->delegate('$', 'expr')->enforceSize(' ', 1, 'More than one space used')];
$expr[] = [OP_VARS, '$ - $', Handler::create()->delegate('$', 'expr')->enforceSize(' ', 1, 'More than one space used')];
$expr[] = [OP_VARS, '$ \+ $', Handler::create()->delegate('$', 'expr')->enforceSize(' ', 1, 'More than one space used')];

$list = [];
$list[] = [LIST_VARS, ' ', Handler::create()->enforceSize(' ', 0, 'Empty list should have no whitespace')];
$list[] = [LIST_VARS, '$_, %', Handler::create()->delegate('$', 'expr')->delegate('%', 'list')];
$list[] = [LIST_VARS, ' @ ', Handler::create()->enforceSize(' ', 0, 'Empty list should have no whitespace')];
$list[] = [LIST_VARS, ' $ ', Handler::create()->enforceSize(' ', 0, 'Empty list should have no whitespace')];

$rules = [
    'root' => $rules,
    'expr' => $expr,
    'list' => $list,
];

$linter = new Linter($rules);
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
