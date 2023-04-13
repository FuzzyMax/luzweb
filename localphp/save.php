<?php
//$path = $_SERVER['DOCUMENT_ROOT'] . '/phpincl/';
//set_include_path(get_include_path() . PATH_SEPARATOR . $path);
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

$table = trim(strip_tags($_POST['table']));
$table = $tables[$table];
$desc = trim(strip_tags($_POST['desc']));
$inh = trim(strip_tags($_POST['inhalt']));
if (isset($_POST['seite'])) {
    $seite = trim(substr($_POST['seite'], 0, 14));
}
else {
    $seite = '';
}

switch ($table) {
    case 'books':
        insertBook($desc, $inh);
        break;
    case 'zitate':
        insertZitat($desc, $inh, $seite);
        break;
    case 'merkzettel':
        insertMerkzettel($desc, $inh);
        break;
    case 'nina':
        insertNina($desc, $inh, $seite);
        break;
    case 'snippets':
        insertSnippet($desc, $inh);
        break;
    case 'notizen':
        $seite = trim(substr($_POST['seite'], 0, 32));
        if ($seite < ' ') {
            $seite = 'Allgemeines';
        }
        insertNotiz($seite, $desc, $inh);
        break;
    case 'chat':
            insertChat($nic, $inh, $desc);
            break;
    default:
        echo '??Unknown table ' . $table;
}

echo 'OK ' . $table;


function insertBook($d, $i)
{
    $d = str_replace('-', '', $d);
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
    $GLOBALS['u']->dbIns($sql, $data, false);
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
    $GLOBALS['u']->dbIns($sql, $data, false);
}

function insertNotiz($b, $h, $i)
{
    $data = array(
        $b,
        $h,
        $i,
    );

    $sql = "INSERT INTO notizen (notiz_bereich, notiz_kopf, notiz_inhalt) VALUES (?, ?, ?);";
    $GLOBALS['u']->dbIns($sql, $data, false);
}

function insertMerkzettel($d, $i)
{
    $data = array(
        $d,
        $i,
    );
    try {
        $sql = "INSERT INTO merkzettel (mz_short, mz_txt) VALUES (?, ?);";
        $GLOBALS['u']->dbIns($sql, $data, false);
    } catch (Exception $e) {
        echo $e->getMessage();
    }
}

function insertNina($d, $i, $s)
{
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


function insertChat($from, $content, $to = 'ALL' )
{
    $data = array(
        $from,
        $content,
        $to,
    );
    try {
        $sql = "INSERT INTO chat (creator_nic, content, target) VALUES (?, ?, ?);";
        $GLOBALS['u']->dbIns($sql, $data, false);
    } catch (Exception $e) {
        echo $e->getMessage();
    }
}