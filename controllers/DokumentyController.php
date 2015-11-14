<?php

/**
 * Klasa odpowiedzialna za operacje związane z dokumentami
 *
 */  
class DokumentyController extends Zend_Controller_Action
{
  /**
   * Metoda inicjalizująca pozostałe funkcje kontrolera Dokumenty.<br />
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
   * Przekierowanie do strony menu Dokumentów
   *
   */        
  public function indexAction()
  {
    $this->_redirect('dokumenty/dokumenty-menu');
  }

  /**
   * Menu dokumentów Użytkownika<br />
   * Metoda wyświetla dostępne opcje dla Dokumentów Użytkownika   
   *
   */        
  public function dokumentyMenuAction()
  {
      // action body
  }

  /**
   * Pobranie danych o dokumentach Użytkownika<br />
   * Metoda wyświetla w postaci tabelarycznej dane dokumentów Użytkownika
   * pobrane z bazy danych. Umożliwia również podjęcie podstawowych operacji,
   * takich jak odczytywanie i pobieranie dokumentów.         
   *
   */        
  public function dokumentyPokazAction()
  {
    //Kontynuacja sesji
    require_once "Autoryzacja/Autoryzacja.php";
    $sesja = Autoryzacja::kontynuujSesje();
    
    //pobranie danych z bazy
    try{
    
      //Dane do pobrania z bazy
      $dane = array(
        "id" => 'id', //niezbędne do wyświetlenia dokumentu
        "tytuł" => 'tytul',
        "data dodania" => 'data_dodania',
        "ostatnia modyfikacja" => 'ostatnia_modyfikacja',
        "autor" => 'autor',
        "przedmiot" => 'przedmiot',
        "rodzaj" => 'rodzaj',
        "isbn" => 'isbn',
        "wydawnictwo" => 'wydawnictwo',
        "rok wydania" => 'rok_wydania',
        "tagi" => 'tagi',
        "opis" => 'opis',
        "dostępność" => 'dostepnosc',
        "wyświetleń" => 'ilosc_wyswietlen'  
      );
      
      //Określenie tabeli źródłowej
      $tabela = array("d" => "dokumenty");
      
      //Połączenie z bazą
      require_once "Baza/Baza.php";
      $bd = Baza::polacz();
      
      //Utworzenie obiektu typu SELECT
      $wybierz = new Zend_Db_Select($bd);
      
      
      //Utworzenie zapytania
      $zapytanie = $wybierz->from($tabela, $dane)
                  ->where('uzytkownik_id=?', $sesja->id);
  
      //Wysłanie zapytania
      $wyniki = $bd->query($zapytanie);
      $wiersze = $wyniki->fetchAll();
      
      //Zamknięcie połączenia z bazą
      $bd->closeConnection();
      
      //Przekazanie danych do widoku
      $this->view->dokumenty = $wiersze;
    
    }catch(Zend_Db_Exception $e){
    
      echo $e->getMessage();
      
    }
  
  }
  
  /**
   * Tworzenie formularza dodawania dokumentu<br />
   * Metoda tworząca formularz dodawania nowego dokumentu Użytkownika,
   * pozwalający określić minimum wymaganych danych dokumentu. Wszystkie pola
   * są wymagane.      
   *  
   *  @return Zend_Form      
   */  
  private function getFormularzDodajDokument(){
  
    //Utworzenie formularza
    $formularz = new Zend_Form;
    $formularz->setAction('sukces-dodaj-dokument');
    $formularz->setMethod('post');
    $formularz->setDescription('Formularz dodawania nowego dokumentu');
    //Przesyłanie plików
    $formularz->setAttrib('enctype', 'multipart/form-data');
    $formularz->setAttrib('sitename', 'czytajwnet');
  
    //Dodanie elementów do formularza
    require_once "Formularz/Elementy.php";
    $dodajDokumentElementy = new Elementy();
    
    //Przeglądaj
    $formularz->addElement($dodajDokumentElementy->getDodajDokumentPolePlik());
    //Tytuł
    $formularz->addElement($dodajDokumentElementy->getDokumentTytulPoleText());
    //Autor
    $formularz->addElement($dodajDokumentElementy->getDokumentAutorPoleText());
    //Captcha
    //$formularz->addElement($dodajDokumentElementy->getCaptcha('dodajDokument', 5));
    //Wyczyść
    $formularz->addElement('reset', 'reset');
    $przyciskWyczysc = $formularz->getElement('reset');
    $przyciskWyczysc->setLabel('Wyczyść pola');
    //Zapisz zmiany
    $formularz->addElement('submit', 'submit');
    $przyciskUtworz = $formularz->getElement('submit');
    $przyciskUtworz->setLabel('Dodaj dokument');
    
    return $formularz;  
  
  }
  
  /**
   * Dodawanie nowego dokumentu<br />
   * Metoda pobierająca formularz dodawania nowego dokumentu i przekazująca
   * go do widoku.      
   * 
   */        
  public function dokumentyDodajAction()
  {
  
    //Przekazanie foemularza do widoku
    $formularz = $this->getFormularzDodajDokument();
    $this->view->formularz = $formularz;  
  
  }
  
  /**
  * Weryfikacja dodanego dokumentu<br />
  * Metoda przetwarzająca formularz dodawania nowego dokumentu. Po poprawnej
  * weryfikacji dane dokumentu zapisywane są w bazie danych, dokument zapisywany
  * jest na serwerze w katalogu publicznym z dokumentami Użytkownika oraz
  * aktualizowany jest indeks wyszukiwania.        
  *
  */
  public function sukcesDodajDokumentAction(){
  
    require_once "Autoryzacja/Autoryzacja.php";
    $sesja = Autoryzacja::kontynuujSesje();
    
    $formularz = $this->getFormularzDodajDokument();
    
      //$mime = $formularz->dokument->getMimeType();
      //echo $mime."<br />";
    
    //Sprawdzenie poprawności otrzymanych danych
    if($formularz->isValid($_POST)){
      
      $tytul = $formularz->getValue('dokumentTytul');
      $autor = $formularz->getValue('dokumentAutor');
      $nazwaTmp = "nowy dokument";
            
      //Zapis danych do bazy
      try{
      
        //ustanowienie połączenia z bazą danych
        require_once "Baza/Baza.php";
        $bd = Baza::polacz();
        
        //oczyszczenie danych
        $tytul = $tytul;
        $autor = $autor;
        $nazwaTmp = $nazwaTmp;
        
        $daneDokumentu = array(
          'uzytkownik_id' => $sesja->id,
          'tytul' => $bd->quote($tytul),
          'autor' => $bd->quote($autor),
          'nazwa' => $bd->quote($nazwaTmp),
          'data_dodania' => new Zend_Db_Expr('NOW()') 
        );
      
        //zapytanie
        $wyniki = $bd->insert('dokumenty', $daneDokumentu);
      
        //Zmiana nazwy pliku
        $dokId = $bd->lastInsertId();
          //pobranie danych o nowym dokumencie
        @$oryginalnaNazwaPliku = pathinfo($formularz->dokument->getFileName());
        $nowaNazwa = $dokId . "_dok." . $oryginalnaNazwaPliku['extension'];
          //Zmiana nazwy pliku na domyślny format
        $formularz->dokument->addFilter('Rename', $nowaNazwa);
          //zapis pliku na serwerze pod nową nazwą
        $formularz->dokument->receive();
        
        //Zapis nowej nazwy w bazie
        $noweDane = array('nazwa' => $bd->quote($nowaNazwa));
        $warunki = "id = '". $dokId ."'";
        $wyniki = $bd->update('dokumenty', $noweDane, $warunki);
        
        //dodanie newsa
        $temat = "Nowy dokument!";
        $tresc = 
          "Użytkownik <b>". $sesja->login ."</b> dodał nowy dokument:<br /><b>". 
          $tytul ."</b>.";
        $news = array(
          'temat' => $bd->quote($temat),
          'tresc' => $bd->quote($tresc),
          'data_dodania' => new Zend_Db_Expr('NOW()')
        );
        
        $wyniki = $bd->insert('newsletter', $news);
      
        //zamknięcie połączenia z bazą
        $bd->closeConnection();

        //aktualizacja indeksu wyszukiwania
        if($wyniki == 1){
        
          //otworzenie istniejącego indeksu
          try{
    
            $indeks = Zend_Search_Lucene::open('../application/wyszukiwanie');
            //ustawienie analizatora dla wartości numerycznych
            $analizator =
              new Zend_Search_Lucene_Analysis_Analyzer_Common_TextNum_CaseInsensitive();
            Zend_Search_Lucene_Analysis_Analyzer::setDefault($analizator);
          
          }catch(Zend_Search_Exception $e){
          
            echo $e->getMessage();
          
          }
        
          //utworzenie nowego dokumentu
          $dok = new Zend_Search_Lucene_Document();
          
          //dodanie pól wyszukiwania dla dokumentu
          $dok->addField(Zend_Search_Lucene_Field::
            Text('tytul', $tytul, 'utf-8'));
          $dok->addField(Zend_Search_Lucene_Field::
            Text('autor', $autor, 'utf-8'));                    
          $dok->addField(Zend_Search_Lucene_Field::
            Text('idBazy', $dokId, 'utf-8'));
            
          //dodanie dokumentu do indeksu
          $indeks->addDocument($dok);
        
        }
      
      }//koniec try
      catch(Zend_Db_Exception $e){
      
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
   * Tworzenie formularza edycji dokumentu<br />
   * Metoda tworzy formularz edycji dokumentu. Wymagane są pola: tytuł
   * oraz autor dokumentu, wskazanie właściwego dokumentu z listy rozwijanej,
   * pozostałe pola sa opcjonalne. Formularz umożliwia podanie szczegółowych
   * danych dokumentu i określenie poziomu dostępności.      
   *    
   * $return Zend_Form      
   */        
  private function getFormularzEdycjiDokumentu(){

    //Kontynuacja sesji
    require_once "Autoryzacja/Autoryzacja.php";
    $sesja = Autoryzacja::kontynuujSesje();

    //Pobranie nazw dokumentow z bazy
    try{
      
      //Utworzenie formularza
      $formularz = new Zend_Form;
      $formularz->setAction('sukces-edytuj-dokument');
      $formularz->setMethod('post');
      $formularz->setDescription('Formularz edycji dokumentu');
      $formularz->setAttrib('sitename', 'czytajwnet');
    
      //Dodanie elementów do formularza
      require_once "Formularz/Elementy.php";
      $edytujDokumentElementy = new Elementy();
      
      //Tytuł
      $formularz->addElement($edytujDokumentElementy->getDokumentTytulPoleText());
      //Autor
      $formularz->addElement($edytujDokumentElementy->getDokumentAutorPoleText());
      //Przedmiot
      $formularz->addElement($edytujDokumentElementy->getDokumentPrzedmiotPoleText());
      //Rodzaj
      $formularz->addElement($edytujDokumentElementy->getDokumentRodzajPoleSelect());
      //Isbn
      $formularz->addElement($edytujDokumentElementy->getDokumentIsbnPoleText());
      //Wydawnictwo
      $formularz->addElement($edytujDokumentElementy->getDokumentWydawnictwoPoleText());
      //Rok wydania
      $formularz->addElement($edytujDokumentElementy->getDokumentRokWydaniaPoleText());
      //Tagi
      $formularz->addElement($edytujDokumentElementy->getDokumentTagiPoleText());
      //Opis
      $formularz->addElement($edytujDokumentElementy->getDokumentOpisPoleText());
      //Dostępność
      $formularz->addElement($edytujDokumentElementy->getDokumentDostepnoscPoleSelect());
    
      //Dane do pobrania z bazy
      $dane = array(
        "id" => 'id',
        "tytuł" => 'tytul'
      );
      
      //Określenie tabeli źródłowej
      $tabela = array("d" => "dokumenty");
      
      //Połączenie z bazą
      require_once "Baza/Baza.php";
      $bd = Baza::polacz();
      
      //Utworzenie obiektu typu SELECT
      $wybierz = new Zend_Db_Select($bd);
      
      //Utworzenie zapytania
      $zapytanie = $wybierz->from($tabela, $dane)
                  ->where('uzytkownik_id=?', $sesja->id);
  
      //Wysłanie zapytania
      $wyniki = $bd->query($zapytanie);
      $wiersze = $wyniki->fetchAll();
      
      //Zamknięcie połączenia z bazą
      $bd->closeConnection();
    
      $dokumentyTmp[''] = '--nie wybrano--';
      foreach($wiersze as $wiersz){
        $dokumentyTmp[ trim($wiersz['id'], "\'")] = trim($wiersz['tytuł'], "\'");
      }
      $dokumenty = array('multiOptions' => $dokumentyTmp);
      
      //Dodanie listy dostępnych dokumentów
      $listaDokumentow = new Zend_Form_Element_Select('listaDokumentow', $dokumenty);
      $listaDokumentow->setLabel('Wybierz dokument do edycji:');
        //Lista dokumentów
      $formularz->addElement($listaDokumentow);
      $listaDokumentowElement = $formularz->getElement('listaDokumentow');
      $listaDokumentowElement->setRequired(true);
    
      //Wyczyść
      $formularz->addElement('reset', 'reset');
      $przyciskWyczysc = $formularz->getElement('reset');
      $przyciskWyczysc->setLabel('Wyczyść pola');
      //Zapisz zmiany
      $formularz->addElement('submit', 'submit');
      $przyciskUtworz = $formularz->getElement('submit');
      $przyciskUtworz->setLabel('Zapisz zmiany');
    
      return $formularz;
         
    }catch(Zend_Db_Exception $e){
      
      $e->getMessages();
    
    }     
  
  }
  
  /**
   * Pobranie formularza edycji dokumentów<br />
   * Metoda pobiera formularz edycji dokumentów i przekazuje go do widoku.  
   *
   */        
  public function dokumentyEdytujAction()
  {
    //Utworzenie formularz
    $formularz = $this->getFormularzEdycjiDokumentu();
    //Przekazanie formularza do widoku
    $this->view->formularz = $formularz;
  
  }
  
  /**
   * Przetworzenie formularza edycji dokumentu<br />
   * Metoda przetwarza formularz edycji dokumentu. Po poprawnej weryfikacji
   * dane w bazie i indeksie wyszukiwania są aktualizowane.      
   *
   */        
  public function sukcesEdytujDokumentAction(){
  
    require_once "Autoryzacja/Autoryzacja.php";
    $sesja = Autoryzacja::kontynuujSesje();
    
    $formularz = $this->getFormularzEdycjiDokumentu();
    
    //Sprawdzenie poprawności otrzymanych danych
    if($formularz->isValid($_POST)){
      
      //pobranie danych z formularza
      $dokId = $formularz->getValue('listaDokumentow');
      //$imie = $formularz->getValue('imieUzytkownika');
      $tytul = $formularz->getValue('dokumentTytul');
      $autor = $formularz->getValue('dokumentAutor');
      $przedmiot = $formularz->getValue('dokumentPrzedmiot');
      $rodzaj = $formularz->getValue('dokumentRodzaj');
      $isbn = $formularz->getValue('dokumentIsbn');
      $wydawnictwo = $formularz->getValue('dokumentWydawnictwo');
      $rokWydania = $formularz->getValue('dokumentRokWydania');
      $tagi = $formularz->getValue('dokumentTagi');
      $opis = $formularz->getValue('dokumentOpis');
      $dostepnosc = $formularz->getValue('dokumentDostepnosc'); 

      //Zapis danych do bazy
      try{
      
        //ustanowienie połączenia z bazą danych
        require_once "Baza/Baza.php";
        $bd = Baza::polacz();
        
        //oczyszczenie danych
        //$dokId = $dokId; 
        //$imie = $imie;
        $tytul = $tytul;
        $autor = $autor;
        $przedmiot = $przedmiot;
        //$rodzaj = $rodzaj;
        //$isbn = $isbn;
        $wydawnictwo = $wydawnictwo;
        $rokWydania = $rokWydania;
        $tagi = $tagi;
        $opis = $opis;
        //$dostepnosc = $dostepnosc;
      
        //dane do zapisu
        $noweDane = array();
        //if(!empty($imie)){
        //  $noweDane["imie"] = $bd->quote($imie);  
        //}
        if(!empty($tytul)){
          $noweDane["tytul"] = $bd->quote($tytul);  
        }
        if(!empty($autor)){
          $noweDane["autor"] = $bd->quote($autor);  
        }
        if(!empty($przedmiot)){
          $noweDane["przedmiot"] = $bd->quote($przedmiot);  
        }
        if(!empty($rodzaj)){
          $noweDane["rodzaj"] = $bd->quote($rodzaj);  
        }
        if(!empty($isbn)){
          $noweDane["isbn"] = $bd->quote($isbn);  
        }
        if(!empty($wydawnictwo)){
          $noweDane["wydawnictwo"] = $bd->quote($wydawnictwo);  
        }
        if(!empty($rokWydania)){
          $noweDane["rok_wydania"] = $bd->quote($rokWydania);  
        }
        if(!empty($tagi)){
          $noweDane["tagi"] = $bd->quote($tagi);  
        }
        if(!empty($opis)){
          $noweDane["opis"] = $bd->quote($opis);  
        }
        if(!empty($dostepnosc)){
          $noweDane["dostepnosc"] = $bd->quote($dostepnosc);  
        }
        
        //data modyfikacji
        $noweDane["ostatnia_modyfikacja"] = new Zend_Db_Expr('NOW()');

        //warunki aktualizacji
        $warunki[] = "id = '". $dokId ."'";
      
        //zapytanie
        $wyniki = $bd->update('dokumenty', $noweDane, $warunki);
        
        //zamknięcie połączenia z bazą
        $bd->closeConnection();
      
        //aktualizacja indeksu wyszukiwania
        if($wyniki == 1){
        
          
          //aktualizacja indeksu wyszukiwania
          try{
    
            //otworzenie istniejącego indeksu
            $indeks = Zend_Search_Lucene::open('../application/wyszukiwanie');
        
            //echo "<br />" .$dokId. "<br />";  
            
            
            //ustawienie analizatora dla wartości numerycznych
            $analizator =
              new Zend_Search_Lucene_Analysis_Analyzer_Common_TextNum_CaseInsensitive();
            Zend_Search_Lucene_Analysis_Analyzer::setDefault($analizator);
            
            //usunięcie poprzedniego dokumentu - jedyna możliwość aktualizacji
            $indeks->setDefaultSearchField('idBazy');
            $skladnik = new Zend_Search_Lucene_Index_Term($dokId);
            $zapytanie = new Zend_Search_Lucene_Search_Query_Term($skladnik);
            $szukaj = $indeks->find($zapytanie);
            
            foreach($szukaj as $wynik){
              //usunięcie właściwych wpisów
              $indeks->delete($wynik->id);
              //echo "<br />".$wynik->id."<br />";
            }
            $indeks->commit();
            
            //utworzenie nowego dokumentu
            $dok = new Zend_Search_Lucene_Document();
            
            //dodanie pól wyszukiwania dla dokumentu
            $dok->addField(Zend_Search_Lucene_Field::
              Text('tytul', $tytul, 'utf-8'));
            $dok->addField(Zend_Search_Lucene_Field::
              Text('autor', $autor, 'utf-8'));                    
            $dok->addField(Zend_Search_Lucene_Field::
              Text('idBazy', $dokId, 'utf-8'));
            
            if(!empty($przedmiot)){
              $przedmiot = trim($przedmiot, "\'");
              $dok->addField(Zend_Search_Lucene_Field::
                Text('przedmiot', $przedmiot, 'utf-8'));  
            }
            if(!empty($isbn)){
              $isbn = trim($isbn, "\'");
              $dok->addField(Zend_Search_Lucene_Field::
                Text('isbn', $isbn, 'utf-8'));  
            }
            if(!empty($wydawnictwo)){
              $wydawnictwo = trim($wydawnictwo, "\'");
              $dok->addField(Zend_Search_Lucene_Field::
                Text('wydawnictwo', $wydawnictwo, 'utf-8'));  
            }
            if(!empty($tagi)){
              $tagi = trim($tagi, "\'");
              $dok->addField(Zend_Search_Lucene_Field::
                Text('tagi', $tagi, 'utf-8'));  
            }
            if(!empty($opis)){
              $opis = trim($opis, "\'");
              $dok->addField(Zend_Search_Lucene_Field::
                Text('opis', $opis, 'utf-8'));  
            }
            
            //dodanie nowego dokumentu do indeksu
            $indeks->addDocument($dok);
          
          }catch(Zend_Search_Exception $e){
          
            echo $e->getMessage();
          
          }
        
        }
      
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
   * Tworzenie formularza usuwania dokumentów<br />
   * Metoda tworzy formularz usuwania dokumentów. Dla każdego dokumentu
   * Użytkownika generuje pole wyboru. Umożliwia jednoczesne usunięcie
   * wszystkich dokumentów.   
   * 
   *    Metoda zwraca formularz i dane dokumentów do usunięcia   
   * @return array    
   */      
  private function getFormularzDokumentyUsun(){

    //Kontynuacja sesji
    require_once "Autoryzacja/Autoryzacja.php";
    $sesja = Autoryzacja::kontynuujSesje();

    //Pobranie nazw dokumentow z bazy
    try{
      
      //Utworzenie formularza
      $formularz = new Zend_Form;
      $formularz->setAction('sukces-usun-dokumenty');
      $formularz->setMethod('post');
      $formularz->setDescription('Formularz usuwania dokumentow');
      $formularz->setAttrib('sitename', 'czytajwnet');
    
      //Dodanie elementów do formularza
      require_once "Formularz/Elementy.php";
      $usunDokumentElementy = new Elementy();
    
      //Dane do pobrania z bazy
      $dane = array(
        "id" => 'id',
        "tytul" => 'tytul'
      );
      
      //Określenie tabeli źródłowej
      $tabela = array("d" => "dokumenty");
      
      //Połączenie z bazą
      require_once "Baza/Baza.php";
      $bd = Baza::polacz();
      
      //Utworzenie obiektu typu SELECT
      $wybierz = new Zend_Db_Select($bd);
      
      //Utworzenie zapytania
      $zapytanie = $wybierz->from($tabela, $dane)
                  ->where('uzytkownik_id=?', $sesja->id);
  
      //Wysłanie zapytania
      $wyniki = $bd->query($zapytanie);
      $wiersze = $wyniki->fetchAll();
      
      //Zamknięcie połączenia z bazą
      $bd->closeConnection();
    
      $daneDokumentow = array();
      $klasaStylu = "usunDokumenty";
      //Dodanie elementów typu checkbox dla usuwania dokumentów
      foreach($wiersze as $wiersz){
    
        $dokId = trim($wiersz['id'], "\'");
        $dokTytul = trim($wiersz['tytul'], "\'");
        $daneDokumentow[$dokId] = $dokTytul;  
      
        $formularz->addElement($usunDokumentElementy
          ->getDokumentUsunCheckbox($dokId, $klasaStylu ));
      }

      //Wyczyść
      $formularz->addElement('reset', 'reset');
      $przyciskWyczysc = $formularz->getElement('reset');
      $przyciskWyczysc->setLabel('Odznacz');
      //Zapisz zmiany
      $formularz->addElement('submit', 'submit');
      $przyciskUtworz = $formularz->getElement('submit');
      $przyciskUtworz->setLabel('Usuń');
    
      //Zapis zwracanych danych w tablicy 
      $formularzIDaneDokumentow['form'] = $formularz;
      $formularzIDaneDokumentow['dokumenty'] = $daneDokumentow;
    
      return $formularzIDaneDokumentow; 
         
    }catch(Zend_Db_Exception $e){
      
      $e->getMessages();
    
    }     
    
  
  }
  
  /**
   * Usuwanie dokumentów<br />
   * Metoda pobiera formularz usuwania dokumentów i przekazuje go do widoku.
   *   
   */      
  public function dokumentyUsunAction()
  {
    $formIDane = $this->getFormularzDokumentyUsun();
    $this->view->formularz = $formIDane['form'];
    $this->view->daneDokumentow = $formIDane['dokumenty']; 
  }
  
  
  /**
   * Przetworzenie formularza usuwania dokumentów<br />
   * Metoda przetwarza formularz usuwania dokumentów. Po poprawnej weryfikacji
   * aktualizowane są dane w bazie, indeksie wyszukiwania i określone pliki
   * zostają fizycznie usunięte z serwera.      
   *   
   */      
  public function sukcesUsunDokumentyAction(){

    
    require_once "Autoryzacja/Autoryzacja.php";
    $sesja = Autoryzacja::kontynuujSesje();
    
    $formIDane = $this->getFormularzDokumentyUsun();
    $formularz = $formIDane['form'];
    $daneDokumentow = $formIDane['dokumenty'];
    
    //Sprawdzenie poprawności otrzymanych danych
    if($formularz->isValid($_POST)){
      
      //pobranie danych z formularza
      $daneZFormularza = array();
      foreach($daneDokumentow as $id => $tytul){
      
        $daneZFormularza[$id] = $formularz->getValue($id);   
      
      }

      //Usunięcie wybranych plików z serwera i z bazy
      try{
      
        //ustanowienie połączenia z bazą danych
        require_once "Baza/Baza.php";
        $bd = Baza::polacz();

        //usunięcie wybranych dokumentów
        $podsumowanie = array();
        $bledySerwera = array();
        foreach($daneZFormularza as $id => $wartosc){
        
          //warunki usunięcia dokumentu
          $warunek = "id = '". $id ."'";
          if($wartosc == 1){
          
            //pobranie nazwy pliku przed usunięciem wpisu z bazy
              //Dane do pobrania z bazy
            $dane = array("nazwa" => 'nazwa');
              //Określenie tabeli źródłowej
            $tabela = array("d" => "dokumenty");
              //Utworzenie obiektu typu SELECT
            $wybierz = new Zend_Db_Select($bd);
              //Utworzenie zapytania
            $zapytanie = $wybierz->from('dokumenty', $dane)
                      ->where('id=?', $id);
              //Wysłanie zapytania
            $wyniki = $bd->query($zapytanie);
            $wiersz = $wyniki->fetchAll();
              //określenie nazwy pliku
            $nazwaPliku = trim($wiersz[0]['nazwa'], "\'");
            //echo "(".$nazwaPliku.")<br />"; 
              //Określenie położenia pliku
            $lokalizacja = $_SERVER['DOCUMENT_ROOT'] . '/public/uzytk/' . (string)$sesja->id . '/dokumenty/'; 
            
            //usunięcie dokumentu z bazy
            $rezultat = $bd->delete('dokumenty', $warunek );
            
            if($rezultat == 1){
            
              $podsumowanie[$id] = "<b>OK</b>: ". $daneDokumentow[$id];
              
              try{
                
                //usunięcie pliku z serwera
                @unlink($lokalizacja.$nazwaPliku);
                
                //aktualizacja indeksu wyszukiwania    
                try{
                  //otworzenie istniejącego indeksu
                  $indeks = Zend_Search_Lucene::open('../application/wyszukiwanie');
                  
                  //ustawienie analizatora dla wartości numerycznych
                  $analizator =
                    new Zend_Search_Lucene_Analysis_Analyzer_Common_TextNum_CaseInsensitive();
                  Zend_Search_Lucene_Analysis_Analyzer::setDefault($analizator);
                    
                  //usunięcie poprzedniego dokumentu - jedyna możliwość aktualizacji
                  $indeks->setDefaultSearchField('idBazy');
                  $skladnik = new Zend_Search_Lucene_Index_Term($id);
                  $zapytanie = new Zend_Search_Lucene_Search_Query_Term($skladnik);
                  $szukaj = $indeks->find($zapytanie);
                  
                  foreach($szukaj as $wynik){
                    //usunięcie właściwych wpisów
                    $indeks->delete($wynik->id);
                  }
                  $indeks->commit();
                
                }catch(Zend_Search_Exception $e){
                
                  echo $e->getMessage();
                
                }
              
              }catch(Exception $e){
              
                $e->getMessage();
                $bledySerwera[] = "Plik <b>". $daneDokumentow[$id] . "</b> pozostał na serwerze.";
              
              }
            }else{
            
              $podsumowanie[$id] = "<b>BŁĄD</b>: ". $daneDokumentow[$id];  
            
            }
          
          }

        }
        
        //zamknięcie połączenia z bazą
        $bd->closeConnection();
        
        //Przekazanie podsumowania do widoku
        $this->view->podsumowanie = $podsumowanie;
        $this->view->bledySerwera = $bledySerwera;
      
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
   * Utworzenie formularza przejścia do nr strony dokumentu<br />
   * Metoda tworzy formularz wyboru określonej strony dokumentu. Przycisk typu
   * "submit" nie jest renderowany. Przejście następuje po wybraniu przycisku
   * ENTER z klawiatury.      
   *    
   */      
  private function getNrStronyForm(){
      
    //Utworzenie formularza
    $formularz = new Zend_Form;
    //$formularz->setAction('dokumenty-czytaj', 'dokumenty');
    $formularz->setAction($this->view->url(array( 'controller' => 'dokumenty',
                                            'action' => 'dokumenty-czytaj'),
                                            'default'));
    $formularz->setMethod('post');
    $formularz->setDescription('Przejdź do numeru strony dokumentu');
    $formularz->setAttrib('sitename', 'czytajwnet');
  
    //Dodanie elementów do formularza
    require_once "Formularz/Elementy.php";
    $elementy = new Elementy();

    //Zapis zwracanych danych w tablicy 
    $formularz->addElement($elementy->getNrStronyPoleText());

    //Zapisz zmiany
    $formularz->addElement('submit', 'submit');
    $przejdz = $formularz->getElement('submit');
    $przejdz->setLabel('>>');

    return $formularz; 
  
  }
  
  /**
   * Odczytywanie dokumentów pdf<br />
   * Metoda umożliwia odczytywanie dokumentu pdf. Weryfikuje poziom dostępności
   * dokumentu. Po poprawnej weryfikacji przekazuje do widoku dane dokumentu.
   * Dalszy mechanizm realizowany jest w pliku widoku.         
   * 
   * @param $dokId numer identyfikacyjny dokumentu do wyświetlenia   
   * @param $kierunek określenie kolejnej strony dokumentu     
   */      
  public function dokumentyCzytajAction(){
  
    require_once "Autoryzacja/Autoryzacja.php";
    $sesja = Autoryzacja::kontynuujSesje();
    
    //$this->_helper->layout->disableLayout();
    //$this->_helper->viewRenderer->setNoRender();
    
    //pobranie id dokumentu do wyświetlenia
    $dokId = $this->_getParam('dokument');
    $kierunek = $this->_getParam('str');
    
    //pobranie nr strony z formularza
    $formularz = $this->getNrStronyForm();
    $this->view->nrStronyForm = $formularz;
    
    if($formularz->isValid($_POST)){
      $nrStrony = $formularz->getValue('nrStrony');
      
      if(!empty($nrStrony)){
        $sesja->strona = $nrStrony;
      }else if(isset($kierunek)){
        if($kierunek == 'p'){
          $sesja->strona -= 1;
        }else if($kierunek == 'n'){
          $sesja->strona += 1;
        }else if($kierunek == 'pa'){
          $sesja->strona = 0;
        }else if($kierunek == 'o'){
          $sesja->strona = $sesja->maxStron;
        }
      }else{
        $sesja->strona = 0;
      }
      
      $nrStronyElem = $formularz->getElement('nrStrony');
      $nrStronyElem->setValue($sesja->strona + 1);
      
    }else{
      $sesja->strona = 0;
    }
   
    //sprawdzenie, czy dokument może być wyświetlany
    try{

      //Dane do pobrania z bazy
      $dane = array("dostepnosc"  => 'dostepnosc',
                    "nazwa"       => 'nazwa');
      $idWlasciciela = array("uId" => 'id');
      
      //Określenie tabeli źródłowej
      $tabela = array("d" => "dokumenty");
      $uzytkTab = array("u" => "uzytkownik");
      
      //Połączenie z bazą
      require_once "Baza/Baza.php";
      $bd = Baza::polacz();
      
      //Utworzenie obiektu typu SELECT
      $wybierz = new Zend_Db_Select($bd);
      
      //Utworzenie zapytania
      $zapytanie = $wybierz->from($tabela, $dane)
        //->where('uzytkownik_id=?', $sesja->id) //upewnienie się, czy to właściciel żąda dokumentu
        ->join($uzytkTab,
          'u.id = d.uzytkownik_id', $idWlasciciela)
        ->where('d.id=?', $dokId);
  
      //Wysłanie zapytania
      $wyniki = $bd->query($zapytanie);
      $wiersze = $wyniki->fetchAll();
      
      //Zamknięcie połączenia z bazą
      $bd->closeConnection();
      
      //przekazanie danych do widoku
      if( (trim($wiersze[0]['dostepnosc'] , "\'") == 'czyt') ||
          (trim($wiersze[0]['dostepnosc'] , "\'") == 'pob')){

        $this->view->bledy = false;
        $this->view->id = $dokId;
        $this->view->nazwa = $wiersze[0]['nazwa'];
        $this->view->uId = $wiersze[0]['uId'];
      
      }else{
      
        $this->view->bledy = true;
      
      }
      
      
         
    }catch(Zend_Db_Exception $e){
      
      echo $e->getMessage();
      $this->view->bledy = true;
      //$this->view->bledyBazy = $e->getMessage();
    
    }                                                         
  
    //$this->_helper->viewRenderer->setNoRender();
  }
  
  /**
   * Pobranie informacji o dokumencie<br />
   * Metoda pobiera z bazy danych informacje o dokumencie. 
   *   
   */      
  public function dokumentInfoAction(){
  
    //Kontynuacja sesji
    require_once "Autoryzacja/Autoryzacja.php";
    $sesja = Autoryzacja::kontynuujSesje();
    
    //pobranie id dokumentu
    $dokId = $this->_getParam('dokument');
        
    //pobranie danych z bazy
    try{
    
      //Dane do pobrania z bazy
      $dane = array(
        "id" => 'id', //niezbędne do wyświetlenia dokumentu
        "uid" => 'uzytkownik_id',
        "tytuł" => 'tytul',
        "nazwa" => 'nazwa',
        "data dodania" => 'data_dodania',
        "ostatnia modyfikacja" => 'ostatnia_modyfikacja',
        "autor" => 'autor',
        "przedmiot" => 'przedmiot',
        "rodzaj" => 'rodzaj',
        "isbn" => 'isbn',
        "wydawnictwo" => 'wydawnictwo',
        "rok wydania" => 'rok_wydania',
        "tagi" => 'tagi',
        "opis" => 'opis',
        "dostępność" => 'dostepnosc',
        "wyświetleń" => 'ilosc_wyswietlen'  
      );
      $dane2 = array("login" => 'login');
      
      //Określenie tabeli źródłowej
      $tabela = array("d" => "dokumenty");
      $tabela2 = array("u" => "uzytkownik");
      
      //Połączenie z bazą
      require_once "Baza/Baza.php";
      $bd = Baza::polacz();
      
      //Utworzenie obiektu typu SELECT
      $wybierz = new Zend_Db_Select($bd);
      
      //Utworzenie zapytania
      $zapytanie = $wybierz->from($tabela, $dane)
                  ->join($tabela2,
                    'd.uzytkownik_id = u.id'
                    , $dane2)
                  ->where('d.id=?', $dokId);
  
      //Wysłanie zapytania
      $wyniki = $bd->query($zapytanie);
      $wiersze = $wyniki->fetchAll();
      
      //Zamknięcie połączenia z bazą
      $bd->closeConnection();
      
      //Przekazanie danych do widoku
      $this->view->dokumentInfo = $wiersze[0];
    
    }catch(Zend_Db_Exception $e){
    
      echo $e->getMessage();
      
    }
      
  
  }
  
  /**
   * Formularz pobierania pliku <br />
   * Metoda tworzy formularz pobierania dokumentu składający się z elementu
   * captcha.    
   * 
   * @return Zend_Form     
   */      
  private function getDokumentPobierzForm(){
      
    //Utworzenie formularza
    $formularz = new Zend_Form;
    //$formularz->setAction('sukces-dokument-pobierz');
    $formularz->setAction($this->view->url(array('controller' => 'dokumenty',
                                            'action' => 'sukces-dokument-pobierz'),
                                            'default', true));
    $formularz->setMethod('post');
    $formularz->setDescription('Pobieranie pliku');
    $formularz->setAttrib('sitename', 'czytajwnet');
  
    //Dodanie elementów do formularza
    require_once "Formularz/Elementy.php";
    $elementy = new Elementy();

    $formularz->addElement($elementy->getCaptcha('pobieraniePliku', 4));

    /***************************************************************
    //ukryte pole z nazwą pliku dokumentu
    $nazwaUkrytePoleTekst = new Zend_Form_Element_Hidden('nazwa');
    $formularz->addElement($nazwaUkrytePoleTekst);
    
    //ukryte pole z id dokumentu
    $idUkrytePoleTekst = new Zend_Form_Element_Hidden('dokId');
    $formularz->addElement($idUkrytePoleTekst);
    ****************************************************************/
    
    //Pobierz
    $formularz->addElement('submit', 'submit');
    $przejdz = $formularz->getElement('submit');
    $przejdz->setLabel('Pobierz plik');

    return $formularz; 
  
  }
  
  /**
   * Pobierania dokumentu<br />
   * Metoda pobiera formularz pobierania dokumentu jak i dane dokumentu z bazy,
   * i przekazuje je do widoku.    
   *   
   * @param $dokId numer identyfikacyjny dokumentu   
   */      
  public function dokumentPobierzAction(){
    
    //Kontynuacja sesji
    require_once "Autoryzacja/Autoryzacja.php";
    $sesja = Autoryzacja::kontynuujSesje();
    
    //pobranie id dokumentu
    $dokId = $this->_getParam('dok');
    
    //pobranie formularza
    $formularz = $this->getDokumentPobierzForm();
    
    if( (isset($dokId)) && (!empty($dokId)) ){    
    
      //pobranie danych z bazy
      try{
      
        //Dane do pobrania z bazy
        $dane = array(
          "id" => 'id',
          "uid" => 'uzytkownik_id',
          "tytul" => 'tytul',
          "nazwa" => 'nazwa',
        );
        
        //Określenie tabeli źródłowej
        $tabela = array("d" => "dokumenty");
        
        //Połączenie z bazą
        require_once "Baza/Baza.php";
        $bd = Baza::polacz();
        
        //Utworzenie obiektu typu SELECT
        $wybierz = new Zend_Db_Select($bd);
        
        //Utworzenie zapytania
        $zapytanie = $wybierz->from($tabela, $dane)
                    ->where('id=?', $dokId);
    
        //Wysłanie zapytania
        $wyniki = $bd->query($zapytanie);
        $wiersze = $wyniki->fetchAll();
        
        //Zamknięcie połączenia z bazą
        $bd->closeConnection();
      
      }catch(Zend_Db_Exception $e){
      
        echo $e->getMessage();
        //$this->view->bledy = true;
        
      }
      
      if( count($wiersze) == 1 ){
      
        /***************************************
        //dodanie ukrytych danych do formularza
        $nazwa = $formularz->getElement('nazwa');
        $nazwa->setValue($wiersze[0]['nazwa']);
        $idDok = $formularz->getElement('dokId');
        $idDok->setValue($wiersze[0]['id']);
        ***************************************/
        
        //dodanie zmiennych sesji
        $sesja->nazwaDok = trim($wiersze[0]['nazwa'], "\'");
        $sesja->idDok = trim($wiersze[0]['id'], "\'");
        $sesja->tytulDok = trim($wiersze[0]['tytul'], "\'");
        $sesja->uid = trim($wiersze[0]['uid'], "\'"); 
        
        //Przekazanie danych do widoku
        $this->view->bledy = false;
        $this->view->formuPob = $formularz;
        
      }else{
      
        $this->view->bledy = true;
      }
    
    } //koniec if( (isset($dokId)) && (!empty($dokId)) )
    else{
    
      $this->view->bledy = true;
    
    }
  
  }
  
  /**
   * przetworzenie formularza pobierania dok.<br />
   * Metoda przetwarza formularz pobierania dokumentu. Po poprawnej weryfikacji
   * ustawia nagłówek odpowiedzi na zawartość typu application/pdf i pozwala
   * Użytkownikowi zapisać dokument na nośniku danych.      
   *    
   */      
  public function sukcesDokumentPobierzAction(){

    //Kontynuacja sesji
    require_once "Autoryzacja/Autoryzacja.php";
    $sesja = Autoryzacja::kontynuujSesje();
    
    $formularz = $this->getDokumentPobierzForm();
    
    if($formularz->isValid($_POST)){
    
      //wyłączenie layout'u i widoku
      $this->_helper->layout->disableLayout();
      $this->_helper->viewRenderer->setNoRender();
      $this->view->bledy = false;
      
      //pobranie pliku
      $lokalizacja = $_SERVER['DOCUMENT_ROOT'] . '/public/uzytk/' . (string)$sesja->uid . '/dokumenty/';
      $nazwaPliku = $sesja->nazwaDok;
      $tytulPliku = str_replace(' ', '_', $sesja->tytulDok);
      
      $plik = $lokalizacja.$nazwaPliku; 
      
      if(file_exists($plik)){
      
        //ustawienie nagłówka odpowiedzi
        $response = $this->getResponse();
        $response ->setHeader('Content-type', 'application/pdf', true)
                  ->setHeader('Content-disposition', 'attachment; filename='. $tytulPliku . '.pdf', true)
                  ->appendBody(readfile($plik));
                  
      }else{
      
        echo "<p>Plik nie istnieje</p>";
      
      }
      
    }else{
    
      //przekazanie formularza do widoku
      $this->view->bledy = true;
      $this->view->formuPob = $formularz;
    
    }

  }
  
  /* 
  // gdy usuwanie przez ftp nie działa
  public function wyrzucAction(){

    //Kontynuacja sesji
    require_once "Autoryzacja/Autoryzacja.php";
    $sesja = Autoryzacja::kontynuujSesje();
   
    $lokalizacja = $_SERVER['DOCUMENT_ROOT'] . '/public/uzytk/' . (string)$sesja->id . '/dokumenty/'; 
      //usunięcie pliku z serwera
      @unlink($lokalizacja. '23_dok.pdf');
      @unlink($lokalizacja. '24_dok.pdf');
      @unlink($lokalizacja. '25_dok.pdf');
      @unlink($lokalizacja. '26_dok.pdf');
    
    
    echo $lokalizacja = $_SERVER['DOCUMENT_ROOT'] . '/uzytk';
    try{
      //@unlink($lokalizacja);
      rmdir($lokalizacja);
      echo "<br />OK<br />";
    }catch( exception $e ){
      echo "<br />BŁĄD<br />";
    }
    
    $this->_helper->viewRenderer->setNoRender();
  }
  
  */

}











