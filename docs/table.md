\Bootstrap\Components\Table
============================

A component that enables you to easily write bootstrap `<table>` form control through PHP.

## Usage
```php

// somewhere in your project.
// sample data from your db
$data = [
    ['name' => 'Jovanni Lo', 'email' => 'lodev09@gmail.com'],
    ['name' => 'foo', 'email' => 'bar@email.com']
];

$table = new \Bootstrap\Components\Table($data);

// continued below...
```

## Options

### Table::options

You can configure the table during initialization.

| Option | Default | Description |
| ------ | ------- | ----------- |
| `checkboxes` | `false` | Add checkboxes to each row |
| `columns` | `true` | Enable/disable columns |
| `cell_class` | `null` | Set global class for each cell |
| `thead` | `true` | Use `<thead>` or not |
| `thead_class` | `'thead-light` | Set the class of the `<thead>` |
| `table` | `true` | Sets the `.table` class |
| `inverse` | `false` | Sets the `.table-inverse` class |
| `striped` | `true` | Sets the `.table-striped` class |
| `responsive` | `false` | Sets the `.table-responsive` class. Also accepts `sm`, `md`, `lg`, `xl` |
| `dark` | `false` | Sets the `.table-dark` class |
| `light` | `false` | Sets the `.table-light` class |
| `small` | `false` | Sets the `.table-sm` class |
| `bordered` | `true` | Sets the `.table-bordered` class |
| `hover` | `false` | Sets the `.table-hover` class |
| `default_col` | "No Data" | Sets the default display when data is empty |

Example:
```php
// during init
$table = new \Bootstrap\Components\Table($data, ['bordered' => true]);

// after init
$table->options('bordered', true);
```

### Cells

Using the `cell` property, you can control the values of each row/column by passing static configuration or callbacks (closure). FOr example:

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
        // plog($row)
        return '<strong>@'.$row['username'].'</strong>';
    }
];
 // can be also done individually like so
 $table->cell('email', ['url' => 'mailto:{{email}}']);

 // continued below...
```

### Columns

The `col` property is where you can configure each column from your data like adding custom class, renaming, etc. This will basically modify the output `<th>`. For example:

```php
// define the columns
// note that columns that are not defined here will be hidden
// to avoid this behaviour, you can use the $table->col(column, value) way
$table->col = [
    'name' => [
        'title' => 'Name',
        'class' => 'bg-primary text-white',
        'attr' => ['style' => 'font-weight: bold;']
    ],
    'username' => '@' // this will rename the column to '@'
];

// can also pass the configuration like this:
$table->col('username', '@');

// continued below...
```

### Other properties

- `Table::data` - Sets the data source.
- `Table::row` - Configure a row given an index. Use `Table:each(row)` if you don't know which index to modify.

    ```php
    $table->row(1, ['class' => 'bg-primary text-white']);
    ```

- `Table::each('row', callback)` - Accepts a `callback` that will be called for each row.

    ```php
    $table->each('row', function($row) {
        return [
            'class' => 'bg-primary text-white'
        ];
    })
    ```

- `Table::id` - Sets the `id` attribute of the `<table>`
- `Table::hide` - Sets the column visibility. Note that this will use the class `d-none`.

    ```php
    $table->hide('id', true);
    ```

- `Table::hidden` - Same as `Table::hide` but accepts array of hidden columns.
- `Table::class` - Sets the `class` attribute of the `<table>`

## Print

Finally, once you configured your table, you can now `htmlPrint` it!
```php
$table->printHtml();
```