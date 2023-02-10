<?php
declare(strict_types=1);

namespace esp\helper\library;


use function esp\core\esp_error;

/**
 * ajax/post中返回数据的封装
 *
 * Class Result
 * @package esp\helper\library
 */
class Result
{
    private int $_success = 1;
    private int $_error = 0;
    private string $_message = 'ok';
    private string $_token = '';
    private array $_data = [];
    private array $_append = [];
    private array $_update = [];
    private array $_error_value = [];

    private array $_pageValue;
    private Paging $_paging;

    public function __construct(string $token = __FILE__)
    {
        $this->_token = $token;
    }

    public function setToken($token = null): Result
    {
        $this->_token = $token;
        return $this;
    }

    public function setError(array $error): Result
    {
        $this->_error_value = $error;
        return $this;
    }

    /**
     * 魔术方法获取变量值
     * @param string $key
     * @return null
     */
    public function __get(string $key)
    {
        return $this->_data[$key] ?? null;
    }

    public function __set(string $key, $value)
    {
        $this->_data[$key] = $value;
        return $this;
    }

    /**
     * @param bool $value 可以是bool或int
     * @return $this
     */
    public function success($value = 1): Result
    {
        $this->_success = intval($value);
        return $this;
    }

    /**
     * @param int $value 错误代码
     * @return $this
     */
    public function error_code(int $value = -1): Result
    {
        if ($value === -1 && $this->_error === 0) $this->_error = 1;
        else $this->_error = $value;

        if (isset($this->_error_value[$this->_error])) {
            $this->_message = $this->_error_value[$this->_error];
        }

        return $this;
    }

    public function error($msg): Result
    {
        if (is_int($msg)) return $this->error_code($msg);
        $this->_message = $msg;
        $this->_error = 1;
        return $this;
    }

    /**
     * @param string $msg
     * @param bool $append
     * @return $this
     */
    public function message(string $msg, bool $append = false): Result
    {
        if ($append) {
            $this->_message .= $msg;
        } else {
            $this->_message = $msg;
        }
        return $this;
    }

    /**
     * @param $key
     * @param $value
     * @return Result
     */
    public function data($key, $value = null): Result
    {
        if (is_array($key)) {
            $this->_data = array_merge($this->_data, $key);

        } else if (is_string($key)) {
            if (strpos($key, '.') > 0) {
                $obj = &$this->_data;
                foreach (explode('.', $key) as $k) {
                    if (!isset($obj[$k])) $obj[$k] = [];
                    $obj = &$obj[$k];
                }
                $obj = $value;
                return $this;
            }
            $this->_data[$key] = $value;
        } else {
            esp_error("Result->data() 第1参数需为array或string类型");
        }
        return $this;
    }

    public function unset_data(string $key): Result
    {
        unset($this->_data[$key]);
        return $this;
    }

    public function append(string $key, $value): Result
    {
        $this->_append[$key] = $value;
        return $this;
    }

    public function update(string $key, $value): Result
    {
        if (is_null($value)) {
            unset($this->_update[$key]);
        } else {
            $this->_update[$key] = $value;
        }
        $this->append('update', $this->_update);
        return $this;
    }

    public function action(string $action): Result
    {
        $this->append('action', $action);
        return $this;
    }

    public function page(array $value): Result
    {
        $this->_pageValue = $value;
        return $this;
    }

    public function paging(Paging $paging): Result
    {
        $this->_paging = $paging;
        return $this;
    }

    public function __debugInfo()
    {
        return $this->display();
    }

    public function __toString(): string
    {
        return json_encode($this->display(), 256 | 64);
    }

    public function display($return = null): array
    {
        if ($return instanceof Result) return $return->display();
        if (is_array($return) and (isset($return['_sign']) or isset($return['_time']))) return $return;

        $value = [
            'success' => $this->_success,
            'error' => $this->_error,
            'message' => $this->_message,
            'data' => $this->_data,
            '_time' => microtime(true),
        ];
        if ($this->_token) $value['_sign'] = md5(json_encode($value, 256 | 64) . $this->_token);

        if (isset($this->_pageValue)) $value['paging'] = $this->_pageValue;
        else if (isset($this->_paging)) $value['paging'] = $this->_paging->value();

        if (!empty($this->_append)) $value += $this->_append;

        if (is_string($return)) {
            $value['message'] = $return;
            if ($value['error'] === 0) $value['error'] = 1;

        } else if (is_int($return)) {
            $value['error'] = $return;
            if ($value['message'] === 'ok') $value['message'] = "Error.{$return}";

        } else if (is_array($return)) {
            $value['data'] = $return;

        } else if (is_float($return)) {
            $value['message'] = strval($return);

        } else if ($return === true) {
            if ($value['message'] === 'ok') $value['message'] = 'True';
            $value['error'] = 0;

        } else if ($return === false) {
            if ($value['message'] === 'ok') $value['message'] = 'False';
            if ($value['error'] === 0) $value['error'] = 1;

        }

        return $value;
    }

}