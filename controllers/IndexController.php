<?php

/**
 * Klasa odpowiedzialna za zawartość głównej strony aplikacji.
 *
 */  
class IndexController extends Zend_Controller_Action
{
  /**
   * Metoda inicjalizująca pozostałe funkcje kontrolera Index.
   *     
   */
  public function init()
  {
      /* Initialize action controller here */
  
  }

  /**
   * pobranie newslettera z paginacją <br />
   * Metoda pobierająca newsletter na podstawie danych z bazy. Korzysta z
   * modułu paginacji.   
   *    
   */      
  public function indexAction()
  {
    //Kontynuacja sesji
    require_once "Autoryzacja/Autoryzacja.php";
    $sesja = Autoryzacja::kontynuujSesje();
    
    //wyświetlenie newsletter'a
    if($sesja->zalogowany){
    
      $biezacaStrona = 1;
      //Sprawdzenie czy Użytkownik nie jest na str. 1.
      $i = $this->_request->getQuery('i');
      
      if(!empty($i)){
      
        $biezacaStrona = $this->_request->getQuery('i');  
      
      }
      
      //pobranie danych z bazy
      try{
      
        //Dane do pobrania z bazy
        $newsy = array(
          "temat" => 'temat',
          "tresc" => 'tresc',
          "data" => 'data_dodania'  
        );
        
        //Określenie tabeli źródłowej
        $tabela = array("n" => "newsletter");
        
        //Połączenie z bazą
        require_once "Baza/Baza.php";
        $bd = Baza::polacz();
        
        //Utworzenie obiektu typu SELECT
        $wybierz = new Zend_Db_Select($bd);
        
        //Utworzenie zapytania
        $zapytanie = $wybierz->from($tabela, $newsy);
  
        //Inicjalizacja paginacji
        $paginator = Zend_Paginator::factory($zapytanie);
        
        //ustawienie właściwości paginacji
          //ilość wyników na stronie
        $paginator->setItemCountPerPage(10);
          //ile odnośników w zakresie paginatora
        $paginator->setPageRange(6);
          //biezaca strona
        $paginator->setCurrentPageNumber($biezacaStrona);
        
        //przekazanie danych do widoku
        $this->view->paginator = $paginator;
    
        /******************************
        //Wysłanie zapytania
        $wyniki = $bd->query($zapytanie);
        $wiersze = $wyniki->fetchAll();
        
        //Zamknięcie połączenia z bazą
        $bd->closeConnection();
        
        //Przekazanie danych do widoku
        $this->view->newsy = $wiersze;
        *****************************/
      
      }catch(Zend_Db_Exception $e){
      
        echo $e->getMessage();
        
      }
    }
  }
  

}

