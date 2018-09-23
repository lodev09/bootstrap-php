Bootstrap PHP
============================

A highly extendable PHP library that generate and prints html for [bootstrap](http://getbootstrap.com/).

## Installation
```term
$ composer require lodev09/bootstrap-php
```

## Built-in Components
The library has built-in components that are already available for you to use. See [creating custom components](https://github.com/lodev09/bootstrap-php/tree/master/docs/custom_components.md) to learn more on how to create your own component.
- [Table](https://github.com/lodev09/bootstrap-php/tree/master/docs/table.md)
- [Button](https://github.com/lodev09/bootstrap-php/tree/master/docs/custom_components.md)

_If you want me to add your own component, feel free to contribute and submit a PR!_

## Usage
```php
// register the component(s) you want to use
\Bootstrap\Component::register('table', 'Bootstrap\Components\Table');

// create the comonent ui instance (this can be used globally)
$ui = new \Bootstrap\Component();

// somewhere in your project.
// sample data from your db
$data = [
  ['name' => 'Jovanni Lo', 'email' => 'lodev09@gmail.com'],
  ['name' => 'foo', 'email' => 'bar@email.com']
];

$table = $ui->create_table($data);
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
$table->print_html();
```

## Feedback
All bugs, feature requests, pull requests, feedback, etc., are welcome. Visit my site at [www.lodev09.com](http://www.lodev09.com "www.lodev09.com") or email me at [lodev09@gmail.com](mailto:lodev09@gmail.com)

## Credits
&copy; 2011-2018 - Coded by Jovanni Lo / [@lodev09](http://twitter.com/lodev09)

## License
Released under the [MIT License](http://opensource.org/licenses/MIT).
See [LICENSE](LICENSE) file.
