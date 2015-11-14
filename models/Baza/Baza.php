<?php

/**
 * Klasa odpowiedzialna za operacje związane z bazą danych.
 *
 */  
class Baza{
  
  /**
   * Nawiązanie połączenia z bazą danych <br />
   * Metoda nawiązuje połączenie z określoną bazą danych.   
   *
   * @return Zend_Db_Adapter_Pdo_Mysql   
   */        
  public static function polacz(){
    
    $parametryPolaczenia = 
      array("host" => "mysql.cba.pl",
        "port" => "3306",
        "username" => "czytajwnetklient",
        "password" => "1qazxsw23edcvfr4",
        "dbname" => "w_kaczorowski_c0_pl",
        "charset" => 'utf8'
      );
    
    $bd = new Zend_Db_Adapter_Pdo_Mysql($parametryPolaczenia);
    
    //Zwrócenie uchwytu do bazy czytajwnet
    return $bd;
  
  }
}
?>
