<?php
// config/mail.php

return [
    'host'       => 'smtp.gmail.com',
    'username'   => 'email_cua_nhom_ba@gmail.com', // 1. Điền chính xác Gmail dùng để gửi
    'password'   => 'abcd efgh ijkl mnop',        // 2. BẮT BUỘC dùng "Mật khẩu ứng dụng" (16 ký tự viết liền)
    'port'       => 587,
    'from_email' => 'email_cua_nhom_ba@gmail.com', // 3. Nên để trùng với username để tránh bị Gmail chặn
    'from_name'  => 'BeePay Wallet'
];