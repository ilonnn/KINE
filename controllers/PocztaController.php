<?php

/**
 * Klasa odpowiedzialna za operacje związane z wiadomościami.
 *
 */  
class PocztaController extends Zend_Controller_Action
{

  /**
   * Metoda inicjalizująca pozostałe funkcje kontrolera Poczta.<br />
   * Sprawdza, czy Użytkownik jest zalogowany. Po negatywnej autoryzacji
   * Użytkownik przenoszony jest do strony logowania.   
   *     
   */
  public function init()
  {
    //Sprawdzenie, czy Użytkownik jest zalogowany
    require_once "Autoryzacja/Autoryzacja.php";
    if(!Autoryzacja::czyZalogowany()){
      //Przeniesienie do strony logowania
      $this->_redirect('konto/loguj');
    }
  }

  /**
   * Główna strona kontrolera poczta<br />
   * Metoda przenosi do strony menu poczty   
   *
   */        
  public function indexAction()
  {
    //przeniesienie do menu poczty
    $this->_redirect('poczta/poczta-menu');
  }

  /**
   * Menu poczty<br />
   * Metoda wyświetla menu główne poczty
   *   
   */      
  public function pocztaMenuAction()
  {
      //Wyświetlenie menu poczty
  }

  /**
   * Tworzenie formularza nowej wiadomości<br />
   * Metoda przygotowuje formularz nowej wiadomości.
   * 
   *  @return Zend_Form     
   */      
  private function getNowaWiadomoscFormularz(array $odbiorcy){
  
    //Utworzenie formularza
    $formularz = new Zend_Form;
    $formularz->setAction('sukces-napisz-wiadomosc');
    $formularz->setMethod('post');
    $formularz->setDescription('Formularz nowej wiadomości');
    $formularz->setAttrib('sitename', 'czytajwnet');
  
    //Dodanie elementów do formularza
    require_once "Formularz/Elementy.php";
    $elementy = new Elementy();
    
    //Lista odbiorców
    $formularz->addElement($elementy->getListaOdbiorcowPoleSelect($odbiorcy));
    //Temat
    $formularz->addElement($elementy->getTematWiadomoscPoleText());
    //Treść
    $formularz->addElement($elementy->getTrescWiadomoscPoleText());
    
    //Zapisz zmiany
    $formularz->addElement('submit', 'submit');
    $przyciskUtworz = $formularz->getElement('submit');
    $przyciskUtworz->setLabel('Wyślij');
    
    return $formularz;  
  
  }

  /**
   * Tworzenie nowej wiadomości<br />
   * Metoda pobiera formularz nowej wiadomości oraz dane dostępnych odbiorców,
   * następnie przekazuje dane do widoku.    
   *   
   */      
  public function napiszWiadomoscAction()
  {
    //kontynuacja sesji
    require_once "Autoryzacja/Autoryzacja.php";
    $sesja = Autoryzacja::kontynuujSesje();
    
    //ustalenie listy odbiorców
    try{
    
      //ustanowienie połączenia z bazą danych
      require_once "Baza/Baza.php";
      $bd = Baza::polacz();
    
      //Dane do pobrania z bazy
      $dane = array(
        "odbiorcy" => 'odbiorcy'
      );
      
      //Określenie tabeli źródłowej
      $tabela = array("u" => "uzytkownik");
      
      //Utworzenie obiektu typu SELECT
      $wybierz = new Zend_Db_Select($bd);
      
      //Utworzenie zapytania
      $zapytanie = $wybierz->from($tabela, $dane)
                  ->where('id=?', $sesja->id);
  
      //Wysłanie zapytania
      $wyniki = $bd->query($zapytanie);
      $wiersze = $wyniki->fetchAll();
      
      //Zamknięcie połączenia z bazą
      $bd->closeConnection();
      
      //zapis listy odbiorców
      $odbiorcyIdLogin = $wiersze[0]['odbiorcy'];
      $odbiorcyIdLogin = trim($odbiorcyIdLogin, "\'");
      $odbiorcyIdLogin = explode(';' ,$odbiorcyIdLogin);
      $odbiorcy = array('' => '-- nie wybrano --');
      
      //sprawdzenie, czy lista odbiorców nie jest pusta
      if(!empty($odbiorcyIdLogin[0])){
        foreach($odbiorcyIdLogin as $o){
        
          $odb = explode(',' ,$o);
          //dane do listy rozwijanej: id => login;
          (string)$odbiorcy[$odb[0]] = (string)$odb[1];
          //echo $odb[0] ." => ". $odb[1]."<br />";
        
        }
      } 
      
      $sesja->odbiorcy = $odbiorcy;
      
    }catch(Zend_Db_Exception $e){
      echo $e->getMessages();
    }
    
    
    //Przekazanie formularza do widoku
    $formularz = $this->getNowaWiadomoscFormularz($sesja->odbiorcy);
    $this->view->formularz = $formularz;
  }
  
  /**
   * Przetworzenie formularza nowej wiadomości<br />
   * Metoda przetwarza dane formularza nowej wiadomości. Po poprawnej
   * weryfikacji zapisuje dane w bazie.   
   *   
   */      
  public function sukcesNapiszWiadomoscAction(){
  
    //kontynuacja sesji
    require_once "Autoryzacja/Autoryzacja.php";
    $sesja = Autoryzacja::kontynuujSesje();
    
    //pobranie formularza
    $formularz = $this->getNowaWiadomoscFormularz($sesja->odbiorcy);
    
    //Sprawdzenie poprawności otrzymanych danych
    if($formularz->isValid($_POST)){
      
      //pobranie danych z formularza
      $adresat = $formularz->getValue('odbiorcy');
      $temat = $formularz->getValue('tematWiadomosci');
      $tresc = $formularz->getValue('trescWiadomosci');


      //Zapis danych do bazy
      try{
      
        //ustanowienie połączenia z bazą danych
        require_once "Baza/Baza.php";
        $bd = Baza::polacz();
        
        //dane do zapisu
        $wiadomosc = array(
          'nadawca_id' => $sesja->id,
          'adresat_id' => $adresat,
          'temat' => $bd->quote($temat),
          'tresc' => $bd->quote($tresc),
          'data_wyslania' => new Zend_Db_Expr('NOW()')  
        );
      
        //zapytanie
        $wyniki = $bd->insert('wiadomosci', $wiadomosc);
        
        //zamknięcie połączenia z bazą
        $bd->closeConnection();
      
        //ustawienie flagi błędu
        $this->view->bledy = false;

      }//koniec try
      catch(Zend_Db_Exception $e){
      
        //Przekazanie błędów do widoku
        $this->view->bledy = $e->getMessages();
        $this->view->formularz = $formularz;
      
      }
    
    }else{
    
      $this->view->bledy = "Błędne dane formularza.<br />";
      $this->view->formularz = $formularz;
    
    }  
  
  }

  /**
   * Wyświetlanie nieprzeczytanych wiadomości<br />
   * Metoda pobiera z bazy nieprzeczytane wiadomości przypisane do
   * zalogowanego Użytkownika jako adresata.   
   *   
   */      
  public function pokazNoweAction()
  {
    //kontynuacja sesji
    require_once "Autoryzacja/Autoryzacja.php";
    $sesja = Autoryzacja::kontynuujSesje();

    //Pobranie wiadomości z bazy
    try{
    
      //ustanowienie połączenia z bazą danych
      require_once "Baza/Baza.php";
      $bd = Baza::polacz();
    
      //Dane do pobrania z bazy
      $dane = array(
        "wiad_id" => 'id',
        "nadawca" => 'nadawca_id',
        "temat" => 'temat',
        //"tresc" => 'tresc',
        "wyslano" => 'data_wyslania'
      );
      $dane2 = array('login');
      
      //Utworzenie obiektu typu SELECT
      $wybierz = new Zend_Db_Select($bd);
      
      //Utworzenie zapytania
      $zapytanie = $wybierz->from(array('w' => 'wiadomosci'), $dane)
          ->join(array('u' => 'uzytkownik'),
            'w.nadawca_id = u.id', $dane2)
          ->where('stan=?', 'nowa')
          ->where('adresat_id=?', $sesja->id)
          ->order('data_wyslania DESC');
  
      //Wysłanie zapytania
      $wyniki = $bd->query($zapytanie);
      $wiersze = $wyniki->fetchAll();
      
      //Zamknięcie połączenia z bazą
      $bd->closeConnection();
      
      //Przesłanie danych do widoku
      $this->view->noweWiadomosci = $wiersze;
      
    }catch(Zend_Db_Exception $e){
      
      echo $e->getMessages();
    
    }
  }

  /**
   * Wyświetlenie treści wiadomości<br />
   * Metoda pobiera z bazy i wyświetla zawartość wiadomości.
   *    
   * @param $wiad_id numer identywikacyjny wiadomości 
   */      
  public function pokazWiadomoscAction(){
  
    //Kontynuacja sesji
    require_once "Autoryzacja/Autoryzacja.php";
    $sesja = Autoryzacja::kontynuujSesje();
    
    //pobranie id Użytkownika
    $wiad_id = $this->_getParam('wiadomosc');
    
    //pobranie danych z bazy
    try{
      
      //Dane do pobrania z bazy
      $dane = array(
        "id" => 'id',
        "nadawca" => 'nadawca_id',
        "odbiorca" => 'adresat_id',
        "temat" => 'temat',
        "tresc" => 'tresc',
        "stan" => 'stan',
        "wyslano" => 'data_wyslania'   
      );
      $nadawca = array('nadawca' => 'login');
      $odbiorca = array('odbiorca' => 'login');
      
      //Określenie tabeli źródłowej
      $uzytkDbTab = array("u" => "uzytkownik");
      $wiadDbTab = array("w" => "wiadomosci");
      
      //Połączenie z bazą
      require_once "Baza/Baza.php";
      $bd = Baza::polacz();
      
      //Utworzenie obiektu typu SELECT
      $wybierz = new Zend_Db_Select($bd);

      //Utworzenie zapytania
      $zapytanie = $wybierz->from($wiadDbTab, $dane)
          ->join($uzytkDbTab,
            'w.nadawca_id = u.id', $nadawca)
          ->join(array('uu' => 'uzytkownik'),
            'w.adresat_id = uu.id', $odbiorca)
          ->where('w.id=?', $wiad_id);

      //Wysłanie zapytania
      $wyniki = $bd->query($zapytanie);
      $wiersz = $wyniki->fetchAll();
      
      if( $wiersz[0]['stan'] == 'nowa' ){
      
        $noweDane = array(
          'stan' => 'przeczyt'
        );
        
        //warunek aktualizacji
        $warunki = array("id = ". $bd->quote($wiad_id) );
      
        //zapytanie
        $wyniki = $bd->update('wiadomosci', $noweDane, $warunki);
           
      }
      
      //Zamknięcie połączenia z bazą
      $bd->closeConnection();
      
      //Przekazanie danych do widoku
      $this->view->wiadomosc = $wiersz;
    
    }catch(Zend_Db_Exception $e){
    
      echo $e->getMessage();
      
    }  
  
  }
  
  /**
   * Wyświetlanie wysłanych wiadomości<br />
   * Metoda pobiera z bazy i wyświetla wiadomości przypisane do zalogowanego
   * Użytkownika jako nadawcy.   
   *   
   */      
  public function pokazWyslaneAction()
  {
      //kontynuacja sesji
    require_once "Autoryzacja/Autoryzacja.php";
    $sesja = Autoryzacja::kontynuujSesje();

    //Pobranie wiadomości z bazy
    try{
    
      //ustanowienie połączenia z bazą danych
      require_once "Baza/Baza.php";
      $bd = Baza::polacz();
    
      //Dane do pobrania z bazy
      $dane = array(
        "wiad_id" => 'id',
        "nadawca" => 'nadawca_id',
        "odbiorca" => 'adresat_id',
        "temat" => 'temat',
        //"tresc" => 'tresc',
        "wyslano" => 'data_wyslania'
      );
      $nadawca = array('nadawca' => 'login');
      $odbiorca = array('odbiorca' => 'login');
      
      //Utworzenie obiektu typu SELECT
      $wybierz = new Zend_Db_Select($bd);
      
      //Utworzenie zapytania
      $zapytanie = $wybierz->from(array('w' => 'wiadomosci'), $dane)
          ->join(array('u' => 'uzytkownik'),
            'w.nadawca_id = u.id', $nadawca)
          ->join(array('uu' => 'uzytkownik'),
            'w.adresat_id = uu.id', $odbiorca)
          ->where('nadawca_id=?', $sesja->id)
          ->order('data_wyslania DESC');
  
      //Wysłanie zapytania
      $wyniki = $bd->query($zapytanie);
      $wiersze = $wyniki->fetchAll();
      
      //Zamknięcie połączenia z bazą
      $bd->closeConnection();
      
      //Przesłanie danych do widoku
      $this->view->wyslaneWiadomosci = $wiersze;
      
    }catch(Zend_Db_Exception $e){
      
      echo $e->getMessages();
    
    }
  }

  /**
   * Wyświetlanie przeczytanych wiadomości (skrzynka odbiorcza)<br />
   * Metoda pobiera z bazy przeczytane wiadomości przypisane do
   * zalogowanego Użytkownika jako adresata.  
   */      
  public function pokazPrzeczytaneAction()
  {
    //kontynuacja sesji
    require_once "Autoryzacja/Autoryzacja.php";
    $sesja = Autoryzacja::kontynuujSesje();

    //Pobranie wiadomości z bazy
    try{
    
      //ustanowienie połączenia z bazą danych
      require_once "Baza/Baza.php";
      $bd = Baza::polacz();
    
      //Dane do pobrania z bazy
      $dane = array(
        "wiad_id" => 'id',
        "nadawca" => 'nadawca_id',
        "temat" => 'temat',
        //"tresc" => 'tresc',
        "wyslano" => 'data_wyslania'
      );
      $dane2 = array('login');
      
      //Utworzenie obiektu typu SELECT
      $wybierz = new Zend_Db_Select($bd);
      
      //Utworzenie zapytania
      $zapytanie = $wybierz->from(array('w' => 'wiadomosci'), $dane)
          ->join(array('u' => 'uzytkownik'),
            'w.nadawca_id = u.id', $dane2)
          ->where('stan=?', 'przeczyt')
          ->where('adresat_id=?', $sesja->id)
          ->order('data_wyslania DESC');
  
      //Wysłanie zapytania
      $wyniki = $bd->query($zapytanie);
      $wiersze = $wyniki->fetchAll();
      
      //Zamknięcie połączenia z bazą
      $bd->closeConnection();
      
      //Przesłanie danych do widoku
      $this->view->przeczytaneWiadomosci = $wiersze;
      
    }catch(Zend_Db_Exception $e){
      
      echo $e->getMessages();
    
    }
  }

  /**
   * Formularz edycji listy odbiorców wiadomości<br />
   * Metoda przygotowyje formularz edycji listy odbiorców wiadomości, przypisanej
   * do zalogowanego Użytkownika.   
   * 
   * Wartość zwracana zawiera formularz i dane odbiorców do usunięcia   
   * @return array     
   */      
  private function getListaOdbiorcowFormularz(){
  
    //kontynuacja sesji
    require_once "Autoryzacja/Autoryzacja.php";
    $sesja = Autoryzacja::kontynuujSesje();

    //Pobranie listy odbiorców z bazy
    try{

      //Dane do pobrania z bazy
      $dane = array(
        "odbiorcy" => 'odbiorcy'
      );
      
      //Określenie tabeli źródłowej
      $tabela = array("u" => "uzytkownik");
      
      //Połączenie z bazą
      require_once "Baza/Baza.php";
      $bd = Baza::polacz();
      
      //Utworzenie obiektu typu SELECT
      $wybierz = new Zend_Db_Select($bd);
      
      //Utworzenie zapytania
      $zapytanie = $wybierz->from($tabela, $dane)
                  ->where('id=?', $sesja->id);
  
      //Wysłanie zapytania
      $wyniki = $bd->query($zapytanie);
      $wiersze = $wyniki->fetchAll();
      
      //Zamknięcie połączenia z bazą
      $bd->closeConnection();
   
    }catch(Zend_Db_Exception $e){
      
      echo $e->getMessages();
    
    }
    
    //Utworzenie formularza
    $formularz = new Zend_Form;
    $formularz->setAction('sukces-usun-odbiorcy');
    $formularz->setMethod('post');
    $formularz->setDescription('Formularz usuwania odbiorców');
    $formularz->setAttrib('sitename', 'czytajwnet');
   
    //Dodanie elementów do formularza
    require_once "Formularz/Elementy.php";
    $elementy = new Elementy();  
    
    //lista odbiorców zapisana jest w bazie w jednej komórce w postaci id,login;id,login;...
    $odbiorcyStr = trim($wiersze[0]['odbiorcy'], "\'"); //usunięcie apostrofów
    $odbiorcyTab = explode(';', $odbiorcyStr);
    //pobranie odbiorców w postaci: id => login;
    $odbiorcy = array();
    foreach($odbiorcyTab as $odb){
     
      $odbTmp = explode(',', $odb);
      @$odbiorcy[$odbTmp[0]] = @$odbTmp[1];  //tłumienie błędów przy braku odbiorców;
   
    }
    
    $klasaStylu = "usunOdbiorce";
    //Dodanie elementów typu checkbox dla usuwania odbiorcow
    foreach($odbiorcy as $id => $login){
    
      if($elementy->getListaOdbiorcowUsunCheckbox((string)$id, $login, $klasaStylu)){
        $formularz->addElement($elementy
          ->getListaOdbiorcowUsunCheckbox((string)$id, $login, $klasaStylu));
        }
    }

    //Zapisz zmiany
    $formularz->addElement('submit', 'submit');
    $przyciskUtworz = $formularz->getElement('submit');
    $przyciskUtworz->setLabel('Usuń zaznaczonych odbiorców');
  
    //Zapis zwracanych danych w tablicy 
    $formularzIDaneOdbiorcow['formularz'] = $formularz;
    $formularzIDaneOdbiorcow['odbiorcy'] = $odbiorcy;
  
    return $formularzIDaneOdbiorcow; 
         
  }
  
  /**
   * Wyświetlanie listy odbiorców wiadomości <br />
   * Metoda pobiera formularz edycji listy odbiorców wiadomości i przekazuje
   * go do widoku.   
   *   
   */      
  public function listaOdbiorcowAction()
  {
    //przekazanie danych do widoku
    $formIDaneOdbiorcow = $this->getListaOdbiorcowFormularz();
    $this->view->formularz = $formIDaneOdbiorcow['formularz'];
    
  }
  
  /**
   * Przetworzenie formularza usuwania odbiorców<br />
   * Metoda przetwarza formularz edycji listy odbiorców. Przy zaistnieniu zmian
   * aktualizuje dane w bazie.   
   *   
   */      
  public function sukcesUsunOdbiorcyAction(){
  
    require_once "Autoryzacja/Autoryzacja.php";
    $sesja = Autoryzacja::kontynuujSesje();
    
    $formIDaneOdbiorcow = $this->getListaOdbiorcowFormularz();
    $formularz = $formIDaneOdbiorcow['formularz'];
    $daneOdbiorcow = $formIDaneOdbiorcow['odbiorcy']; 
    
    //Sprawdzenie poprawności otrzymanych danych
    if($formularz->isValid($_POST)){
      
      //pobranie danych z formularza
      $daneZFormularza = array();
      foreach($daneOdbiorcow as $id => $login){
      
        $daneZFormularza[$id] = $formularz->getValue($id);   
      
      }
      
      //przygotowanie nowej listy odbiorców na podstawie danych z formularza
      $nowaListaOdb = '';
      foreach($daneZFormularza as $id => $wartosc){
      
        if($wartosc == 0 ){
        
          $nowaListaOdb .= $id;
          $nowaListaOdb .= ',';
          $nowaListaOdb .= $daneOdbiorcow[$id];
          $nowaListaOdb .= ';';   
        
        }else{
          //zaznaczenie, że w obecnej liście odbiorców zaszły zmiany
          $zmiany = true;
        }
        
      
      }
      if(!isset($zmiany)){
        $zmiany = false;  
      }
      //usunięcie średnika z końca listy
      $nowaListaOdb = trim($nowaListaOdb, ";");
      
      //nadpisanie w bazie listy odbiorców
      if($zmiany){
        
        try{
        
          //ustanowienie połączenia z bazą danych
          require_once "Baza/Baza.php";
          $bd = Baza::polacz();
  
          //dane do aktualizacji
          $noweDane = array('odbiorcy' => $nowaListaOdb );
          
          //warunki aktualizacji
          $warunki[] = "id = '". $sesja->id ."'";
        
          //zapytanie
          $wyniki = $bd->update('uzytkownik', $noweDane, $warunki);
          
          //zamknięcie połączenia z bazą
          $bd->closeConnection();
          
          $this->view->bledy = false;
          
        }catch(Zend_Db_Exception $e){
        
          echo $e->getMessage();
          $this->view->bledy = true;
        
        }
        
      } //koniec if($zmiany)
      
      //przekazanie danych do widoku
      $this->view->zmiany = $zmiany;
      $this->view->lista = $nowaListaOdb;
      $this->view->dane = $daneZFormularza; 
      
    } //koniec if($formularz->isValid($_POST))
  
  }
  
  /**
   * Dodanie Użytkownika do listy odbiorców wiadomości<br />
   * Metoda dodaje nowego Użytkownika do listy odbiorców wiadomości.
   *
   *  @param $login login nowego odbiorcy    
   */         
  public function dodajDoListyOdbiorcowAction(){
  
    //Kontynuacja sesji
    require_once "Autoryzacja/Autoryzacja.php";
    $sesja = Autoryzacja::kontynuujSesje();
    
    //pobranie loginu Użytkownika
    $login = $this->_getParam('uzytkownik');
    
    if(!empty($login)){
      //pobranie danych z bazy
      try{
  
        //Dane do pobrania z bazy
          //aktualna lista odbiorców
        $dane = array(
          "odbiorcy" => 'odbiorcy'
        );
          //id uzytkownika o loginie $login
        $dane2 = array(
          "id_odbiorcy" => 'id'
        );
        
        //Określenie tabeli źródłowej
        $tabela = array("u" => "uzytkownik");
        $tabela2 = array("uu" => "uzytkownik");        
        
        //Połączenie z bazą
        require_once "Baza/Baza.php";
        $bd = Baza::polacz();
        
        //Utworzenie obiektu typu SELECT
        $wybierz = new Zend_Db_Select($bd);
                  
        $zapytanie = $wybierz
          ->from($tabela, $dane)
            ->where('u.id=?', $sesja->id)
          ->from($tabela2, $dane2)
            ->where('uu.login=?', $login);
    
        //Wysłanie zapytania
        $wyniki = $bd->query($zapytanie);
        $wiersze = $wyniki->fetchAll();
        
        //Zamknięcie połączenia z bazą
        $bd->closeConnection();
      
      }catch(Zend_Db_Exception $e){
      
        echo $e->getMessage();
        $this->view->bledy = true;
        
      }
      
      if(!empty($wiersze[0])){
      
        //lista odbiorców zapisana jest w bazie w jednej komórce w postaci id,login;id,login;...
        $odbiorcyStr = trim($wiersze[0]['odbiorcy'], "\'"); //usunięcie apostrofów
        $odbiorcyTab = explode(';', $odbiorcyStr);

        //pobranie odbiorców w postaci: id => login;
        
        $odbiorcy = array();
        if(!empty($odbiorcyTab[0])){
          foreach($odbiorcyTab as $odb){
           
            $odbTmp = explode(',', $odb);
            $odbiorcy[$odbTmp[0]] = $odbTmp[1];
         
            //sprawdzenie, czy odbiorca jest już na liście
            if($odbTmp[1] == $login){
            
              $odbIstnieje = true;
              break;
            
            }
          
          }
        }
        
        if(!isset($odbIstnieje)){
          
          //dodanie nowego odbiorcy do tabeli
          $odbiorcy[$wiersze[0]['id_odbiorcy']] = $login;
          
          //przygotowanie nowej listy odbiorców
          $nowaLista = '';
          foreach($odbiorcy as $id => $login){
          
            $nowaLista .= $id;
            $nowaLista .= ',';
            $nowaLista .= $login;
            $nowaLista .= ';';  
          
          }
          
          //usunięcie ostatniego średnika
          $nowaLista = trim($nowaLista, ";");
          
          //zapis nowej listy do bazy
          //ustanowienie połączenia z bazą danych
          require_once "Baza/Baza.php";
          $bd = Baza::polacz();
          
          //dane do aktualizacji
          $noweDane = array('odbiorcy' => $nowaLista );
          
          //warunki aktualizacji
          $warunki[] = "id = '". $sesja->id ."'";
        
          //zapytanie
          $wyniki = $bd->update('uzytkownik', $noweDane, $warunki);
          
          //zamknięcie połączenia z bazą
          $bd->closeConnection();
          
          $this->view->bledy = false;
        
        }else{
          
          $this->view->odbiorcaIstnieje = $odbIstnieje;
          $this->view->bledy = true;
          
        }
        
        $this->view->nowyOdbiorca = $login;
        
        //dane do testów
        /*************************************
        //przekazanie danych do widoku
        //$this->view->nowaLista = $nowaLista;
        //$this->view->odbiorcy = $odbiorcy;
        *************************************/
        
      }else{
      
        $this->view->bledy = true;  
      
      }  
    } //koniec if(!empty($login))
    else{
    
      $this->view->bledy = true;
    
    }
  }

}













