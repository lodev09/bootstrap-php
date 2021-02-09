\Bootstrap\Components\Select
============================

A component that enables you to easily write bootstrap `<select>` form control through PHP.

## Usage

Instantiate the `Select` by passing the `data`, `name` and `value`. The data should look something like this:

```php
// sample data from your db
$data = [
    ['id' => 1, 'name' => 'Jovanni', 'email' => 'lodev09@gmail.com'],
    ['id' => 2, 'name' => 'foo', 'email' => 'bar@email.com'],
    ['id' => 3, 'name' => 'bar', 'email' => 'foo@email.com']
];

$select = new \Bootstrap\Components\Select($data, 'demo', 'id');

// continued below...
```

## Options

### Select::options

| Option | Default | Description |
| ------ | ------- | ----------- |
| `disabled` | `false` | If the control is disabled |
| `grouped` | `true` | If grouped by `div.form-group` |
| `group_class` | `''` | Additional `div.form-gruop.*` classes |
| `required` | `false` | Add or omit `required="true"` attribute |

Example:
```php
// during init
$select = new \Bootstrap\Components\Select($data, 'demo', 'id', ['disabled' => true]);

// after init
$select->options('disabled', true);
```

### Other properties

- `Select::data` - Sets the data source.
- `Select::name` - Sets the `name` attribute
- `Select::value` - Sets the `value` attribute key
- `Select::display` - Sets the `display` attribute key
- `Select::id` - Sets the `id` attribute
- `Select::class` - Sets the `class` attribute
- `Select::attr` - Sets additional custom attributes
- `Select::label` - Sets the control `<label>` content
- `Select::placeholder` - Sets the `placeholder` attribute
- `Select::help` - Sets the help text below the control
- `Select::append` - Sets the appended content
- `Select::each` - Sets the callback for each row of the content

	```php
	$select->each(function($row) {
		return [
			'content' => $row['name'] + ' - ' + $row['id'];
		];
	})
	```

- `Select::selected` - Sets the default selected value

	```php
	$select->selected('1');
	```

- `Select::default` - Sets the default value

### Styling

To learn more about styling the `<select>`, see [Basic Tables](form_basic_inputs.php) demo.

## Print

Finally, once you configured your select, you can now `htmlPrint` it!
```php
$select->printHtml();
```

## Credits

`\Bootstrap\Components\Select` is part of the [lodev09/bootstrap-php](https://github.com/lodev09/bootstrap-php) package created by [@lodev09](https://twitter.com/lodev09).