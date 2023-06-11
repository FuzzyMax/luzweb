<?php
require_once('util.inc.php');

$db = new util();


$table = 'verbrauch';
$verbr = trim(strip_tags($_POST['verbr']));
$km = trim(strip_tags($_POST['km']));

$km = str_replace ( ',' , '.' , $km );
$verbr = str_replace ( ',' , '.' , $verbr );

if (intval($verbr) < 1) {
	echo 'Fehlende Daten';
	exit;
}
if (intval($km) < 1) {
	echo 'Fehlende Daten km';
	exit;
}

insertTanken($verbr,$km);

$sql = 'SELECT SUM(verbr) AS v, SUM(km) AS k FROM verbrauch';
$result = $GLOBALS['db']->db->fetchAll($sql);
$erg = $verbr / $km * 100;
echo 'Verbrauch aktuell : ';
printf("%.2f", round($erg,2));
echo " l/100km<hr>";
echo 'Verbrauch gesamt : ';
foreach ($result AS $res) {
	$erg = $res['v'] / $res['k'] * 100;
	printf("%.2f", round($erg,2));
	echo " l/100km<hr>";
	exit;
}


function insertTanken($v,$k)
{
	global $db;
	$data = array(
		'verbr'  => $v,
		'km' => $k,
		'datum' => date('Y-m-d')
	);
	$db->db->insert('verbrauch', $data);
}

