<?php

namespace esp\helper;

use esp\helper\library\Error;
use esp\helper\library\ext\Xml;

/**
 * 查询域名的根域名，兼容国别的二级域名
 * @param string $domain
 * @param null $branch
 * @return string
 */
function host(string $domain, $branch = null): string
{
    if (empty($domain)) return '';
    if (strpos($domain, '/')) $domain = explode('/', "{$domain}//")[2];
    if (!is_null($branch)) {
        if (is_string($branch)) $branch = explode(',', str_replace(';', ',', $branch));
        foreach ($branch as $bch) {
            if (stripos($domain, $bch)) {
                $bv = str_replace('.', '\.', $bch);
                $p2 = "/^(?:[\w\.\-]+\.)?([a-z0-9]+)\.({$bv})$/i";
                if (preg_match($p2, $domain, $match)) return "{$match[1]}.{$match[2]}";
            }
        }
    }

    $p1 = "/^(?:[\w\.\-]+\.)?([a-z]+)\.(com|net|org|gov|idv|co|name)\.(cn|cm|my|ph|tw|uk|hk)$/i";
    $p2 = "/^(?:[\w\.\-]+\.)?([a-z0-9]+)\.([a-z]+)$/i";
    if (preg_match($p1, $domain, $match)) {
        return "{$match[1]}.{$match[2]}.{$match[3]}";

    } elseif (preg_match($p2, $domain, $match)) {
        return "{$match[1]}.{$match[2]}";
    }

    return '';
}

/**
 * 提取URL中的域名
 * @param string $url
 * @return string
 */
function domain(string $url): string
{
    if (empty($url)) return '';
    if (substr($url, 0, 4) !== 'http') return $url;
    return explode('/', "{$url}//")[2];
}


/**
 * 查询2个字串从开头起的相同部分
 * @param string $str1
 * @param string $str2
 * @return bool|string
 */
function same_first(string $str1, string $str2): string
{
    $pos = strspn($str1 ^ $str2, "\0");
    return $pos ? substr($str1, 0, $pos) : '';
}

/**
 * 加载文件，同时加载结果被缓存
 * @param string $file
 * @return bool|mixed
 */
function load(string $file)
{
    $file = root($file);
    if (!$file or !is_readable($file)) return false;
    static $recode = array();
    $md5 = md5($file);
    if (isset($recode[$md5])) return $recode[$md5];
    $recode[$md5] = include $file;
    return $recode[$md5];
}

/**
 * 修正为_ROOT开头
 * @param string $path
 * @param bool $real 检查文件或目录是否存在
 * @return string
 */
function root(string $path, bool $real = false): string
{
    foreach (['/home/', '/mnt/', '/mdb/', '/mda/', '/mdc/', '/tmp/', '/www/'] as $r) {
        if (strpos($path, $r) === 0) goto end;
    }

    if (stripos($path, _ROOT) !== 0) $path = _ROOT . "/" . trim($path, '/');
    end:
    if ($real) $path = realpath($path);
    if ($path === false) return '';
    return rtrim($path, '/');
}

/**
 * 储存文件
 * @param string $file
 * @param  $content
 * @param bool $append
 * @param array|null $trace
 * @return int
 * @throws Error
 */
function save_file(string $file, $content, bool $append = false, array $trace = null): int
{
    if (is_null($trace)) $trace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 1)[0];
    mk_dir($file, 0740, $trace);
    if (is_array($content)) $content = json_encode($content, 256 | 64);
    return file_put_contents($file, $content, $append ? FILE_APPEND : LOCK_EX);
}

/**
 * 注意：如果系统服务是分布式，要考虑统一性问题，这个锁只锁同一服务器上的业务
 *
 * 锁内返回值都不得是字符串，因为锁返回的如果是字串，则表示为出错信息
 *
 * @param string $lockKey
 * @param callable $callable
 * @param mixed ...$args
 * @return mixed
 */
function locked(string $lockKey, callable $callable, ...$args)
{
    $operation = ($lockKey[0] === '#') ? (LOCK_EX | LOCK_NB) : LOCK_EX;
    $lockKey = str_replace(['/', '\\', '*', '"', "'", '<', '>', ':', ';', '?'], '', $lockKey);
    $fn = fopen(($lockFile = "/tmp/{$lockKey}.flock"), 'a');
    if (flock($fn, $operation)) {//加锁
        try {
            $rest = $callable(...$args);//执行
        } catch (\Exception $exception) {
            $rest = 'locked: ' . $exception->getMessage();
        } catch (\Error $error) {
            $rest = 'locked: ' . $error->getMessage();
        }
        flock($fn, LOCK_UN);//解锁
    } else {
        $rest = "locked: Running";
    }
    fclose($fn);
    if (is_readable($lockFile)) @unlink($lockFile);
    return $rest;
}

/**
 * 文件权限： 所有者  所有者组    其它用户
 *
 * r    read    4
 * w    write   2
 * x    exec    1
 *
 * 通过PHP建立的文件夹权限一般为0744就可以了
 *
 * 若php-fpm由www账号运行，且目录有可能需要写入，则需要将此目录改为0777，或chown为www账号
 *
 * @param string $path 若不是以/结尾，则会向上缩一级
 * @param int $mode
 * @param array|null $trace
 * @return bool
 * @throws Error
 */
function mk_dir(string $path, int $mode = 0744, array $trace = null): bool
{
    if (!$path) return false;
    $check = strrchr($path, '/');
    if ($check === false) {
        throw new Error("目录或文件名中必须要含有/号，当前path=" . var_export($path, true));
    } else if ($check !== '/') $path = dirname($path);

    return locked('mkdir', function ($path, $mode) {
        if (!file_exists($path)) @mkdir($path, $mode ?: 0740, true);
        return true;
    }, $path, $mode);
}


/**
 * XML解析成数组或对象
 * @param string $str
 * @param bool $toArray
 * @return mixed|null
 */
function xml_decode(string $str, bool $toArray = true)
{
    if (!$str) return null;
    $xml_parser = xml_parser_create();
    if (!xml_parse($xml_parser, $str, true)) {
        xml_parser_free($xml_parser);
        return null;
    }
    return json_decode(json_encode(@simplexml_load_string($str, "SimpleXMLElement", LIBXML_NOCDATA)), $toArray);
}


/**
 * 将数组转换成XML格式
 * @param $root
 * @param array $array
 * @param bool $outHead
 * @return string
 * @throws Error
 */
function xml_encode($root, array $array, bool $outHead = true): string
{
    return (new Xml($array, $root))->render($outHead);
}


/**
 * 随机字符，如果只需要唯一值则用：uniqid()
 * @param int $min 最小长度
 * @param int|null $max 最大长度，若不填，则以$min为固定长度
 * @param bool $hex 是否只取16进制内
 * @return string
 */
function str_rand(int $min = 10, int $max = null, bool $hex = false): string
{
    if (is_bool($max)) [$max, $hex] = [null, $max];
    $max = $max ? mt_rand($min, $max) : $min;
    if ($max > 60) $max = 60;
    $pond = ['A' => 0, 'B' => 0, 'C' => 0, 'D' => 0, 'E' => 0, 'F' => 0, 'G' => 0, 'H' => 0, 'I' => 0, 'J' => 0, 'K' => 0, 'L' => 0, 'M' => 0, 'N' => 0, 'O' => 0, 'P' => 0, 'Q' => 0, 'R' => 0, 'S' => 0, 'T' => 0, 'U' => 0, 'V' => 0, 'W' => 0, 'X' => 0, 'Y' => 0, 'Z' => 0, 'a' => 0, 'b' => 0, 'c' => 0, 'd' => 0, 'e' => 0, 'f' => 0, 'g' => 0, 'h' => 0, 'i' => 0, 'j' => 0, 'k' => 0, 'l' => 0, 'm' => 0, 'n' => 0, 'o' => 0, 'p' => 0, 'q' => 0, 'r' => 0, 's' => 0, 't' => 0, 'u' => 0, 'v' => 0, 'w' => 0, 'x' => 0, 'y' => 0, 'z' => 0, '0' => 0, '1' => 0, '2' => 0, '3' => 0, '4' => 0, '5' => 0, '6' => 0, '7' => 0, '8' => 0, '9' => 0];
    if ($hex) $pond = ['A' => 0, 'B' => 0, 'C' => 0, 'D' => 0, 'E' => 0, 'F' => 0, 'a' => 0, 'b' => 0, 'c' => 0, 'd' => 0, 'e' => 0, 'f' => 0, 'g' => 0, '0' => 0, '1' => 0, '2' => 0, '3' => 0, '4' => 0, '5' => 0, '6' => 0, '7' => 0, '8' => 0, '9' => 0];
    $arr = array_rand($pond, $max ?: 10);
    if ($max === 1) return $arr;
    shuffle($arr);//取得的字串是顺序的，要打乱
    return implode($arr);
}


/**
 * 格式化字符串，对date函数的一点延伸
 * @param string $str
 * @return mixed
 */
function format(string $str): string
{
    return preg_replace_callback('/\%([a-z])/i', function ($matches) {
        switch ($matches[1]) {
            case 'D':       //从2015-8-3算起的天数
                return ceil((time() - 1438531200) / 86400);
            case 'u':       //唯一性值
                return uniqid();
            case 'r':       //随机数
                return mt_rand(1000, 9999);
            default:        //用data
                return date($matches[1]);
        }
    }, $str);
}


/**
 * 将字符串中相应指定键替换为数据相应值
 * @param string $str ="姓名:{name},年龄:{age}"
 * @param array $arr =['name'=>'张三','age'=>20]
 * @return string
 */
function replace_array(string $str, array $arr): string
{
    return str_replace(array_map(function ($k) {
        return "{{$k}}";
    }, array_keys($arr)), array_values($arr), $str);
}


/**
 * 从arr内取出相关的字段组成一个新的数组
 * @param array $arr
 * @param mixed ...$key
 * @return array
 */
function array_select(array $arr, ...$key): array
{
    if (count($key) === 1) {
        if (is_array($key[0])) $key = $key[0];
        else if (is_string($key[0])) $key = explode(',', $key[0]);
    }
    if (is_string($key)) $key = explode(',', $key);
    return array_filter($arr, function ($k) use ($key) {
        return in_array($k, $key);
    }, ARRAY_FILTER_USE_KEY);
}

