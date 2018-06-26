<?php
namespace Smidswater;
class ABOS {
    private static function recursive(&$data, &$parents = []) {
        foreach ($data as $key => &$row) {
            if (is_array($row) && !array_key_exists('_value', $row)) {
                $_parents = [];
                foreach ($parents as &$parent) {
                    $_parents[] = &$parent;
                }
                $_parents[] = &$row;
                self::recursive($row, $_parents);
            }
            if (is_string($row) || is_string($row['_value'])) {
                $hooked = is_array($row) && array_key_exists('_hooked', $row) ? $row['_hooked'] : [];
                if (is_array($row) && array_key_exists('_value', $row)) {
                    $row =& $row['_value'];
                }
                $row = \preg_replace_callback('/\$\{(.*?)\}/', function($matches) use (&$parents, &$data, &$row, $key, $hooked) {
                    $variables = explode('.', $matches[1]);
                    $parent = count($parents);
                    $seekIn = &$parents[$parent-1];
                    if ($variables[0] === '@this') {
                        $seekIn = &$parents[--$parent];
                        array_splice($variables, 0, 1);
                    } elseif ($variables[0] === '@parent') {
                        $parent -= 2;
                        $seekIn = &$parents[$parent];
                        array_splice($variables, 0, 1);
                    } elseif ($variables[0] === '@top') {
                        $parent = 0;
                        $seekIn = &$parents[$parent];
                        array_splice($variables, 0, 1);
                    }
                    if (count($variables) > 1) {
                        for($i = 0; $i < count($variables) - 1; $i++) {
                            if ($variables[$i] === '@parent') {
                                $seekIn = &$parents[--$parent];
                            } else {
                                $seekIn = &$seekIn[$variables[$i]];
                            }
                        }
                    }
                    if (is_array($seekIn)) {
                        if (is_string($seekIn[$variables[count($variables)-1]]) && strpos($seekIn[$variables[count($variables)-1]], '${@') !== false) {
                            $_value = &$seekIn[$variables[count($variables)-1]];
                            $seekIn[$variables[count($variables)-1]] = array(
                                '_value' => $_value,
                                '_hooked' => [[&$data, $key, $matches[0]]]
                            );
                            return $matches[0];
                        } elseif (is_array($seekIn[$variables[count($variables)-1]]) && array_key_exists('_value', $seekIn[$variables[count($variables)-1]]) && strpos($seekIn[$variables[count($variables)-1]]['_value'], '${@') !== false) {
                            $seekIn[$variables[count($variables)-1]]['_hooked'][] = [&$data, $key, $matches[0]];
                            return $matches[0];
                        }
                        $val = $seekIn[$variables[count($variables)-1]];

                        if (count($hooked)) {
                            for ($i = 0; $i < count($hooked); $i++) {
                                $hooked[$i][0][$hooked[$i][1]] = str_replace($hooked[$i][2], str_replace($matches[0], $val, $row), $hooked[$i][0][$hooked[$i][1]]);
                            }
                        }
                        return $val;
                    }
                    return $matches[0];
                }, $row);
                $data[$key] = $row;
            }
        }
        return $data;
    }
    public static function decode($abos) {
        if (\is_string($abos)) {
            if (\file_exists($abos)) {
                $abos = \file_get_contents($abos);
            }
            $abos = json_decode($abos, true);
        }
        $_abos = &$abos;
        $parents = [&$_abos];
        return self::recursive($_abos, $parents);
    }
}


var_dump(ABOS::decode([
    'jsonItem1' => 'Hello',
    'jsonItem2' => [
        'jsonItem3' => '${@top.jsonItem1} W',
        'jsonItem4' => '${@this.jsonItem3}or'
    ],
    'jsonItem3' => [
        'jsonItem5' => '${@this.jsonItem4}',
        'jsonItem4' => '${@parent.jsonItem2.jsonItem4}ld'
    ]
]));
