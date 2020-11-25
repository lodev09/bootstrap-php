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

        $type = $this->getPropValue($properties->type);
        $content = $this->getPropValue($properties->content);
        $class = $this->getPropValue($properties->class);

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