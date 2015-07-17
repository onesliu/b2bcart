<?php
// HTTP
//define('MY_DOMAIN', 'localhost/opencart');
define('MY_DOMAIN', '192.168.1.4/b2bcart');
define('HTTP_SERVER', 'http://'.MY_DOMAIN.'/');

// HTTPS
define('HTTPS_SERVER', 'http://'.MY_DOMAIN.'/');

// DIR
define('DIR_PREFIX', 'D:\\PHPnow\\htdocs\\b2bcart\\');
define('DIR_APPLICATION', DIR_PREFIX.'/catalog/');
define('DIR_SYSTEM', DIR_PREFIX.'/system/');
define('DIR_DATABASE', DIR_PREFIX.'/system/database/');
define('DIR_LANGUAGE', DIR_PREFIX.'/catalog/language/');
define('DIR_TEMPLATE', DIR_PREFIX.'/catalog/view/theme/');
define('DIR_CONFIG', DIR_PREFIX.'/system/config/');
define('DIR_IMAGE', DIR_PREFIX.'/image/');
define('DIR_CACHE', DIR_PREFIX.'/system/cache/');
define('DIR_DOWNLOAD', DIR_PREFIX.'/download/');
define('DIR_LOGS', DIR_PREFIX.'/system/logs/');

// DB
define('DB_DRIVER', 'mysql');
define('DB_HOSTNAME', 'localhost');
define('DB_USERNAME', 'root');
define('DB_PASSWORD', 'nb33l2l3');
define('DB_DATABASE', 'b2bcart');
define('DB_PREFIX', 'oc_');

//Weixin
define('WEIXIN_USERPWD', '123456');
?>
