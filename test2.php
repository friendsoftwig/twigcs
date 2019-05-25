<?php

use Allocine\Twigcs\Experimental\Handler;
use Allocine\Twigcs\Experimental\Linter;

require_once __DIR__.'/vendor/autoload.php';

const OP_VARS = [
    ' ' => ' *',
    '$' => '.+?',
    '@' => '[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*',
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
$expr[] = [OP_VARS, '@ \( \)', Handler::create()->enforceSize(' ', 0, 'No space should be used inside function call with no argument.')];
$expr[] = [OP_VARS, '@ \( $ \)', Handler::create()->delegate('$', 'list')->enforceSize(' ', 0, 'No space should be used')];
$expr[] = [OP_VARS, '\[ \]', Handler::create()->enforceSize(' ', 0, 'No space should be used for empty arrays.')];
$expr[] = [OP_VARS, '\[ $ \]', Handler::create()->delegate('$', 'list')->enforceSize(' ', 0, 'No space should be used')];
$expr[] = [OP_VARS, '\{ \}', Handler::create()->enforceSize(' ', 0, 'No space should be used for empty hashes.')];
$expr[] = [OP_VARS, '\{ $ \}', Handler::create()->delegate('$', 'hash')->enforceSize(' ', 1, 'One space should be used')];
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
$list[] = [LIST_VARS, ' $ ', Handler::create()->delegate('$', 'expr')->enforceSize(' ', 0, 'Empty list should have no whitespace')];

$hash = [];
$hash[] = [LIST_VARS, ' ', Handler::create()->enforceSize(' ', 0, 'Empty hash should have no whitespace')];
$hash[] = [LIST_VARS, '@ :_$ ,_%', Handler::create()->delegate('$', 'expr')->delegate('%', 'hash')->enforceSize(' ', 0, 'No space should be used')->enforceSize('_', 1, 'One space should be used')];
$hash[] = [LIST_VARS, '"@" :_$ ,_%', Handler::create()->delegate('$', 'expr')->delegate('%', 'hash')->enforceSize(' ', 0, 'No space should be used')->enforceSize('_', 1, 'One space should be used')];
$hash[] = [LIST_VARS, '@ :_$', Handler::create()->delegate('$', 'expr')->enforceSize(' ', 0, 'No space should be used')->enforceSize('_', 1, 'One space should be used')];
$hash[] = [LIST_VARS, '"@" :_$', Handler::create()->delegate('$', 'expr')->enforceSize(' ', 0, 'No space should be used')->enforceSize('_', 1, 'One space should be used')];

$rules = [
    'root' => $rules,
    'expr' => $expr,
    'list' => $list,
    'hash' => $hash,
];

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


$linter = new Linter($rules);
$linter->lint('root', '{% set foo = { "foo":  "bar", baz:  true } %}');
print_r($linter->errors);

$linter = new Linter($rules);
$linter->lint('root', '{% set foo = { foo:  "bar", "baz": true,  tata: 1 } %}');
print_r($linter->errors);

$linter = new Linter($rules);
$linter->explain();
$linter->lint('root', '{% set foo = toto(1 +  (2 * 5)) %}');
print_r($linter->errors);
*/

$linter = new Linter($rules);
$linter->explain();
$linter->lint('root', '{% set foo = {  toto : { tata:  1 } } %}');
print_r($linter->errors);
