# \Bootstrap\Components\Alert

A component that enables you to easily write `<div.alert>` control through PHP.

## Usage

Instantiate the `Alert` by passing a `content` and `type`.

```php
$alert = new \Bootstrap\Components\Alert('Your transaction completed successfully', 'success');

// continued below...
```

## Options

### Alert::options

| Option | Default | Description |
| ------ | ------- | ----------- |
| `dismissible` | `true` | If the alert is dismissible |

Example:
```php
// during init
$alert = new \Bootstrap\Components\Alert('Your transaction completed successfully', 'success', ['dismissible' => false]);

// after init
$alert->options('dismissible', false);
```

### Other properties

- `Alert::content` - The content of the alert
- `Alert::class` - Sets the `class` attribute

## Print

Finally, once you configured your alert, you can now `htmlPrint` it!
```php
$alert->printHtml();
```
