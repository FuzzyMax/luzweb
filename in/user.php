<?php
ini_set('session.cookie_secure', "1");
ini_set('session.cookie_httponly', "1");
ini_set('session.cookie_samesite','None');
define('PROGNAME','LUZWEB');
define('DOCROOT',$_SERVER['DOCUMENT_ROOT']);
define('INCL',DOCROOT.DIRECTORY_SEPARATOR.'in'.DIRECTORY_SEPARATOR);

session_start ();
require_once(INCL.'prog.ini.php');
require_once(INCL.'token.trait.php');
require_once(INCL.'sql.trait.php');
require_once(INCL.'crypt.trait.php');
require_once(INCL.'log.trait.php');

/**
 *  Klasse mit wichtigen Hilfs-Funktionalitäten.
 *  DB-Tabellen logtables, user und personen müssen vorhanden sein.
 *
 * 	Folgende Aufgaben übernimmt die Klasse :
 *  - logging
 *  - DB Zugriffe
 *  - Mehrsprachigkeit
 *  - Rechteverwaltung/überprüfung
 *
 * 	Dabei ist zu beachten, daß die Nutzinformationen in der Eigenschaft data steckt (diese Info steht auch in $_SESSION["userInfo"])
 * 	@package user
 */
class user {
    use Token, Sql, Crypt, Log;
    /**
     * Daten des Users, falls angemeldet aus der DB.
     * @var object
    */
    private $data = null; //Userdaten aus DB bzw. Session
    private $dbConn = null;

    /**
     * IP-Adresse des Users
     * @var string
     */
    private $ipAdr = '0.0.0.0';

    /**
     * dev, prod, test ...
     * @var string
     */
    private $appstatus = 'UNKNOWN';

    /**
     * Ist der User bereits eingeloggt ?
     * @var bool
     */
    private $loggedin = false;

    /**
     * Token zum Absichern einer Seite/ eines Formulars.
     * @var string
     */
    private $token = '';

    /**
     * Könnte gerade ein Bruteforce-Angriff laufen ?
     * @var boolean
     */
    private $isSecProblem = false;
    private $securityType = null;

    /**
     * 	Konstruktor der Klasse.
     * 	Je nachdem ob eine Session existiert oder nicht werden die Userdaten aus der DB oder der Session geholt.
     * 	Setzt $_SESSION["userInfo"] oder liest diese aus.
     */
    function __construct($u, $p, $s = false) {
   
        $this->dbConn = $GLOBALS['_INI']['db']['conn'];
        if (filter_input(INPUT_SERVER, 'REMOTE_ADDR', FILTER_VALIDATE_IP)) {
            $this->ipAdr = $_SERVER['REMOTE_ADDR'];
        }
        $this->appstatus = APPSTATUS;
        if (!$s) { //Keine Session also Daten aus Login-Formular + Datenbank
	        $this->cryptKey = sha1(CRYPT_BASICKEY.$p).CRYPT_BASICKEY;
	        $_SESSION['cryptPW'] = $this->aes_encrypt($p); //Verschlüsseltes PW
	        $_SESSION['cryptKey'] = $this->cryptKey;

            if (isset($_SESSION['logTry']) && $_SESSION['logTry'] < 1) {
                header('Location: /index/index?meldung=error3');
                exit;
            }
            #$p = $this->pwhash($p);
            $sql = "SELECT * FROM user AS u
                    JOIN personen ON u.person_id = personID
                    WHERE u.nic = ?
                    AND (u.pw = sha1(?))
                    AND u.deleted = 0;";
            $e = $this->dbSelOne($sql,array($u,$p));
            if ($e) {
                $this->data = $e;
                $_SESSION["userInfo"] = $e;
                $this->log("LOGIN $u", $p . ' -- ' . $this->cryptKey,'login');
                $this->log("LOGIN Role:" . $e->role, "OK",'login');
                $sql = "UPDATE user SET lastlogin = NOW() WHERE userID = ?;";
                $this->dbUpdate($sql,array($e->userID));
                
                //ZUM TESTEN DB-Crypt
                #$sql = "UPDATE personen SET personSecdata = AES_ENCRYPT(?, '{$this->cryptKey}') WHERE personID = ?;";
                #$this->dbUpdate($sql,array('TEST '.date('H:i:s'),$e->personID));
                #$sql = "SELECT AES_DECRYPT(personSecdata, '{$this->cryptKey}') AS data FROM personen WHERE personID = ?;";
                #$testdata = $this->dbSelOne($sql,array($e->personID));
                
                $this->loggedin = true;
                session_regenerate_id();
                $this->refreshToken();
            } else {
                $this->cryprtKey = CRYPT_BASICKEY;
                $this->data = new stdClass();
                $this->data->nic = 'Gast';
                $this->data->role = 0;
                $this->data->userID = 0;
                $this->data->sprache = 'DE';
                $this->log("LOGIN $u", 'missglückt','login');
                $this->checkSecurity(true);  //Prüfen ob Sicherheitsprobleme erkennbar sind.
            }
            $_SESSION['ipAdr'] = $this->ipAdr;
            $_SESSION['userAgent'] = $_SERVER['HTTP_USER_AGENT'];
        } else { //Hole Userdaten aus Session
            if (isset($_SESSION['userInfo'])) {
                $this->data = $_SESSION['userInfo'];
                if ($this->data->role != 0) {
                    $this->loggedin = true;
                    $this->checkSecurityLogin();  //Prüfen ob Sicherheitsprobleme erkennbar sind.
                }
                $pw = $this->aes_decrypt($_SESSION['cryptKey']);
            } else {
                $this->data = new stdClass();
                $this->data->nic = 'Gast';
                $this->data->role = 0;
                $this->data->userID = 0;
                $this->data->sprache = 'DE';
            }
            $this->ipAdr = $_SESSION['ipAdr'];
        }
        #Falls Sicherheitsprobleme erkannt wurden, kann hier entsprechend gehandelt werden.
        #z.B. kann ein erfolgreiches Login einmal abgelehnt werden, wenn der username zuoft genutzt wurde.
        #Ebenso wäre es möglich ein erfolgreiches Login vorzutäuschen.
        if ($this->isSecProblem) {

        }
        if (isset($_SESSION['usertoken'])) {
            $this->token = $_SESSION['usertoken'];
        } else {
            $this->refreshToken();
        }
    }

    /**
     * Prüft ob es innerhalb einer gewissen Zeit (aktuell 4 Stunden) zuviele misslungene login-Versuche gab.
     * Wenn ja wird evtl ein Päuschen eingelegt.
     * Hier könnten auch weitere Aktionen stattfinden.
     * z.B. Mailversand, blockieren gewisser IP-Adressen......
     * Verbesserung : statt DB-Abfrage könnte eine performantere Art der Ermittlung Fehlversuche sinnvoll sein.
     * Setzt auch die Eigenschaft isSecProblem falls es mehr als 16 Fehlversuche gab.
     * @param boolean $wait wenn true, dann wird eine Weile gewartet (abh. von der Anz.d.Fehlversuche).
     * @return int Anzahl der misslungenen Loginversuche in den letzten 4 Stunden.
    */
    private function checkSecurity($wait = false) {
        $hours = 4;
        $sql = "SELECT COUNT(id) AS anz FROM log
                WHERE short LIKE 'LOGIN%' AND detail != 'OK' AND ADDDATE(time, INTERVAL $hours HOUR) > NOW();";
        $erg = $this->dbSel($sql);
        $anz = (int) $erg[0]->anz;
        $ms = 1;
        if ($anz < 4) {
            if ($wait)
                sleep($ms);
        }
        elseif ($anz < 16) {
            if ($wait)
                sleep($ms * $anz / 2);
        }
        else {
            $this->isSecProblem = true;
            $this->securityType = 'BruteForce';
            $ms = $anz % 23;
            if ($wait) sleep($ms);
        }
        if (!is_null($this->securityType)) {
            error_log($this->securityType);
        }
        return $anz;
    }
    
    /**
     * Prüft ob es Hinweise auf eine Sessionübernahme gibt.
     * Schreibt nur einen Logeintrag.
     * Die beiden hier benutzten Sessionvariablen werden nur beim Login gesetzt.
     * @todo soll man hier was machen ?
    */
    private function checkSecurityLogin() {
        if ($_SESSION['ipAdr'] !== $this->ipAdr) {
            $this->securityType = 'IP';
        }
        if ($_SESSION['userAgent'] !== $_SERVER['HTTP_USER_AGENT']) {
            $this->securityType = 'Browser';
        }
        if (!is_null($this->securityType)) {
            error_log('Session Hijacking ?? '.$this->securityType);
        }
    }

    /**
     * Gibt zurück ob der User bereits authentifiziert ist.
     * @return bool
     */
    public function is_loggedin() {
        return $this->loggedin;
    }

    /**
     * Gibt die Userdaten zurück.
     * @return object
     */
    public function get_data() {
        return $this->data;
    }

    /**
     * 	Führt ein logout durch.
     * 	Beendet die session und schreibt einen Logeintrag.
     */
    public function logOut() {
        $sql = "UPDATE user SET lastlogout = NOW() WHERE userID = ?;";
        $this->dbUpdate($sql,array($this->data->userID));
        $this->log('LOGOUT ' . $this->data->role, $this->data->nic);
        $this->dbClose();
        $_SESSION = array();
        unset($_COOKIE[session_name()]);
        session_destroy();
    }
    
    
    public function changePW($pw) {
        $this->cryptKey = sha1(CRYPT_BASICKEY.$pw).CRYPT_BASICKEY;
        $this->log("CHANGEPW ", $pw . ' -- ' . $this->cryptKey,'changepw');
	    $_SESSION['cryptPW'] = $this->aes_encrypt($pw); //Verschlüsseltes PW
	    $_SESSION['cryptKey'] = $this->cryptKey;
        
        $sql = "UPDATE user SET
                pw = md5(?)
                WHERE userID = ?;";
        return $this->dbUpdate($sql,array($pw, $this->data->userID));
    }


    /**
     * 	Methode zum Holen der mehrsprachigen Beschriftungen einer Seite.
     * 	Holt alle Texte zu einer Seite in der Sprache des Users. Sollten die entsprechenden Übersetzungen nicht vorhanden sein
     * 	wird nichts angezeigt !
    */
    public function getTexte($type) {
        $sql = "SELECT marker,text
        	    FROM textmarker
                JOIN texte ON txtid = textid AND sprache = ?
		WHERE type = ? OR type = 'all';";
        $texte = $this->dbSel($sql,array($this->data->sprache, $type));

        $t = array();
        if ($texte) {
            for ($i = 0; $i < count($texte); $i++) {
                $t[$texte[$i]->marker] = $texte[$i]->text;
            }
        }
        return $t;
    }

    /**
     * 	nur für Testing
     */
     
      /**
     * Legt einen User an.
     */
    static function createUser($uname, $pw) {
       
    }

    function dump($data, $descr = '') {
        if ($this->appstatus == 'prod')
            return;
        echo '<pre>';
        var_dump($data);
        echo '</pre>';
    }

    /**
     * Gibt eine Fehlermeldung aus und bricht dann mit die() ab.
     * @param mixed $data
     * @param string $descr
     */
    function error_out($data, $descr = '') {
        if ($this->appstatus == 'prod') {
            include (APPLICATION_PATH . '/views/error.htm');
            die();
        }
        echo '<p color="red">Fehler</p>';
        echo '<pre>';
        var_dump($data);
        echo '</pre><br />';
        die();
    }
    
    function echoSec($str) {
        echo htmlentities($str);
    }
    
    /**
     * Prüft ob eine gültige Session existiert.
     * Wenn nein wird auf die index.php weitergeleitet.
     */
    static function checkSession() {
        if (!isset($_SESSION['userInfo'])) {
            header('location: /index.php');
            exit;
        }
    }

}
?>
