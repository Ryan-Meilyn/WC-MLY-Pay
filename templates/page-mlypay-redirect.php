<?php

if (!isset($_GET['html'])) {
    wp_die('缺少支付数据');
}

$form_html = base64_decode(rawurldecode($_GET['html']));

// 输出 HTML 页面并自动提交
?>
<!DOCTYPE html>
<html lang="zh">
<head>
  <meta charset="UTF-8">
  <title>正在跳转到收银台...</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <style>
    body {
      background: #f5f6fa;
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
      margin: 0;
      padding: 0;
      display: flex;
      justify-content: center;
      align-items: center;
      height: 100vh;
    }
    .card {
      background: white;
      padding: 30px 40px;
      border-radius: 16px;
      box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
      text-align: center;
      max-width: 400px;
    }
    .card h1 {
      font-size: 20px;
      color: #333;
    }
    
    
    @keyframes spin {
      to { transform: rotate(360deg); }
    }
    .fallback-button {
      margin-top: 20px;
      display: inline-block;
      background: #4a90e2;
      color: #fff;
      padding: 10px 20px;
      border-radius: 8px;
      text-decoration: none;
      font-size: 14px;
    }
  </style>
</head>
<body>
  <div class="card">
    
<img src="https://www.meily.top/wp-content/uploads/2025/05/logo.svg" style="height: 30px;margin: 20px;">

    <h1>请稍候，正在跳转到收银台</h1>
    <?php echo $form_html; ?>
    
  </div>

</body>
</html>
