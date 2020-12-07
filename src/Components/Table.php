<?php

namespace Bootstrap\Components;
use \Bootstrap\Helper;
use \Common\Util;

class Table extends Component {

    private $_options_map = array(
        'row_details' => false,
        'row_details_opened' => false,
        'row_detail_icons' => array('closed' => 'chevron-right', 'opened' => 'chevron-down'),
        'checkboxes' => false,
        'columns' => true,
        'cell_class' => null,
        'thead_class' => 'thead-light',
        'table' => true,
        'inverse' => false,
        'striped' => true,
        'light' => false,
        'dark' => false,
        'small' => false,
        'responsive' => false,
        'bordered' => true,
        'hover' => false,
        'placeholder' => 'No data available',
        'thead' => true
    );

    private $_uid = '';

    private $_data = [];

    private $_cells_map = [];
    private $_cols_map = [];
    private $_hide_map = [];
    private $_col_list = [];

    public function __construct($data, $options = []) {
        $id = Util::token();
        $this->_uid = $id;

        $options = Util::setValues($this->_options_map, $options);
        if (!$data) {
            $this->_col_list = $options['placeholder'] ? array($options['placeholder']) : [];
        } else {
            $this->_col_list = array_keys(is_object($data[0]) ? get_object_vars($data[0]) : $data[0]);
        }

        $cols = array_combine($this->_col_list, $this->_col_list);
        $cells = array_fill_keys($this->_col_list, []);
        $hide = array_fill_keys($this->_col_list, false);

        $this->_cells_map = $cells;
        $this->_cols_map = $cols;
        $this->_hide_map = $hide;

        parent::__construct([
            'cell' => $cells,
            'col' => $cols,
            'options' => $options,
            'data' => $data,
            'each' => [],
            'id' => $id,
            'row' => $data ? array_fill(1, count($data) + 1, []) : [],
            'hidden' => [],
            'visible' => [],
            'hide' => $hide,
            'sort' => [],
            'class' => [],
            'attr' => []
        ]);
    }

    public function rows() {
        return count($this->_properties->row);
    }

    public function is_with_details() {
        return isset($this->_properties->options['row_details']) && $this->_properties->options['row_details'];
    }

    public function is_with_checkboxes() {
        return isset($this->_properties->options['checkboxes']) && $this->_properties->options['checkboxes'];
    }

    public function printHtml($return = false) {
        $properties = $this->_properties;
        $col_names = array_keys($properties->col);

        $rows = $this->getPropValue($properties->data, array(
            'if_array' => function($data) use ($properties, $col_names) {
                $html_rows = [];

                foreach ($data as $row_index => $row_data) {

                    $row_prop = array(
                        'hidden' => false,
                        'checkbox' => [],
                        'detail' => true,
                        'class' => '',
                        'attr' => [],
                        'content' => true
                    );

                    if (Helper::isClosure($properties->row)) {
                        $row_prop = Helper::setClosureDefaults($row_prop, $properties->row, array($row_data, $row_index), 'class');
                    } else {
                        if (!is_int(key($properties->row))) {
                            $row_prop = Helper::getProps($row_prop, $properties->row, array($row_data, $row_index), 'class');
                        } else {
                            if (isset($properties->each['row']) && $properties->each['row']) {
                                $properties->row[$row_index + 1] = Helper::setClosureDefaults($row_prop, $properties->each['row'], array($row_data, $row_index), 'class');
                            }

                            if (isset($properties->row[$row_index + 1])) {
                                $row_prop_value = $properties->row[$row_index + 1];
                                if ($row_prop_value === false) {
                                    $row_prop['hidden'] = true;
                                } else if ($row_prop_value === '') {
                                    $row_prop['content'] = '';
                                } else {
                                    $row_prop = Helper::getProps($row_prop, $row_prop_value, array($row_data, $row_index), 'class');
                                }
                            }
                        }
                    }

                    $rows_html = '';
                    foreach ($col_names as $col_name) {
                        $cell_classes = [];
                        $cell_attrs = '';
                        if ((isset($properties->hide[$col_name]) && $properties->hide[$col_name] === true) || in_array($col_name, $properties->hidden)) {
                            $cell_classes[] = 'd-none';
                        }

                        if ($properties->options['cell_class']) {
                            $cell_classes[] = $properties->options['cell_class'];
                        }

                        if (isset($row_prop['content']) && !$row_prop['content']) {
                            $rows_html .= '<td class="'.implode(' ', $cell_classes).'"></td>';
                            continue;
                        }

                        if (is_array($row_data)) {
                            $cell_value = isset($row_data[$col_name]) ? $row_data[$col_name] : null;
                        } else if (is_object($row_data)) {
                            $cell_value = isset($row_data->{$col_name}) ? $row_data->{$col_name} : null;
                        } else {
                            $cell_value = null;
                        }

                        $cell_html = $cell_value;

                        if (isset($properties->cell[$col_name]) && $properties->cell[$col_name]) {
                            $cell_prop = $properties->cell[$col_name];
                            $cell_html = Helper::getValue($cell_prop, array(
                                'if_closure' => function($prop) use ($row_data, $row_index, $cell_value) {
                                    return Helper::parseValue(Helper::runCallback($prop, array($row_data, $cell_value, $row_index)), $row_data);
                                },
                                'if_array' => function($cell_prop) use ($row_data, $row_index, $cell_value, &$cell_classes, &$cell_attrs) {
                                    //icon, content, color, url[href, title, tooltip, attr]
                                    $cell_html = $cell_value;

                                    //content
                                    if (isset($cell_prop['content'])) {
                                        $cell_html = Helper::getValue($cell_prop['content'], array(
                                            'if_closure' => function($content) use ($row_data, $row_index, $cell_value) {
                                                $content_value = Helper::parseValue(Helper::runCallback($content, array($row_data, $cell_value, $row_index)), $row_data);

                                                return $content_value;

                                            },
                                            'if_other' => function($content) use ($row_data, $cell_html) {
                                                $cell_html = Helper::parseValue($content, $row_data);
                                                return $cell_html;
                                            }
                                        ));
                                    }

                                    //url
                                    if (isset($cell_prop['url'])) {
                                        $map_url_prop = array(
                                            'href' => '#',
                                            'target' => '_self',
                                            'title' => '',
                                            'attr' => ''
                                        );

                                        $map_url_prop = Helper::getValue($cell_prop['url'], array(
                                            'if_closure' => function($prop) use ($row_data, $row_index, $cell_value, $map_url_prop) {
                                                $url = Helper::runCallback($prop, array($row_data, $cell_value, $row_index));
                                                $map_url_prop['href'] = $url;

                                                return $map_url_prop;
                                            },
                                            'if_array' => function($url_prop) use ($row_data, $cell_html, $map_url_prop) {
                                                $map_url_prop['target'] = isset($url_prop['target']) ? $url_prop['target'] : '_self';
                                                $map_url_prop['href'] = isset($url_prop['href']) ? Helper::parseValue($url_prop['href'], $row_data, true) : '#';
                                                $map_url_prop['attr'] = isset($url_prop['attr']) && $url_prop['attr'] ? $url_prop['attr'] : '';
                                                $map_url_prop['title'] = isset($url_prop['title']) ? Helper::parseValue($url_prop['title'], $row_data, true) : '';
                                                return $map_url_prop;

                                            },
                                            'if_other' => function($url_prop) use ($row_data, $cell_html, $map_url_prop) {
                                                $map_url_prop['href'] = Helper::parseValue($url_prop, Util::toArray($row_data), true);
                                                return $map_url_prop;
                                            }
                                        ));

                                        $cell_html = '<a href="'.$map_url_prop['href'].'" target="'.$map_url_prop['target'].'" '.$map_url_prop['attr'].' title="'.$map_url_prop['title'].'">'.$cell_html.'</a>';
                                    }

                                    //icon
                                    if (isset($cell_prop['icon'])) {
                                        $cell_html = Helper::getValue($cell_prop['icon'], array(
                                            'if_closure' => function($icon) use ($row_data, $row_index, $cell_value, $cell_html) {
                                                $icon_value = Helper::runCallback($prop, array($row_data, $cell_value, $row_index));
                                                return '<i class="'.parent::$icon_source.' '.$icon_value.'"></i> '.$cell_html;
                                            },
                                            'if_other' => function($icon) use ($cell_html) {
                                                return '<i class="'.parent::$icon_source.' '.$icon.'"></i> '.$cell_html;
                                            }
                                        ));
                                    }

                                    //color
                                    if (isset($cell_prop['color'])) {
                                        $cell_html = Helper::getValue($cell_prop['color'], array(
                                            'if_closure' => function($color) use ($row_data, $row_index, $cell_value, $cell_html) {
                                                $color_value = Helper::runCallback($color, array($row_data, $cell_value, $row_index));
                                                return '<span class="'.$color_value.'">'.$cell_html.'</span>';
                                            },
                                            'if_other' => function($color) use ($cell_html) {
                                                return '<span class="'.$color.'">'.$cell_html.'</span>';
                                            }
                                        ));
                                    }

                                    //class
                                    if (isset($cell_prop['class'])) {
                                         if (is_array($cell_prop['class'])) {
                                            $cell_classes = array_merge($cell_classes, $cell_prop['class']);
                                         } else $cell_classes[] = $cell_prop['class'];
                                    }

                                    if (isset($cell_prop['attr'])) {
                                        $cell_attrs = Helper::attrs($cell_prop['attr'], $row_data);
                                    }

                                    //callback
                                    if (isset($cell_prop['callback']) && Helper::isClosure($cell_prop['callback'])) {
                                        $new_cell_html = Helper::runCallback($cell_prop['callback'], array($row_data, $cell_html, $row_index));
                                        if (trim($new_cell_html) != '') {
                                            $cell_html = $new_cell_html;
                                        }
                                    }

                                    return $cell_html;
                                },
                                'if_other' => function($cell_prop) use ($row_data) {
                                    return Helper::parseValue($cell_prop, $row_data);
                                }
                            ));
                        }

                        $rows_html .= '<td'.($cell_classes ? ' class="'.implode(' ', $cell_classes).'"' : '').' '.$cell_attrs.'> '.$cell_html.' </td>';
                    }

                    // construct custom columns
                    $row_classes = [];
                    if ($row_prop['class']) $row_classes[] = $row_prop['class'];
                    if ($row_prop['hidden'] === true) $row_classes[] = 'd-none';

                    $attr = Helper::attrs($row_prop['attr'], $row_data);
                    $row_class = $row_classes ? ' class="'.implode(' ', $row_classes).'"' : '';

                    $row_checkbox = '';
                    $row_details = '';

                    if (isset($properties->options['checkboxes']) && $properties->options['checkboxes']) {

                        if ($row_prop['checkbox'] === false)
                            $checkbox_content = '';
                        else {
                            // global checkboxes configuration
                            $checkbox_options = $properties->options['checkboxes'];
                            $checkbox_options = array_merge(is_array($checkbox_options) ? $checkbox_options : [], $row_prop['checkbox'] ? : []);

                            $checkbox_prop = array(
                                'name' => $properties->id.'_checkbox[]',
                                'id' => '',
                                'checked' => false,
                                'disabled' => false,
                                'value' => 1,
                                'attr' => [],
                                'class' => ''
                            );

                            $checkbox_prop = Helper::getProps($checkbox_prop, $checkbox_options, array($this, $row_data, $row_index), 'name');

                            $value = '';

                            if (Helper::isClosure($checkbox_prop['value']))
                                $value = Helper::runCallback($checkbox_prop['value'], array($row_data));
                            else $value = $checkbox_prop['value'];

                            $value = Helper::parseValue($value, $row_data);

                            $checkbox_attrs = [];
                            if ($checkbox_prop['checked']) $checkbox_attrs[] = 'checked';
                            if ($checkbox_prop['id']) $checkbox_attrs[] = 'id="'.$checkbox_prop['id'].'"';
                            if ($checkbox_prop['disabled']) $checkbox_attrs[] = 'disabled';
                            if ($checkbox_prop['attr']) $checkbox_attrs[] = Helper::attrs($checkbox_prop['attr'], $row_data);

                            $checkbox_attrs[] = 'class="form-check-input '.$checkbox_prop['class'].'"';
                            $checkbox_attrs[] = 'value="'.$value.'"';
                            $checkbox_attrs[] = 'name="'.$checkbox_prop['name'].'"';

                            $input = '<input type="checkbox" '.implode(' ', $checkbox_attrs).'>';

                            $checkbox_content = '
                                <label class="form-check m-0">
                                    '.$input.'
                                </label>';

                        }

                        $row_checkbox = '<td style="max-width: 10px;"> '.$checkbox_content.' </td>';
                    }

                    if (isset($properties->options['row_details']) && $properties->options['row_details']) {
                        $option = $properties->options['row_details'];

                        $detail_prop = array(
                            'id' => '',
                            'icon' => parent::$icon_source."-".$this->options['row_detail_icons']['closed'],
                            'title' => 'Show Details'
                        );

                        $new_detail_prop = Helper::getProps($detail_prop, $option, array($this, $row_data, $row_index), 'icon');
                        $id = $new_detail_prop['id'] ? 'id="'.$new_detail_prop['id'].'"' : '';

                        $content = '<a href="#" '.$id.'>
                                    <i class="'.parent::$icon_source.' '.$detail_prop['icon'].' '.parent::$icon_source.'" data-toggle="row-detail" title="'.$detail_prop['title'].'"></i>
                                </a>';
                        if ($row_prop['detail'] === false)
                            $content = '';

                        $row_details =
                            '<td width="20px"> '.$content.' </td>';
                    }

                    $html_rows[] = '<tr '.$row_class.$attr.'>'.$row_details.$row_checkbox.$rows_html.'</tr>';
                }
                return implode('', $html_rows);
            },
            'if_closure' => function($data) {
                parent::err('Table requires an array of objects/array');
                return '';
            },
            'if_other' => function($data) {
                parent::err('Table requires an array of objects/array');
                return '';
            }
        ));

        if ($properties->options['columns']) {
            $cols = Helper::getValue($properties->col, array(
                'if_array' => function($cols) use ($properties) {
                    $html_col_list = [];

                    foreach ($cols as $col_name => $col_value) {

                        if (is_null($col_value) || $col_value === false) continue;;
                        $col_value_prop = array(
                            'title' => $col_name,
                            'class' => '',
                            'attr' => [],
                            'icon' => '',
                            'hidden' => (isset($properties->hide[$col_name]) && $properties->hide[$col_name] === true) || in_array($col_name, $properties->hidden)
                        );

                        $new_col_value = Helper::getProps($col_value_prop, $col_value, array($this, $cols), 'title');
                        $col_attrs = Helper::attrs($new_col_value['attr']);

                        $classes = [];
                        if ($new_col_value['class'])
                            $classes[] = $new_col_value['class'];

                        if ($new_col_value['hidden'] === true)
                            $classes[] = "d-none";

                        $class = $classes ? 'class="'.implode(' ', $classes).'"' : '';

                        $htm_attrs = trim($class.' '.$col_attrs);
                        $html_col_list[] = '<th '.$htm_attrs.'>'.$new_col_value['icon'].' '.$new_col_value['title'].' </th>';
                    }


                    $html_cols = implode('', $html_col_list);

                    $checkbox_header = '';
                    $detail_header = '';

                    if (isset($properties->options['checkboxes']) && ($properties->options['checkboxes'])) {
                        $checkbox_header = '
                            <th style="max-width: 10px;" data-sortable="false">
                                <label class="form-check m-0">
                                    <input class="form-check-input" type="checkbox" value="">
                                </label>
                            </th>';
                    }

                    if (isset($properties->options['row_details']) && ($properties->options['row_details'])) {
                        $detail_header = '
                            <th width="20px"></th>';
                    }

                    return '<tr>'.$detail_header.$checkbox_header.$html_cols.'</tr>';
                }
            ));
        } else $cols = '';

        $classes = [];
        if ($properties->options['table']) $classes[] = 'table';
        if ($properties->options['inverse']) $classes[] = 'table-inverse';
        if ($properties->options['striped']) $classes[] = 'table-striped';
        if ($properties->options['bordered']) $classes[] = 'table-bordered';
        if ($properties->options['hover']) $classes[] = 'table-hover';
        if ($properties->options['responsive']) $classes[] = 'table-responsive'.(is_string($properties->options['responsive']) ? '-'.$properties->options['responsive'] : '');
        if ($properties->options['light']) $classes[] = 'table-light';
        if ($properties->options['dark']) $classes[] = 'table-dark';
        if ($properties->options['small']) $classes[] = 'table-sm';

        if ($properties->class) {
            $classes[] = is_array($properties->class) ? implode(' ', $properties->class) : $properties->class;
        }

        $attrs = '';
        if ($properties->attr) {
            if (isset($properties->attr['class'])) {
                $classes[] = $properties->attr['class'];
                unset($properties->attr['class']);
            }

            if (isset($properties->attr['id']) && $properties->id) {
                unset($properties->attr['id']);
            }

            $attrs = $properties->attr ? Helper::attrs($properties->attr) : '';
        }

        $table_html = '<table id="'.$properties->id.'" class="'.implode(' ', $classes).'" '.$attrs.'>';
        if ($properties->options['thead']) {
            $table_html .= '<thead class="'.$properties->options['thead_class'].'">'.$cols.'</thead>';
            $table_html .= '<tbody>'.$rows.'</tbody>';
        } else {
            $table_html .= '<tbody>'.$cols.$rows.'</tbody>';
        }

        $table_html .= '</table>';

        $result = $table_html;

        if ($return) return $result;
        else echo $result;

    }
}