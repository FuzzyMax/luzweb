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

if (isset($_GET['downloads'])) {
    $key = $_GET['downloads'];
    $message = 'LUZ' . date('d');
    $key2 = hash('sha256', $message, false);
    if ($key === $key2) {
        echo showDownloads();
    } else {
        echo 'MIST : ';
        echo $message;
    }
    exit;
}

$table = trim($_GET['table']);

if (!isset($tables[$table])) {
    echo 'Es ist ein Problem aufgetreten!!';
    exit;
}

$table = $tables[$table];

switch ($table) {
    case 'books':
        showBooks();
        break;
    case 'zitate':
        showZitate();
        break;
    case 'merkzettel':
        if ($auth === true) {
            showMerkzettel();
        }
        break;
    case 'nina':
        if ($auth === true) {
            showNina();
        }
        break;
    case 'snippets':
        showSnippet();
        break;
    case 'notizen':
        showNotiz();
        break;
    case 'chat':
        showChat($nic);
        break;
    default:
        echo '??Unknown table ' . $table;
}

function showBooks()
{
    $sql = 'SELECT * FROM books ORDER BY b_id DESC';
    $result = $GLOBALS['u']->dbSelArray($sql);
    echo json_encode($result);
}

function showZitate()
{
    $sql = 'SELECT z_id, z_quelle, z_inhalt, z_seite, b_titel, b_author
            FROM zitate
            LEFT JOIN books ON z_quelle = b_isbn
            ORDER BY b_id DESC, z_quelle, z_id;';
    $result = $GLOBALS['u']->dbSelArray($sql);
    foreach ($result as $res) {
        #$res['z_inhalt'] = nl2br($res['z_inhalt']);
        if ($res['b_titel']) {
            $q = $res['b_titel'] . '-' . $res['b_author'] . '-Seite ' . $res['z_seite'];
        } else $q = $res['z_quelle'];
        echo "<span style='color: red'>{$q}</span><div> ";
        $tog = 'onclick="$(\'#z' . $res['z_id'] . '\').toggle();"';
        $onchg = "onchange='chgzitat({$res['z_id']},this.value)'";
        echo "<span $tog>Details</span>";
        echo "<div class='detail' style='display:none' id='z{$res['z_id']}'><textarea $onchg>{$res['z_inhalt']}</textarea></div></div><hr>";
    }
}

function showMerkzettel()
{
    $sql = 'SELECT * FROM merkzettel ORDER BY mz_id DESC;';
    $result = $GLOBALS['u']->dbSelArray($sql);
    foreach ($result as $res) {
        if (substr($res['mz_txt'], 0, 7) == 'http://' or substr($res['mz_txt'], 0, 8) == 'https://') {
            echo "<a href='{$res['mz_txt']}' target='_blank'>{$res['mz_short']}</a><hr>";
        } else {
            echo "<strong>{$res['mz_short']}<strong><br><textarea onchange='chgmerk(\"{$res['mz_id']}\",this.value);'>{$res['mz_txt']}</textarea><hr width='50%'>";
        }
    }
}

function showNina()
{
    $sql = 'SELECT * FROM nina ORDER BY n_id DESC;';
    $result = $GLOBALS['u']->dbSelArray($sql);
    foreach ($result as $res) {
        if (substr($res['n_txt'], 0, 7) == 'http://' or substr($res['n_txt'], 0, 8) == 'https://') {
            echo "<a href='{$res['n_txt']}' target='_blank'>{$res['n_short']}</a><hr>";
        } else {
            echo "<strong>{$res['n_short']}<strong><br><textarea onchange='chgnina(\"{$res['n_id']}\",this.value);'>{$res['n_txt']}</textarea><hr width='50%'>";
        }
    }
}

function showSnippet()
{
    $sql = 'SELECT * FROM snippets ORDER BY s_id DESC;';
    $result = $GLOBALS['u']->dbSelArray($sql);
    echo json_encode($result);
}

function showNotiz()
{
    $sql = 'SELECT * FROM notizen ORDER BY notiz_id DESC;';
    $result = $GLOBALS['u']->dbSelArray($sql);
    foreach ($result as $res) {
        echo "<strong>{$res['notiz_kopf']}<strong><br><textarea onchange='chgnotiz(\"{$res['notiz_id']}\",this.value);'>{$res['notiz_inhalt']}</textarea><br>";
    }
}

function showChat($nic)
{
    require_once('entity.db.php');
    $e = new entity($GLOBALS['u'], 'chat');
    $e->set_fieldlist(array('creator_nic','content'));
    $where = " target = 'all' or target = '$nic' ";
    $e->set_orderby('id');
    $result = $e->get_list($where);

    echo '<div>';
    foreach ($result as $res) {
        echo "<div class='chatfrom'>{$res['creator_nic']} :</div><div class='chat'>{$res['content']}</div>";
    }
    echo '</div>';
}

function showDownloads()
{
    $erg = "Verzeichnisinhalt:<br/>";
    $pfad = $_SERVER["DOCUMENT_ROOT"] . "/downloads";
    $handle=opendir ($pfad);
    while ($datei = readdir ($handle)) {
        if(!is_dir($datei)) {
            $link = '/downloads/'.$datei;
            $erg.= "<a href='$link' target='extern'>$datei</a><br/>";
        };
    }
    closedir($handle);
    return $erg;
}
