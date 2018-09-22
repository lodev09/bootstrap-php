<?php

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

	private $_structure = [
		'options' => [],
		'content' => '',
		'icon' => '',
		'type' => '',
		'container' => self::BUTTON_CONTAINER_BUTTON,
		'size' => self::BUTTON_SIZE_MEDIUM,
		'attr' => [],
		'id' => '',
		'class' => '',
		'dropdown' => []
	];

	public function __construct($content = '', $type = 'default', $options = []) {
		$this->_init_structure($type, $content, $options);
	}

	private function _init_structure($type, $content, $user_options) {
		$this->_structure = Util::to_object($this->_structure);
		$this->_structure->type = $type;
		$this->_structure->content = $content;
		$this->_structure->options = Util::set_values($this->_options_map, $user_options);

	}

	public function __get($name) {
		if (isset($this->_structure->{$name})) {
            return $this->_structure->{$name};
        }
        parent::err('Undefined structure property: '.$name);
        return null;
	}

	public function __set($name, $value) {
		if (isset($this->_structure->{$name})) {
            $this->_structure->{$name} = $value;
            return;
        }
		parent::err('Undefined structure property: '.$name);
	}

	public function __call($name, $args) {
		return parent::_call($this, $this->_structure, $name, $args);
	}

	public function print_html($return = false) {

		$that = $this;
		$structure = $this->_structure;

		$icon = Util::get_value($structure->icon, array(
			'if_closure' => function($icon) use ($that) { return Util::run_callback($icon, array($that)); },
			'if_array' => function($icon) {
				parent::err('Bootstrap::Button::icon requires string.');
				return '';
			}
		));

		$container = Util::get_value($structure->container, array(
			'if_closure' => function($container) use ($that) { return Util::run_callback($container, array($that)); },
			'if_array' => function($container) {
				parent::err('Bootstrap::Button::container requires string.');
				return '';
			}
		));

		$content = Util::get_value($structure->content, array(
			'if_closure' => function($content) use ($that) { return Util::run_callback($content, array($that)); },
			'if_array' => function($content) {
				parent::err('Bootstrap::Button::content requires string.');
				return '';
			}
		));

		$attr = Util::get_value($structure->attr, array(
			'if_closure' => function($attr) use ($that) {
				$callback_return = Util::run_callback($attr, $array($that));
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

		$class = Util::get_value($structure->class, array(
			'if_closure' => function($class) use ($that) { return Util::run_callback($class, array($that)); },
			'if_array' => function($class) {
				return implode(' ', $class);
			}
		));

		$type = Util::get_value($structure->type, array(
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
		if ($structure->size) {
			$size_class = 'btn-'.$structure->size;
			$classes[] = $size_class;
		}
		// disabled
		$disabled = $structure->options['disabled'] ? 'disabled' : '';
		$classes[] = $disabled;

		$class_htm = $classes ? ' '.implode(' ', $classes) : '';

		$result = '<'.$container.' class="btn btn-'.$type.$class_htm.'" '.implode(' ', $attr).'>';
		$result .= $content;
		$result .= '</'.$container.'>';

		if ($structure->id) $attr[] = 'id="'.$structure->id.'"';

		if ($return) return $result;
		else echo $result;
	}
}

?>