<?php

namespace Bootstrap\Components;
use \Bootstrap\Util;

class Table extends \Bootstrap\Component {

  private $_options_map = array(
    'row_details' => false,
    'row_details_opened' => false,
    'row_detail_icons' => array('closed' => 'chevron-right', 'opened' => 'chevron-down'),
    'checkboxes' => false,
    'paginate' => true,
    'columns' => true,
    'table' => true,
    'inverse' => false,
    'striped' => true,
    'responsive' => false,
    'bordered' => true,
    'hover' => false,
    'default_col' => 'No Data'
  );

  private $_structure = array(
    'cell' => array(),
    'col' => array(),
    'options' => array(),
    'data' => array(),
    'each' => array(),
    'id' => '',
    'row' => array(),
    'hidden' => array(),
    'visible' => array(),
    'hide' => array(),
    'sort' => array(),
    'class' => array()
  );

  private $_uid = '';

  private $_data = array();

  private $_cells_map = array();
  private $_cols_map = array();
  private $_hide_map = array();
  private $_col_list = array();

  public function __construct($data, $options = array()) {
    $this->_init_structure($data, $options);
  }

  private function _init_structure($data, $user_options) {
    $this->_structure = Util::to_object($this->_structure);
    $this->_structure->data = $data;
    $this->_structure->options = Util::set_values($this->_options_map, $user_options);
    $uid = Util::create_id();
    $this->_structure->id = $uid; // set the id as default id
    $this->_uid = $uid;
    $ui = new parent();

    if (!$this->_structure->data) {
      $this->_col_list = $this->_structure->options['default_col'] ? array($this->_structure->options['default_col']) : array();
    } else {
      $this->_col_list = array_keys(is_object($data[0]) ? get_object_vars($data[0]) : $data[0]);
    }

    $cols = array_combine($this->_col_list, $this->_col_list);
    $cells = array_fill_keys($this->_col_list, array());
    $hide = array_fill_keys($this->_col_list, false);

    $this->_cells_map = $cells;
    $this->_cols_map = $cols;
    $this->_hide_map = $hide;

    $this->_structure->col = $cols;
    $this->_structure->cell = $cells;
    $this->_structure->hide = $hide;
    $this->_structure->row = array_fill(1, count($data) + 1, array());
  }

  public function rows() {
    return count($this->_structure->row);
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


  public function is_with_details() {
    return isset($this->_structure->options['row_details']) && $this->_structure->options['row_details'];
  }

  public function is_with_checkboxes() {
    return isset($this->_structure->options['checkboxes']) && $this->_structure->options['checkboxes'];
  }

  public function print_html($return = false) {
    $structure = $this->_structure;
    $col_names = array_keys($structure->col);

    $rows = Util::get_value($structure->data, array(
      'if_array' => function($data) use ($structure, $col_names) {
        $html_rows = array();

        foreach ($data as $row_index => $row_data) {

          $row_prop = array(
            'hidden' => false,
            'checkbox' => array(),
            'detail' => true,
            'class' => '',
            'attr' => array(),
            'content' => true
          );

          if (Util::is_closure($structure->row)) {
            $row_prop = Util::set_closure_defaults($row_prop, $structure->row, array($row_data, $row_index), 'class');
          } else {
            if (!is_int(key($structure->row))) {
              $row_prop = Util::get_props($row_prop, $structure->row, array($row_data, $row_index), 'class');
            } else {
              if (isset($structure->each['row']) && $structure->each['row']) {
                $structure->row[$row_index + 1] = Util::set_closure_defaults($row_prop, $structure->each['row'], array($row_data, $row_index), 'class');
              }

              if (isset($structure->row[$row_index + 1])) {
                $row_prop_value = $structure->row[$row_index + 1];
                if ($row_prop_value === false) {
                  $row_prop['hidden'] = true;
                } else if ($row_prop_value === '') {
                  $row_prop['content'] = '';
                } else {
                  $row_prop = Util::get_props($row_prop, $row_prop_value, array($row_data, $row_index), 'class');
                }
              }
            }
          }

          $rows_html = '';
          foreach ($col_names as $col_name) {
            $cell_classes = array();
            $cell_attrs = '';
            if ((isset($structure->hide[$col_name]) && $structure->hide[$col_name] === true) || in_array($col_name, $structure->hidden)) {
                    $cell_classes[] = 'd-none';
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

                  if (isset($structure->cell[$col_name]) && $structure->cell[$col_name]) {
                    $cell_prop = $structure->cell[$col_name];
                    $cell_html = Util::get_value($cell_prop, array(
                      'if_closure' => function($prop) use ($row_data, $row_index, $cell_value) {
                        return Util::parse_value(Util::run_callback($prop, array($row_data, $cell_value, $row_index)), $row_data);
                      },
                      'if_array' => function($cell_prop) use ($row_data, $row_index, $cell_value, &$cell_classes, &$cell_attrs) {
                        //icon, content, color, url[href, title, tooltip, attr]
                        $cell_html = $cell_value;

                            //content
                            if (isset($cell_prop['content'])) {
                                $cell_html = Util::get_value($cell_prop['content'], array(
                                  'if_closure' => function($content) use ($row_data, $row_index, $cell_value) {
                                    $content_value = Util::parse_value(Util::run_callback($content, array($row_data, $cell_value, $row_index)), $row_data);

                                    return $content_value;

                                  },
                                  'if_other' => function($content) use ($row_data, $cell_html) {
                                    $cell_html = Util::parse_value($content, $row_data);
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

                              $map_url_prop = Util::get_value($cell_prop['url'], array(
                                'if_closure' => function($prop) use ($row_data, $row_index, $cell_value, $map_url_prop) {
                                  $url = Util::run_callback($prop, array($row_data, $cell_value, $row_index));
                                  $map_url_prop['href'] = $url;

                                  return $map_url_prop;
                                },
                                'if_array' => function($url_prop) use ($row_data, $cell_html, $map_url_prop) {
                                  $map_url_prop['target'] = isset($url_prop['target']) ? $url_prop['target'] : '_self';
                                      $map_url_prop['href'] = isset($url_prop['href']) ? Util::parse_value($url_prop['href'], $row_data, true) : '#';
                                      $map_url_prop['attr'] = isset($url_prop['attr']) && $url_prop['attr'] ? $url_prop['attr'] : '';
                                      $map_url_prop['title'] = isset($url_prop['title']) ? Util::parse_value($url_prop['title'], $row_data, true) : '';
                                      return $map_url_prop;

                                },
                                'if_other' => function($url_prop) use ($row_data, $cell_html, $map_url_prop) {
                                  $map_url_prop['href'] = Util::parse_value($url_prop, Util::to_array($row_data), true);
                                  return $map_url_prop;
                                }
                              ));

                    $cell_html = '<a href="'.$map_url_prop['href'].'" target="'.$map_url_prop['target'].'" '.$map_url_prop['attr'].' title="'.$map_url_prop['title'].'">'.$cell_html.'</a>';
                            }

                            //icon
                            if (isset($cell_prop['icon'])) {
                                $cell_html = Util::get_value($cell_prop['icon'], array(
                                  'if_closure' => function($icon) use ($row_data, $row_index, $cell_value, $cell_html) {
                                    $icon_value = Util::run_callback($prop, array($row_data, $cell_value, $row_index));
                                    return '<i class="'.parent::$icon_source.' '.$icon_value.'"></i> '.$cell_html;
                                  },
                                  'if_other' => function($icon) use ($cell_html) {
                                    return '<i class="'.parent::$icon_source.' '.$icon.'"></i> '.$cell_html;
                                  }
                                ));
                            }

                            //color
                            if (isset($cell_prop['color'])) {
                                $cell_html = Util::get_value($cell_prop['color'], array(
                                  'if_closure' => function($color) use ($row_data, $row_index, $cell_value, $cell_html) {
                                    $color_value = Util::run_callback($color, array($row_data, $cell_value, $row_index));
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
                              $cell_attrs = Util::attrs($cell_prop['attr'], $row_data);
                            }

                            //callback
                            if (isset($cell_prop['callback']) && Util::is_closure($cell_prop['callback'])) {
                              $new_cell_html = Util::run_callback($cell_prop['callback'], array($row_data, $cell_html, $row_index));
                              if (trim($new_cell_html) != '') {
                                $cell_html = $new_cell_html;
                              }
                            }

                            return $cell_html;
                      },
                      'if_other' => function($cell_prop) use ($row_data) {
                        return Util::parse_value($cell_prop, $row_data);
                      }
                    ));
                  }

                  $rows_html .= '<td'.($cell_classes ? ' class="'.implode(' ', $cell_classes).'"' : '').' '.$cell_attrs.'> '.$cell_html.' </td>';
          }

          // construct custom columns


          $row_classes = array();
          if ($row_prop['class']) $row_classes[] = $row_prop['class'];
          if ($row_prop['hidden'] === true) $row_classes[] = 'd-none';

          $attr = Util::attrs($row_prop['attr'], $row_data);
          $row_class = $row_classes ? ' class="'.implode(' ', $row_classes).'"' : '';

          $row_checkbox = '';
          $row_details = '';

          if (isset($structure->options['checkboxes']) && $structure->options['checkboxes']) {

            if ($row_prop['checkbox'] === false)
              $checkbox_content = '';
            else {
              // global checkboxes configuration
              $checkbox_options = $structure->options['checkboxes'];
              $checkbox_options = array_merge(is_array($checkbox_options) ? $checkbox_options : array(), $row_prop['checkbox'] ? : array());

              $checkbox_prop = array(
                'name' => $structure->id.'_checkbox[]',
                'id' => '',
                'checked' => false,
                'disabled' => false,
                'value' => 1,
                'attr' => array(),
                'class' => ''
              );

              $checkbox_prop = Util::get_props($checkbox_prop, $checkbox_options, array($this, $row_data, $row_index), 'name');

              $value = '';

              if (Util::is_closure($checkbox_prop['value']))
                $value = Util::run_callback($checkbox_prop['value'], array($row_data));
              else $value = $checkbox_prop['value'];

              $value = Util::parse_value($value, $row_data);

              $checkbox_attrs = array();
              if ($checkbox_prop['checked']) $checkbox_attrs[] = 'checked';
              if ($checkbox_prop['id']) $checkbox_attrs[] = 'id="'.$checkbox_prop['id'].'"';
              if ($checkbox_prop['disabled']) $checkbox_attrs[] = 'disabled';
              if ($checkbox_prop['attr']) $checkbox_attrs[] = Util::attrs($checkbox_prop['attr'], $row_data);

              $checkbox_attrs[] = 'class="custom-control-input '.$checkbox_prop['class'].'"';
              $checkbox_attrs[] = 'value="'.$value.'"';
              $checkbox_attrs[] = 'name="'.$checkbox_prop['name'].'"';

                        $input = '<input type="checkbox" '.implode(' ', $checkbox_attrs).'>';

                          $checkbox_content = '
                            <label class="custom-control custom-checkbox">
                                  '.$input.'
                                  <span class="custom-control-indicator"></span>
                              </label>';

            }

            $row_checkbox = '<td style="max-width: 10px;"> '.$checkbox_content.' </td>';
          }

          if (isset($structure->options['row_details']) && $structure->options['row_details']) {
            $option = $structure->options['row_details'];

            $detail_prop = array(
              'id' => '',
              'icon' => parent::$icon_source."-".$this->options['row_detail_icons']['closed'],
              'title' => 'Show Details'
            );

            $new_detail_prop = Util::get_props($detail_prop, $option, array($this, $row_data, $row_index), 'icon');
            $id = $new_detail_prop['id'] ? 'id="'.$new_detail_prop['id'].'"' : '';

            $content = '<a href="#" '.$id.'>
                  <i class="'.parent::$icon_source.' '.$detail_prop['icon'].' '.parent::$icon_source.'" data-toggle="row-detail" title="'.$detail_prop['title'].'"></i>
                </a>';
            if ($row_prop['detail'] === false)
              $content = '';

            $row_details =
              '<td class="center" width="20px"> '.$content.' </td>';
          }

          $html_rows[] = '<tr'.$row_class.$attr.'>'.$row_details.$row_checkbox.$rows_html.'</tr>';
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

    if ($structure->options['columns']) {
      $cols = Util::get_value($structure->col, array(
        'if_array' => function($cols) use ($structure) {
          $html_col_list = array();

          foreach ($cols as $col_name => $col_value) {

            if (is_null($col_value) || $col_value === false) continue;;
            $col_value_prop = array(
              'title' => $col_name,
              'class' => '',
              'attr' => array(),
              'icon' => '',
              'hidden' => (isset($structure->hide[$col_name]) && $structure->hide[$col_name] === true) || in_array($col_name, $structure->hidden)
            );

            $new_col_value = Util::get_props($col_value_prop, $col_value, array($this, $cols), 'title');
            $col_attrs = Util::attrs($new_col_value['attr']);

            $classes = array();
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

          if (isset($structure->options['checkboxes']) && ($structure->options['checkboxes'])) {
            $checkbox_header = '
              <th class="center table-checkbox" style="max-width: 10px;">
                <label class="custom-control custom-checkbox">
                  <input type="checkbox" class="custom-control-input" data-toggle="table-checkall">
                  <span class="custom-control-indicator"></span>
                </label>
              </th>';
          }

          if (isset($structure->options['row_details']) && ($structure->options['row_details'])) {
            $detail_header = '
              <th class="center" width="20px"></th>';
          }

          return '<tr>'.$detail_header.$checkbox_header.$html_cols.'</tr>';
        }
      ));
    } else $cols = '';

    $classes = array();
    if ($structure->options['table']) $classes[] = 'table';
    if ($structure->options['inverse']) $classes[] = 'table-inverse';
    if ($structure->options['striped']) $classes[] = 'table-striped';
    if ($structure->options['bordered']) $classes[] = 'table-bordered';
    if ($structure->options['hover']) $classes[] = 'table-hover';
    if ($structure->options['responsive']) $classes[] = 'table-responsive';

    if ($structure->class) {
      $classes[] = is_array($structure->class) ? implode(' ', $structure->class) : $structure->class;
    }

    $table_html = '<table class="'.implode(' ', $classes).'">';
    $table_html .= '<thead>';
    $table_html .= $cols;
    $table_html .= '</thead>';
    $table_html .= '<tbody>';
    $table_html .= $rows;
    $table_html .= '</tbody>';
    $table_html .= '</table>';

    $result = $table_html;

    if ($return) return $result;
    else echo $result;

  }


}



?>