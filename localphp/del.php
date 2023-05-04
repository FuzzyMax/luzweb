<?php
$path = $_SERVER['DOCUMENT_ROOT'] . '/in/';
set_include_path(get_include_path() . PATH_SEPARATOR . $path);
ini_set('display_errors', '1');
$tables = array(
	'buch' => 'books', 'zitat' => 'zitate', 'link' => 'merkzettel',
	'link2' => 'nina', 'notiz' => 'notizen', 'snippet' => 'snippets',
    'chat' => 'chat'
);
$auth = trim(strip_tags($_GET['auth']));
$auth = explode('//',$auth);
$nic = $auth[1];

require_once('../in/user.php');
$u = new user($nic, $auth[0]);
$auth = $u->is_loggedin();

if (!$auth) {
    die('NO DATA AVAILABLE !');
}

$entity = trim($_GET['entityname']);
$id = intval($_GET['id']);

if (!isset($tables[$entity])) {
    echo 'Es ist ein Problem aufgetreten!!';
    exit;
}

if ($id < 1) {
    die('PROBLEM OCCURED');
}

$table = $tables[$entity];

require_once('entity.db.php');
$e = new entity($u, $table);
$erg = $e->delete_byID ($id);

$result = [];
$result['id'] = $id;

echo json_encode($result);