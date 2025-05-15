<?php
/**
 * Plugin Name: WooCommerce MLY支付插件
 * Description: 为 WooCommerce 添加 YPay 和码支付的扫码支付方式，插件名 wc-mlypay。
 * Version: 1.0.0
 * Author: 又见梅林
 */

if (!defined('ABSPATH')) {
    exit;
}

// 设置插件路径常量（供后续使用）
define('WC_MLYPAY_PLUGIN_URL', plugin_dir_url(__FILE__));
define('WC_MLYPAY_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('WC_MLYPAY_TEMPLATE_PATH', WC_MLYPAY_PLUGIN_DIR . 'templates/');

// 注册支付网关类
add_filter('woocommerce_payment_gateways', 'add_mlypay_gateway_class');
function add_mlypay_gateway_class($gateways) {
    $gateways[] = 'WC_Gateway_Mlypay';
    return $gateways;
}

// 加载支付网关类文件
add_action('plugins_loaded', 'init_mlypay_gateway_class');
function init_mlypay_gateway_class() {
    foreach (glob(WC_MLYPAY_PLUGIN_DIR . 'includes/class-wc-gateway-*.php') as $file) {
        require_once $file;
    }
}

//注册伪静态路由
add_action('init', 'wc_mlypay_register_redirect_route');
function wc_mlypay_register_redirect_route() {
    add_rewrite_rule(
        '^mlypay-redirect/?',
        'index.php?mlypay_redirect=1',
        'top'
    );
    add_rewrite_tag('%mlypay_redirect%', '1');
}

//处理页面渲染逻辑（使用插件内模板）
add_action('template_redirect', 'wc_mlypay_handle_redirect_page');
function wc_mlypay_handle_redirect_page() {
    if (get_query_var('mlypay_redirect') == '1') {
        include plugin_dir_path(__FILE__) . 'templates/page-mlypay-redirect.php';
        exit;
    }
}

register_activation_hook(__FILE__, 'wc_mlypay_flush_rewrite');
function wc_mlypay_flush_rewrite() {
    wc_mlypay_register_redirect_route();
    flush_rewrite_rules();
}


// 可选：后台提示易支付回调地址
add_action('admin_notices', function () {
    if (!class_exists('WooCommerce')) return;
    ?>
    <div class="notice notice-info is-dismissible">
        <p><strong>WooCommerce MLY支付插件已启用。</strong> 请将以下地址填写至你的易支付后台：</p>
        <ul>
            <li>异步通知地址：<code><?php echo home_url('/wp-content/plugins/wc-mlypay/includes/notify_url.php'); ?></code></li>
            <li>同步跳转地址：<code><?php echo home_url('/wp-content/plugins/wc-mlypay/includes/return_url.php'); ?></code></li>
        </ul>
    </div>
    <?php
});
