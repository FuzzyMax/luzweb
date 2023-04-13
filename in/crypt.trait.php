<?php

trait Crypt {
    /**
     * Schlüssel zum Verschlüsseln sicherheitsrelevanter Daten in der DB.
     * Der Schlüssel muss im Konstruktor der Klasse gesetzt werden.
     * Wenn Daten vorgehalten werden sollen, auf die nur die User selbst Zugriff haben
     * sollen müsste ein Schlüssel auf der Grundlage des Passwortes o.ä. erstellt werden.
     * Aber was dann bei Passwortänderung ?
     * @var string
    */
    public $cryptKey = CRYPT_BASICKEY;
    
    /**
     * Hasht das Passwort. In die DB wird es dann als sha1(salt.hash.salt) geschrieben.
     * @param string $pw Klartextpasswort
     * @return string Das gehashte Passwort.
     */
    public function pwhash($pw) {
        return md5($pw.APPKEY).CRYPT_BASICKEY;
    }
    
    public function getRandomStr($minlen = 10) {
        $erg = '';
        $chars('23456789abcdefghijkmnopqrstuvwxyzABCDEFGHJKLMNPQRSTUVWXYZ-+*_#?$§%=');
        $max = strlen($chars);
        for ($i=0; $i<$minlen; $i++) {
            $erg .= $chars[mt_rand(0,$max)];
        }
    }

    /**
     * Die folgenden drei Methoden bilden die AES-Verschlüsselung von mysql nach.
     * Die erste Methode liefert einen Key wie mysql ihn erzeugt.
     * @return string
    */
    function mysql_aes_key()
    {
        $new_key = str_repeat(chr(0), 16);
        $key = $this->cryptKey;
        for($i=0,$len=strlen($key);$i<$len;$i++)
        {
            $new_key[$i%16] = $new_key[$i%16] ^ $key[$i];
        }
        return $new_key;
    }
    
    function aes_decrypt($val)
    {
        $key = $this->mysql_aes_key();
        //TODO: Create a more comprehensive map of mcrypt <-> openssl cyphers
        $cypher = 'aes-128-ecb';
        return openssl_decrypt($val, $cypher, $key, true);
    }
    
    function aes_encrypt($val )
    {
        $key =  $this->mysql_aes_key();
        //TODO: Create a more comprehensive map of mcrypt <-> openssl cyphers
        $cypher = 'aes-128-ecb';
        return openssl_encrypt($val, $cypher, $key, true);
    }

}
