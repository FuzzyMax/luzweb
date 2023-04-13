<?php

trait Sql {

    /**
     * Feld mit bisher angelegten prepared Statements.
     * @var array
     */
    protected $prepStmt = array();
    
    /**
     * 	db-Methode für Select-Abfragen.
     * 	über diese Methode sollte evtl. jede Art von Logging laufen ?!
     * 	@return Gibt Array von Objekten mit allen gefundenen Datensätzen zurück
    */
    public function dbSel($q, $data = false) {
        try {
            if (!$data) {
                $res = $this->dbConn->query($q,PDO::FETCH_OBJ);
                return $res->fetchAll();
            }
            else {
                if (!is_array($data)) {
                    error_log('Fehlende Daten SQL Select : '. $q);
                    return false;
                }
                $s = $this->getPrepStmt($q);
        		$s->execute($data);
                if (!$s) {
                    return false;
                }
                else {
                    return $s->fetchAll(PDO::FETCH_OBJ);
                }
            }
        } catch (Exception $ex) {
            $this->error_out($ex->__toString());
            error_log($ex->__toString());
            return false;
        }
    }

    /**
     * 	db-Methode für Select-Abfragen.
     * 	über diese Methode sollte evtl. jede Art von Logging laufen ?!
     * 	@return Gibt Objekt mit dem ersten gefundenen Datensatz zurück.
    */
    public function dbSelOne($q,$data = false) {
        try {
            if (!$data) {
                $res = $this->dbConn->query($q,PDO::FETCH_OBJ);
                if ($res !== false) {
                    $erg = $res->fetch();
                    $res->closeCursor();
                    return $erg;
                }
                else {
                    return false;
                }
            }
            else {
                if (!is_array($data)) {
                    error_log('Fehlende Daten SQL Select : '. $q);
                    return false;
                }
                $s = $this->getPrepStmt($q);
		        $s->execute($data);
                if (!$s) {
                    return false;
                }
                else {
                    return $s->fetch(PDO::FETCH_OBJ);
                }
            }
        } catch (Exception $ex) {
            error_log($ex->__toString());
            $this->error_out($ex->__toString());
            return false;
        }
    }

    /**
     * Gibt Array von Arrays mit allen gefundenen Datensätzen zurück
     * @return array
     */
    public function dbSelArray($q, $data=false) {
        try {
            if (!$data) {
                $res = $this->dbConn->query($q,PDO::FETCH_ASSOC);
                return $res->fetchAll();
            }
            else {
                if (!is_array($data)) {
                    error_log('Fehlende Daten SQL SelectArray : '. $q);
                    return false;
                }
                $s = $this->getPrepStmt($q);
		        $s->execute($data);
                if (!$s) {
                    return false;
                }
                else {
                    return $s->fetchAll(PDO::FETCH_ASSOC);
                }
            }
        } catch (Exception $ex) {
            $this->error_out($ex->__toString());
            error_log($ex->__toString());
            return false;
        }
    }

    /**
     * db-Methode für Insert-Anweisungen.
     * @param string $q SQL-Anweisung
     * @param mixed $data Falls es ein $q prepared-Statement ist ein array mit den Werten, sonst false.
     *                    Es kann auch ein array von arrays sein, dann wird das preparedSt mehrfach ausgeführt.
     *                    $data muss ein indexed array sein - index 0 muss existieren.
     * @param bool $log falls true wird ein Eintrag im changelog gemacht.
     * @return int Gibt den insert_id des neuen Datensatzes zurück (oder true false es keinen gibt !!)
    */
    public function dbIns($q, $data = false, $log = true) {
        $id = 0;
        try {
            if (!$data) { //Normales SQL
                $myerg = $this->dbConn->exec($q);
                if ($myerg === false) {
                    return false;
                }
                else {
                    $id = $this->dbConn->lastInsertId();
                }
                if ($log) $this->logChange('Insert', $q, (int)$id);

                if ($id) return $id;
                else     return true;
            }
            else { //prepared Statement.
                if (!is_array($data)) {
                    error_log('Fehlende Daten SQL Insert : '. $q);
                    return false;
                }
                $s = $this->getPrepStmt($q);
                
                if (is_array($data[0])) {   //Mehrere Datensätze über preparedStatement.
                    foreach ($data AS $it) {
                        $s->execute($it);
                        if (!$s) return false; //Da stimmt was nicht! (Hier vielleicht Transaktion?)
                        $id = $this->dbConn->lastInsertId();
                        if ($log) {
                            $this->logChange('Insert : '. (int)$id, $q, serialize($it));
                        }
                    }
                    return true;
                }
                $s->execute($data);
                if (!$s) {
                    return false;
                }
                else {
                    $id = $this->dbConn->lastInsertId();
                    if ($log) {
                        $this->logChange('Insert : '. (int)$id, $q, serialize($data));
                    }
                    if ($id) return $id;
                    else     return true;
                }
            }
        } catch (Exception $ex) {
            error_log($q);
            error_log($ex->__toString());
            $this->error_out($ex->__toString());
            return false;
        }
    }

    /**
     * db-Methode für Updateanweisungen.
     * @param string $q SQL-Anweisung
     * @param mixed $data Ist q ein prepared Statement, dann ists ein array sonst false.
     *                    Wenn das prepStatement mehrfach ausgeführt werden soll, dann array of array.
     * @return int Gibt Anzahl der betroffenen DS zurück. 0 wenn nichts geändert wird, false bei Fehler.
     * @todo logging einbauen ?
    */
    public function dbUpdate($q, $data = false) {
        try {
            if (!$data) {
                return $this->dbConn->exec($q);
            }
            else {
                if (!is_array($data)) {
                    error_log('Fehlende Daten SQL Update : '. $q);
                    return false;
                }
                $s = $this->getPrepStmt($q);
                if (is_array($data[0])) {   //Statement mehrfach ausführen.
                    foreach ($data AS $it) {
                        $s->execute($it);
                        if (!$s) {
                            return false;
                        }
                    }
                    return true;
                }
				$s->execute($data);
                if (!$s) {
                    return false;
                }
                else {
                    return $s->rowCount();
                }
            }
        } catch (Exception $ex) {
            error_log($ex->__toString());
            $this->error_out($ex->__toString());
        }
        return false;
    }

    /**
     * 	db-Methode für Delete-Befehle.
     * 	über diese Methode sollte evtl. jede Art von Logging laufen ?!
     * @return int Gibt Zahl der gelöschten Datensätze zurück
     */
    public function dbDel($q, $data = false) {
        try {
            if (!$data) {
                return $this->dbConn->exec($q);
            }
            else {
                if (!is_array($data)) {
                    error_log('Fehlende Daten SQL Update : '. $q);
                    return false;
                }
                $s = $this->getPrepStmt($q);
                if (is_array($data[0])) {   //Statement mehrfach ausführen.
                    foreach ($data AS $it) {
                        $s->execute($it);
                        if (!$s) {
                            return false;
                        }
                    }
                    return true;
                }
		        $s->execute($data);
                if (!$s) {
                    return false;
                }
                else {
                    return $s->rowCount();
                }
            }
        } catch (Exception $ex) {
            error_log($ex->__toString());
            $this->error_out($ex->__toString());
        }
    }

    /**
     * macht ein Lock bzw. ein unlock
     */
    public function dbLock($tables = '') {
        if (strlen($tables) > 2)
            $sql = "LOCK TABLES $tables WRITE;";
        else
            $sql = 'UNLOCK TABLES;';
        $this->dbConn->getConnection()->exec($sql);
    }
    
    /**
     * db-Methode zur Abfrage aller Spaltennamen einer DB-Tabelle.
     * @return array Gibt array mit einem Eintrag für jede Spalte zurück.
     */
    public function dbFieldnames($table) {
        try {
            $sql = 'describe '.$table;
            $rslt = $this->dbSel($sql);
            for ($i = 0; $i < count($rslt); $i++) {
                $list[] = $rslt[$i]->Field;
            }
            return $list;
        } catch (Exception $ex) {
            $this->errorLog($q);
            $this->error_out($ex->__toString());
        }
    }

    /**
     * Verbindung zur DB beenden
    */
    public function dbClose() {
        if (is_object($this->dbConn))
            unset($this->dbConn);
    }
    
    /**
     * Erzeugt aus einem SQL-Statement ein PreparedStatements und gibt dieses zurück.
     * Das erzeugte Statement wird gespeichert und direkt zurückgegeben, wenn es bereits vorhanden ist.
     * @param type $q
     * @return type
    */
    protected function getPrepStmt($q) {
        $md5 = md5($q);
        if (isset($this->prepStmt[$md5])) {
            $s = $this->prepStmt[$md5];
        }
        else {
            $s = $this->dbConn->prepare($q);
            $this->prepStmt[$md5] = $s;
        }
        return $s;
    }

}
