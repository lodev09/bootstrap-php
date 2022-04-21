<?php
/*
 * File: Card.php
 * Project: Components
 * Created Date: Fr Apr 2022
 * Author: Ayatulloh Ahad R
 * Email: ayatulloh@indiega.net
 * Phone: 085791555506
 * -------------------------
 * Last Modified: Fri Apr 22 2022
 * Modified By: Ayatulloh Ahad R
 * -------------------------
 * Copyright (c) 2022 Indiega Network 

 * -------------------------
 * HISTORY:
 * Date      	By	Comments 

 * ----------	---	---------------------------------------------------------
 * INDONESIA :
 * menambahkan fitur card untuk Component Bootstrap.
 * 
 * ENGLISH:
 * added card feature for Bootstrap Component.
 */


namespace Bootstrap\Components;

use Common\Util;

class Card extends Component
{

    private $_options_map = [
        'footer'        => false,
        'niceTitle'     => false,
        'card_class'    => false,
        'header_class'  => false,
    ];


    /**
     * __construct
     *
     * @param mixed $content
     * @param $title = 'string'
     * @param array $options
     * 
     * @author Ayatulloh Ahad R <ayatulloh@indiega.net>
     */
    public function __construct($content, $title = 'Card Title', $options = [])
    {
        parent::__construct([
            'type' => 'info',
            'title' => $title,
            'content' => $content,
            'options' => Util::setValues($this->_options_map, $options),
        ]);
    }

    /**
     * printHtml
     *
     * @param bool $return
     * 
     * @return mixed
     * @author Ayatulloh Ahad R <ayatulloh@indiega.net>
     */
    public function printHtml($return = false)
    {
        $properties = $this->_properties;

        $type       = $this->getPropValue($properties->type);
        $content    = $this->getPropValue($properties->content);
        $title      = $this->getPropValue($properties->title);
        $class      = $properties->options['card_class'];
        $headerClass      = $properties->options['header_class'];

        #make a nice title
        if ($properties->options['niceTitle']) $title = ucwords(strtolower($title));

        # custom class
        $classes = [];
        $headerClasses = '';
        if ($class) $classes[] = $class;
        if ($headerClass) $headerClasses = $headerClass;

        # make footer card if available
        $footer = $properties->options['footer'];

        $result = '<div class="card ' . implode(' ', $classes) . '">';
        if (!empty($title)) $result .= '<div class="card-header ' . $headerClasses . '">
                <h4 class="card-title">' . $title . '</h4>
            </div>';
        $result .= '<div class="card-body">
                ' . $content . '
            </div>';
        if ($footer) $result .= '<div class="card-footer text-muted">
                ' . $footer . '
            </div>';
        $result .= '</div>';

        if ($return) return $result;
        else echo $result;
    }
}
