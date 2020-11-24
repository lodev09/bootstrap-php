<?php

namespace Bootstrap;
use \Common\Util;

class Helper {

    public static function isClosure($obj) {
        return (is_object($obj) && ($obj instanceof \Closure));
    }

    public static function getValue($prop, $prop_methods) {
        if (self::isClosure($prop)) {
            return isset($prop_methods['if_closure']) ? $prop_methods['if_closure']($prop) : $prop($prop);
        } else if (is_array($prop) || is_object($prop)) {
            if (is_object($prop))
                $prop = Util::toArray($prop);
            return isset($prop_methods['if_array']) ? $prop_methods['if_array']($prop) : $prop;
        } else {
            return isset($prop_methods['if_other']) ? $prop_methods['if_other']($prop) : $prop;
        }
    }

    public static function getProps($default_prop, $value, $closure_defaults = [], $default_key = '') {
        $property = self::getValue($value, [
            'if_array' => function ($value) use ($default_prop, $default_key) {
                return Util::setValues($default_prop, $value, $default_key);
            },
            'if_closure' => function($value) use ($closure_defaults, $default_prop, $default_key) {
                return self::setClosureDefaults($default_prop, $value, $closure_defaults, $default_key);
            },
            'if_other' => function($value) use ($default_prop, $default_key) {
                $default_prop[$default_key] = $value;
                return $default_prop;
            }
        ]);

        return $property;
    }

    public static function setClosureDefaults($default_props, $callback, $args = [], $default_key = "") {
        if ($default_key != "") {
            if (!self::isClosure($callback)) {
                if (isset($default_props[$default_key]))
                    $default_props[$default_key] = $callback;
                return $default_props;
            }
        }

        $callback_return = self::runCallback($callback, $args);

        if (is_array($callback_return)) {
            $default_props = Util::setValues($default_props, $callback_return);
        } else if ($default_key != "" && isset($default_props[$default_key])) {
            $default_props[$default_key] = $callback_return;
        }
        return $default_props;
    }

    public static function runCallback($callback, $default_args) {
        $reflection = new \ReflectionFunction($callback);
        $params = $reflection->getParameters();
        if (!$params || !$default_args) return call_user_func($callback);

        $ref_args = array_keys($params);
        foreach ($ref_args as $param_index) {
            if (isset($default_args[$param_index]))
                $ref_args[$param_index] = $default_args[$param_index];
            else
                $ref_args[$param_index] = null;
        }

        return call_user_func_array($callback, $ref_args);
    }

    public static function parseValue($str, $row, $url_encode = false) {
        preg_match_all("/\{([^&={{}}]+)\}/", $str, $matched_cols);
        if ($matched_cols[1]) {
            $col_replace = [];
            $col_search = [];
            foreach($matched_cols[1] as $matched_col) {
                if (is_array($row)) $row = Util::toObject($row);
                if (isset($row->{$matched_col})) {
                    $col_replace[] = $url_encode ? urlencode($row->{$matched_col}) : $row->{$matched_col};
                    $col_search[] = "/{{".$matched_col."}}/";
                }
            }
            return preg_replace($col_search, $col_replace, $str);
        }

        return $str;
    }

    public static function attrs($attrs, $row = null) {
        $attrs = is_array($attrs) ? $attrs : [$attrs];

        $new_attrs = [];
        array_walk($attrs, function($value, $attr) use ($row, &$new_attrs) {
            if ($row) $value = self::parseValue($value, $row);

            if (is_numeric($attr)) {
                $new_attrs[] = $value;
            } else {
                $new_attrs[] = $attr.'="'.$value.'"';
            }
        });

        return implode(' ', $new_attrs);
    }
}

?>