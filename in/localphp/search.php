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

$entityBody = file_get_contents('php://input');
$jsonData = json_decode($entityBody);

if ($jsonData->table) {
    $table = trim(strip_tags($jsonData->table));
    $desc = trim(strip_tags($jsonData->desc));
    $inh = trim(strip_tags($jsonData->inhalt));
    $seite = trim(strip_tags($jsonData->seite));
}
else {  //Sollte nicht mehr benutzt werden
    $table = trim(strip_tags($_POST['table']));
    $desc = trim(strip_tags($_POST['desc']));
    $inh = trim(strip_tags($_POST['inhalt']));
    if (isset($_POST['seite'])) {
        $seite = trim(substr($_POST['seite'], 0, 14));
    }
}
$table = $tables[$table];