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

        $icon = Helper::getValue($properties->icon, [
            'if_closure' => function($icon) { return Helper::runCallback($icon, [$this]); },
            'if_array' => function($icon) {
                parent::err('Bootstrap::Button::icon requires string.');
                return '';
            }
        ]);

        $container = Helper::getValue($properties->container, [
            'if_closure' => function($container) { return Helper::runCallback($container, [$that]); },
            'if_array' => function($container) {
                parent::err('Bootstrap::Button::container requires string.');
                return '';
            }
        ]);

        $content = Helper::getValue($properties->content, [
            'if_closure' => function($content) { return Helper::runCallback($content, [$this]); },
            'if_array' => function($content) {
                parent::err('Bootstrap::Button::content requires string.');
                return '';
            }
        ]);

        $attr = Helper::getValue($properties->attr, [
            'if_closure' => function($attr) {
                $callback_return = Helper::runCallback($attr, [$this]);
                if (is_array($callback_return)) return $callback_return;
                else return [$callback_return];
            },
            'if_array' => function($attr) {
                $attrs = [];
                foreach ($attr as $key => $value) {
                    $attrs[] = $key.'="'.$value.'"';
                }

                return $attrs;
            },
            'if_other' => function($attr) {
                return [$attr];
            }
        ]);

        $class = Helper::getValue($properties->class, [
            'if_closure' => function($class) { return Helper::runCallback($class, [$this]); },
            'if_array' => function($class) {
                return implode(' ', $class);
            }
        ]);

        $type = Helper::getValue($properties->type, [
            'if_array' => function($class) {
                parent::err('Bootstrap::Button:type requires string.');
                return self::BUTTON_TYPE_DEFAULT;
            }
        ]);

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