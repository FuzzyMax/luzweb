<?php
/**
 * Methoden fürs logging.
 */
trait Log {
    /**
     * 	Eintrag in die Tabelle log_change
     */
    public function logChange($short, $detail, $bemerk = '') {
	if (isset($_SESSION['userInfo'])) {
		$uid = (int)$_SESSION['userInfo']->userID;
	}
	else $uid = (int)$this->data->userID;
        $log = "INSERT INTO log_change
		(userid, short, detail, bemerk, iphash, time, src)
		VALUES (?, ?, ?, ?, ?, NOW(), ?);";
        $data = array($uid, $short, $detail, $bemerk, $this->ipAdr, __FILE__ . __LINE__);
        $this->dbIns($log, $data, false);
    }

    /**
     * neuer Eintrag in die Tabelle log
    */
    public function log($act, $bemerk, $type = 'message') {
        $f = __FILE__ . __LINE__;
        if (is_null($bemerk)) {
            $bemerk = '-';
        }
        $log = "INSERT INTO log
		(type, short, detail,userid, time, src, iphash, useragent)
		VALUES (?, ?, ?, ?, NOW(), ?, ?, ?);";
	    if (isset($_SESSION['userInfo'])) {
		    $uid = (int)$_SESSION['userInfo']->userID;
	    }
	    else {
            $uid = (int)$this->data->userID;
        }
        $data = array($type, $act, $bemerk, $uid, $f, $this->ipAdr, $_SERVER['HTTP_USER_AGENT']);
        $this->dbIns($log, $data, false);
    }

    /**
     * neuer Eintrag in die Tabelle log_error
    */
    public function logError($error, $typ = 'sql', $b = '', $in = '') {
	if (isset($_SESSION['userInfo'])) {
		$uid = (int)$_SESSION['userInfo']->userID;
	}
	else $uid = (int)$this->data->userID;
        $script = __FILE__ . __LINE__;
        $br = $_SERVER['HTTP_USER_AGENT'];
        $log = "INSERT INTO log_error
                (type, short, detail, userid, time, src, indata, iphash)
                VALUES (?, ?, ?, ?, NOW(), ?, ?, Hash55(?,'loG-ErRoR'));";
        $data = array($typ, $error, $b, $uid, $script, $in, $this->ipAdr );
        $this->dbIns($log, $data, false);
    }

    /**
     * neuer Eintrag in die Tabelle log_security
    */
    public function logSec($error, $typ = 'sql', $b = '', $in = '') {
	if (isset($_SESSION['userInfo'])) {
		$uid = (int)$_SESSION['userInfo']->userID;
	}
	else $uid = (int)$this->data->userID;
        $script = __FILE__ . __LINE__;
        $br = $_SERVER['HTTP_USER_AGENT'];
        $log = "INSERT INTO log_security
                (type, short, detail, userid, time, src, indata, iphash)
                VALUES (?, ?, ?, ?, NOW(), ?, ?, Hash55(?,'loG-ErRoR'));";
        $data = array($typ, $error, $b, $uid, $script, $in, $this->ipAdr );
        $this->dbIns($log, $data, false);
    }
    
}
