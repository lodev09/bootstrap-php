<?php

namespace Bootstrap\Components;
use \Bootstrap\Helper;
use \Common\Util;

class Component {
    protected $_properties;

    public function __construct($properties) {
        $this->_properties = Util::toObject($properties);
    }

    public function __get($name) {
        if (isset($this->_properties->{$name})) {
            return $this->_properties->{$name};
        }

        throw new \Exception('Undefined property: '.$name);
    }

    public function __set($name, $value) {
        if (isset($this->_properties->{$name})) {
            $this->_properties->{$name} = $value;
            return;
        }

        throw new \Exception('Undefined property: '.$name);
    }

    public function __call($name, $args) {
        if (property_exists($this->_properties, $name)) {
            if (!$args) return $this->_properties->{$name};

            $value = null;
            $key = null;
            if (count($args) > 1 && is_array($this->_properties->{$name})) {
                $key = $args[0];
                $value = $args[1];
                if (!is_string($key) && !is_int($key)) {
                    throw new \Exception('UI property: '.$name.' must be string or int.');
                }

                $this->_properties->{$name}[$key] = $value;

                if (isset($args[2]) && Helper::isClosure($args[2])) {
                    //process callback
                    $callback = $args[2];
                    Helper::runCallback($callback, [$this]);
                }

                return $this;
            } else {
                if (isset($args[1]) && Helper::isClosure($args[1])) {
                    $value = $args[0];
                    $this->_properties->{$name} = $value;
                    $callback = $args[1];
                    Helper::runCallback($callback, [$this]);
                    return $this;
                } else if (is_array($this->_properties->{$name}) && (is_string($args[0]) || is_int($args[0]))) {
                    $key = $args[0];
                    if (!is_string($key) && !is_int($key)) {
                        throw new \Exception('UI property key: '.$key.' must be string or int.');
                    }
                    return $this->_properties->{$name}[$key];
                } else {
                    $value = $args[0];
                    $this->_properties->{$name} = $value;
                    return $this;
                }
            }
        }

        throw new \Exception('Undefined property: '.$name);
    }

    protected function getPropValue($prop, $methods = null) {
        if (!$methods) {
            $methods = [
                'if_closure' => function($prop) {
                    return Helper::runCallback($prop, [$this]);
                },
                'if_array' => function($prop) {
                    throw new \Exception(get_called_class().':property requires string');
                }
            ];
        }

        return Helper::getValue($prop, $methods);
    }

    protected function getPropValueAttr($prop, $data = null) {
        return Helper::getValue($prop, [
            'if_closure' => function($attr) {
                $callback_return = Helper::runCallback($attr, [$this]);
                if (is_array($callback_return)) return $callback_return;
                else return [$callback_return];
            },
            'if_array' => function($attr) use ($data) {
                $attrs = Helper::attrs($attr, $data);
                return [$attrs];
            },
            'if_other' => function($attr) {
                return [$attr];
            }
        ]);
    }

    public static function printSelect($items, $options = [], $return = false) {
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

        $options = Util::setValues($defaults, $options, 'class');

        if (empty($options['display']) || empty($options['value'])) {
            throw new \Exception('display and value are required');
        }

        $has_selected = false;

        if ($items) {

            foreach ($items as $item) {
                $content = '';
                $value = '';
                if (is_string($item)) {
                    $content = $item;
                } else if (is_array($item)) {
                    $content = isset($item[$options['display']]) ? $item[$options['display']] : '';
                    $value = isset($item[$options['value']]) ? $item[$options['value']] : '';
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
                    if (Helper::isClosure($options['each'])) {
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

                $props = Helper::getProps($default_props, $item, [$item], 'content');

                $content = $props['content'];

                $attrs = [];
                if ($props['class']) $attrs[] = 'class="'.(is_array($props['class']) ? implode(' ', $props['class']) : $props['class']).'"';
                if ($options['selected'] == $value) {
                    $attrs[] = 'selected';
                    $has_selected = true;
                }

                if ($props['attr']) {
                    $attrs[] = Helper::attrs($props['attr'], !is_string($item) ? $item : null);
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
            $main_attrs[] = Helper::attrs($options['attr']);
        }

        if ($options['id']) $main_attrs[] = 'id="'.$options['id'].'"';
        if ($options['name']) $main_attrs[] = 'name="'.$options['name'].'"';

        $result = '
            <'.$options['type'].($main_attrs ? ' '.implode(' ', $main_attrs) : '').'>
                '.($options['default'] ? '<option value="" disabled '.(!$has_selected ? 'selected' : '').'>'.$options['default'].'</option>' : '').'
                '.$items_html.'
            </'.$options['type'].'>
        ';

        if ($return) return $result;
        else echo $result;
    }

    public static function printList($items, $options = [], $return = false) {
        $items_html = '';
        $main_attrs = [];

        $defaults = [
            'type' => 'ul',
            'attr' => [],
            'class' => [],
            'id' => ''
        ];

        $options = Util::setValues($defaults, $options, 'class');

        foreach ($items as $item) {
            $item_html = '';
            $item_prop = [
                'content' => '',
                'subitems' => [],
                'class' => '',
                'attr' => []
            ];

            $new_item_prop = Helper::getProps($item_prop, $item, [$item], 'content');

            $content = $new_item_prop['content'];

            if ($new_item_prop['subitems']) {
                $content .= self::printList($new_item_prop['subitems'], false, true);
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

    public static function printDropdown($items, $options = [], $return = false) {
        $items_html = '';

        $main_attrs = [];

        $defaults = [
            'type' => 'ul',
            'attr' => [],
            'class' => '',
            'id' => '',
            'multilevel' => false
        ];

        $options = Util::setValues($defaults, $options, 'class');

        foreach ($items as $item) {
            $item_html = '';
            $item_prop = [
                'content' => '',
                'submenu' => [],
                'class' => [],
                'attr' => []
            ];

            $new_item_prop = Helper::getValue($item, [
                'if_array' => function($item) use ($item_prop) {
                    return Util::setValues($item_prop, $item, 'content');
                },
                'if_closure' => function($item) use ($item_prop) {
                    return Helper::setClosureDefaults($item_prop, $item);
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
                $content .= self::printDropdown($new_item_prop['submenu'], null, true);
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