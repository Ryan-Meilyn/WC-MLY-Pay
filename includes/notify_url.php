<?php
// ✅ 引入 WordPress
require_once $_SERVER['DOCUMENT_ROOT'] . '/wp-load.php';

// ✅ 配置密钥（动态从插件获取也可手动写死）
require_once dirname(__FILE__) . '/class-wc-gateway-mlypay.php';
$gateway = new WC_Gateway_Mlypay();
$key = $gateway->get_option('key');

// ✅ 获取 GET 数据
$data = $_GET;

// ✅ 日志路径
$log_file = WP_CONTENT_DIR . '/uploads/easypay_notify_debug.log';
if (!file_exists(dirname($log_file))) {
    mkdir(dirname($log_file), 0755, true);
}

// ✅ 验证签名函数
function verify_notify_sign($data, $key) {
    if (!isset($data['sign'])) return false;
    $client_sign = $data['sign'];
    unset($data['sign'], $data['sign_type']);

    ksort($data);
    $signStr = '';
    foreach ($data as $k => $v) {
        if ($v !== '') {
            $signStr .= $k . '=' . $v . '&';
        }
    }
    $signStr = rtrim($signStr, '&');
    $server_sign = md5($signStr . $key);

    // ✅ 写入日志
    file_put_contents(WP_CONTENT_DIR . '/uploads/easypay_notify_debug.log',
        "==== Easypay Notify Debug ====\n" .
        "时间: " . date('c') . "\n" .
        "原始数据: " . print_r($_GET, true) .
        "参与签名字段: {$signStr}\n" .
        "本地签名: {$server_sign}\n" .
        "远程签名: {$client_sign}\n\n",
        FILE_APPEND
    );

    return $server_sign === $client_sign;
}

// ✅ 验签失败
if (!verify_notify_sign($data, $key)) {
    echo 'sign error';
    exit;
}

// ✅ 检查支付状态
if (!isset($data['trade_status']) || $data['trade_status'] !== 'TRADE_SUCCESS') {
    echo 'trade not success';
    exit;
}

// ✅ 获取订单对象
$order_id = isset($data['out_trade_no']) ? (int) $data['out_trade_no'] : 0;
$order = wc_get_order($order_id);

if (!$order) {
    echo 'order not found';
    exit;
}

// ✅ 避免重复标记
if ($order->get_status() !== 'completed') {
    $order->payment_complete();
    $order->add_order_note('Meily Pay：付款成功（异步通知确认）');
}

echo 'success';
exit;
