<?php

namespace Bootstrap;

class Component {

    private static $_ui_calls = ['create', 'set', 'get', 'print'];
    private $_track_start_time;

    private static $_uis = [];
    private static $_alerts = ['info', 'danger', 'primary', 'default', 'warning', 'success'];
    public static $debug = true;

    public static function register($name, $class) {
        self::$_uis[$name] = $class;
    }

    protected function _call($ui_member, $structure, $name, $args) {
        if (property_exists($structure, $name)) {
            if (!$args) return $structure->{$name};

            $value = null;
            $key = null;
            if (count($args) > 1 && is_array($structure->{$name})) {
                $key = $args[0];
                $value = $args[1];
                if (!is_string($key) && !is_int($key)) {
                    self::err("UI structure property: $name must be string or int.");
                    return null;
                }

                $structure->{$name}[$key] = $value;

                if (isset($args[2]) && Util::is_closure($args[2])) {
                    //process callback
                    $callback = $args[2];
                    Util::run_callback($callback, [$ui_member]);
                }

                return $ui_member;
            } else {
                if (isset($args[1]) && Util::is_closure($args[1])) {
                    $value = $args[0];
                    $structure->{$name} = $value;
                    $callback = $args[1];
                    Util::run_callback($callback, [$ui_member]);
                    return $ui_member;
                } else if (is_array($structure->{$name}) && (is_string($args[0]) || is_int($args[0]))) {
                    $key = $args[0];
                    if (!is_string($key) && !is_int($key)) {
                        self::err("UI property key: $key must be string or int.");
                        return null;
                    }
                    return $structure->{$name}[$key];
                } else {
                    $value = $args[0];
                    $structure->{$name} = $value;
                    return $ui_member;
                }
            }
        }

        self::err('Undefined structure property: '.$name);

        return null;
    }

    public function start_track() {
        $this->_track_start_time = microtime(true);
    }

    public function __call($name, $args) {
        $calls = explode('_', $name);
        if (!in_array($calls[0], self::$_ui_calls)) {
            self::err("Undefined call: $calls[0]");
            return null;
        }

        $ui_class = strtolower($calls[1]);

        if (isset(self::$_uis[$ui_class]) && $calls[0] == 'create') {
            $reflection = new \ReflectionClass(self::$_uis[$ui_class]);
            $new_ui = $args ? $reflection->newInstanceArgs($args) : $reflection->newInstance();

            $this->start_track();
            return $new_ui;
        } else if ($calls[0] == 'print' && in_array($calls[1], self::$_alerts)) {
            $alert_args = [$args[0], $calls[1]];
            for ($i = 1; $i < count($args); $i++) {
                $alert_args[] = $args[$i];
            }
            return call_user_func_array([$this, 'print_alert'], $alert_args);
        }

        self::err("\"$ui_class\" is not a registered member of UI: Class not found");
    }

    public function run_time($print = true) {
        $time_end = microtime(true);
        $execution_time = number_format($time_end - $this->_track_start_time, 4);
        if ($print) echo $execution_time.'s';
        else return $execution_time.'s';
    }

    public static function err($message = "UI Error notice:") {
        if (self::$debug) {
            $trace = debug_backtrace();
            trigger_error($message.' in '.$trace[0]['file'].' on line '.$trace[0]['line'], E_USER_NOTICE);
        }
    }

    public static function get_progress($value, $type = '', $options = []) {
        $real_value = str_replace('%', '', $value);
        $percent_value = $real_value.'%';

        $defaults = [
            'transitional' => false,
            'class' => [],
            'attr' => [],
            'background' => '',
            'container' => 'div'
        ];

        $options = Util::set_values($defaults, $options, 'class');

        $classes = [];
        $classes[] = 'progress-bar';
        if ($type) $classes[] = 'progress-bar-'.$type;

        // add additional user classes
        if (is_array($options['class'])) {
            array_merge($classes, $options['class']);
        } else {
            $classes[] = $options['class'];
        }

        if ($options['background']) $classes[] = 'bg-color-'.$options['background'];

        $attrs = [];
        $attrs_html = [];

        if ($options['transitional'])
            $attrs['aria-valuetransitiongoal'] = $real_value;
        else
            $attrs['style'] = 'width: '.$percent_value;


        // add additional user attributes
        if (is_array($options['attr'])) {
            array_merge($attrs, $options['attr']);
        } else {
            $attrs_html[] = $options['attr'];
        }

        foreach ($attrs as $attr => $attr_value) {
            $attrs_html[] = $attr.'="'.$attr_value.'"';
        }

        return'<'.$options['container'].' class="'.implode(' ', $classes).'" '.implode(' ', $attrs_html).'></'.$options['container'].'>';
    }

    public static function print_stack_progress($progress_bars, $options = [], $return = false) {
        $defaults = [
            'tooltip' => [],
            'position' => 'left', // left, right, bottom (for vertical)
            'wide' => false,
            'size' => 'md', // sm, xs, md, xl, micro
            'striped' => false, // true or 'active'
            'vertical' => false
        ];

        $options = Util::set_values($defaults, $options, 'class');

        $container_classes = [];
        $container_classes[] = "progress";
        if ($options['vertical']) $container_classes[] = 'vertical';
        if ($options['position']) $container_classes[] = $options['position'];
        if ($options['wide']) $container_classes[] = 'wide-bar';
        if ($options['striped']) {
            $container_classes[] = 'progress-striped';
            if ($options['striped'] === 'active')
                $container_classes[] = 'active';
        }

        $container_classes[] = 'progress-'.$options['size'];

        $container_attrs = [];
        $container_attrs_html = [];

        if ($options['tooltip']) {
            $tooltip_prop =  [
                'placement' => 'top',
                'title' => ''
            ];
            $tooltip = $options['tooltip'];
            $new_tooltip_prop = Util::set_values($tooltip_prop, $tooltip, 'title');
            $container_attrs['rel'] = 'tooltip';
            $container_attrs['data-original-title'] = $new_tooltip_prop['title'];
            $container_attrs['data-placement'] = $new_tooltip_prop['placement'];
        }

        foreach ($container_attrs as $container_attr => $attr_value) {
            $container_attrs_html[] = $container_attr.'="'.$attr_value.'"';
        }

        $result = '<div class="'.implode(' ', $container_classes).'" '.implode(' ', $container_attrs_html).'>';
        $result .= implode('', $progress_bars);
        $result .= '</div>';

        if ($return) return $result;
        else echo $result;
    }

    public static function print_progress($value, $type = '', $options = [], $return = false) {
        $real_value = str_replace('%', '', $value);
        $percent_value = $real_value.'%';

        $defaults = [
            'transitional' => false,
            'class' => [],
            'attr' => [],
            'background' => '',
            'tooltip' => [],
            'position' => 'left', // left, right, bottom (for vertical)
            'wide' => false,
            'size' => 'md', // sm, xs, md, xl, micro
            'striped' => false, // true or 'active'
            'vertical' => false,
            'container' => 'div'
        ];

        $options = Util::set_values($defaults, $options, 'class');

        $container_classes = [];
        $container_classes[] = "progress";
        if ($options['vertical']) $container_classes[] = 'vertical';
        if ($options['position']) $container_classes[] = $options['position'];
        if ($options['wide']) $container_classes[] = 'wide-bar';
        if ($options['striped']) {
            $container_classes[] = 'progress-striped';
            if ($options['striped'] === 'active')
                $container_classes[] = 'active';
        }

        $container_classes[] = 'progress-'.$options['size'];

        $container_attrs = [];
        $container_attrs_html = [];

        if ($options['tooltip']) {
            $tooltip_prop =  [
                'placement' => 'top',
                'title' => $percent_value
            ];
            $tooltip = $options['tooltip'];
            $new_tooltip_prop = Util::set_values($tooltip_prop, $tooltip, 'title');
            $container_attrs['rel'] = 'tooltip';
            $container_attrs['data-original-title'] = $new_tooltip_prop['title'];
            $container_attrs['data-placement'] = $new_tooltip_prop['placement'];
        }

        foreach ($container_attrs as $container_attr => $attr_value) {
            $container_attrs_html[] = $container_attr.'="'.$attr_value.'"';
        }

        $classes = [];
        $classes[] = implode(' ', $container_classes);
        $classes[] = implode(' ', $options['class'] ? $options['class'] : []);

        $container_attrs_html[] = implode(' ', $options['attr']);

        $result = '<'.$options['container'].' class="'.implode(' ', $classes).'" '.implode(' ', $container_attrs_html).'>';
        $result .= self::get_progress($value, $type, $options);
        $result .= '</'.$options['container'].'>';

        if ($return) return $result;
        else echo $result;
    }

    public static function print_alert($message, $type = 'info', $options = [], $return = false) {
        $defaults = [
            'dismiss' => true,
            'block' => false,
            'container' => 'div',
            'class' => [],
            'fade_in' => true,
            'js_escape' => false,
            'heading' => false
        ];

        $options = Util::set_values($defaults, $options, 'heading');
        $closebutton_html = $options['dismiss'] ?
                        '<button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>' : '';

        $classes = [];
        $classes[] = 'alert';
        $classes[] = 'alert-'.$type;
        if ($options['block']) $classes[] = 'alert-block';

        $heading = $options['heading'] ? '<h4 class="alert-heading">'.$options['heading'].'</h4>' : '';
        $result = '<div class="'.implode(' ', $classes).'">
                        '.$closebutton_html.'
                        '.$heading.'
                        '.$message.'
                    </div>';

        if ($options['js_escape']) $result = preg_replace("/\s+/", " ", $result);

        if ($return) return $result;
        else echo $result;
    }

    public static function print_select($items, $options = [], $return = false) {
        $items_html = '';
        $main_attrs = [];

        $defaults = [
            'name' => null,
            'type' => 'select',
            'attr' => [],
            'class' => [],
            'display' => '',
            'value' => '',
            'default' => null,
            'selected' => null,
            'each' => null,
            'id' => null
        ];

        $options = Util::set_values($defaults, $options, 'class');

        if (empty($options['display']) || empty($options['value'])) {
            self::err('display and value are required');
            return false;
        }

        if ($items) {
            foreach ($items as $item) {
                $content = '';
                $value = '';
                if (is_string($item)) {
                    $content = $item;
                } else if (is_array($item)) {
                    $content = isset($item[$options['display']]) ? $item[$options['display']] : '';
                    $value = isset($item[$options['value']]) ? $item->{$options['value']} : '';
                } else if (is_object($item)) {
                    $content = isset($item->{$options['display']}) ? $item->{$options['display']} : '';
                    $value = isset($item->{$options['value']}) ? $item->{$options['value']} : '';
                }

                $default_props = [
                    'content' => $content,
                    'class' => '',
                    'attr' => ['value' => $value]
                ];

                if ($options['each']) {
                    if (Util::is_closure($options['each'])) {
                        $each_options = $options['each']($item);
                    } else {
                        $each_options = $options['each'];
                    }

                    $each_options = is_string($each_options) ? ['content' => $each_options] : $each_options;

                    // make attr as array
                    if (isset($each_options['attr']) && is_string($each_options['attr'])) {
                        $attr_arr = [];
                        $each_attrs = explode(' ', $each_options['attr']);
                        foreach ($each_attrs as $attr) {
                            $parts = explode('=', $attr);
                            $attr_arr[$parts[0]] = isset($parts[1]) ? trim($parts[1], '"') : '';
                        }

                        $each_options['attr'] = $attr_arr;
                    }

                    $default_props = array_merge_recursive($default_props, $each_options);
                }

                $item_html = '';

                $props = Util::get_props($default_props, $item, [$item], 'content');

                $content = $props['content'];

                $attrs = [];
                if ($props['class']) $attrs[] = 'class="'.(is_array($props['class']) ? implode(' ', $props['class']) : $props['class']).'"';
                if ($options['selected'] == $value) {
                    $attrs[] = 'selected';
                }

                if ($props['attr']) {
                    $attrs[] = Util::attrs($props['attr'], !is_string($item) ? $item : null);
                }

                $item_html = '
                    <option'.($attrs ? ' '.implode(' ', $attrs) : '').'>
                        '.$content.'
                    </option>';

                $items_html .= $item_html;
            }
        }

        if ($options['class']) $main_attrs[] = 'class="'.(is_array($options['class']) ? implode(' ', $options['class']) : $options['class']).'"';
        if ($options['attr']) {
            $main_attrs[] = Util::attrs($options['attr']);
        }

        if ($options['id']) $main_attrs[] = 'id="'.$options['id'].'"';
        if ($options['name']) $main_attrs[] = 'name="'.$options['name'].'"';

        $result = '
            <'.$options['type'].($main_attrs ? ' '.implode(' ', $main_attrs) : '').'>
                '.($options['default'] ? '<option value="">'.$options['default'].'</option>' : '').'
                '.$items_html.'
            </'.$options['type'].'>
        ';

        if ($return) return $result;
        else echo $result;
    }

    public static function print_list($items, $options = [], $return = false) {
        $items_html = '';
        $main_attrs = [];

        $defaults = [
            'type' => 'ul',
            'attr' => [],
            'class' => [],
            'id' => ''
        ];

        $options = Util::set_values($defaults, $options, 'class');

        foreach ($items as $item) {
            $item_html = '';
            $item_prop = [
                'content' => '',
                'subitems' => [],
                'class' => '',
                'attr' => []
            ];

            $new_item_prop = Util::get_props($item_prop, $item, [$item], 'content');

            $content = $new_item_prop['content'];

            if ($new_item_prop['subitems']) {
                $content .= self::print_list($new_item_prop['subitems'], false, true);
            }

            $attrs = [];
            if ($new_item_prop['class']) $attrs[] = 'class="'.(is_array($new_item_prop['class']) ? implode(' ', $new_item_prop['class']) : $new_item_prop['class']).'"';

            if ($new_item_prop['attr']) {
                foreach ($new_item_prop['attr'] as $key => $value) {
                    $attrs[] = $key.'="'.$value.'"';
                }
            }

            $item_html = '
                <li'.($attrs ? ' '.implode(' ', $attrs) : '').'>
                    '.$content.'
                </li>';

            $items_html .= $item_html;
        }

        if ($options['class']) $main_attrs[] = 'class="'.(is_array($options['class']) ? implode(' ', $options['class']) : $options['class']).'"';
        if ($options['attr']) {
            foreach ($options['attr'] as $key => $value) {
                $main_attrs[] = $key.'="'.$value.'"';
            }
        }

        if ($options['id']) $main_attrs[] = 'id="'.$options['id'].'"';

        $result = '
            <'.$options['type'].($main_attrs ? ' '.implode(' ', $main_attrs) : '').'>
                '.$items_html.'
            </'.$options['type'].'>
        ';

        if ($return) return $result;
        else echo $result;
    }

    public static function print_dropdown($items, $options = [], $return = false) {
        $items_html = '';

        $main_attrs = [];

        $defaults = [
            'type' => 'ul',
            'attr' => [],
            'class' => '',
            'id' => '',
            'multilevel' => false
        ];

        $options = Util::set_values($defaults, $options, 'class');

        foreach ($items as $item) {
            $item_html = '';
            $item_prop = [
                'content' => '',
                'submenu' => [],
                'class' => [],
                'attr' => []
            ];

            $new_item_prop = Util::get_value($item, [
                'if_array' => function($item) use ($item_prop) {
                    return Util::set_values($item_prop, $item, 'content');
                },
                'if_closure' => function($item) use ($item_prop) {
                    return Util::set_closure_defaults($item_prop, $item);
                },
                'if_other' => function($item) use ($item_prop) {
                    $item_prop['content'] = $item;
                    return $item_prop;
                }
            ]);

            $classes = [];
            if ($new_item_prop['class'])
                $classes[] = is_array($new_item_prop['class']) ? implode(' ', $new_item_prop['class']) : $new_item_prop['class'];

            $attrs = [];

            if ($new_item_prop['attr']) {
                foreach ($new_item_prop['attr'] as $key => $value) {
                    $attrs[] = $key.'="'.$value.'"';
                }
            }

            $content = $new_item_prop['content'];

            if ($new_item_prop['submenu']) {
                $content .= self::print_dropdown($new_item_prop['submenu'], null, true);
                $classes[] = 'dropdown-submenu';
            } else if ($content === '-') {
                $classes[] = 'divider';
            } else if (preg_match("/##(.*)?##/", $content, $header_matches)) {
                $classes[] = 'dropdown-header';
                $content = trim($header_matches[1]);
            }

            $attrs[] = $classes ? ' class="'.trim(implode(' ', $classes)).'"' : '';

            $item_html = '<li'.($attrs ? ' '.implode(' ', $attrs) : '').'>'.$content.'</li>';
            $items_html .= $item_html;
        }

        $classes = ['dropdown-menu'];
        if ($options['multilevel']) $classes[] = 'multi-level';
        $main_attrs[] = 'role="menu"';

        if ($options['class']) $classes[] = $options['class'];
        if ($options['attr']) {
            foreach ($options['attr'] as $key => $value) {
                $main_attrs[] = $key.'="'.$value.'"';
            }
        }

        if ($options['id']) $main_attrs[] = 'id="'.$options['id'].'"';

        $main_attrs[] = 'class="'.implode(' ', $classes).'"';

        $result = '<ul '.implode(' ', $main_attrs).'>';
        $result .= $items_html;
        $result .= '</ul>';

        if ($return) return $result;
        else echo $result;
    }
}


?>