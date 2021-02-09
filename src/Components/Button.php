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
            'class' => ''
        ]);
    }

    public function printHtml($return = false) {

        $that = $this;
        $properties = $this->_properties;

        $icon = $this->getPropValue($properties->icon);
        $container = $this->getPropValue($properties->container);
        $content = $this->getPropValue($properties->content);
        $attr = $this->getPropValueAttr($properties->attr);
        $class = $this->getPropValue($properties->class);
        $type = $this->getPropValue($properties->type);

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