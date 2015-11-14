<?php

/**
 * Klasa odpowiedzialna za operacje związane z Profilem Użytkownika.
 *
 */  
class ProfilController extends Zend_Controller_Action
{

  /**
   * Metoda inicjalizująca pozostałe funkcje kontrolera Profil.<br />
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
   * Przeniesienie do menu głównego profilu Użytkownika.
   *
   */        
  public function indexAction()
  {
    $this->_redirect('profil/profil-menu');
  }

  /**
   * Wyświetlenie menu głównego profilu Użytkownika
   *
   */        
  public function profilMenuAction()
  {

  }

  /**
   * Wyświetla dane profilu Użytkownika<br />
   * Metoda pobiera z bazy i wyświetla dane zalogowanego Użytkownika.
   *      
   */         
  public function profilPokazAction()
  {

    //Kontynuacja sesji
    require_once "Autoryzacja/Autoryzacja.php";
    $sesja = Autoryzacja::kontynuujSesje();
    
    //pobranie danych z bazy
    try{
      
      //Dane do pobrania z bazy
      $daneUzytkownika = array(
        "imie" => 'imie',
        "nazwisko" => 'nazwisko',
        "podpis" => 'podpis',
        "opis konta" => 'opis_konta',
        "avatar" => 'avatar'    
      );
      
      //Określenie tabeli źródłowej
      $tabela = array("u" => "uzytkownik");
      
      //Połączenie z bazą
      require_once "Baza/Baza.php";
      $bd = Baza::polacz();
      
      //Utworzenie obiektu typu SELECT
      $wybierz = new Zend_Db_Select($bd);
      
      
      //Utworzenie zapytania
      $zapytanie = $wybierz->from($tabela, $daneUzytkownika)
                    ->where("id=?", $sesja->id);

    
      //Wysłanie zapytania
      $wyniki = $bd->query($zapytanie);
      $wiersz = $wyniki->fetchAll();
      
      //Zamknięcie połączenia z bazą
      $bd->closeConnection();
      
      //Przekazanie danych do widoku
      $this->view->daneUzytkownika = $wiersz;
    
    }catch(Zend_Db_Exception $e){
    
      echo $e->getMessage();
      
    }
  }
  
  /**
   * Edycja danych profilu Użytkownika<br />
   * Metoda przygotowuje formularz edycji danych profilu Użytkownika.
   *       
   * @return Zend_Form
   */        
  private function getFurmularzEdycji(){
  
    
    //Utworzenie formularza
    $formularz = new Zend_Form;
    $formularz->setAction('sukces-edycja');
    $formularz->setMethod('post');
    $formularz->setDescription('Formularz edycji Profilu Użytkownika');
    //Przesyłanie plików
    $formularz->setAttrib('enctype', 'multipart/form-data');
    $formularz->setAttrib('sitename', 'czytajwnet');
  
    //Dodanie elementów do formularza
    require_once "Formularz/Elementy.php";
    $edycjaProfiluElementy = new Elementy();
    
    //Imie
    $formularz->addElement($edycjaProfiluElementy->getImiePoleTekst());
    //Nazwisko
    $formularz->addElement($edycjaProfiluElementy->getNazwiskoPoleTekst());
    //Podpis
    $formularz->addElement($edycjaProfiluElementy->getPodpisPoleTekst());
    //Opis
    $formularz->addElement($edycjaProfiluElementy->getOpisKontaPoleTekst());
    //Avatar
    $formularz->addElement($edycjaProfiluElementy->getAvatarPolePlik());
    //Captcha
    $formularz->addElement($edycjaProfiluElementy->getCaptcha('edycjaProfilu', 5));
    //Wyczyść
    $formularz->addElement('reset', 'reset');
    $przyciskWyczysc = $formularz->getElement('reset');
    $przyciskWyczysc->setLabel('Wyczyść pola');
    //Zapisz zmiany
    $formularz->addElement('submit', 'submit');
    $przyciskUtworz = $formularz->getElement('submit');
    $przyciskUtworz->setLabel('Zapisz zmiany');
    
    return $formularz;
    
  }

  /**
   * Edycja profilu Użytkownika <br />
   * Metoda pobiera formularz edycji profilu Użytkownika i przekazuje go
   * do widoku.      
   *
   */        
  public function profilEdytujAction()
  {
    //Przekazanie formularza do widoku
    $formularz = $this->getFurmularzEdycji();
    $this->view->formularz = $formularz;  
  }
  
  /**
   * Przetworzenie formularza edycji profilu Użytkownika<br />
   * Metoda przetwarza formularz edycji Użytkownika. Po poprawnej weryfikacji
   * aktualizuje dane w bazie. W przypadku dodania obrazu avatara zapisuje plik
   * w katalogu publicznym Użytkownika.         
   *
   */        
  public function sukcesEdycjaAction(){
  
    require_once "Autoryzacja/Autoryzacja.php";
    $sesja = Autoryzacja::kontynuujSesje();
    
    $formularz = $this->getFurmularzEdycji();
    
    //Sprawdzenie poprawności otrzymanych danych
    if($formularz->isValid($_POST)){
      
      $imie = $formularz->getValue('imieUzytkownika');
      $nazwisko = $formularz->getValue('nazwiskoUzytkownika');
      $podpis = $formularz->getValue('podpisUzytkownika');
      $opis = $formularz->getValue('opisKontaUzytkownika');
      
      //pobranie danych o pliku avatara
      @$oryginalnaNazwaPliku = pathinfo($formularz->avatar->getFileName());
      $nowaNazwaPliku = $sesja->id. "_av.". $oryginalnaNazwaPliku['extension'];
      //Zmiana nazwy pliku na domyślny format
      $formularz->avatar->addFilter('Rename', $nowaNazwaPliku);      
      
      //zapis pliku na serwerze
      $formularz->avatar->receive();
      
      
      //Zapis danych do bazy
      try{
      
        //ustanowienie połączenia z bazą danych
        require_once "Baza/Baza.php";
        $bd = Baza::polacz();
        
        //oczyszczenie danych
        $imie = addslashes($imie);
        $nazwisko = addslashes($nazwisko);
        $podpis = addslashes($podpis);
        $opis = addslashes($opis);
      
        //dane do zapisu
        $noweDane = array();
        if(!empty($imie)){
          $noweDane["imie"] = $bd->quote($imie);  
        }
        if(!empty($nazwisko)){
          $noweDane["nazwisko"] = $bd->quote($nazwisko);
        }
        if(!empty($podpis)){
          $noweDane["podpis"] = $bd->quote($podpis);
        }
        if(!empty($opis)){
          $noweDane["opis_konta"] = $bd->quote($opis);
        }
        if(!empty($oryginalnaNazwaPliku['extension'])){
          $noweDane["avatar"] = $bd->quote($nowaNazwaPliku);
        }
        
        //warunki aktualizacji
        $warunki[] = "id = '". $sesja->id ."'";
      
        //zapytanie
        $wyniki = $bd->update('uzytkownik', $noweDane, $warunki);
        
        //zamknięcie połączenia z bazą
        $bd->closeConnection();
      
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
   * Wyświetlenie publicznych danych Użykownika<br />
   * Metoda wyświetla publiczne dane profilu Użytkownika określonego w pobieranym
   * parametrze.
   *        
   * @param $login login Użytkownika, którego profil publiczny ma zostać wyświetlony 
   */      
  public function pokazPublicznyProfilAction(){
  
    //Kontynuacja sesji
    require_once "Autoryzacja/Autoryzacja.php";
    $sesja = Autoryzacja::kontynuujSesje();
    
    //pobranie id Użytkownika
    $login = $this->_getParam('uzytkownik');
    
    //pobranie danych z bazy
    try{
      
      //Dane do pobrania z bazy
      $daneUzytkownika = array(
        "id" => 'id',
        "login" => 'login',
        "imie" => 'imie',
        "nazwisko" => 'nazwisko',
        "podpis" => 'podpis',
        "opis konta" => 'opis_konta',
        "avatar" => 'avatar'    
      );
      
      //Określenie tabeli źródłowej
      $tabela = array("u" => "uzytkownik");
      
      //Połączenie z bazą
      require_once "Baza/Baza.php";
      $bd = Baza::polacz();
      
      //Utworzenie obiektu typu SELECT
      $wybierz = new Zend_Db_Select($bd);
      
      //Utworzenie zapytania
      $zapytanie = $wybierz->from($tabela, $daneUzytkownika)
                    ->where("login=?", $login)
                    ->where('widocznosc=?', 'publi');

      //Wysłanie zapytania
      $wyniki = $bd->query($zapytanie);
      $wiersz = $wyniki->fetchAll();
      
      //Zamknięcie połączenia z bazą
      $bd->closeConnection();
      
      //Przekazanie danych do widoku
      $this->view->daneUzytkownika = $wiersz;
    
    }catch(Zend_Db_Exception $e){
    
      echo $e->getMessage();
      
    }    
  
  }
    
  

}