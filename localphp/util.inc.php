<?php
$path = '/www/htdocs//w015a331/luz-web.eu/phpincl/';
$path = $_SERVER['DOCUMENT_ROOT'] . '/phpincl/';
set_include_path(get_include_path() . PATH_SEPARATOR . $path);
ini_set('display_errors', '1');
ini_set('session.cookie_secure', "1");
ini_set('session.cookie_httponly', "1");
ini_set('session.cookie_samesite','None');
$tables = array(
	'buch' => 'books', 'zitat' => 'zitate', 'link' => 'merkzettel',
	'link2' => 'nina', 'notiz' => 'notizen', 'snippet' => 'snippets'
);
require_once 'Zend/Db/Adapter/Pdo/Mysql.php';

/**
 *	Klasse, die der Appikation wichtige Funktionalitäten global verfügbar macht.
 *
 * 	Folgende Aufgaben übernimmt die Klasse :
 *   - logging
 * 	- DB Zugriffe
 *   - Mehrsprachigkeit
 *   - Mailversand
 *   - Rechteverwaltung/überprüfung
 *
 *	Dabei ist zu beachten, daß die Nutzinformationen in der Eigenschaft data steckt (diese Info steht auch in $_SESSION["userInfo"])
 *	@package user
 */

class util
{

	public $db = null;

	/**
	 * IP-Adresse des Users
	 * @var string
	 */
	private $ipAdr = null;

	/**
	 * development, production, testing ...
	 * @var string
	 */
	private $appstatus;


	/**
	 *	Konstruktor der Klasse.
	 *	Je nachdem ob eine Session existiert oder nicht werden die Userdaten aus der DB oder der Session geholt.
	 * 	Setzt $_SESSION["userInfo"] oder liest diese aus.
	 */
	function __construct()
	{
		include_once('config.inc.php');
		try {
			$this->db = new Zend_Db_Adapter_Pdo_Mysql($this->dbconf);
			$this->db->getConnection();
			$this->db->query("SET NAMES 'utf8';");
		} catch (Zend_Db_Adapter_Exception $e) {
			echo $e->__toString();
		} catch (Zend_Exception $e) {
			echo $e->__toString();
		}

		$this->ipAdr = $_SERVER['REMOTE_ADDR'];
		$this->appstatus = APPLICATION_ENVIRONMENT;
	}


	/**
	 *	db-Methode für Select-Abfragen.
	 *	@param string $q 
	 * @return Gibt Array von Objekten mit allen gefundenen Datensätzen zurück
	 */
	function sel($q)
	{
		try {
			$this->db->setFetchMode(Zend_Db::FETCH_OBJ);
			$e = $this->db->fetchAll($q);
			return $e;
		} catch (Exception $ex) {
			$this->error_out($ex->__toString());
			$this->errorLog($q);
		}
	}

	/**
	 *	db-Methode für Select-Abfragen.
	 *	@param string $q  
	 * return Gibt Array von Objekten mit dem ersten gefundenen Datensatz zurück
	 */
	function dbSelOne($q)
	{
		try {
			$this->db->setFetchMode(Zend_Db::FETCH_OBJ);
			$e = $this->db->fetchRow($q);
			return $e;
		} catch (Exception $ex) {
			$this->errorLog($q);
			$this->error_out($ex->__toString());
		}
	}

	function dbSelArray($q)
	{
		$this->db->setFetchMode(Zend_Db::FETCH_ASSOC);
		$d = $this->db->fetchRow($q);
		return $d;
	}

	/**
	 *	db-Methode für Insert-Anweisungen.
	 *	@param string $q
	 *	@param bool $log
	 *	@return mixed 
	 */
	function dbIns($q, $log = true)
	{
		try {
			$erg = $this->db->getConnection()->exec($q);
			if ($erg === false) return false;
			else $id = $this->db->lastInsertId();

			if ($log) $this->logEintrag($q, '');

			if ($id) return $id;
			else return true;
		} catch (Exception $ex) {
			$this->errorLog($q);
			$this->error_out($ex->__toString());
		}
	}

	/**
	 *	db-Methode für Updateanweisungen.
	 *	über diese Methode sollte evtl. jede Art von Logging laufen ?!
	 * @return int Gibt Anzahl der betroffenen DS zurück. 0 wenn nichts geändert wird.
	 */
	function dbUpdate($q)
	{
		try {
			return $this->db->getConnection()->exec($q);
		} catch (Exception $ex) {
			$this->errorLog($q);
			$this->error_out($ex->__toString());
		}
	}

	/**
	 *	db-Methode für Delete-Befehle.
	 *	über diese Methode sollte evtl. jede Art von Logging laufen ?!
	 * @return int Gibt Zahl der gelöschten Datensätze zurück
	 */
	function dbDel($q)
	{
		try {
			return $this->db->getConnection()->exec($q);
		} catch (Exception $ex) {
			$this->errorLog($q);
			$this->error_out($ex->__toString());
		}
	}

	function dbLock($tables = '')
	{ //macht ein Lock bzw. ein unlock
		if (strlen($tables) > 2) $sql = "LOCK TABLES $tables WRITE;";
		else $sql = 'UNLOCK TABLES;';
		$this->db->getConnection()->exec($sql);
	}

	function dbClose()
	{
		if (is_object($this->db)) $this->db->closeConnection();
	}

	/**
	 *	Eintrag in die Tabelle changelog
	 */
	function changelogEintrag($act, $altInh, $bemerk = '')
	{
		$a = addslashes($act);
		$inh = addslashes($altInh);
		$b = addslashes($_SERVER["REMOTE_ADDR"] . "\n$bemerk");
		$user = $_SESSION['userInfo']->userID;
		$log = "INSERT INTO changelog
		(changeLogUserFID, changeLogAction, changeLogTime, changeLogAlt, changeLogBemerk, changeLog_skript)
		VALUES ($user, '$a', NOW(),'$inh','$b','{$_SERVER['PHP_SELF']}');";
		$this->dbIns($log);
	}

	function logEintrag($act, $bemerk)
	{
		$act = addslashes($act);
		$bemerk = addslashes($bemerk);
		$user = (int)$_SESSION['userInfo']->userID;
		$log = "INSERT INTO logtable
		(logUserFID, logAction, logTime, logHost, logBemerk, logBrowser)
		VALUES ($user, '$act', NOW(), '{$_SERVER["REMOTE_ADDR"]}', '$bemerk', '{$_SERVER["HTTP_USER_AGENT"]}');";
		$this->dbIns($log, false);
	}

	function errorLog($error, $typ = 'db', $b = '')
	{
		$error = addslashes($error);
		$script = addslashes($_SERVER['PHP_SELF']);
		$aufrufer =  addslashes($_SERVER['REMOTE_ADDR']);
		$b = addslashes($b);
		$log = "INSERT INTO errorlog
		VALUES ('','$error', '$script', NOW(),'$typ','$b','{$_SESSION['userInfo']->userID}','$aufrufer');";
		$this->dbIns($log);
	}

	/**
	 *	db-Methode zum Ermitteln aller Feldnamen einer DB-Tabelle.
	 */

	function dbFieldnames($table)
	{
		try {
			$sql = "describe $table";
			$rslt = $this->dbSel($sql);
			for ($i = 0; $i < count($rslt); $i++) {
				$list[$rslt[$i]->Field] = '';
			}
			return $list;
		} catch (Exception $ex) {
			$this->errorLog($q);
			$this->error_out($ex->__toString());
		}
	}

	/**
	 * Gibt eine Fehlermeldung aus und bricht dann mit die() ab.
	 * @param mixed $data
	 * @param string $descr
	 */
	function error_out($data, $descr = '')
	{
		if ($this->appstatus == 'production') {
			include(APPLICATION_PATH . '/views/error.htm');
			die();
		}
		echo '<p color="red">Fehler</p>';
		echo '<pre>';
		var_dump($data);
		echo '</pre><br />';
		die();
	}
}	//end class user
