<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/wp-load.php';
require_once dirname(__FILE__) . '/class-wc-gateway-mlypay.php';

header('Content-Type: text/plain');

// 获取插件配置
$gateway = new WC_Gateway_Mlypay();
$key = $gateway->get_option('key');

// 获取 GET 数据
$data = $_GET;

// 签名验证函数（不加入 notify_url / return_url）
function check_sign($data, $key) {
    if (!isset($data['sign'])) return false;
    $client_sign = $data['sign'];
    unset($data['sign'], $data['sign_type']);

    $fields = ['money', 'name', 'out_trade_no', 'pid', 'trade_status', 'trade_no', 'type'];
    $filtered = [];
    foreach ($fields as $f) {
        if (isset($data[$f])) {
            $filtered[$f] = $data[$f];
        }
    }

    ksort($filtered);
    $str = '';
    foreach ($filtered as $k => $v) {
        $str .= $k . '=' . $v . '&';
    }
    $sign_str = rtrim($str, '&') . $key;
    $local_sign = md5($sign_str);

    file_put_contents(WP_CONTENT_DIR . '/uploads/easypay_return_debug.log',
        "==== 验签调试 ====\n签名字段: " . print_r($filtered, true) .
        "拼接字符串: {$str}\n最终参与签名字符串: {$sign_str}\n本地签名: {$local_sign}\n客户端签名: {$client_sign}\n\n",
        FILE_APPEND
    );

    return $client_sign === $local_sign;
}

// 验签失败
if (!check_sign($data, $key)) {
    wp_die('验签失败，请不要直接访问该页面。');
}

// 获取订单
$out_trade_no = $data['out_trade_no'] ?? '';
$order_id = absint($out_trade_no);
$order = wc_get_order($order_id);

if (!$order) {
    wp_die('订单不存在');
}

// 跳转到 WooCommerce 订单确认页
$order_key = $order->get_order_key();
$url = wc_get_endpoint_url('order-received', $order_id, wc_get_checkout_url());
$url = add_query_arg('key', $order_key, $url);

wp_safe_redirect($url);
exit;
