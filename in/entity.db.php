<?php

/**
 *  Klasse zum allgemeinen Zugriff auf die DB-Tabellen.
 *
 * 	Folgende Aufgaben übernimmt die Klasse :
 *  - logging
 *  - DB Zugriffe
 *
 * 	@package db
 */
class entity {
   
    private $user = null;
    private $table = '';
    private $field_list = array();
    private $id_field = '';
    private $akt_fields = '';
    private $orderby = '';

    public function __construct($u, $table) {
        if ( is_a($u, 'user') ) {
            $this->user = $u;
        }
        else {
            throw new Exception('Missing db connection.');
        }
        if ( $table < ' ' ) {
            throw new Exception('Missing table.');
        }

        $this->field_list = $u->dbFieldnames($table);
        if ( is_array($this->field_list) ) {
            $this->table = $table;
            $this->akt_fields = implode(', ', $this->field_list);
            $this->id_field = $u->last_pkey;
        }
        else {
            throw new Exception('System ERROR table.');
        }
    }

    public function get_list( string $where = ' 1 ', $data = '' )
    {
        $sql = 'SELECT ' . $this->akt_fields . ' FROM ' . $this->table . ' WHERE ' . $where ;
        $sql .= $this->orderby . ';';
        return $this->user->dbSelArray($sql,$data);
    }

    public function get_byID( int $id ) {
        $sql = 'SELECT ' . $this->akt_fields . ' FROM ' . $this->table . ' WHERE ' . $this->id_field .' = ?;';
        return $this->user->dbSelOne($sql,array($id));
    }

    public function set_orderby( $field, $desc = true ) {
        if (false !== array_search($field, $this->field_list)) {
            $this->orderby = " ORDER BY $field ";
            if ($desc) {
                $this->orderby .= 'DESC ';
            }
            else {
                $this->orderby .= 'ASC ';
            }
        }
        else {
            $this->orderby = '';
        }
    }

    /**
     * Setzt die Feldliste für Selects.
    */
    public function set_fieldlist ( array $fields ) {
        foreach ($fields as $field) {
            if (false === array_search($field, $this->field_list)) {
                #Unbekanntes Feld --> Abbruch
                return;
            }
        }
        $this->akt_fields = implode(', ', $fields);
    }

    /**
     * 
     */
    public function insert ( array $in ) {
        $fields = array();
        $data = array();
        foreach ($in as $field => $content ) {
            if (false === array_search($field, $this->field_list)) {
                #Unbekanntes Feld --> Abbruch
                echo 'ERROR SAVE !!' . $field;
                return;
            }
            $fields[] = $field;
            $data[] = $content;
        }
        $field_list = implode(',', $fields);
        $sql = "INSERT INTO {$this->table} ($field_list) VALUES (?, ?, ?);";
        return $this->user->dbIns($sql, $data, false);
    }

    /**
     * updates an entity
     */
    public function update_byID ( array $in, int $id ) {
        if ($id < 1) {
            return;
        }
        $fields = array();
        $data = array();
        foreach ($in as $field => $content ) {
            if (false === array_search($field, $this->field_list)) {
                #Unbekanntes Feld --> Abbruch
                echo 'ERROR SAVE !!' . $field;
                return;
            }
            $fields[] = "$field = ? ";
            $data[] = $content;
        }
        $fieldlist = implode(',', $fields);
        $sql = "UPDATE {$this->table} SET $fieldlist WHERE {$this->id_field} = $id;";
        return $this->user->dbUpdate($sql, $data, false);
    }

    public function delete_byID (int $id) {
        $sql = "DELETE FROM {$this->table} WHERE {$this->id_field} = '$id';";
        return $this->user->dbDel($sql);
    }
    
}