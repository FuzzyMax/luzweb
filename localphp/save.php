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
else {
    $table = trim(strip_tags($_POST['table']));
    $desc = trim(strip_tags($_POST['desc']));
    $inh = trim(strip_tags($_POST['inhalt']));
    if (isset($_POST['seite'])) {
        $seite = trim(substr($_POST['seite'], 0, 14));
    }
}
$table = $tables[$table];

switch ($table) {
    case 'books':
        $erg = insertBook($desc, $inh);
        die($erg);
        break;
    case 'zitate':
        insertZitat($desc, $inh, $seite);
        break;
    case 'merkzettel':
        $erg = insertMerkzettel($desc, $inh);
        die($erg);
        break;
    case 'nina':
        $erg = insertNina($desc, $inh, $seite);
        die($erg);
        break;
    case 'snippets':
        $erg = insertSnippet($desc, $inh);
        die($erg);
        break;
    case 'notizen':
        if ($seite < ' ') {
            $seite = 'Allgemeines';
        }
        $erg = insertNotiz($seite, $desc, $inh, $nic);
        die($erg);
        break;
    case 'chat':
            $erg = insertChat($nic, $inh, $desc);
            die($erg);
            break;
    default:
        echo '??Unknown table ' . $table;
}

echo 'OK ' . $table;


function insertBook($d, $i)
{
    $d = str_replace('-', '', $d); //ISBN ?
    $sql = "INSERT INTO books 
            (b_author, b_titel, b_verlag, b_isbn, b_bemerk, b_link)
            VALUES (?, ?, ?, ?, ?, ?);";
    if ((strlen($d) == 13) or (strlen($d) == 10)) {
        $data = getBookInfo($d);
        if ($data === false) {
            $data = getBookFromGoogle($d);
        }
        $data['b_bemerk'] .= '<br>' . $i;

        if ($data === false) {
            $data = array( $i, '', '', $d, $i, '' );
        }
        else {
            $data = array( $data['b_author'], $data['b_titel'], $data['b_verlag'], $data['b_isbn'], $data['b_bemerk'], $data['b_link'] );
        }
    } else {
        $data = array( $d, $i, '', '', '', '' );
    }
    $erg = $GLOBALS['u']->dbIns($sql, $data, false);
    if ($erg === false) {
        die('Problem occured !');
    }
    else {
        $data['b_id'] = $erg;
        $data['entityName'] = 'book';
        return json_encode($data);
    }
}

function getBookFromGoogle($isbn)
{
    $url = 'https://www.googleapis.com/books/v1/volumes?q=' . $isbn;
    $erg = file_get_contents($url);
    if ($erg === false) {
        return false;
    }
    $erg = json_decode($erg);
    if ((int)$erg->totalItems > 0) {
        $data = array(
            'b_author' => implode(',', $erg->items[0]->volumeInfo->authors),
            'b_titel'  => $erg->items[0]->volumeInfo->title . '<br>' . $erg->items[0]->volumeInfo->subtitle,
            'b_verlag' => $erg->items[0]->volumeInfo->publisher,
            'b_isbn' => $isbn,
            'b_bemerk' => $erg->items[0]->volumeInfo->description,
            'b_link' => $url
        );
    }
    return $data;
}

function getBookInfo($isbn)
{
    $url = 'https://portal.dnb.de/opac.htm?method=simpleSearch&query=' . $isbn;
    $url = 'https://portal.dnb.de/opac.htm?method=showFullRecord&currentResultId=%22' . $isbn . '%22%26any&currentPosition=0';
    $erg = file_get_contents($url);
    if ($erg === false) return getBookFromGoogle($isbn);
    $pstart = strpos($erg, '<table id="fullRecordTable"');
    if ($pstart === false) return getBookFromGoogle($isbn);
    $pende = strpos($erg, '</table>', $pstart) + 8;
    $erg = substr($erg, $pstart, $pende - $pstart);
    $pstart = strpos($erg, '<strong>Titel</strong>');
    $pstart = strpos($erg, '<td', $pstart);
    $pende = strpos($erg, '</td>', $pstart);
    $titel = trim(strip_tags(substr($erg, $pstart, $pende - $pstart)));
    $pstart = strpos($erg, '<strong>Person(en)</strong>');
    $pstart = strpos($erg, '<td', $pstart);
    $pende = strpos($erg, '</td>', $pstart);
    $autor = trim(strip_tags(substr($erg, $pstart, $pende - $pstart)));
    $pstart = strpos($erg, '<strong>Verlag</strong>');
    $pstart = strpos($erg, '<td', $pstart);
    $pende = strpos($erg, '</td>', $pstart);
    $verlag = trim(strip_tags(substr($erg, $pstart, $pende - $pstart)));
    $data = array(
        'b_author' => $autor,
        'b_titel'  => $titel,
        'b_verlag' => $verlag,
        'b_isbn' => $isbn,
        'b_bemerk' => '',
        'b_link' => $url,
    );
    return $data;
}

function insertZitat($d, $i, $s = 0)
{
    require_once('../in/zitate.db.php');
    $z = new zitate($GLOBALS['u']);
    $z->insert($d, $i, $s);
}

function insertSnippet($d, $i)
{
    $data = array(
        $d,
        $i,
    );

    $sql = "INSERT INTO snippets (s_descr, s_inhalt) VALUES (?, ?);";
    $erg = $GLOBALS['u']->dbIns($sql, $data, false);
    if ($erg === false) {
        die('Problem occured !');
    }
    else {
        $data['s_id'] = $erg;
        $data['entityName'] = 'snippet';
        return json_encode($data);
    }
}

function insertNotiz($b, $h, $i, $nic)
{
    $data = array(
        $b,
        $h,
        $i,
        $nic,
    );

    $sql = "INSERT INTO notizen (notiz_bereich, notiz_kopf, notiz_inhalt, nic) VALUES (?, ?, ?, ?);";
    $erg = $GLOBALS['u']->dbIns($sql, $data, false);
    if ($erg === false) {
        die('Problem occured !');
    }
    else {
        $data['notiz_id'] = $erg;
        $data['notiz_kopf'] = $h;
        $data['notiz_inhalt'] = $i;
        $data['entityName'] = 'notiz';
        return json_encode($data);
    }
}

function insertMerkzettel($d, $i)
{
    $data = array(
        $d,
        $i,
    );
    try {
        $sql = "INSERT INTO merkzettel (mz_short, mz_txt) VALUES (?, ?);";
        $erg = $GLOBALS['u']->dbIns($sql, $data, false);
        if ($erg === false) {
            die('Problem occured !');
        }
        else {
            $data['mz_id'] = $erg;
            $data['mz_short'] = $d;
            $data['mz_txt'] = $i;
            $data['entityName'] = 'link';
            return json_encode($data);
        }
    } catch (Exception $e) {
        echo $e->getMessage();
    }
}

function insertNina($d, $i, $s)
{
    $data = array(
        'n_short' => $d,
        'n_txt'   => $i,
        'n_hash'  => $s,
    );
    require_once('entity.db.php');
    $e = new entity($GLOBALS['u'], 'nina');
    $erg = $e->insert($data);
    if ($erg === false) {
        die('Problem occured !');
    }
    else {
        $data['n_id'] = $erg;
        $data['n_short'] = $d;
        $data['n_txt'] = $i;
        $data['entityName'] = 'link2';
        return json_encode($data);
    }
    return;

    $data = array(
        $d,
        $i,
        $s,
    );
    try {
        $sql = "INSERT INTO nina (n_short, n_txt, n_hash) VALUES (?, ?, ?);";
        $GLOBALS['u']->dbIns($sql, $data, false);
    } catch (Exception $e) {
        echo $e->getMessage();
    }
}


function insertChat($from, $content, $to )
{
    if ($to < ' ') {
        $to = 'all';
    }
    $data = array(
        'creator_nic' => $from,
        'content'     => $content,
        'target'      => $to,
    );
    require_once('entity.db.php');
    $e = new entity($GLOBALS['u'], 'chat');
    $result = $e->insert($data);
    if ($result === false) {
        die('Problem occured chat!');
    }
    else {
        $data['id'] = $result;
        $data['content'] = $content;
        $data['target'] = $to;
        $data['creator_nic'] = $from;
        $data['entityName'] = 'chat';
        return json_encode($data);
    }
}