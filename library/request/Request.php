<?php

namespace esp\helper\library\request;

use esp\error\Error;

abstract class Request
{
    protected bool $_isPost = false;
    protected array $_data = array();
    protected string $_raw = '';
    protected array $_error = [];
    protected bool $_off = false;

    /**
     * 受理post时的原始数据，也就是file_get_contents('php://input')
     * @return string
     */
    public function raw()
    {
        return $this->_raw;
    }

    /**
     * @param $number
     * @param int $type
     * @return string
     * $type=1 时间
     * $type=2 金额
     */
    protected function errorNumber($number, int $type = 0): string
    {
        $min = -1;
        $max = 4294967295;

        if ($type === 1) {//日期时间格式
            $min = 0;
            $max = strtotime('2100-12-31');
        } else if ($type === 2) {//2位小数的金额
//            $min = intval(floatval($min) * 100);
//            $max = intval(floatval($max) * 100);
        }

        if ($min > 0 && $min > $number) {
            return "不能小于最小值({$min})，当前={$number}";
        }
        if ($max > 0 && $max < $number) {
            return "不能大于最大值({$max})，当前={$number}";
        }

        return '';
    }

    protected function errorString($string, int $maxLen = 0): string
    {
        $len = mb_strlen($string);
        if ($maxLen && $maxLen < $len) return "不能多于({$maxLen})个字";
        return '';
    }


    public function data()
    {
        return $this->_data;
    }

    /**
     * 传入数据签名校验
     *
     * 只能满足常见签名方法 https://pay.weixin.qq.com/wiki/doc/api/H5.php?chapter=4_3
     *
     * @param array $param
     * @param string|null $signStr
     * @return bool|string
     */
    public function signCheck(array $param = [], string &$signStr = null)
    {
        $sKey = $param['sign_key'] ?? 'sign';
        $tKey = $param['token_key'] ?? 'key';
        $token = $param['token'] ?? '';
        $data = $param['sign_data'] ?? $this->_data;

        if (str_starts_with($sKey, 'HTTP_')) {
            $sign = getenv($sKey);

        } else if (isset($param[$sKey])) {
            $sign = $param[$sKey];

        } else {
            $sign = $data[$sKey] ?? '';
            unset($data[$sKey]);
        }

        ksort($data);
        $str = '';
        foreach ($data as $k => $v) {
            if ($v === '' or is_null($v)) continue;
            if (is_bool($v)) $v = intval($v);
            else if (is_array($v)) $v = json_encode($v, 256 | 64);
            $str .= "{$k}={$v}&";
        }
        $signStr = ("{$str}{$tKey}={$token}");

        if ($sign === 'string') return ($signStr);
        else if ($sign === 'create') return md5($signStr);

        return hash_equals(strtoupper($sign), strtoupper(md5($signStr)));
    }

    protected function getData(string &$key, &$force)
    {
        if ($this->_off && $this->_isPost) throw new Error('POST已被注销，不能再次引用，请在调用error()之前读取所有数据。', 2);

        if (empty($key)) throw new Error('参数必须明确指定', 2);

        $force = true;
        if ($key[0] === '?') {
            $force = false;
            $key = substr($key, 1);
        }

        $keyName = $key;
        $param = $key;
        $default = null;
        $f = strpos($key, ':');
        $d = strpos($key, '=');
        if ($f && $d === false) {
            $ka = explode(':', $key);
            $param = $ka[0];
            $keyName = $ka[1];
        } else if ($d && $f === false) {
            $ka = explode('=', $key);
            $param = $ka[0];
            $keyName = $ka[0];
            $default = $ka[1];
        } else if ($d && $f) {
            $ka = explode(':', $key);
            if ($d > $f) {//分号在前： 键名:参数名=默认值
                $param = $ka[0];
                $den = explode('=', $ka[1]);
                $keyName = $den[0];
                $default = $den[1];
            } else {
                //分号在后： 键名=默认值:参数名
                $keyName = $ka[1];
                $den = explode('=', $ka[0]);
                $param = $den[0];
                $default = $den[1];
            }
        }

        if (strpos($param, '.') > 0) {
            $val = $this->_data;
            foreach (explode('.', $param) as $k) {
                $val = $val[$k] ?? $default;
                if (is_null($val) or $default === $val) break;
            }
        } else {
            $val = $this->_data[$param] ?? $default;
        }

        $key = $keyName;
        if (is_null($val) && $force) $this->recodeError($keyName);

        return $val;
    }

    protected function recodeError(string $key, string $message = '值不能为空')
    {
        $this->_error[] = "{$key}-{$message}";
    }


    public function __debugInfo()
    {
        return [
            'data' => $this->_data,
            'error' => $this->_error,
            'referer' => $_SERVER['HTTP_REFERER'] ?? ''
        ];
    }

    public function __toString(): string
    {
        return json_encode($this->_data, 256 | 64);
    }


}