# \Bootstrap\Components\Card

A component that enables you to easily write `<div.card>` control through PHP.

## Usage

Instantiate the `Card` by passing a `content` and `title`.

```php
$card = new \Bootstrap\Components\Card('Your content here', 'this is title');

// continued below...
```

## Options

### Alert::options

| Option | Default | Description |
| ------ | ------- | ----------- |
| `footer` | `false` | if you don't need footer |
| `card_class` | `false` | if you don't need class in main class  |
| `header_class` | `false` | if you don't need class in class header  |
| `niceTitle` | `false` | if you don't need the format on the title  |

Example:
```php
// during init
$card = new \Bootstrap\Components\Card('Your content here', 'this is title', [
    'footer'        => false,
    'card_class'    => 'card-primary',
    'header_class'  => 'bg-info text-white',
    'niceTitle'     => true
]);


## Print

Finally, once you configured your alert, you can now `htmlPrint` it!
```php
$card->printHtml();
```
