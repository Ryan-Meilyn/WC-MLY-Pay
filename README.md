# WooCommerce MLY 支付插件

为 WordPress 的 WooCommerce 提供 YPay / 易支付平台的扫码支付支持，兼容支付宝、微信等支付通道。

- 博客文章 (https://www.meily.top/2025/05/1731/)
- 插件文档 (https://www.meily.top/docs/meily-pay/)

## ✨ 插件特色

- 🔗 支持易支付、YPay 等平台标准接口  
- 💳 支持 WooCommerce 订单支付流程  
- ⚙️ 提供独立后台设置界面，可配置商户信息与回调地址  
- 🔁 支持异步（notify_url）+ 同步（return_url）双向回调  
- 🌐 完全前后端分离，适配大多数主题  
- 🧩 可移植结构，适合二次开发  


## 📂 插件结构
```text
wc-mlypay/
├── includes/
│   ├── class-wc-gateway-mlypay.php     # 主支付网关逻辑
│   ├── notify_url.php                  # 异步通知地址
│   └── return_url.php                  # 同步跳转地址
├── templates/
│   └── page-mlypay-redirect.php        # 跳转页模板
├── wc-mlypay.php                       # 插件主文件
└── README.md
```


## 🛠️ 安装方法

1. 下载本项目 zip 压缩包  
2. 上传至 WordPress 插件目录 `/wp-content/plugins/`  
3. 后台启用插件  
4. 前往 `WooCommerce > 设置 > 支付` 启用 “MLY支付”  
5. 配置以下参数：

| 参数名称       | 说明                                                         |
|----------------|--------------------------------------------------------------|
| 商户ID（pid）   | 易支付平台提供的商户号                                        |
| 商户密钥（key） | 易支付后台获取的密钥                                          |
| 接口地址       | 支付接口根路径，如：https://pay.xxx.com                      |
| 同步跳转地址   | 默认可使用插件提供路径，也可自定义                            |
| 异步通知地址   | 建议设置为：https://yoursite.com/wp-content/plugins/wc-mlypay/includes/notify_url.php |

## ✅ 已测试成功版本

- WordPress 6.8.1
- WooCommerce 9.8.5（测试的最新版）
- PHP 8.1

## ⚠️ 注意事项

- 本插件为表单跳转支付（扫码），不支持 WooCommerce Blocks 新版结账页（如需支持请联系开发者）  
- 保证网站启用 HTTPS 以确保兼容性  
- 插件默认使用 GET 回调方式（符合易支付多数兼容系统）  

## 📄 License

MIT License
