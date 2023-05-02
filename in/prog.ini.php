<?php
if (!defined('PROGNAME')) {
	die('DEPRECATED');
}
define('APPSTATUS', 'dev'); #prod steht für live-Betrieb
define('APPKEY', '__MAzzy§=<:');
define('CRYPT_BASICKEY', 'MaXFuZZy.§&');


if (APPSTATUS === 'PROD') {
    ini_set('display_errors', '0');
}
header("Content-Type: text/html; charset=utf-8");

$db = array();
$db['host'] = 'localhost';
$db['port'] = '3306';
$db['dbname'] = 'luzeu';
$db['user'] = 'mysql';
$db['pw'] = 'mysql1234TEST';

$dsn = 'mysql:host='.$db['host'].';port='.$db['port'].';dbname='.$db['dbname'];
$options = array(
    PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8',
    PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION,
); 
try {
    $db['conn'] = new PDO($dsn, $db['user'], $db['pw'], $options);
} catch (PDOException $e) {
    echo 'System ERROR. Try later!';
    error_log($e->getMessage());
    exit;
}

#$db['conn']->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$_INI['db'] = $db;

unset($db);
