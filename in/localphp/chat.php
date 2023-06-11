<?php
require_once('util.inc.php');

$db = new util();

if (!isset($_POST['auth'])) {
  echo 'Es ist ein Problem aufgetreten.';
  exit;
}
if (!isset($_POST['action'])) {
    echo 'Es ist ein Problem aufgetreten.';
    exit;
}

$auth = trim(strip_tags($_POST['auth']));
$action = trim(strip_tags($_POST['action']));

if ($auth !== 'read' && $auth !== 'set') {
    echo 'Es ist ein Problem aufgetreten.';
    exit;
}

if ($auth !== 'DasIstSicherOder?//JaGanzBestimmt') {
  die('Es ist ein Problem aufgetreten. Bitte später versuchen!');
}

switch ($action) {
  case 'read':
    getChat();
    break;
  case 'set':
    setChat();
    break;
  default:
    echo '?????';
}

function setChat()
{
  global $db;
}

function getChat()
{
  global $db;
  $data = array(
    'mz_txt' => $txt,
  );
  try {
    $db->db->update('merkzettel', $data, 'mz_id = ' . $id);
  } catch (Exception $e) {
    echo $e->getMessage();
  }
}