<?php

namespace Bootstrap\Components;
use \Bootstrap\Helper;
use \Common\Util;

class Alert extends \Bootstrap\Component {
	private $_options_map = [
        'dismissible' => true
    ];

    // optional
    public function __construct($content, $type = 'info', $options = []) {
        parent::__construct([
            'type' => $type,
            'content' => $content,
            'options' => Util::setValues($this->_options_map, $options),
            'class' => ''
        ]);
    }

    // required
    public function printHtml($return = false) {
    	$properties = $this->_properties;

    	$type = Helper::getValue($properties->type, array(
            'if_closure' => function($type) { return Helper::runCallback($type, array($this)); },
            'if_array' => function($type) {
                parent::err('Bootstrap::Alert::type requires string.');
                return '';
            }
        ));

        $content = Helper::getValue($properties->content, array(
            'if_closure' => function($content) { return Helper::runCallback($content, array($this)); },
            'if_array' => function($content) {
                parent::err('Bootstrap::Alert::content requires string.');
                return '';
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

        $dismissible = $properties->options['dismissible'];
        if ($dismissible) $classes[] = 'alert-dismissible fade show';

        $result = '
        	<div class="alert alert-'.$type.' '.$classes.'">
				'.($dismissible ? '<button type="button" class="close" data-dismiss="alert">×</button>' : '').'
				'.$content.'
			</div>';

    	if ($return) return $result;
        else echo $result;
    }
}