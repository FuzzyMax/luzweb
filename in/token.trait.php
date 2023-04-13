<?php
/**
 * Methoden zum Umgang mit Token zur Verhinderung von CSRF-Angriffen.
 */
trait Token {
    /**
     * Prüft ob das Token aus der Session mit checkVal übereinstimmt.
     * Falls ein Tokennamen übergeben wird wird in $_SESSION['formTokens'][$tokenName] gesucht,
     * ansosnten in this->token (=$_SESSION['usertoken']).
     * @param string $checkVal
     * @param string $tokenName
     * @return boolean
    */
    function checkToken($checkVal,$tokenName = '') {
        if ($tokenName > '') {
            if (isset($_SESSION['formTokens'][$tokenName])) {
               return ($_SESSION['formTokens'][$tokenName] === $checkVal);
            }
            else {
                return false;
            }
        }
        else {
            return ($this->token === $checkVal);
        }
    }
    
    /**
     * Gibt das gewünschte Token zurück.
     * Sollte zur Absicherung von Formularen genutzt werden.
     * @param string $tokenName, leer (oder auch unbekanntes Token) heisst Standardtoken.
     * @return string das Token
    */
    public function getToken($tokenName = '') {
        if ($tokenName > '' && isset($_SESSION['formTokens'][$tokenName])) {
            return $_SESSION['formTokens'][$tokenName];
        } else {
            return $this->token;
        }
    }
    
    /**
     * Setzt das Token des aktuellen users bzw. das angeforderte Token auf einen neuen Wert.
     * @param string $tokenName
     * @return string
    */
    public function refreshToken($tokenName = '') {
        if ($tokenName > ' ') {
            $_SESSION['formTokens'][$tokenName] = sha1('TOKEN' . $tokenName . microtime());
        } else {
            $this->token = sha1('rAdRaR' . $this->data->nic . microtime());
            $_SESSION['usertoken'] = $this->token;
        }
    }
    
}
