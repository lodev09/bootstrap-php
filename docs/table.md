\Bootstrap\Components\Table
============================

## Usage
```php
$ui = new \Bootstrap\Component();

// somewhere in your project.
// sample data from your db
$data = [
    ['name' => 'Jovanni Lo', 'email' => 'lodev09@gmail.com'],
    ['name' => 'foo', 'email' => 'bar@email.com']
];

$table = $ui->create_table($data);
```

### Cell
```php
// changing class of a cell
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
 // can be also done individually like so
 $table->cell('email', ['url' => 'mailto:{{email}}']);
```

### Col
```php
// define the columns
$table->col = [
    'name' => [
        'class' => 'bg-primary text-white',
        'attr' => ['style' => 'font-weight: bold;'],
        'icon' => '',
        'hidden'
    ]
];
// can also be called individually e.g. rename a column
$table->col('username', '@');
```

### Other properties
Properties follows the same workflow as the `cell` and `col` does so it's self explainatory :)

- Table::options
- Table::data
- Table::each
- Table::id
- Table::row
- Table::hidden
- Table::visible
- Table::hide
- Table::sort
- Table::class

### print it!
```php
$table->print_html();
```