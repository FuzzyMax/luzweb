<?php
ini_set('session.cookie_secure', "1");
ini_set('session.cookie_httponly', "1");
ini_set('session.cookie_samesite','None');
define('PROGNAME','LUZWEB');
define('DOCROOT',$_SERVER['DOCUMENT_ROOT']);
define('INCL',DOCROOT.DIRECTORY_SEPARATOR.'in'.DIRECTORY_SEPARATOR);
set_include_path(get_include_path() . PATH_SEPARATOR . INCL);
$tables = array(
	'buch' => 'books', 'zitat' => 'zitate', 'link' => 'merkzettel',
	'link2' => 'nina', 'notiz' => 'notizen', 'snippet' => 'snippets',
    'chat' => 'chat'
);
require_once('../in/user.php');

if (!defined('PROGNAME')) {
	die('DEPRECATED');
}
define('APPSTATUS', 'dev'); #prod steht für live-Betrieb
define('APPKEY', '__MAzzy§=<:');
define('CRYPT_BASICKEY', 'MaXFuZZy.§&');


if (APPSTATUS === 'PROD') {
    ini_set('display_errors', '0');
}
else {
    ini_set('display_errors', '1');
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
