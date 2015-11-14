<?php

/**
* Klasa odpowiedzialna za operacje związane z kontem Użytkownika
*
*/  
class KontoController extends Zend_Controller_Action
{

  /**
   * Metoda inicjalizująca pozostałe funkcje kontrolera Konto. 
   *     
   */
  public function init()
  {
    /* Initialize action controller here */
  }

  /**
   * Główna strona kontrolera Konto<br />
   * Brak możliwości ogólnej autoryzacji dla całego kontrolera wymusza
   * oddzielne potraktowanie każdej akcji.      
   *
   */        
  public function indexAction()
  {
    //Sprawdzenie, czy Użytkownik jest zalogowany
    require_once "Autoryzacja/Autoryzacja.php";
    if(!Autoryzacja::czyZalogowany()){
      $this->_forward('loguj', 'konto');
    }
  }

  /**
   * Tworzenie nowego konta<br />
   * Metoda pobiera formularz tworzenia nowego konta i przekazuje go do widoku.   
   *
   */        
  public function nowyAction()
  {
  
    //Pobranie formularza rejestracyjnego
    $formularz = $this->getFormularzRej();
    
    //Dodanie formularza do widoku
    $this->view->formularz = $formularz;  
   
  }

  /**
   * Tworzenie formularza nowego konta<br />
   * Metoda tworzy formularz rejestracji nowego konta. Wszystkie pola sa
   * wymagane.
   * 
   * @return Zend_Form     
   */        
  private function getFormularzRej()
  {
    //Utworzenie formularza
    $formularz = new Zend_Form();
    $formularz->setAction('sukces');
    $formularz->setMethod('post');
    $formularz->setDescription('Formularz rejestracyjny');
    $formularz->setAttrib('sitename', 'czytajwnet');
    
    //Dodanie elementów do formularza
    require "Formularz/Elementy.php";
    $czytajwnetElementy = new Elementy();
    
    //Utworzenie pola dla nazwy użytkownika
    $formularz->addElement($czytajwnetElementy->getNazwaUzytkownikaPoleTekst());
    
    //Utworzenie pola dla adresu email
    $formularz->addElement($czytajwnetElementy->getAdresEmailPoleTekst());
  
    //Utworzenie pola dla hasła
    $formularz->addElement($czytajwnetElementy->getHasloPoleTekst());
    
    //Dodanie Captcha
    $captcha = new Zend_Form_Element_Captcha(
      'rejestracja',
      array(
        'captcha' => array(
          'captcha' => 'Dumb',
          'wordLen' => 5,
          'timeout' => 600
        )
      )
    );
    
    $captcha->setLabel('Przepisz odwrotnie:');
    $formularz->addElement($captcha);
    
    //Utworzenie przycisku wyczyść formularz
    $formularz->addElement('reset', 'reset');
    $przyciskWyczysc = $formularz->getElement('reset');
    $przyciskWyczysc->setLabel('Wyczyść formularz');
    
    //Utworzenie przycisku zatwierdź
    $formularz->addElement('submit', 'submit');
    $przyciskUtworz = $formularz->getElement('submit');
    $przyciskUtworz->setLabel('Utwórz nowe konto');
  
    
    return $formularz;
  }

  /**
   * Rejestracja nowego konta<br />
   * Metoda przetwarza formularz tworzenia nowego konta. Po poprawnej
   * weryfikacji zapisuje dane w bazie i wysyła Użytkownikowi 2 wiadomości
   * email (powitalna i aktywacyjna). Nowe konto nie jest jeszcze aktywne.   
   *
   */        
  public function sukcesAction()
  {
  
    $formularz = $this->getFormularzRej();
    
    //Sprawdzenie, czy otrzymane dane są typu POST 
    if($formularz->isValid($_POST)){
                  
      $email = $formularz->getValue('email');
      $nazwaUzytkownika = $formularz->getValue('nazwaUzytkownika');
      $haslo = $formularz->getValue('haslo');
      
      //Utworzenie obiektu z dostępem do bazy
      require_once "Baza/Baza.php";
      $bd = Baza::polacz();
      
      //Utworzenie wpisu do bazy danych
      $daneUzytkownika = array(
        "login" => $nazwaUzytkownika,
        "email" => $email,
        "haslo" => $haslo,
        //domyślny status nowego Użytkownika
        "status" => 'nowy',
        //zapis aktualnej daty
        "data_utworzenia_konta" => new Zend_Db_Expr("NOW()")  
      );
                
    //Zapis danych do bazy
    try{
    
      //wstawienie danych do tabeli uzytkownik
      $bd->insert('uzytkownik', $daneUzytkownika);
      
      //Pobranie nr id Uzytkownika
      $idUrzytkownika = $bd->lastInsertId();
      
      //Zamknięcie połączenia z bazą danych
      $bd->closeConnection();
      
      //CBA blokuje protokół SMTP
      /* -------------------------------------------------------------
      //Wysłanie powitalnego email'a 
      $konfiguracja = array(
                  'ssl' => 'tls',   //nie działa z kontem na wp.pl
                  'auth' => 'login',
                  'username' => 'czytajwnet',
                  'password' => '1qazxcde32ws',
                  //'port' => '465'   //nie działa z kontem na wp.pl 
                );
      
      $transport = new Zend_Mail_Transport_Smtp(
        'smtp.wp.pl', $konfiguracja
      );
      ------------------------------------------------------------- */
      
      //Dane powitalnego email'a
      $EmailObj = new Zend_Mail('UTF-8');
      $wiadomosc = "<h3>Witamy w serwisie <b>CzytajWNET.pl</b></h3>";
      $wiadomosc .= "<br />Dziękujemy za skorzystanie z naszych usług.<br />";
      $wiadomosc .= "<br />Twoje <b>nowe konto</b> nie jest jeszcze aktywne.<br />";
      $wiadomosc .= "Wkrótce otrzymasz wiadomość, dzięki której będziesz mógł ";
      $wiadomosc .= "dokonać aktywacji.<br /><br />";
      $wiadomosc .= "<hr width=\"100px\" align=\"left\" />Zespół <b>CzytajWNET.pl</b>";
      $nadawcaEmail = "aktywacja@w-kaczorowski.c0.pl";
      $nadawcaNazwa = "CzytajWNET";
      $adresatEmail = "$email";
      $temat = "Witamy w CzytajWNET.pl";
      
      //Wysłanie powitalnego email'a    
      $EmailObj->setBodyHtml($wiadomosc);
      $EmailObj->setFrom($nadawcaEmail, $nadawcaNazwa);
      $EmailObj->addTo($adresatEmail);
      $EmailObj->setSubject($temat);
      //$EmailObj->send($transport);
      $EmailObj->send(); //wysłanie poprzez Zend_Mail_Transport_Sendmail
      
      //Przygotowanie aktywacyjnego email'a
      $tematAktywacja = "CzytajWNET.pl - aktywacja konta";
      $aktywacjaWiadomosc = "<h3>Aktywacja konta CzytajWNET.pl</h3>"
      ."Twoje nowe konto dla uzyskania pełnej funkcjonalności "
      ."wymaga <b>aktywacji</b>.<br />Konto możesz aktywowować klikając " 
      ."poniższy link:<br /><br />" 
      ."<a href=\"http://czytajwnet.w-kaczorowski.c0.pl/public/konto/aktywacja?email=". $email ."\">"
      ."<b>aktywuj konto</b></a><br /><br /><hr width=\"100px\" align=\"left\" />"
      ."Zespół <b>CzytajWNET.pl</b>";
      $aktywacjaNadawca = "aktywacja@w-kaczorowski.c0.pl";
      $aktywacjaNadawcaNazwa = "CzytajWNET.pl - aktywacja";
      
      //Wysłanie aktywacyjnego email/a
      $EmailObj = new Zend_Mail('UTF-8');
      $EmailObj->setBodyHtml($aktywacjaWiadomosc);
      $EmailObj->setFrom($aktywacjaNadawca, $aktywacjaNadawcaNazwa);
      $EmailObj->addTo($adresatEmail);
      $EmailObj->setSubject($tematAktywacja);
      //$EmailObj->send($transport);
      $EmailObj->send(); //wysłanie poprzez Zend_Mail_Transport_Sendmail
      
    }catch(Zend_Db_Exception $e){
    
      //Wyświetlenie blędów związanych z bazą danych
      echo "Błędy bazy danych: <br /><hr />". $this->view->bledyBazy = $e->getMessage();
      //Ponowne wyświetlenie formularza
      $this->view->formularz = $formularz;
    
    }catch (Zend_Mail_Exception $e){
    
      //Wyświetlenie blędów związanych z wiadomościami email
      echo "Błędy Email'a: <br /><hr />". $this->view->bledyEmaila = $e->getMessage();
      //Ponowne wyświetlenie formularza
      $this->view->formularz = $formularz;
      
    }  
          
    }else{
      //Przechwycenie błędnego formatu uzyskanych danych
      $this->view->errors = $formularz->getMessages();
      //Ponowne wyświetlenie formularza
      $this->view->formularz = $formularz;
    }

  }

  /**
   * Aktywacja konta Użytkownika<br />
   * Metoda pobiera dane aktywacyjne. Po poprawnej weryfikacji konto zostaje
   * aktywowane. Po tej operacji logowanie jest już możliwe.      
   *
   */        
  public function aktywacjaAction()
  {
  
    //Przechwycenie adresu email z żądania GET
    $emailAktywacyjny = $this->_request->getQuery("email");
    
    try{
      
      //Ustanowienie połączenia z bazą danych
      require_once "Baza/Baza.php";
      $bd = Baza::polacz();
    
      //Sprawdzenie, czy Użytkownik o podanym email'u istnieje w bazie
      $zapytanie = "
        SELECT
          COUNT(id)
        AS
          wszystkie
        FROM
          uzytkownik
        WHERE
          email ='". $emailAktywacyjny ."'
        AND
          status = 'nowy'
      ";
    
      //Przechwycenie pojedynczego rekordu
      $wyniki = $bd->fetchOne($zapytanie);
      
      //Jeśli zmienna $wyniki zawiera 1, email jest poprawny, Użytkownik
      //istnieje i nie został jeszcze aktywowany
      if($wyniki == 1){
      
        //Aktywacja konta
        $warunki[] = "email = '". $emailAktywacyjny ."'";
        
        //Dane aktualizacyjne do wprowadzenia
        $aktualizacje = array("status" => 'aktywny');
        $wyniki = $bd->update(
          'uzytkownik',
          $aktualizacje,
          $warunki    
        );
        
        //Zamknięcie połączenia z bazą danych
        $bd->closeConnection();
        
        //Ustawienie flagi aktywacyjnej na true
        $this->view->aktywowany = true;  
      
      }else{
      
        //Ustawienie flagi aktywacyjnej na false
        $this->view->aktywowany = false;
      
      }
      
    //Przechwycenie błędów bazy danych
    }catch(Zend_Db_Exception $e){
    
      //Wyświetlenie informacji o błędach bazy danych
      echo "<br />". $e->getMessage() ."<br />";
    
    }

  }

  /**
   * Tworzenie formularza logowania Użytkownika<br />
   * Metoda tworzy formularz logowania. Wszystkie pola sa wymagane.
   * 
   * @param $captcha określa, czy do formularza dołączyć captcha
   *          
   * @return Zend_Form
   */        
  private function getFormularzLogowania($captcha)
  {
  
    //Utworzenie formularza
    $formularz = new Zend_Form();
    $formularz->setAction('autentykacja', 'konto');
    $formularz->setMethod('post');
    $formularz->setDescription('Formularz logowania');
    $formularz->setName('formularzLogowania');
    $formularz->setAttrib('sitename', 'czytajwnet');
    
    //Dodanie elementów do formularza
    require "Formularz/Elementy.php";
    $logujElementy = new Elementy();
        
    //Utworzenie pola dla adresu email
    $formularz->addElement($logujElementy->getAdresEmailPoleTekst());
    //Utworzenie pola dla hasła
    $formularz->addElement($logujElementy->getHasloPoleTekst());    
    
    //dodanie elementu captcha
    if($captcha){
    //Dodanie Captcha
    $captcha = new Zend_Form_Element_Captcha(
      'logowanie',
      array(
        'captcha' => array(
          'captcha' => 'Dumb',
          'wordLen' => 7,
          'timeout' => 600
        )
      )
    );
    
    $captcha->setLabel('Przepisz odwrotnie:');
    $formularz->addElement($captcha);
    
    }
    
    //Utworzenie przycisku wyczyść formularz
    $formularz->addElement('reset', 'reset');
    $przyciskWyczysc = $formularz->getElement('reset');
    $przyciskWyczysc->setLabel('Wyczyść formularz');
    
    //Utworzenie przycisku zatwierdź
    $formularz->addElement('submit', 'submit');
    $przyciskUtworz = $formularz->getElement('submit');
    $przyciskUtworz->setLabel('Zaloguj');
  
    return $formularz;    
  
  }

  /**
   * Pobranie formularza logowania<br />
   * Metoda pobiera formularz logowania i przekazuje go do widoku.   
   *
   */        
  public function logujAction(){
  
    //Inicjalizacja formularza dla widoku
    $this->view->formularz = $this->getFormularzLogowania(false);
  
  }

  /**
   * Wylogowanie Użytkownika<br />
   * Metoda kasuje dane seji logowania i odsyła do strony głównej.   
   *
   */        
  public function wylogujAction()
  {
  
    //zniszczenie danych bieżącej sesji
    
    //pobranie informacji o sesji logowania
    require_once "Autoryzacja/Autoryzacja.php";
    $sesjaLogowania = Autoryzacja::kontynuujSesje();
    //wyłączenie blokady tylko-do-odczytu
    $sesjaLogowania->unlock();
    //usunięcie danych sesji logowania
    Zend_Session::namespaceUnset('logowanie');
    
    //przeniesienie do strony głównej
    $this->_redirect('index');
  
  }

  /**
   * Autentykacja Użytkownika<br />
   * Metoda przetwarza formularz logowania Użytkownika. Po poprawnej weryfikacji      
   * zapisuje dane sesji i przekierowuje do strony głównej.
   *
   */           
  public function autentykacjaAction()
  {
      //kontynuacja sesji logowania
      require_once "Autoryzacja/Autoryzacja.php";
      $sesjaLogowania = Autoryzacja::kontynuujSesje();
      //Czas do wygaśnięcia sesji w sekundach
      //$sesjaLogowania->setExpirationSeconds(1);
    
    if( !isset($sesjaLogowania->proby) ){
      $sesjaLogowania->proby = 0;
    }
    
    //Inicjalizacja formularza...
    if($sesjaLogowania->proby > 2){
      //...z captcha
      $formularz = $this->getFormularzLogowania(true);
    }else{
      //...bez captcha
      $formularz = $this->getFormularzLogowania(false);
    }
    
    //Sprawdzenie poprawności typu otrzymanych danych
    if($formularz->isValid($_POST)){
    
      //Inicjalizacja zmiennych
      $emailLogowania = $formularz->getValue("email");
      $hasloLogowania = $formularz->getValue("haslo");
      
      //Utworzenie obiektu bazy danych
      require_once "Baza/Baza.php";
      $bd = Baza::polacz();
      
      //Oczyszczenie danych
      $emailLogowania = $bd->quote($emailLogowania);
      $hasloLogowania = $bd->quote($hasloLogowania);
      
      //Sprawdzenie, czy Użytkownik istnieje w bazie i czy aktywował konto
      $zapytanie = "
        SELECT 
          COUNT(id)
        AS 
          wszystkie  
        FROM
          uzytkownik
        WHERE 
          email = ".$emailLogowania." AND
          haslo = ".$hasloLogowania." AND
          status = 'aktywny'
        ";
    
      //Przechwycenie pojedynczego rekordu
      $wyniki = $bd->fetchOne($zapytanie);
      
      //Jeśli istnieje 1 rekord spełniający warunki zapytania...
      if($wyniki == 1){
      
        //Przechwycenie danych Użytkownika
        $zapytanie = "SELECT
                        id, login, data_utworzenia_konta 
                      FROM
                        uzytkownik
                      WHERE
                        email = ".$emailLogowania." AND
                        haslo = ".$hasloLogowania."
                      ";
      
        //Przechwycenie pojedynczego rekordu
        $wyniki = $bd->fetchRow($zapytanie);
        
        //Ustawienie danych sesji Użytkownika
        $sesjaLogowania->zalogowany = true;
        $sesjaLogowania->id = $wyniki['id'];
        $sesjaLogowania->email = $emailLogowania;
        $sesjaLogowania->login = $wyniki['login'];
        $sesjaLogowania->dataUtworzenia = $wyniki['data_utworzenia_konta'];
        
        //Usunięcie danych o ilości prób logowania
        unset($sesjaLogowania->proby);
        
        
        //Zapis daty logowania
        $warunki[] = "email = ". $emailLogowania;
        $warunki[] = "haslo = ". $hasloLogowania;
        $aktualizacja = array('ostatnie_logowanie' => new Zend_Db_Expr("NOW()") ); 
        $bd->update('uzytkownik', $aktualizacja, $warunki);
        
        //Zamknięcie połączenia z bazą danych
        $bd->closeConnection();
        
        //Przeniesienie Użytkownika do strony głównej
        //$this->_forward('index', 'index');
        $this->_redirect('index');
        
      
      } //Koniec if($wyniki == 1)
      else{
        
        //Zwiększenie licznika prób logowania
        $sesjaLogowania->proby += 1;
        
        //Wyświetlenie komunikatu o błędzie i zwrócenie formularza logowania
        $this->view->komunikat = "Nieprawidłowe dane logowania.";
        $this->view->formularz = $formularz;
        $this->render("loguj");    
      
      }
    
    } //Koniec if($formularz->isValid($_POST))
    else{
      
      //Zwiększenie licznika prób logowania
      $sesjaLogowania->proby += 1;
      
      //ponowne wyświetlenie formularza logowania
      $this->view->formularz = $formularz;
      $this->render("loguj");  
    
    }

  }

  /**
   * Globalne preferencje konta<br />
   * Metoda umożliwiająca edycję głównych ustawień konta Użytkownika.
   * 
   * Obecnie nie zaimplementowana 
   */      
  public function preferencjeAction()
  {
    //Sprawdzenie, czy Użytkownik jest zalogowany
    require_once "Autoryzacja/Autoryzacja.php";
    if(!Autoryzacja::czyZalogowany()){
      $this->_forward('loguj', 'konto');
    }
  }



}




