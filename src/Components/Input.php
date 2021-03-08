<?php

namespace Bootstrap\Components;
use \Bootstrap\Helper;
use \Common\Util;

class Input extends Component {
	const INPUT_TYPE_TEXT = 'text';
	const INPUT_TYPE_PASSWORD = 'password';

	private $_options_map = [
        'disabled' => false,
        'grouped' => true,
        'group_class' => '',
        'label_class' => 'd-flex',
        'required' => false
    ];

    // optional
    public function __construct($name, $options = []) {
        $id = 'input-'.Util::slugify($name).'-'.Util::token();
        parent::__construct([
            'name' => $name,
            'options' => Util::setValues($this->_options_map, $options),
            'type' => self::INPUT_TYPE_TEXT,
            'value' => '',
            'id' => $id,
            'class' => '',
            'attr' => [],
            'label' => '',
            'placeholder' => '',
            'help' => null,
            'append' => null
        ]);
    }

    public function printHtml($return = false) {
    	$properties = $this->_properties;
        $value = $this->getPropValue($properties->value);
        $label = $this->getPropValue($properties->label);
    	$attr = $this->getPropValueAttr($properties->attr);
        $class = $this->getPropValue($properties->class);

        $classes = array();

        // custom class
        if ($class) $classes[] = $class;

        // disabled
        $disabled = $properties->options['disabled'] ? 'disabled' : '';
        $classes[] = $disabled;

        $class_htm = $classes ? ' '.implode(' ', $classes) : '';
        $attr[] = 'id="'.$properties->id.'"';

        if ($properties->placeholder) $attr[] = 'placeholder="'.$properties->placeholder.'"';

        $result = '<input
            type="'.escape($properties->type).'"
            name="'.escape($properties->name).'"
            value="'.escape($value).'"
            class="form-control '.$class_htm.'"
            '.implode(' ', $attr).'
            '.($properties->options['required'] ? 'required' : null).'
        >';

        if ($properties->label) {
            $result = '
                <label class="form-label '.$properties->options['label_class'].'" for="'.$properties->id.'">
                    '.$properties->label.'
                </label>
                '.$result;
        }

        if ($properties->help) {
            $result .= '
                <small class="form-text text-muted">'.$properties->help.'</small>';
        }

        if ($properties->append) {
            $result .= $properties->append;
        }

        if ($properties->options['grouped']) {
        	$result = '
	        	<div class="form-group '.$properties->options['group_class'].'">
	        		'.$result.'
	        	</div>
            ';
        }

        if ($return) return $result;
        else echo $result;
    }
}