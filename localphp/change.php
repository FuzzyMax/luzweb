<?php
ini_set('display_errors', '1');
$tables = array(
  'buch' => 'books', 'zitat' => 'zitate', 'link' => 'merkzettel',
  'link2' => 'nina', 'notiz' => 'notizen', 'snippet' => 'snippets',
  'chat' => 'chat'
);
$auth = trim(strip_tags($_POST['auth']));
$auth = explode('//', $auth);
$nic = $auth[1];

require_once('../in/user.php');
$u = new user($nic, $auth[0]);
$auth = $u->is_loggedin();

if (!$auth) {
  die('NO DATA AVAILABLE !');
}
$table = trim($_POST['table']);
$id = intval($_POST['id']);

if (!isset($tables[$table])) {
  echo 'Es ist ein Problem aufgetreten!!';
  exit;
}
$table = $tables[$table];

if ($id < 1) {
  die('Falsche ID');
}

switch ($table) {
  case 'books':
    #setBook($desc,$inh);
    break;
  case 'zitate':
    $txt = trim(strip_tags($_POST['txt']));
    setZitat($id, $txt);
    break;
  case 'merkzettel':
    $txt = trim(strip_tags($_POST['txt']));
    setMerkzettel($id, $txt);
    break;
  case 'nina':
    $txt = trim(strip_tags($_POST['txt']));
    setNina($id, $txt);
    break;
  case 'notizen':
    $txt = trim(strip_tags($_POST['txt']));
    setNotiz($id, $txt);
    break;
  case 'snippets':
    $txt = trim(strip_tags($_POST['txt']));
    setSnippet($id, $txt);
    break;
  default:
    echo '?????' . $table;
}

echo 'changeOK ' . $table . $id;


function setBook($d, $i)
{
  global $u;
}

function setZitat($id, $txt)
{
  global $u;
  if (strlen($txt) < 4) {
    return;
  }
  $data = array(
    $txt,
    $id
  );
  $sql = "UPDATE zitate SET z_inhalt = ? WHERE z_id = ?;";
  try {
    $u->dbUpdate($sql, $data);
  } catch (Exception $e) {
    echo $e->getMessage();
  }
}

function setMerkzettel($id, $txt)
{
  global $u;
  $data = array(
    'mz_txt' => $txt,
    'mz_id'  => $id
  );
  $sql = "UPDATE merkzettel SET mz_txt = ? WHERE mz_id = ?;";
  try {
    $u->dbUpdate($sql, $data);
  } catch (Exception $e) {
    echo $e->getMessage();
  };
}

function setNina($id, $txt)
{
  global $u;
  $data = array(
    'n_txt' => $txt,
    'n_id'  => $id
  );
  $sql = "UPDATE nina SET n_txt = ? WHERE n_id = ?;";
  try {
    $u->dbUpdate($sql, $data);
  } catch (Exception $e) {
    echo $e->getMessage();
  };
}

function setSnippet($id, $txt)
{
  global $u;
  if (strlen($txt) < 4) {
    return;
  }
  $data = array(
    $txt,
    $id
  );
  $sql = "UPDATE snippets SET s_inhalt = ? WHERE s_id = ?;";
  try {
    $u->dbUpdate($sql, $data);
  } catch (Exception $e) {
    echo $e->getMessage();
  };
}


function setNotiz($id, $txt)
{
  global $u;
  if (strlen($txt) < 4) {
    return;
  }
  $data = array(
    $txt,
    $id
  );
  $sql = "UPDATE notizen SET notiz_inhalt = ? WHERE notiz_id = ?;";
  try {
    $u->dbUpdate($sql, $data);
  } catch (Exception $e) {
    echo $e->getMessage();
  };
}
