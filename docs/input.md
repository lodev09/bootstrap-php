# \Bootstrap\Components\Input

A component that enables you to easily write `<input>` control through PHP.

## Usage

Instantiate the `Input` by passing a `name`.

```php
$input = new \Bootstrap\Components\Input($name);

// continued below...
```

## Options

### Input::options

| Option | Default | Description |
| ------ | ------- | ----------- |
| `disabled` | `false` | If the control is disabled |
| `grouped` | `true` | If grouped by `div.form-group` |
| `group_class` | `''` | Additional `div.form-gruop.*` classes |
| `required` | `false` | Add or omit `required="true"` attribute |

Example:
```php
// during init
$input = new \Bootstrap\Components\Input($name, ['required' => true]);

// after init
$input->options('disabled', true);
```

### Other properties

- `Input::name` - Sets the `name` attribute
- `Input::type` - Sets the `type` attribute
- `Input::id` - Sets the `id` attribute
- `Input::class` - Sets the `class` attribute
- `Input::attr` - Sets additional custom attributes
- `Input::label` - Sets the control `<label>` content
- `Input::placeholder` - Sets the `placeholder` attribute
- `Input::help` - Sets the help text below the control
- `Input::append` - Sets the appended content

## Print

Finally, once you configured your input, you can now `htmlPrint` it!
```php
$input->printHtml();
```