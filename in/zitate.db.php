<?php

/**
 *  Klasse zum Zugriff auf die DB-Tabelle zitate.
 *
 * 	Folgende Aufgaben übernimmt die Klasse :
 *  - logging
 *  - DB Zugriffe
 *
 * 	@package db
 */
class zitate {
   
    private $user = null;
    private $table = 'zitate';
    private $items = 'z_id, z_quelle, z_inhalt, z_seite';

    public function __construct($u) {
        if ( is_a($u, 'user') ) {
            $this->user = $u;
            $fields = $u->dbFieldnames('zitate');
            $this->items = implode(',', $fields);
        }
        else {
            throw new Exception('Missing user.');
        }
    }

    public function get_list( string $where = ' 1 ', $data = '' )
    {
        $sql = 'SELECT ' . $this->items . ' FROM ' . $this->table . ' WHERE ' . $where . ';';
        return $this->user->dbSel($sql,$data);
    }

    public function get_byID( int $id ) {
        $sql = 'SELECT ' . $this->items . ' FROM ' . $this->table . ' WHERE z_id = ?;';
        echo $sql . '<br>';
        return $this->user->dbSelOne($sql,array($id));
    }

    public function set_itemlist ( $items ) {
        if (is_array($items)) {
            $this->items = implode(',', $items);
        }
        else {
            $this->items = $items;
        }
    }
    /**
     * 
     */
    public function insert ( string $isbn, string $inh, string $page = '0') {
        $data = array(
            $isbn,
            $inh,
            $page
        );
        $sql = "INSERT INTO zitate (z_quelle, z_inhalt, z_seite) VALUES (?, ?, ?);";
        $this->user->dbIns($sql, $data, false);
    }
    
}