<?php
define('SITE_FRONTEND_URL', ''); // Со слешем на конце
define('SITE_BACKEND_URL', ''); // Со слешем на конце (только домен, без подкаталога)

define('DB_HOST', '');
define('DB_NAME', '');
define('DB_USER', '');
define('DB_PASSWORD', '');
define('DB_PREFIX', '');
define('DB_CHARSET', 'UTF8');
define('TIMEZONE_OFFSET', '+07:00');
define('TIMEZONE_LOCATION', 'Asia/Novokuznetsk');

define('SEND_MAIL_METHOD', 'MAIL');
define('MAIL_FROM', '');
define('MAIL_FROM_NAME', '');
define('SMTP_HOST', 'smtp.mail.ru');
define('SMTP_PORT', 465);
define('SMTP_PASSWORD', '');
define('SMTP_SECURE', 'ssl'); // 'ssl', 'tls', false
define('SMTP_VERIFY_HOST', false);

define('JWT_SECRET_KEY', '');
define('JWT_REFRESH_TOKEN_EXPIRE', 60 * 60 * 24 * 30);
define('JWT_TOKEN_EXPIRE', 60 * 5);

define('RESET_PASSWORD_EXPIRE', 60 * 60 * 24);

define('ALLOWED_HTTP_ORIGINS', ['http://localhost:4200']);
