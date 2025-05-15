<?php
if (!defined('ABSPATH')) {
    exit;
}

class WC_Gateway_Mlypay extends WC_Payment_Gateway {

    public function __construct() {
        $this->id                 = 'mlypay';
        $this->icon               = ''; // 可添加图标 URL
        $this->has_fields         = false;
        $this->method_title       = 'MLY支付';
        $this->method_description = '通过Ypay、易支付等支付平台（支付宝、微信等）完成扫码付款';

        // 支持的功能
        $this->supports = [
            'products'
        ];

        // 初始化表单字段
        $this->init_form_fields();
        $this->init_settings();

        // 获取后台设置
        $this->title        = $this->get_option('title');
        $this->description  = $this->get_option('description');
        $this->pid          = $this->get_option('pid');
        $this->key          = $this->get_option('key');
        $this->apiurl       = rtrim($this->get_option('apiurl'), '/') . '/';
        $this->return_url   = $this->get_option('return_url');
        $this->notify_url   = $this->get_option('notify_url');

        // 保存设置
        add_action('woocommerce_update_options_payment_gateways_' . $this->id, [$this, 'process_admin_options']);
    }

    public function is_available() {
    return parent::is_available()
        && $this->enabled === 'yes'
        && $this->pid
        && $this->key
        && $this->apiurl;
}

    public function init_form_fields() {
        $this->form_fields = [
            'enabled' => [
                'title'   => '启用/禁用',
                'type'    => 'checkbox',
                'label'   => '启用易支付网关',
                'default' => 'yes'
            ],
            'title' => [
                'title'       => '支付名称',
                'type'        => 'text',
                'description' => '在结账页显示的名称',
                'default'     => '支付宝 / 易支付扫码',
                'desc_tip'    => true,
            ],
            'description' => [
                'title'       => '描述',
                'type'        => 'textarea',
                'description' => '可选：在结账页显示的附加描述',
                'default'     => '通过支付宝/微信扫码支付完成订单',
            ],
            'pid' => [
                'title'       => '商户 ID (pid)',
                'type'        => 'text',
                'description' => '你的易支付平台商户 ID',
                'default'     => '',
            ],
            'key' => [
                'title'       => '商户密钥 (key)',
                'type'        => 'text',
                'description' => '你的易支付平台商户密钥',
                'default'     => '',
            ],
            'apiurl' => [
                'title'       => '接口地址',
                'type'        => 'text',
                'description' => '易支付平台的接口根地址，如：https://pay.xxx.com',
                'default'     => '',
            ],
            'return_url' => [
                'title'       => '同步跳转地址 (return_url)',
                'type'        => 'text',
                'description' => '用户付款完成后的跳转地址，一般为：' . home_url('/wp-content/plugins/wc-mlypay/includes/return_url.php'),
                'default'     => home_url('/wp-content/plugins/wc-mlypay/includes/return_url.php'),
            ],
            'notify_url' => [
               'title'       => '异步回调地址 (notify_url)',
               'type'        => 'text',
               'description' => '支付成功后的服务器异步通知地址，一般为：' . home_url('/wp-content/plugins/wc-mlypay/includes/notify_url.php'),
               'default'     => home_url('/wp-content/plugins/wc-mlypay/includes/notify_url.php'),
            ],
        ];
    }

    public function process_payment($order_id) {
        $order = wc_get_order($order_id);

        // 构造参数
        $params = [
    'pid'           => $this->pid,
    'type'          => 'alipay',
    'out_trade_no'  => $order->get_order_number(),
    'notify_url'    => $this->notify_url,
    'return_url'    => $this->return_url,
    'name'          => '订单号：' . $order->get_order_number(), // 中文冒号！
    'money'         => $order->get_total(),
    'sign_type'     => 'MD5'
];



        // 签名
        $params['sign'] = $this->generate_sign($params, $this->key);

        file_put_contents(ABSPATH . 'easypay-sign-final.log', "最终带入表单的签名：" . $params['sign'] . "\n", FILE_APPEND);

        // 自动跳转构造 HTML 表单
        $form = '<form id="easypay_submit" action="' . esc_url($this->apiurl . 'submit.php') . '" method="post">';
        foreach ($params as $key => $val) {
            $form .= '<input type="hidden" name="' . esc_attr($key) . '" value="' . esc_attr($val) . '">';

        }
        $form .= '</form>';
        $form .= '<script>document.getElementById("easypay_submit").submit();</script>';

        return [
    'result'   => 'success',
    'redirect' => home_url('/mlypay-redirect/?html=' . rawurlencode(base64_encode($form)))

];
    }

    // 签名函数
    private function generate_sign($data, $key) {
    unset($data['sign'], $data['sign_type']);
    ksort($data);

    $signStr = '';
    foreach ($data as $k => $v) {
        if ($v !== '') {
            $signStr .= $k . '=' . $v . '&';
        }
    }

    $signStr = rtrim($signStr, '&') . $key;


file_put_contents(ABSPATH . 'easypay-sign-debug.log', "本地拼接字符串：$signStr\n本地签名：" . md5($signStr) . "\n\n", FILE_APPEND);


    return md5($signStr);
}



}