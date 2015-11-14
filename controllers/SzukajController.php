<?php

/**
 * Klasa odpowiedzialna za operacje związane z wyszukiwaniem.
 *
 */  
class SzukajController extends Zend_Controller_Action
{

  /**
   * Metoda inicjalizująca pozostałe funkcje kontrolera szukaj<br />
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
   * Przeniesienie do menu głównego kontrolera szukaj.
   *
   */        
  public function indexAction()
  {
    $this->_redirect('szukaj/szukaj-menu');
  }

  /*
  //tworzenie nowego indeksu wyszukiwania
  //Nowy indeks jest tworzony jednokrotnie przy instalacji aplikacji, po ręcznym
  //wywołaniu poniższej metody, która dla bezpieczeństwa została zakomentowana.
  
  public function utworzIndexAction(){
  
    try{
    
      //lokalizacja nowego indeksu wyszukiwania
      $lokalizacja = '../application/wyszukiwanie';
    
      Zend_Search_Lucene::create($lokalizacja);
    
      //ustawienie analizatora dla wartości numerycznych
      $analizator =
        new Zend_Search_Lucene_Analysis_Analyzer_Common_TextNum_CaseInsensitive();
      Zend_Search_Lucene_Analysis_Analyzer::setDefault($analizator);
    
    }catch(Zend_Search_Exception $e){
      
      echo $e->getMessage();
      
    }  
    
    $this->_helper->viewRenderer->setNoRender();
  
  }
  */
  
  /**
   * Menu wyszukiwania<br />
   * Metoda wyświetlająca menu główne wyszukiwania
   *   
   */      
  public function szukajMenuAction()
  {
      // action body
  }
  
  /**
   * Wyszukiwanie dokumentów<br />
   * Metoda pobierająca formularz wyszukiwania dokumentów i przekazująca go
   * do widoku   
   *   
   */      
  public function szukajDokumentAction(){
  
    //Przekazanie foemularza do widoku
    $formularz = $this->getFormularzWyszukiwaniaDok();
    $this->view->formularz = $formularz;    
  
  }
  
  /**
   * Utworzenie formularza wyszukiwania<br />
   * Metoda przygotowuje formularz wyszukiwania dokumentów.
   * 
   * @return Zend_Form     
   */      
  private function getFormularzWyszukiwaniaDok(){
  
    //Utworzenie formularza
    $formularz = new Zend_Form;
    $formularz->setAction('sukces-szukaj-dokument');
    $formularz->setMethod('post');
    $formularz->setDescription('Formularz wyszukiwania dokumentów');
    $formularz->setAttrib('sitename', 'czytajwnet');
  
    //Dodanie elementów do formularza
    require_once "Formularz/Elementy.php";
    $elementy = new Elementy();
    
    //pole wyszukiwania
    $formularz->addElement($elementy->getWyszukajDokumentPoleText());

    //Szukaj
    $formularz->addElement('submit', 'submit');
    $przyciskUtworz = $formularz->getElement('submit');
    $przyciskUtworz->setLabel('Szukaj');
    
    return $formularz;  
  
  }
  
  /**
   * Wyniki wyszukiwania dokumentów<br />
   * Metoda przetwarzająca formularz wyszukiwania dokumentów. Przeszukiwane są
   * dane indeksu wyszukiwania.
   *   
   */      
  public function sukcesSzukajDokumentAction(){
  
    require_once "Autoryzacja/Autoryzacja.php";
    $sesja = Autoryzacja::kontynuujSesje();
    
    $formularz = $this->getFormularzWyszukiwaniaDok();
    
    //Sprawdzenie poprawności otrzymanych danych
    if($formularz->isValid($_POST)){
      
      $fraza = $formularz->getValue('wyszukajDokument');
            
      //przeszukanie indeksu
      try{

        //Otworzenie indeksu wyszukiwania
        $indeks = Zend_Search_Lucene::open('../application/wyszukiwanie');
        
        //ustawienie właściwości indeksu
        //przeszukiwanie we wszystkich polach
        $indeks->setDefaultSearchField(null);
        
        //limit zwracanych wyników
        //$indeks->setResultSetLimit(2);
        
        //ustawienie analizatora dla wartości numerycznych
        $analizator =
          new Zend_Search_Lucene_Analysis_Analyzer_Common_TextNum_CaseInsensitive();
        Zend_Search_Lucene_Analysis_Analyzer::setDefault($analizator);
        
        //utworzenie zapytania
        $zapytanie = $fraza;
        $zapytanie = Zend_Search_Lucene_Search_QueryParser::parse($zapytanie);
        
        //przeszukanie indeksu
        $wyniki = $indeks->find($zapytanie);
        
        //pobranie danych o ilości dokumentów
        $ileOgolemDokumentow = $indeks->maxDoc();
        $ileNieUsunietychDok = $indeks->numDocs();
        
        //przekazanie wyników do widoku
        $this->view->fraza = $fraza;
        $this->view->wyniki = $wyniki;
        $this->view->ogolem = $ileOgolemDokumentow;
        $this->view->dostepnych = $ileNieUsunietychDok;
 
      }//koniec try
      catch(Zend_Search_Exception $e){
      
        //Przekazanie błędów do widoku
        $this->view->bledy = true;
        $this->view->formularz = $formularz;
        echo $e->getMessages();
      }
      
      $this->view->bledy = false;
    
    }else{
    
      $this->view->bledy = true;
      $this->view->formularz = $formularz;
    
    }
      
  
  }
  
  /**
   * Wyszukiwanie Użytkowników<br />
   * Metoda pobiera formularz wyszukiwania Użytkowników i przekazuje go do
   * widoku.   
   *
   */         
  public function szukajUzytkownikAction(){
  
    //Przekazanie foemularza do widoku
    $formularz = $this->getFormularzWyszukiwaniaUzyt();
    $this->view->formularz = $formularz;    
  
  }
  
  /**
   * Utworzenie formularza wyszukiwania Użytkowników<br />
   * Metoda tworzy formularz wyszukiwania Użytkowników.
   * 
   * @return Zend_Form     
   */      
  private function getFormularzWyszukiwaniaUzyt(){
  
    //Utworzenie formularza
    $formularz = new Zend_Form;
    $formularz->setAction('sukces-szukaj-uzytkownik');
    $formularz->setMethod('post');
    $formularz->setDescription('Formularz wyszukiwania Użytkowników');
    $formularz->setAttrib('sitename', 'czytajwnet');
  
    //Dodanie elementów do formularza
    require_once "Formularz/Elementy.php";
    $elementy = new Elementy();
    
    //pole wyszukiwania
    $formularz->addElement($elementy->getWyszukajUzytkownikaPoleText());

    //Szukaj
    $formularz->addElement('submit', 'submit');
    $przyciskUtworz = $formularz->getElement('submit');
    $przyciskUtworz->setLabel('Szukaj');
    
    return $formularz;  
  
  }
  
  /**
   * Przetworzenie formularza wyszukiwania Uzytkownika <br />
   * Metoda przetwarza formularz wyszukiwania Użytkowników. Przeszukiwane są zasoby
   * bazy danych.   
   *
   */         
  public function sukcesSzukajUzytkownikAction(){
  
    require_once "Autoryzacja/Autoryzacja.php";
    $sesja = Autoryzacja::kontynuujSesje();

    $formularz = $this->getFormularzWyszukiwaniaUzyt();

    //sprawdzenie poprawności danych z formularza
    if($formularz->isValid($_POST)){
  
      //pobranie wyszukiwanej frazy
      $fraza = $formularz->getValue('wyszukajUzytkownika');
  
      $fraza = htmlspecialchars($fraza, ENT_COMPAT, "utf-8");

      //pobranie danych z bazy
      try{
  
        //sprawdzenie, czy dane Użytkownika mogą być wyświetlane
  
        //Dane do pobrania z bazy
        $dane = array("id"        => 'id',
                      "login"     => 'login',
                      "imie"      => 'imie',
                      "nazwisko"  => 'nazwisko',
                      "opis"      => 'opis_konta',
                      "avatar"    => 'avatar',
                      "dolaczyl"  => 'data_utworzenia_konta');
        
        //Określenie tabeli źródłowej
        $tabela = array("u" => "uzytkownik");
        
        //Połączenie z bazą
        require_once "Baza/Baza.php";
        $bd = Baza::polacz();
        
        //oczyszczenie frazy
        $fraza = htmlspecialchars($fraza, ENT_NOQUOTES, "UTF-8");
        $fraza = $bd->quote($fraza);
        
        //Utworzenie obiektu typu SELECT
        $wybierz = new Zend_Db_Select($bd);
        
        //Utworzenie zapytania
        $zapytanie = $wybierz
          ->from($tabela, $dane)
          ->where('login LIKE ?', $fraza)
          ->orWhere('login LIKE ?', trim($fraza, "\'"))
          ->orWhere('imie LIKE ?', $fraza)
          ->orWhere('nazwisko LIKE ?', $fraza)
          ->orWhere('opis_konta LIKE ?', $fraza)
          ->order('login ASC');

        //Wysłanie zapytania
        $wyniki = $bd->query($zapytanie);
        $wiersze = $wyniki->fetchAll();
        
        //Zamknięcie połączenia z bazą
        $bd->closeConnection();
        
        if(count($wiersze >= 1)){
        
          $this->view->uzytkownicy = $wiersze;
        
        }
        
        $this->view->bledy = false;
  
      }catch(Zend_Db_Exception $e){
        
        //$this->_helper->viewRenderer->setNoRender();
        echo $e->getMessages();
        $this->view->bledy = true;
        $this->view->formularz = $formularz; 
      
      }
      
    } //koniec if($formularz->isValid($_POST))                             
    else{
    
      $this->view->bledy = true;
      $this->view->formularz = $formularz; 
    
    }
  
  }


}



