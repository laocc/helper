<?php
declare(strict_types=1);

namespace esp\helper\library\request;

use Error;
use esp\helper\library\ext\Xss;
use function esp\helper\is_mob;
use function esp\helper\is_card;
use function esp\helper\is_date;
use function esp\helper\is_domain;
use function esp\helper\is_ip;
use function esp\helper\is_mail;
use function esp\helper\is_phone;
use function esp\helper\is_time;
use function esp\helper\is_match;
use function esp\helper\is_url;
use function esp\helper\xml_encode;
use function esp\helper\xml_decode;

final class Post extends Request
{


    public function string(string $key, int $xssLevel = 1): string
    {
        $value = $this->getData($key, $force);
        if (is_null($value)) return '';

        if (is_array($value)) $value = json_encode($value, 256 | 64);
        $value = trim(strval($value));

        if ($xssLevel === 1) {
            $value = preg_replace('/["\']/', '', $value);

        } elseif ($xssLevel === 2) {
            $value = preg_replace('/[\"\'\%\&\^\$\#\(\)\[\]\{\}\?]/', '', $value);

        } else if ($xssLevel > 2) {
            Xss::clear($value);

        }

        if (empty($value) && $force) $this->recodeError($key);
        if ($chk = $this->errorString($value)) $this->recodeError($key, $chk);

        return $value;
    }

    private function checkString(string $type, $value)
    {
        if (is_array($value)) return false;

        switch ($type) {
            case 'mobile':
                return is_mob($value);
            case 'phone':
                return is_phone($value);
            case 'card':
                return is_card($value);
            case 'url':
                return is_url($value) || is_domain($value);
            case 'mail':
            case 'email':
                return is_mail($value);
            case 'ip':
            case 'ip4':
                return is_ip($value, 'ipv4');
            case 'ip6':
                return is_ip($value, 'ipv6');
            case 'date':
                return is_date($value);
            case 'time':
                return is_time($value);
            case 'domain':
                return is_domain($value);
            case 'datetime':
                return strtotime($value);
            case 'cn':
                return preg_match('/^[\x{4e00}-\x{9fa5}]+$/u', strval($value));
            case 'en':
                return preg_match('/^[a-zA-Z]+$/', strval($value));
            case 'number':
                return preg_match('/^\d+$/', strval($value));
            case 'decimal':
                return preg_match('/^\d+(\.\d+)?$/', strval($value));
            case 'alphanumeric':
                return preg_match('/^[a-zA-Z0-9]+$/', strval($value));
            default:
                if (is_match($type)) return preg_match($type, strval($value));
        }
        return false;
    }

    /**
     * ??????????????????????????????????????????????????????
     * @param string $key
     * @param string ...$type ????????????????????????????????????????????????????????????
     * @return string
     */
    public function filter(string $key, string ...$type): string
    {
        $this->_min = null;
        $this->_max = null;
        $value = $this->getData($key, $force);
        if (is_null($value)) return '';
        $value = trim($value);

        if ($value === '' && !$force) return '';

        $len = count($type);
        if ($len > 1) {
            $isTrue = 0;
            foreach ($type as $t) {
                if ($this->checkString($t, $value)) {
                    $isTrue++;
                    continue;
                }
            }
            if ($isTrue === 0) {
                if ($force or !empty($value)) $this->recodeError($key, "?????????????????????");
                return '';
            } else {
                return $value;
            }

        } else if (!$this->checkString($type[0], $value)) {
            switch ($type[0]) {
                case 'cn':
                    $this->recodeError($key, "??????????????????");
                    break;
                case 'en':
                    $this->recodeError($key, "???????????????????????????");
                    break;
                case 'number':
                    $this->recodeError($key, "???????????????");
                    break;
                case 'decimal':
                    $this->recodeError($key, "?????????????????????");
                    break;
                case 'alphanumeric':
                    $this->recodeError($key, "???????????????????????????");
                    break;
                case 'mobile':
                    $this->recodeError($key, "???????????????????????????");
                    break;
                case 'phone':
                    $this->recodeError($key, "???????????????????????????");
                    break;
                case 'card':
                    $this->recodeError($key, "???????????????????????????????????????");
                    break;
                case 'domain':
                    $this->recodeError($key, "?????????????????????");
                    break;
                case 'url':
                    $this->recodeError($key, "?????????URL??????");
                    break;
                case 'mail':
                case 'email':
                    $this->recodeError($key, "?????????????????????????????????");
                    break;
                case 'ip':
                case 'ip4':
                    $this->recodeError($key, "?????????IP4??????");
                    break;
                case 'ip6':
                    $this->recodeError($key, "?????????IP6??????");
                    break;
                case 'date':
                    $this->recodeError($key, "?????????????????????");
                    break;
                case 'time':
                    $this->recodeError($key, "?????????????????????");
                    break;
                case 'datetime':
                    $this->recodeError($key, "???????????????????????????");
                    break;
                default:
                    $this->recodeError($key, "???????????????????????????");
            }
        }

        if (empty($value) && $force) $this->recodeError($key);

        return $value;
    }

    public function date(string $key): int
    {
        return $this->datetime($key);
    }

    public function time(string $key): int
    {
        return $this->datetime($key);
    }

    public function datetime(string $key): int
    {
        $value = $this->getData($key, $force);
        if (is_null($value)) return 0;
        $value = str_replace(['+', '%3A'], [' ', ':'], $value);
        if (empty($value) && $force) $this->recodeError($key);

        $value = strtotime($value) ?: 0;
        if ($chk = $this->errorNumber($value, 1)) $this->recodeError($key, $chk);
        return $value;
    }

    public function number(string $key): string
    {
        $value = $this->getData($key, $force);
        if (is_null($value)) return '';
        if (!preg_match('/^\d+$/', strval($value))) $this->recodeError($key, "??????????????????");
        return strval($value);
    }

    public function int(string $key, bool $zero = true): int
    {
        $value = $this->getData($key, $force);
        if (is_null($value)) return 0;
        if (is_string($value) and preg_match('/^\[.+\]$/', $value)) $value = json_decode($value, true);
        if (is_array($value)) $value = array_sum($value);

        if ($value === '' && $force) $this->recodeError($key);
        $value = intval($value);
        if ($value === 0 && !$zero) $this->recodeError($key, '?????????????????????');
        if ($chk = $this->errorNumber($value)) $this->recodeError($key, $chk);
        return $value;
    }

    public function tinyint(string $key, bool $zero = true): int
    {
        $value = $this->int($key, $zero);
        if ($value < 0) $this->recodeError($key, '???????????????0');
        else if ($value > 255) $this->recodeError($key, '?????????255');
        return $value;
    }

    public function float(string $key, bool $zero = true): float
    {
        $value = $this->getData($key, $force);
        if (is_null($value)) return floatval(0);
        if ($value === '' && $force) $this->recodeError($key);
        $value = floatval($value);
        if ($value == 0 && !$zero) $this->recodeError($key, '?????????????????????');
        if ($chk = $this->errorNumber($value)) $this->recodeError($key, $chk);
        return $value;
    }

    public function bool(string $key): bool
    {
        $this->_min = null;
        $this->_max = null;
        $value = $this->getData($key, $force);
        if (is_null($value)) return false;
        if ($value === '' && $force) $this->recodeError($key);
        if (is_bool($value)) return $value;
        if (strtolower($value) === 'false') return false;
        return boolval($value);
    }

    /**
     * ????????????[?????????]????????????[?????????]????????????float
     * @param string $key
     * @param bool $cent
     * @return int
     */
    public function money(string $key, bool $cent = true): int
    {
        $value = $this->getData($key, $force);
        if (is_null($value)) return 0;
        if ($value === '' && $force) $this->recodeError($key);
        $value = intval(strval(floatval($value) * 100));
        if ($chk = $this->errorNumber($value, 2)) $this->recodeError($key, $chk);
        return $value;
    }

    public function match(string $key, string $pnt): string
    {
        $this->_min = null;
        $this->_max = null;
        if (!is_match($pnt)) throw new Error('???????????????????????????', 1);
        $value = $this->getData($key, $force);
        if (is_null($value)) return '';
        if (empty($value) && $force) $this->recodeError($key);

        if (!preg_match($pnt, strval($value))) {
            if ($force) $this->recodeError($key, "???????????????????????????");
            return '';
        }
        return strval($value);
    }


    /**
     * ??????json???????????????????????????????????????json
     *
     * @param string $key
     * @param int $options
     * @return string
     */
    public function json(string $key, int $options = 256 | 64): string
    {
        $this->_min = null;
        $this->_max = null;

        $value = $this->getData($key, $force);
        if (is_null($value)) return '';

        if (is_array($value)) $value = json_encode($value, $options);

        if (empty($value) && $force) $this->recodeError($key);

        if (!preg_match('/^[\{\[].+[\]\}]$/', strval($value))) {
            if ($force) $this->recodeError($key, "???????????????JSON??????");
            return '';
        }

        return $value;
    }


    /**
     * ??????xml???????????????????????????????????????xml
     * @param string $key
     * @param string $root
     * @return string
     */
    public function xml(string $key, string $root = 'xml'): string
    {
        $this->_min = null;
        $this->_max = null;

        $value = $this->getData($key, $force);
        if (is_null($value)) return '';

        if (is_array($value)) $value = xml_encode($root, $value, false);
        if (empty($value) && $force) $this->recodeError($key);

        if (!preg_match('/^<\w+>.+<\/\w+>$/', strval($value))) {
            if ($force) $this->recodeError($key, "???????????????XML??????");
            return '';
        }

        return $value;
    }


    /**
     * ??????????????????????????????json???xml????????????
     *
     * @param string $key
     * @param string $encode
     * @return array
     */
    public function array(string $key, string $encode = 'json'): array
    {
        $this->_min = null;
        $this->_max = null;

        $value = $this->getData($key, $force);
        if (is_null($value)) return [];

        if (is_string($value)) {
            if ($encode === 'xml') {
                $value = xml_decode($value, true);

            } else if ($encode === 'json') {
                $value = json_decode($value, true);

            } else if ($encode === "ini") {
                $value = parse_ini_string($value, true);

            } else {
                parse_str($value, $value);
            }
        }

        if (!is_array($value) or empty($value)) {
            if ($force) $this->recodeError($key, "????????????????????????????????????");
            return [];
        }

        return $value;
    }

    /**
     * @param int $option
     * @return false|mixed|string|null
     *
     * $option:
     * 1????????????????????????????????????????????????
     * 2?????????json
     * 4???????????????
     * 8??????<br>??????
     */
    public function error(int $option = 1)
    {
        $this->_off = true;
        if (empty($this->_error)) return null;
        if (count($this->_error) === 1) return $this->_error[0];
        if ($option & 1) return $this->_error[0];
        if ($option & 2) return json_encode($this->_error, 256 | 64);
        if ($option & 4) return implode("\r\n", $this->_error);
        if ($option & 8) return implode("<br>", $this->_error);
        return $this->_error;
    }

    public function __construct(string $type = null)
    {
        $this->_isPost = true;
        if ($type === 'post' or (strpos(getenv('HTTP_CONTENT_TYPE') ?: '', 'boundary') > 0)) {
            $this->_data = $_POST;
            return;
        }

        $this->_raw = file_get_contents('php://input');
        if (empty($this->_raw) and is_null($type)) return;

        switch ($type) {
            case 'json':
                $this->_data = json_decode($this->_raw, true);
                break;

            case 'xml':
                $this->_data = xml_decode($this->_raw, true);
                break;

            case 'php':
                $this->_data = unserialize($this->_raw);
                break;

            case 'unknown':
                //???????????????
                if (($this->_raw[0] === '{' and $this->_raw[-1] === '}')
                    or ($this->_raw[0] === '[' and $this->_raw[-1] === ']')) {
                    $this->_data = json_decode($this->_raw, true);

                } else if ($this->_raw[0] === '<' and $this->_raw[-1] === '>') {
                    $this->_data = xml_decode($this->_raw, true);

                }
                break;

            default:
                parse_str($this->_raw, $this->_data);
        }


        if (!is_array($this->_data) or empty($this->_data)) $this->_data = [];
    }


}