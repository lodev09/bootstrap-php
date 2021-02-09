<?php

namespace Bootstrap\Components;
use \Bootstrap\Helper;
use \Common\Util;

class Select extends Component {
    const INPUT_TYPE_TEXT = 'text';
    const INPUT_TYPE_PASSWORD = 'password';

    private $_options_map = [
        'disabled' => false,
        'grouped' => true,
        'group_class' => '',
        'required' => false
    ];

    // optional
    public function __construct($data, $name, $value_field, $display_field = null, $options = []) {
        parent::__construct([
            'data' => $data,
            'name' => $name,
            'options' => Util::setValues($this->_options_map, $options),
            'value' => $value_field,
            'display' => $display_field ?: $value_field,
            'id' => '',
            'class' => '',
            'attr' => [],
            'label' => '',
            'placeholder' => '',
            'help' => null,
            'append' => null,
            'each' => null,
            'selected' => null,
            'default' => null
        ]);
    }

    public function printHtml($return = false) {
        $properties = $this->_properties;
        $value = $this->getPropValue($properties->value);
        $label = $this->getPropValue($properties->label);
        $attr = $this->getPropValueAttr($properties->attr);
        $class = $this->getPropValue($properties->class);
        $value_field = $this->getPropValue($properties->value);
        $display_field = $this->getPropValue($properties->display);
        $selected = $this->getPropValue($properties->selected);
        $default = $this->getPropValue($properties->default);
        $each = $properties->each;

        if (empty($value_field)) {
            throw new \Exception('value property is required');
        }

        $classes = array();

        // custom class
        if ($class) $classes[] = $class;

        // disabled
        $disabled = $properties->options['disabled'] ? 'disabled' : '';
        $classes[] = $disabled;

        $class_htm = $classes ? ' '.implode(' ', $classes) : '';

        $id = $properties->id ?: 'select-'.str_replace('_', '-', $properties->name).'-'.Util::token();
        $attr[] = 'id="'.$id.'"';

        if ($properties->placeholder) $attr[] = 'placeholder="'.$properties->placeholder.'"';

        $has_selected = false;
        $options_html = $this->getPropValue($properties->data, [
            'if_array' => function($data) use (&$has_selected, $each, $selected, $value_field, $display_field) {
                $options_html = '';

                foreach ($data as $row) {
                    $content = '';
                    $value = '';
                    if (is_string($row)) {
                        $content = $row;
                    } else {
                        // direct from row field or parsed value
                        $content = preg_match('/{{.+}}/', $display_field) ? Helper::parseValue($display_field, $row) : get($display_field, $row);
                        $value = preg_match('/{{.+}}/', $value_field) ? Helper::parseValue($value_field, $row) : get($value_field, $row);
                    }

                    $default_props = [
                        'content' => $content,
                        'class' => '',
                        'attr' => ['value' => $value]
                    ];

                    if ($each) {
                        if (Helper::isClosure($each)) {
                            $each_options = $each($row);
                        } else {
                            $each_options = $each;
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

                    $props = Helper::getProps($default_props, $row, [$row], 'content');

                    $content = $props['content'];

                    $attrs = [];
                    if ($props['class']) $attrs[] = 'class="'.(is_array($props['class']) ? implode(' ', $props['class']) : $props['class']).'"';
                    if ($selected == $value) {
                        $attrs[] = 'selected';
                        $has_selected = true;
                    }

                    if ($props['attr']) {
                        $attrs[] = Helper::attrs($props['attr'], !is_string($row) ? $row : null);
                    }

                    $item_html = '
                        <option'.($attrs ? ' '.implode(' ', $attrs) : '').'>
                            '.$content.'
                        </option>';

                    $options_html .= $item_html;
                }

                return $options_html;
            }
        ]);

        $default_option_html = '';
        if ($default) {
            $default_option_html = '<option value="" disabled '.(!$has_selected ? 'selected' : '').'>'.$default.'</option>';
        }

        $result = '<select
            name="'.escape($properties->name).'"
            class="form-control '.$class_htm.'"
            '.implode(' ', $attr).'
            '.($properties->options['required'] ? 'required' : null).'
        >
            '.$default_option_html.'
            '.$options_html.'
        </select>';

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