<?php
// 新闻署实名测试脚本

// ========== 在此处配置你的生产环境或测试环境凭证 ==========
const DEFAULT_SECRET_KEY = '102cbca54611d48ba4441283451cf5e0';
const DEFAULT_APP_ID     = '7b31f2c2dfa24530afdc595c8c24c3d2';
const DEFAULT_BIZ_ID     = '1101999999';
// ===========================================================

// $result = getIdCard('某一一', '110000190101010001', '100000000000000001', 'mMddQE');
// print_r($result);
// $result = getIdCard('某二一', '110000190201010009', '200000000000000001', 'aZe7VJ');
// print_r($result);
// $result = getIdCard('在二一', '110000190201010009', '200000000000000001', 'ZQXsEJ');
// print_r($result);
// $result = queryIdCard('100000000000000001', 'gM8Cmh');
// print_r($result);
// $result = queryIdCard('200000000000000001', 'rZBHEo');
// print_r($result);
// $result = queryIdCard('300000000000000001', 'nUwdrJ');
// print_r($result);

// 游客
$result = queryLoginout('1234', '1fffbjzos82bs9cnyj1dna7d6d29zg4esnh99u', '', $bt = 1, $ct = 0,'5fYh7G');
print_r($result);
// 实名
$result = queryLoginout('2345', '', '123123', $bt = 1, $ct = 2,'QJzdVW');
print_r($result);




/**
 * 身份验证接口调用
 *
 * @param string $name      姓名
 * @param string $idNum     身份证号
 * @param string $member_id 平台用户 ID
 * @param string $test_code 测试码（为空则调用正式地址）
 * @return array
 */
function getIdCard($name, $idNum, $member_id, $test_code = '')
{
    // 默认使用生产环境配置
    $secret_key = DEFAULT_SECRET_KEY;
    $app_id     = DEFAULT_APP_ID;
    $biz_id     = DEFAULT_BIZ_ID;

    $timestamp = getMillisecond();
    // $ai        = md5($member_id);
    $ai        = $member_id;
    $url       = 'https://api.wlc.nppa.gov.cn/idcard/authentication/check';

    // 如果传入测试码，则切换到测试环境
    if ($test_code !== '') {
        $url         = 'https://wlc.nppa.gov.cn/test/authentication/check/' . $test_code;
    }

    // 系统头参数
    $head = [
        'appId'      => $app_id,
        'bizId'      => $biz_id,
        'timestamps' => $timestamp,
    ];

    // HTTP 请求头
    $headers = [
        'Content-Type:application/json;charset=utf-8',
        'appId:'      . $app_id,
        'bizId:'      . $biz_id,
        'timestamps:' . $timestamp,
    ];

    // 请求体明文
    $body = [
        'ai'    => $ai,
        'name'  => $name,
        'idNum' => $idNum,
    ];

    // 加密 payload
    $payload  = ['data' => bodyEncrypt($body, $secret_key)];
    
    // 计算签名并加入请求头
    $headers[] = 'sign:' . getSign($payload, [], $secret_key, $head);

    // 发起接口调用
    $response = reqCurl($url, 5, 'post', $payload, $headers);
    // print_r($response);exit;
    return json_decode($response, true) ?: [];
}

/**
 * 查询实名认证状态
 *
 * @param string $member_id
 * @param string $test_code
 * @return array
 */
function queryIdCard($member_id, $test_code = '')
{
    $secret_key = DEFAULT_SECRET_KEY;
    $app_id     = DEFAULT_APP_ID;
    $biz_id     = DEFAULT_BIZ_ID;

    $timestamp = getMillisecond();
    // $ai        = md5($member_id);
    $ai        = $member_id;
    $url       = "http://api2.wlc.nppa.gov.cn/idcard/authentication/query?ai={$ai}";

    if ($test_code !== '') {
        $url        = 'https://wlc.nppa.gov.cn/test/authentication/query/' . $test_code . '?ai=' . $ai;
    }

    $head = [
        'ai'         => $ai,
        'appId'      => $app_id,
        'bizId'      => $biz_id,
        'timestamps' => $timestamp,
    ];

    $headers = [
        'Content-Type:application/json;charset=utf-8',
        'appId:'      . $app_id,
        'bizId:'      . $biz_id,
        'timestamps:' . $timestamp,
        'sign:'       . getSign('', [], $secret_key, $head),
    ];

    $response = reqCurl($url, 5, 'get', [], $headers);
    return json_decode($response, true) ?: [];
}

/**
 * 上报登录/登出行为 游戏用户行为数据上报接口说明
 * @param $member_id        用户id
 * @param $pi               已通过实名认证用户的唯一标识
 * @param $di               游客模式设备标识，由游戏运营单位生成，游客用户下必填
 * @param $bt               游戏用户行为类型: 0：下线; 1：上线
 * @param $ct               用户行为数据上报类型 0：已认证通过用户 2：游客用户
 * @return res  array
 */
function queryLoginout($member_id, $pi, $di, $bt = 0, $ct = 0, $test_code = '')
{
    $secret_key = DEFAULT_SECRET_KEY;
    $app_id     = DEFAULT_APP_ID;
    $biz_id     = DEFAULT_BIZ_ID;

    $timestamp = getMillisecond();
    $now        = time();
    $url        = 'http://api2.wlc.nppa.gov.cn/behavior/collection/loginout';

    if ($test_code !== '') {
        $url        = 'https://wlc.nppa.gov.cn/test/collection/loginout/' . $test_code;
    }

    $head = [
        'appId'      => $app_id,
        'bizId'      => $biz_id,
        'timestamps' => $timestamp,
    ];

    $headers = [
        'Content-Type:application/json;charset=utf-8',
        'appId:'      . $app_id,
        'bizId:'      . $biz_id,
        'timestamps:' . $timestamp,
    ];

    $body = [
        'collections' => [[
            'no' => 1,
            'si' => $member_id,
            'bt' => $bt,
            'ot' => $now,
            'ct' => $ct,
            'pi' => $pi,
            'di' => $di,
        ]],
    ];

    $payload   = ['data' => bodyEncrypt($body, $secret_key)];
    $headers[] = 'sign:' . getSign($payload, [], $secret_key, $head);

    $response = reqCurl($url, 5, 'post', $payload, $headers);
    return json_decode($response, true) ?: [];
}

// ---------- 工具函数 ----------

/** 毫秒时间戳 */
function getMillisecond()
{
    list($msec, $sec) = explode(' ', microtime());
    return (int)round(($msec + $sec) * 1000);
}

/** AES-128-GCM 加密 */
function bodyEncrypt(array $body, string $secret_key): string
{
    $key    = hex2bin($secret_key);
    $cipher = 'aes-128-gcm';
    $ivlen  = openssl_cipher_iv_length($cipher);
    $iv     = openssl_random_pseudo_bytes($ivlen);
    $tag    = '';
    $enc    = openssl_encrypt(json_encode($body), $cipher, $key, OPENSSL_RAW_DATA, $iv, $tag);
    return base64_encode($iv . $enc . $tag);
}

/**
 * 计算签名
 *
 * @param array       $body_data    加密后的请求体形如 ['data'=>...]
 * @param array       $query_params GET 参数数组
 * @param string      $secret_key
 * @param array       $headerParams 系统头参数 ['appId'=>..,'bizId'=>..,'timestamps'=>..]
 * @return string
 */
function getSign($body_data, array $query_params, string $secret_key, array $headerParams): string
{
    $encrypted_body = !empty($body_data) ? json_encode($body_data) : '';
    $final_params   = array_merge($headerParams, $query_params);
    ksort($final_params);

    $str = '';
    foreach ($final_params as $k => $v) {
        $str .= $k . $v;
    }
    $str .= $encrypted_body;
    $str = $secret_key . $str;
    return hash('sha256', $str);
}

/**
 * 简易 cURL 请求封装
 *
 * @param string $url
 * @param int    $timeout
 * @param string $method  'get'|'post'
 * @param array  $body
 * @param array  $headers
 * @return string
 */
function reqCurl(string $url, int $timeout, string $method, array $body, array $headers): string
{
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

    if (strtolower($method) === 'post') {
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($body));
    }

    $resp = curl_exec($ch);
    curl_close($ch);
    return $resp ?: '';
}