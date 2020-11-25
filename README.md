Bootstrap PHP
============================

A highly extendable PHP library that generate and prints html for [bootstrap](http://getbootstrap.com/).

## Installation
```term
$ composer require lodev09/bootstrap-php
```

## Built-in Components
The library has built-in components that are already available for you to use. See [creating custom components](https://github.com/lodev09/bootstrap-php/tree/master/docs/custom_components.md) to learn more on how to create your own component.
- [Table](src/Components/Table.php) - Print tables from datasource
- [Button](src/Components/Button.php) - Print simple buttons
- [Select](src/Components/Select.php) - Print select from datasource
- [Alert](src/Components/Alert.php) - Print alert
- [Input](src/Components/Input.php) - Print input (text, password, etc)

_If you want me to add your own component, feel free to contribute and submit a PR!_

## Usage
```php
use \Bootstrap\Components\Table;

// somewhere in your project.
// sample data from your db
$data = [
    ['name' => 'Jovanni Lo', 'email' => 'lodev09@gmail.com'],
    ['name' => 'foo', 'email' => 'bar@email.com']
];

$table = new Table($data);
$table->cell = [
    'name' => [
        'class' => 'text-primary',
        'url' => '#docs',
        // ... so much more
    ],
    // can also be a closure
    'username' => function($row, $index, $value) {
        // print_r($row)
        return '<strong>@'.$row['username'].'</strong>';
    }
];

// print the html
$table->printHtml();
```

## Feedback
All bugs, feature requests, pull requests, feedback, etc., are welcome. Visit my site at [www.lodev09.com](http://www.lodev09.com "www.lodev09.com") or
[![LICENSE MIT](https://img.shields.io/badge/Mail%20me%20at-lodev09%40gmail.com-green.svg)](mailto:lodev09@gmail.com)

## Credits
&copy; 2018 - Coded by Jovanni Lo / [@lodev09](http://twitter.com/lodev09)

## License
Released under the [![LICENSE MIT](https://img.shields.io/badge/license-MIT-red.svg)](http://opensource.org/licenses/MIT)
See [LICENSE](LICENSE) file.
