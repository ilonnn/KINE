<?php

/**
 * Klasa odpowiedzialna za operacje związane z autoryzacją Użytkownika.
 *
 */  
class Autoryzacja{

  /**
   * Sprawdzenie czy Użytkownik jest zalogowany<br />
   * Metoda sprawdza, czy Użytkownik jest zalogowany
   *
   * @return boolean      
   */      
  static function czyZalogowany(){
  
    //Inicjalizacja sesji logowania
    //Identyfikator 'logowanie' jest również ustawiony dla akcji konto/wyloguj
    $sesjaLogowania = new Zend_Session_Namespace('logowanie');
    
    //Jeśli niezalogowany
    if($sesjaLogowania->zalogowany){
      
      return true;
      
    }else{
    
      return false;
    
    }    
    
  }
  
  /**
   * Kontynuacja sesji logowania <br />
   * Metoda kontynuuje sesję logowania Użytkownika
   *
   * @return Zend_Session_Namespace  
   */         
  static function kontynuujSesje(){
  
    //Inicjalizacja sesji logowania
    //Identyfikator 'logowanie' jest również ustawiony dla akcji konto/wyloguj
    $sesjaLogowania = new Zend_Session_Namespace('logowanie');
    
    return $sesjaLogowania;
  
  }

}

?>
