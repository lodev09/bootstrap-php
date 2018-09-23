<?php

namespace Bootstrap;

class Util {

    public static function is_assoc($array) {
        foreach (array_keys($array) as $k => $v) {
            if ($k !== $v)
                return true;
        }
        return false;
    }

    public static function html_escape($str_value, $nl2br = true) {
        if (is_null($str_value)) $str_value = "";
        $new_str = is_string($str_value) ? htmlentities(html_entity_decode($str_value, ENT_QUOTES)) : $str_value;
        return $nl2br ? nl2br($new_str) : $new_str;
    }

    public static function is_closure($obj) {
        return (is_object($obj) && ($obj instanceof \Closure));
    }

    public static function to_object($array, $recursive = false) {
        if (!is_object($array) && !is_array($array))
            return $array;

        if (!$recursive) return (object)$array;

        if (is_array($array))
            return (object)array_map([__CLASS__, 'to_object'], $array);
        else return $array;
    }

    public static function to_array($object) {
        if (!is_object($object) && !is_array($object))
            return $object;

        if (is_object($object))
            $object = get_object_vars($object);

        return array_map([__CLASS__, 'to_array'], $object);
    }

    public static function create_id($len = 16) {
        if (is_bool($len)) $len = $len === true ? 128 : 16;

        $rand = function($min, $max) {
            $range = $max - $min;
            if ($range < 1) return $min; // not so random...
            $log = ceil(log($range, 2));
            $bytes = (int) ($log / 8) + 1; // length in bytes
            $bits = (int) $log + 1; // length in bits
            $filter = (int) (1 << $bits) - 1; // set all lower bits to 1
            do {
                $rnd = hexdec(bin2hex(openssl_random_pseudo_bytes($bytes)));
                $rnd = $rnd & $filter; // discard irrelevant bits
            } while ($rnd >= $range);
            return $min + $rnd;
        };

        $token = "";
        $codeAlphabet = "ABCDEFGHIJKLMNOPQRSTUVWXYZ";
        $codeAlphabet.= "abcdefghijklmnopqrstuvwxyz";
        $codeAlphabet.= "0123456789";
        $max = strlen($codeAlphabet) - 1;
        for ($i=0; $i < $len; $i++)
            $token .= $codeAlphabet[$rand(0, $max)];

        return $token;
    }

    public static function get_value($prop, $prop_methods) {
        if (self::is_closure($prop)) {
            return isset($prop_methods['if_closure']) ? $prop_methods['if_closure']($prop) : $prop($prop);
        } else if (is_array($prop) || is_object($prop)) {
            if (is_object($prop))
                $prop = self::to_array($prop);
            return isset($prop_methods['if_array']) ? $prop_methods['if_array']($prop) : $prop;
        } else {
            return isset($prop_methods['if_other']) ? $prop_methods['if_other']($prop) : $prop;
        }
    }

    public static function get_props($default_prop, $value, $closure_defaults = [], $default_key = '') {
        $structure = self::get_value($value, [
            'if_array' => function ($value) use ($default_prop, $default_key) {
                return self::set_values($default_prop, $value, $default_key);
            },
            'if_closure' => function($value) use ($closure_defaults, $default_prop, $default_key) {
                return self::set_closure_defaults($default_prop, $value, $closure_defaults, $default_key);
            },
            'if_other' => function($value) use ($default_prop, $default_key) {
                $default_prop[$default_key] = $value;
                return $default_prop;
            }
        ]);

        return $structure;
    }

    public static function set_closure_defaults($default_props, $callback, $args = [], $default_key = "") {
        if ($default_key != "") {
            if (!self::is_closure($callback)) {
                if (isset($default_props[$default_key]))
                    $default_props[$default_key] = $callback;
                return $default_props;
            }
        }

        $callback_return = self::run_callback($callback, $args);

        if (is_array($callback_return)) {
            $default_props = self::set_values($default_props, $callback_return);
        } else if ($default_key != "" && isset($default_props[$default_key])) {
            $default_props[$default_key] = $callback_return;
        }
        return $default_props;
    }

    public static function set_values($default_props, $array_value, $default_key = null) {
        if ($default_key) {
            if (!is_array($array_value)) {
                if (isset($default_props[$default_key]))
                    $default_props[$default_key] = $array_value;
                return $default_props;
            }
        }

        foreach ($array_value as $key => $value) {
            $default_props[$key] = $value;
        }
        return $default_props;
    }

    public static function run_callback($callback, $default_args) {
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

    public static function parse_value($str, $row, $url_encode = false) {
        preg_match_all("/\{([^&={{}}]+)\}/", $str, $matched_cols);
        if ($matched_cols[1]) {
            $col_replace = [];
            $col_search = [];
            foreach($matched_cols[1] as $matched_col) {
                if (is_array($row)) $row = self::to_object($row);
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
            if ($row) $value = self::parse_value($value, $row);

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