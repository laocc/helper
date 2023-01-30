<?php

namespace esp\helper;


/**
 * 是否为手机号码
 * @param string $value
 * @return bool
 */
function is_mob(string $value): bool
{
    if (empty($value)) return false;
    return (boolean)preg_match('/^1[3456789]\d{9}$/', $value);
}

/**
 * 电话号码格式
 * @param string $value
 * @return bool
 */
function is_phone(string $value): bool
{
    if (empty($value)) return false;
    return (boolean)preg_match('/^1[3456789]\d{9}$/', $value) or //手机号格式
        (boolean)preg_match('/^0\d{2,3}(-|\x20)?\d{7,8}$/', $value) or //010-12388855
        (boolean)preg_match('/^0\d{2,3}(-|\x20)?\d{7,8}(-|\x20)\d{1,5}$/', $value) or //区号+号码+分号
        (boolean)preg_match('/^0\d{10,11}$/', $value) or //01025456365
        (boolean)preg_match('/^400(-|\x20)?\d{7,8}$/', $value); //400电话
}

/**
 * @param string $value
 * @param bool $canEmpty
 * @return bool
 */
function is_username(string $value, bool $canEmpty = false): bool
{
    if ($canEmpty and empty($value)) return true;
    if (empty($value)) return false;
    return (boolean)preg_match('/^1[3456789]\d{9}$/', $value) or preg_match('/^\w{3,11}$/', $value);
}

/**
 * 电子邮箱地址格式
 * @param string $value
 * @return bool
 */
function is_mail(string $value): bool
{
    if (empty($value)) return false;
    return (bool)filter_var($value, FILTER_VALIDATE_EMAIL);
}

/**
 * 是否一完网址
 * @param string $value
 * @return bool
 */
function is_url(string $value): bool
{
    if (empty($value)) return false;
    return (bool)filter_var($value, FILTER_VALIDATE_URL);
}

/**
 * 是否英文或数字组合
 * @param string $value
 * @return bool
 */
function is_alphanumeric(string $value): bool
{
    if (empty($value)) return false;
    return (bool)preg_match("/^[a-z0-9]+$/i", $value);
}

/**
 * 是否中文
 * @param string $value
 * @return bool
 */
function is_cn(string $value): bool
{
    if (empty($value)) return false;
    return (bool)preg_match("/^[\x{4e00}-\x{9fa5}]+$/u", $value);
}

/**
 * 是否中英文，即是否含有符号
 * @param string $value
 * @return bool
 */
function is_cen(string $value): bool
{
    if (empty($value)) return false;
    return (bool)preg_match("/^[\x{4e00}-\x{9fa5}a-z0-9]+$/iu", $value);
}

/**
 * 是否符合整形值，含纯字串的数字也视作整型，如果单纯只判断是否===整型，直接用is_int
 * @param $value
 * @return bool
 */
function is_integers($value): bool
{
    if (!is_scalar($value) || is_bool($value)) return false;
    if (!(bool)preg_match('/^[+-]?\d+$/', strval($value))) return false;
    if (intval($value) > PHP_INT_MAX) return false;
    return true;
}

/**
 * 是否浮点型，包括可以转换为浮点的字串
 * @param $value
 * @return bool
 */
function is_floats($value): bool
{
    if (!is_scalar($value)) return false;
    return \is_float($value + 0);
}

/**
 * 是否URI格式
 * @param string $value
 * @return bool
 */
function is_uri(string $value): bool
{
    if (empty($value)) return false;
    return (bool)filter_var($value, FILTER_VALIDATE_REGEXP, ['options' => ['regexp' => '/^(\/[\w\-\.\~]*)?(\/.+)*$/i']]);
}

/**
 * @param string $value
 * @return bool
 */
function is_datetime(string $value): bool
{
    if (empty($value)) return false;
    $tmp = explode(' ', trim($value), 2);
    if (!isset($tmp[1])) return false;
    return is_date($tmp[0]) and is_time($tmp[1]);
}

/**
 * 日期格式：2015-02-05 或 20150205
 * @param string $value
 * @return bool
 */
function is_date(string $value): bool
{
    if (empty($value)) return false;
    if (1) {
        if (preg_match('/^(\d{4})-(\d{1,2})-(\d{1,2})$/', $value, $mac)) {
            return checkdate($mac[2], $mac[3], $mac[1]);
        } elseif (preg_match('/^(\d{4})(\d{1,2})(\d{1,2})$/', $value, $mac)) {
            return checkdate($mac[2], $mac[3], $mac[1]);
        } else {
            return false;
        }
    } else {
        return (boolean)preg_match('/^(?:(?:1[789]\d{2}|2[012]\d{2})[-\/](?:(?:0?2[-\/](?:0?1\d|2[0-8]))|(?:0?[13578]|10|12)[-\/](?:[012]?\d|3[01]))|(?:(?:0?[469]|11)[-\/](?:[012]?\d|30)))|(?:(?:1[789]|2[012])(?:[02468][048]|[13579][26])[-\/](?:0?2[-\/]29))$/', $value);
    }
}

/**
 * 时间格式：12:23:45
 * @param string $value
 * @return bool
 */
function is_time(string $value): bool
{
    if (empty($value)) return false;
    return (boolean)preg_match('/^([0-1]\d|2[0-3])(\:[0-5]\d){2}$/', $value);
}

/**
 * 是否json格式
 * @param string $value
 * @return bool
 */
function is_json(string $value): bool
{
    if (empty($value)) return false;
    if (!preg_match('/^(\{.*\})|(\[.*\])$/', $value)) return false;
    try {
        $a = json_decode($value, true);
        if (!is_array($a)) return false;
    } catch (\Error $exception) {
        return false;
    }
    return true;
}


/**
 * 字串是否为正则表达式
 * @param string $value
 * @return bool
 */
function is_match(string $value): bool
{
    if (empty($value)) return false;
    return (bool)filter_var($value, FILTER_VALIDATE_REGEXP, ['options' => ['regexp' => '/^([\/\#\@\!\~])\^?.+\$?\1[imUuAsDSXxJ]{0,3}$/i']]);
}

/**
 * 是否mac码
 * @param string $value
 * @return bool
 */
function is_mac(string $value): bool
{
    if (empty($value)) return false;
    return (bool)filter_var($value, FILTER_VALIDATE_MAC);
}


/**
 * @param string $value
 * @param string $which
 * @return bool
 */
function is_ip(string $value, string $which = 'ipv4'): bool
{
    if (empty($value)) return false;
    switch (strtolower($which)) {
        case 'ipv4':
            $which = FILTER_FLAG_IPV4;
            break;
        case 'ipv6':
            $which = FILTER_FLAG_IPV6;
            break;
        default:
            $which = NULL;
            break;
    }
    return (bool)filter_var($value, FILTER_VALIDATE_IP, $which);
}

/**
 * 英文域名规则判断
 *
 * 1，总长3-255字符
 * 2，每一节开头只能是a-z0-9
 * 3，每一节只能是[a-z0-9][a-z0-9-]{0,62}\.且最少有一节
 * 4，每一节含.号最长64字符
 * 5，最后一节只能是a-z且2-10字符
 *
 * @param string $value
 * @return bool
 */
function is_domain(string $value): bool
{
    if (empty($value)) return false;
    return (boolean)preg_match('/^(?=^.{3,255}$)([a-z\d][a-z\d-]{0,62}\.)+[a-z]{2,10}$/i', $value);
}

/**
 * 身份证号码检测，较验最后识别码
 * @param string $code
 * @return bool
 */
function is_card(string $code): bool
{
    if (empty($code)) return false;
    if (!preg_match('/^\d{6}(\d{8})\d{3}(\d|x)$/i', $code, $mac)) return false;
    $num = str_split($code, 2);
    if ($num[1] === '00' or $num[2] === '00') return false;
    $pro = [11, 12, 13, 14, 15, 21, 22, 23, 31, 32, 33, 34, 35, 36, 37, 38, 41, 42, 43, 44, 45, 46, 50, 51, 52, 53, 54, 61, 62, 63, 64, 65, 71, 81, 82];
    if (!in_array($pro, $num[0])) return false;
    if (!is_date($mac[1])) return false;
    return strtoupper($mac[2]) === make_card_suffix($code);
}
