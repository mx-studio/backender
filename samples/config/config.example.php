<?php
define('MODE', ''); // development or production
define('ABSPATH', __DIR__);
if (!file_exists(ABSPATH . '/config.' . MODE . '.php')) {
    throw new \Exception("Error: " . ABSPATH . "/config." . MODE . ".php doesn't exist. Use config example " . __DIR__ . '/config/config.' . MODE . '.sample.php');
}
require_once ABSPATH . '/config.' . MODE . '.php';
define('BACKEND_BASE_URL', '/');
define('TEMPLATE', 'default');
define('EMAIL_TEMPLATES_DIRECTORY', ABSPATH . '/views/emails/');
define('EMAIL_SIGNATURE', '<p>--</p><p>С уважением, служба поддержки ...</p>');

// Роли пользователей (ключ - системное имя роли, значение - отображаемое в интерфейсе имя)
define('USER_ROLES', [
    'admin' => 'Администратор',
    'user' => 'Пользователь',
]);
define('REGISTER_DEFAULT_USER_ROLE', 'user'); // Роль, задаваемая пользователю при регистрации
// Роли пользователей, которые может создавать пользователь в зависимости от его собственной роли
define('REGISTER_ALLOWED_ROLES', [
    'admin' => ['user'],
]);

define('FILES_UPLOAD_PATH', ABSPATH . '/upload/');

define('LOG_DIRECTORY', ABSPATH . '/logs/');
define('TMP_DIRECTORY', ABSPATH . '/tmp/');

define('DEFAULT_CONTROLLER', 'default');
define('DEFAULT_CONTROLLER_METHOD', 'default');

define('CLI_ACCESS', [
    'user_id' => 1,
    'roles' => ['superadmin'],
    'exp' => time() + 3600 * 24 * 1000,
]);
