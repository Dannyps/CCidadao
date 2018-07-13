# CCidadao

CCidadao is a PHP Class used to validate and generate Citizen Card numbers.

## How to use

### Installing

CCidadao is available on [Packagist](https://packagist.org/)! You can install it via [Composer](https://getcomposer.org/), by typing:

`$ composer require Dannyps/CCidadao`

The class will be auto-loaded by composer. Thus, in order to use it, you need only `require 'vendor/autoload.php';` in your PHP script.

### Softening the curve

Portuguese Citizen Cards are a complicated subject.
They have two check digits, and a weird version control system.
Internal variables contain the following fields:

In the example number:

```
12345678 9 ZZ 0
┃        ┃ ┃  ┃
┃        ┃ ┃  ┗━━━> Versioned check digit ----> $vcd
┃        ┃ ┗━━━━━━> Version chars ------------> $vcc
┃        ┗━━━━━━━━> Constant check digit -----> $ccd
┗━━━━━━━━━━━━━━━━━> the number itself --------> $num
```
 * The version chars represent the version of the document in the following manner:
- ZZ => v1
- ZY => v2
- ZX => v3
- ...
- ZA => v26
- YZ => v27
- etc...
Both `$ccd` and `$vcd` can be determined, provided the `$num` and `$ver/$vcc` are available, respectively.

Moreover, the `$ccd` depends solely on the `$num`, whereas the `$vcd` depends on the `$num` and on the `$vcc`.

### Code Examples

Test code is a good place to start. However, code examples are displayed below for your convenience.

#### Generating an array of _valid_ CC numbers.
_Note: The fact these numbers are valid does not mean they are being used by anyone._

```php
<?php
require 'vendor/autoload.php';
use Dannyps\CCidadao\CCidadao;

$ver = [
    'min' => 1,
    'max' => 2,
];

$beg = 15000000;
$end = 15000100;

$arr = [];

for ($i = $beg; $i < $end; $i++) {

    for ($j = $ver['min']; $j <= $ver['max']; $j++) {

        array_push($arr, (new CCidadao($i . "_", $j))->__toString());
    }
}

// $arr now contains versions 1 and 2 of all CCs from 15000000 to 15000100. These are valid values.
```
#### Getting the next version of a CC number

As time goes by, people get new Citizen Cards. However, their numbers are already predestined. Because of the algorithm used, it is possible to foresee all possible numbers. Moreover, we know there numbers are sequential.

```php
<?php
require 'vendor/autoload.php';
use Dannyps\CCidadao\CCidadao;

$cc = new CCidadao("15632563ZZ7");
$cc->next(); // advance current cc
echo ($cc);

// -- another method --

echo new CCidadao("15632563", 2);
```

#### Validating a CC number

```php
<?php
require 'vendor/autoload.php';
use Dannyps\CCidadao\CCidadao;

$valid = true;
try {
    new CCidadao("153666960ZZ1");
} catch(Exception $e){
    $valid = false;
}

var_dump($valid); // either true or false
```

## Motivation
There was an interest in being able to quickly generate valid CC numbers for pentesting reasons. Thus, CCidadao was born. Its applications, however, are more abrangent than that.

## Contributing
You are welcome to contribute to the code, as well as to the documentation. You should do so by means of a Pull Request. You may use xDebug to profile the execution and find the less effecient methods.

## How does it work?
The algorithms to determine both control digits are available online, in some blogs and similar pages. Testing of this class began with real CC numbers, as this was the only way to make sure the code was developed according to reality.

