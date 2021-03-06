Create your own component
============================

## Template
```php
namespace Bootstrap\Components;
use \Bootstrap\Helper;
use \Common\Util;

class ComponentName extends \Bootstrap\Component {
    private $_properties = [];

    // optional
    public function __construct() {}

    // required
    public function printHtml() {}
}
```

## In action: Button
Create the class that will be **registered** to the `\Bootstrap\Component` class. Let's the create `Button` class

```php
namespace Bootstrap\Components;
use \Bootstrap\Util;

class Button extends \Bootstrap\Component {
    const BUTTON_CONTAINER_ANCHOR = 'a';
    const BUTTON_CONTAINER_BUTTON = 'button';

    const BUTTON_SIZE_LARGE = 'lg';
    const BUTTON_SIZE_SMALL = 'sm';
    const BUTTON_SIZE_XSMALL = 'xs';
    const BUTTON_SIZE_MEDIUM = 'md';

    private $_options_map = [
        'disabled' => false
    ];

    public function __construct($content = '', $type = 'default', $options = []) {
        parent::__construct([
            'options' => Util::setValues($this->_options_map, $options),
            'content' => content,
            'icon' => '',
            'type' => type,
            'container' => self::BUTTON_CONTAINER_BUTTON,
            'size' => self::BUTTON_SIZE_MEDIUM,
            'attr' => [],
            'id' => '',
            'class' => '',
            'dropdown' => []
        ]);
    }

    public function printHtml($return = false) {
        $properties = $this->_properties;

        $icon = $this->getPropValue($properties->icon);
        $container = $this->getPropValue($properties->container);
        $content = $this->getPropValue($properties->content);
        $attr = $this->getPropValueAttr($properties->attr);
        $class = $this->getPropValue($properties->class);
        $type = $this->getPropValue($properties->type);

        $classes = [];

        // custom class
        if ($class) $classes[] = $class;

        // size
        $size_class = '';
        if ($properties->size) {
            $size_class = 'btn-'.$properties->size;
            $classes[] = $size_class;
        }
        // disabled
        $disabled = $properties->options['disabled'] ? 'disabled' : '';
        $classes[] = $disabled;

        $class_htm = $classes ? ' '.implode(' ', $classes) : '';

        $result = '<'.$container.' class="btn btn-'.$type.$class_htm.'" '.implode(' ', $attr).'>';
        $result .= $content;
        $result .= '</'.$container.'>';

        if ($properties->id) $attr[] = 'id="'.$properties->id.'"';

        if ($return) return $result;
        else echo $result;
    }
}
```

## Usage

### `printHtml` it!
```php
use \Bootstrap\Components\Button;
// register the component(s) you want to use

$button = new Button('Click me');
$button->class('text-success');
$button->printHtml();
```