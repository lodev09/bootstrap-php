<?php

namespace Bootstrap\Components;
use \Bootstrap\Helper;
use \Common\Util;

class Button extends Component {
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
            'content' => $content,
            'icon' => '',
            'type' => $type,
            'container' => self::BUTTON_CONTAINER_BUTTON,
            'size' => self::BUTTON_SIZE_MEDIUM,
            'attr' => [],
            'id' => '',
            'class' => '',
            'dropdown' => []
        ]);
    }

    public function printHtml($return = false) {

        $that = $this;
        $properties = $this->_properties;

        $icon = Helper::getValue($properties->icon, array(
            'if_closure' => function($icon) use ($that) { return Helper::runCallback($icon, array($that)); },
            'if_array' => function($icon) {
                parent::err('Bootstrap::Button::icon requires string.');
                return '';
            }
        ));

        $container = Helper::getValue($properties->container, array(
            'if_closure' => function($container) use ($that) { return Helper::runCallback($container, array($that)); },
            'if_array' => function($container) {
                parent::err('Bootstrap::Button::container requires string.');
                return '';
            }
        ));

        $content = Helper::getValue($properties->content, array(
            'if_closure' => function($content) use ($that) { return Helper::runCallback($content, array($that)); },
            'if_array' => function($content) {
                parent::err('Bootstrap::Button::content requires string.');
                return '';
            }
        ));

        $attr = Helper::getValue($properties->attr, array(
            'if_closure' => function($attr) use ($that) {
                $callback_return = Helper::runCallback($attr, $array($that));
                if (is_array($callback_return)) return $callback_return;
                else return array($callback_return);
            },
            'if_array' => function($attr) {
                $attrs = array();
                foreach ($attr as $key => $value) {
                    $attrs[] = $key.'="'.$value.'"';
                }

                return $attrs;
            },
            'if_other' => function($attr) {
                return array($attr);
            }
        ));

        $class = Helper::getValue($properties->class, array(
            'if_closure' => function($class) use ($that) { return Helper::runCallback($class, array($that)); },
            'if_array' => function($class) {
                return implode(' ', $class);
            }
        ));

        $type = Helper::getValue($properties->type, array(
            'if_array' => function($class) {
                parent::err('Bootstrap::Button:type requires string.');
                return self::BUTTON_TYPE_DEFAULT;
            }
        ));

        $classes = array();

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

?>