<?php
declare(strict_types=1);

namespace esp\helper\library;

/**
 * Class Jump
 * @package esp\helper\library
 *
 * 两个系统后台相互跳
 * 条件：
 * 1，两个服务器的时间相差不能太大；
 * 2，跳入链接有效时间60秒，在临近58秒以上时跳入有可能会失败。
 *
 * 程序容错1秒
 *
 */
final class Jump
{
    private string $token = '0ad4b59c4cbf7423a8e7f4cf178ab11a';

    public function __construct(string $token = '')
    {
        if ($token) $this->token = $token;
    }

    public function encode(int $userID, string $userName, $extend = ''): string
    {
        $time = time();
        if (!$extend) $extend = $time;
        $extend = serialize($extend);
        $sign = md5(date('YmdHi', $time) . $userID . $this->token . $userName . $extend);
        $data = [
            'u' => $userID,
            'n' => $userName,
            'e' => $extend,
            's' => $sign,
        ];
        $base = base64_encode(json_encode($data, 320));
        return urlencode(str_replace('/', '_', $base));
    }


    public function decode(string $code)
    {
        $str = urldecode($code);
        if (!$str) return 'empty url';
        $json = base64_decode(str_replace('_', '/', $str));
        if (!$json) return 'fail base';
        $data = json_decode($json, true);
        if (!$data) {
            $json = base64_decode($code);
            if (!$json) return 'fail base';
            $data = json_decode($json, true);
            if (!$data) return 'fail json';
        }
        if (!isset($data['u']) or !isset($data['n']) or !isset($data['s'])) return 'no uns';
        $time = time();
        $sign = md5(date('YmdHi', $time) . $data['u'] . $this->token . $data['n'] . ($data['e'] ?? ''));
        if ($sign !== $data['s']) {
            $sign = md5(date('YmdHi', $time - 1) . $data['u'] . $this->token . $data['n'] . ($data['e'] ?? ''));
            if ($sign !== $data['s']) return 'token error';
        }
        $ext = unserialize($data['e'] ?? '');
        return ['id' => $data['u'], 'name' => $data['n'], 'extend' => $ext];
    }


}