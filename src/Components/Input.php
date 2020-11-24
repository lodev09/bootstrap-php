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
        'required' => false
    ];

    // optional
    public function __construct($name, $options = []) {
        parent::__construct([
            'name' => $name,
            'options' => Util::setValues($this->_options_map, $options),
            'type' => self::INPUT_TYPE_TEXT,
            'value' => '',
            'id' => '',
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

        $value = Helper::getValue($properties->value, array(
            'if_closure' => function($value) { return Helper::runCallback($value, array($this)); },
            'if_array' => function($value) {
                throw new \Exception('Bootstrap::Input::value requires string.');
            }
        ));

        $label = Helper::getValue($properties->label, array(
            'if_closure' => function($label) { return Helper::runCallback($label, array($this)); },
            'if_array' => function($label) {
                throw new \Exception('Bootstrap::Input::value requires string.');
            }
        ));

    	$attr = Helper::getValue($properties->attr, array(
            'if_closure' => function($attr) {
                $callback_return = Helper::runCallback($attr, $array($this));
                if (is_array($callback_return)) return $callback_return;
                else return array($callback_return);
            },
            'if_array' => function($attr) {
                $attrs = array();
                foreach ($attr as $key => $value) {
                    if (is_int($key)) $attrs[] = $value;
                    else if (is_bool($value)) $attrs[] = $key;
                    else $attrs[] = $key.'="'.$value.'"';
                }

                return $attrs;
            },
            'if_other' => function($attr) {
                return array($attr);
            }
        ));

        $class = Helper::getValue($properties->class, array(
            'if_closure' => function($class) { return Helper::runCallback($class, array($this)); },
            'if_array' => function($class) {
                return implode(' ', $class);
            }
        ));

        $classes = array();

        // custom class
        if ($class) $classes[] = $class;

        // disabled
        $disabled = $properties->options['disabled'] ? 'disabled' : '';
        $classes[] = $disabled;

        $class_htm = $classes ? ' '.implode(' ', $classes) : '';

        $id = $properties->id ?: 'input-'.str_replace('_', '-', $properties->name);
        $attr[] = 'id="'.$id.'"';

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
                <label class="form-label" for="'.$id.'">'.$properties->label.'</label>
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