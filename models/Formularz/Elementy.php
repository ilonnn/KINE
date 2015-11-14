<?php

/**
 * Klasa odpowiedzialna za elementy formularzy.
 *
 */  
 class Elementy{
 
  

  /*****************************************************************************
   *                                                                           *
   *             Pola dla elementów konta i profilu Użytkownika                *
   *                                                                           *
   ****************************************************************************/     
  
  /**
   * Utworzenie pola dla adresu email
   *
   * @return Zend_Form_Element_Text   
   **/
  public function getAdresEmailPoleTekst(){
 
    //Utworzenie obiektu dla adresu email
    $email = new Zend_Form_Element_Text('email');
    $email->setLabel('e-mail:');
    //Ustawienie pola jako wymagane
    $email->setRequired(true);
    
    //Dodanie mechanizmu sprawdzania poprawności
    $email->addValidator(new Zend_Validate_EmailAddress());
    
    //Dodanie filtrów
    $email->addFilter(new Zend_Filter_HtmlEntities());
    $email->addFilter(new Zend_Filter_StripTags());
    
    return $email; 
        
  }
   
   /**
    * Utworzenie pola dla hasła
    *
    * @return Zend_Form_Element_Password      
    **/
  public function getHasloPoleTekst(){
  
    //Utworzenie objektu dla hasła
    $haslo = new Zend_Form_Element_Password('haslo');
    $haslo->setLabel('Hasło:');
    //Ustawienie pola jako wymagane
    $haslo->setRequired(true);
          
    //Dodanie mechanizmu sprawdzania poprawności
    $haslo->addValidator(new Zend_Validate_StringLength(6,20));
    
    //Dodanie filtrów
    $haslo->addFilter(new Zend_Filter_HtmlEntities());
    $haslo->addFilter(new Zend_Filter_StripTags());
    
    return $haslo;
  
  }
  
   /**
    * Utworzenie pola dla nowego hasla Użytkownika.
    * Obecnie nie wykorzystywane    
    *
    * @return Zend_Form_Element_Password      
    **/
  public function getNoweHasloPoleTekst(){
  
    //Utworzenie objektu dla hasła
    $noweHaslo = new Zend_Form_Element_Password('noweHaslo');
    $noweHaslo->setLabel('Nowe hasło:');
    //Ustawienie pola jako wymagane
    $noweHaslo->setRequired(true);
          
    //Dodanie mechanizmu sprawdzania poprawności
    $noweHaslo->addValidator(new Zend_Validate_StringLength(6,20));
    
    //Dodanie filtrów
    $noweHaslo->addFilter(new Zend_Filter_HtmlEntities());
    $noweHaslo->addFilter(new Zend_Filter_StripTags());
    
    return $noweHaslo;
  
  }   
    
  /**
   * Utworzenie pola dla nazwy Użytkownika
   *
   * @return Zend_Form_Element_Text    
   **/  
  public function getNazwaUzytkownikaPoleTekst(){
  
    //Utworzenie objektu dla nazwy Użytkownika
    $nazwaUzytkownika = new Zend_Form_Element_Text('nazwaUzytkownika');
    $nazwaUzytkownika->setLabel('Nazwa Użytkownika:');
    //Ustawienie pola jako wymagane
    $nazwaUzytkownika->setRequired(true);
    
    //Dodanie mechanizmu sprawdzania poprawności
    $nazwaUzytkownika->addValidator(new Zend_Validate_StringLength(6,20));
    
    //Dodanie filtrów
    $nazwaUzytkownika->addFilter(new Zend_Filter_StripTags());
    $nazwaUzytkownika->addFilter(new Zend_Filter_HtmlEntities());
    $nazwaUzytkownika->addFilter(new Zend_Filter_StringToLower());
    
    return $nazwaUzytkownika;
  
  }
  
   /**
   * Utworzenie pola dla Imienia Użytkownika
   *
   * @return Zend_Form_Element_Text    
   **/  
  public function getImiePoleTekst(){
  
    //Utworzenie objektu dla imienia Użytkownika
    $imieUzytkownika = new Zend_Form_Element_Text('imieUzytkownika');
    $imieUzytkownika->setLabel('Twoje Imię:');

    //Dodanie mechanizmu sprawdzania poprawności
    $imieUzytkownika->addValidator(new Zend_Validate_StringLength(0,50));
    
    //Dodanie filtrów
    $imieUzytkownika->addFilter(new Zend_Filter_StripTags());
    $imieUzytkownika->addFilter(new Zend_Filter_HtmlEntities());
    $imieUzytkownika->addFilter(new Zend_Filter_StringTrim());
    
    return $imieUzytkownika;
  
  }
  
   /**
   * Utworzenie pola dla nazwiska Użytkownika
   *
   * @return Zend_Form_Element_Text    
   **/  
  public function getNazwiskoPoleTekst(){
    
    //Utworzenie objektu dla nazwiska Użytkownika
    $nazwiskoUzytkownika = new Zend_Form_Element_Text('nazwiskoUzytkownika');
    $nazwiskoUzytkownika->setLabel('Twoje Nazwisko:');

    //Dodanie mechanizmu sprawdzania poprawności
    $nazwiskoUzytkownika->addValidator(new Zend_Validate_StringLength(0,50));
    
    //Dodanie filtrów
    $nazwiskoUzytkownika->addFilter(new Zend_Filter_StripTags());
    $nazwiskoUzytkownika->addFilter(new Zend_Filter_HtmlEntities());
    $nazwiskoUzytkownika->addFilter(new Zend_Filter_StringTrim());
    
    return $nazwiskoUzytkownika;
    
  }
  
   /**
   * Utworzenie pola dla podpisu Użytkownika
   *
   * @return Zend_Form_Element_Textarea    
   **/  
  public function getPodpisPoleTekst(){
  
    //Utworzenie objektu dla podpisu Użytkownika
    $podpisUzytkownika = new Zend_Form_Element_Textarea('podpisUzytkownika');
    $podpisUzytkownika->setLabel('Twój podpis:');
    $podpisUzytkownika->setAttrib('COLS', '40');
    $podpisUzytkownika->setAttrib('ROWS', '5');
    $podpisUzytkownika->setAttrib('maxlength', '80');

    //Dodanie mechanizmu sprawdzania poprawności
    $podpisUzytkownika->addValidator(new Zend_Validate_StringLength(0,80));
    
    //Dodanie filtrów
    $podpisUzytkownika->addFilter(new Zend_Filter_StripTags(array('encoding' => 'UTF-8')));
    $podpisUzytkownika->addFilter(new Zend_Filter_HtmlEntities(array('encoding' => 'UTF-8')));
    $podpisUzytkownika->addFilter(new Zend_Filter_StringTrim(array('encoding' => 'UTF-8')));
    
    return $podpisUzytkownika;  
  
  }
  
   /**
   * Utworzenie pola dla opisu konta Użytkownika
   *
   * @return Zend_Form_Element_Textarea    
   **/  
  public function getOpisKontaPoleTekst(){
  
    //Utworzenie objektu dla opisu konta Użytkownika
    $opisKontaUzytkownika = new Zend_Form_Element_Textarea('opisKontaUzytkownika');
    $opisKontaUzytkownika->setLabel('Opis Twojego konta:');
    $opisKontaUzytkownika->setAttrib('COLS', '40');
    $opisKontaUzytkownika->setAttrib('ROWS', '8');
    $opisKontaUzytkownika->setAttrib('maxlength', '200');

    //Dodanie mechanizmu sprawdzania poprawności
    $opisKontaUzytkownika->addValidator(new Zend_Validate_StringLength(0,200));
    
    //Dodanie filtrów
    $opisKontaUzytkownika->addFilter(new Zend_Filter_StripTags());
    $opisKontaUzytkownika->addFilter(new Zend_Filter_HtmlEntities());
    $opisKontaUzytkownika->addFilter(new Zend_Filter_StringTrim());
    
    return $opisKontaUzytkownika;  
  
  }
  
  
   /**
   * Utworzenie pola dla pliku avatara Użytkownika
   *
   * @return Zend_Form_Element_File  
   **/  
  public function getAvatarPolePlik(){
  
    require_once "Autoryzacja/Autoryzacja.php";
    $sesja = Autoryzacja::kontynuujSesje();
    
    //Utworzenie objektu dla avatara Użytkownika
    $avatarUzytkownika = new Zend_Form_Element_File('avatar');
    $avatarUzytkownika->setLabel('Twój avatar:');
    
    //Określenie miejsca zapisu dla dodawanego pliku avatara
    $lokalizacja = $_SERVER['DOCUMENT_ROOT'] . '/public/uzytk/' . (string)$sesja->id . '/avatar/';
    
    //Utworzenie nowej lokalizacji, jeśli nie istnieje
    if( !is_dir( $lokalizacja ) ){
      mkdir($lokalizacja, 0777, true);
    }
    
    //$frontController = Zend_Controller_Front::getInstance();
    //$baseUrl =  $frontController->getBaseUrl();
    //echo $lokalizacja . "<br />"; 
    //echo APPLICATION_PATH . '/../public/uzytk/' . (string)$sesja->id . '/avatar/';
    
    $avatarUzytkownika->setDestination(APPLICATION_PATH . '/../public/uzytk/' . (string)$sesja->id . '/avatar/');
    
    //Dodanie mechanizmu sprawdzania poprawności
    $avatarUzytkownika->addValidator('Count', false, 1);
    $avatarUzytkownika->addValidator('Extension', false, 'jpg,jpeg,gif,png');
    $avatarUzytkownika->addValidator('Size', false, 102400);
    $avatarUzytkownika->addValidator('MimeType', false, array('image/jpg', 'image/jpeg', 'image/gif', 'image/png'));
    $avatarUzytkownika->addValidator('ImageSize', false,
                          array('minwidth' => 50,
                            'maxwidth' => 100,
                            'minheight' => 50,
                            'maxheight' => 100
                          ));
    
    //Dodanie filtrów
    $avatarUzytkownika->addFilter(new Zend_Filter_StripTags());
    $avatarUzytkownika->addFilter(new Zend_Filter_HtmlEntities());
    $avatarUzytkownika->addFilter(new Zend_Filter_StringTrim());
    
    return $avatarUzytkownika;  
  
  }
  
   /**
   * Utworzenie pola dla elementu captcha Użytkownika
   *
   * @param $etykieta nazwa etykiety elementu
   * @param $dlugosc ilość znaków do przepisania
   *        
   * @return Zend_Form_Element_Captcha    
   **/  
  public function getCaptcha($etykieta, $dlugosc){
  
    $captcha = new Zend_Form_Element_Captcha(
      $etykieta,
      array(
        'captcha' => array(
          'captcha' => 'Dumb',
          'wordLen' => $dlugosc,
          'timeout' => 600
        )
      )
    );
    
    $captcha->setLabel('Przepisz odwrotnie:');
    
    return $captcha;
  
  }
  
  
  
  /*****************************************************************************
   *                                                                           *
   *                    Pola dla elementów dokumentów                          *
   *                                                                           *
   ****************************************************************************/     
  
   /**
   * Utworzenie pola dla nowego dokumentu
   *
   * @return Zend_Form_Element_File  
   **/  
  public function getDodajDokumentPolePlik(){
  
    require_once "Autoryzacja/Autoryzacja.php";
    $sesja = Autoryzacja::kontynuujSesje();
    
    //Utworzenie objektu dla nowego dokumentu Użytkownika
    $dokument = new Zend_Form_Element_File('dokument');
    $dokument->setLabel('Dodaj nowy dokument:');
    $dokument->setRequired(true);
    
    //Określenie miejsca zapisu dla dodawanego pliku dokumentu
    $lokalizacja = $_SERVER['DOCUMENT_ROOT'] . '/public/uzytk/' . (string)$sesja->id . '/dokumenty/';
    
    //Utworzenie nowej lokalizacji, jeśli nie istnieje
    if( !is_dir( $lokalizacja ) ){
      mkdir($lokalizacja, 0777, true);
    }
    $dokument->setDestination($lokalizacja);
    
    //Dodanie mechanizmu sprawdzania poprawności
    $dokument->addValidator('Count', false, 1);
    $dokument->addValidator('Extension', false, 'pdf,doc,docx');
    $dokument->addValidator('Size', false, array('min' => '10kb', 'max' => '6MB')); 
    $dokument->addValidator('MimeType', true, array('application/pdf'));
                          //'application/msword'));
                          //'application/x-pdf', 'applications/vnd.pdf',
                          //'text/pdf', 'text/x-pdf', 'application/octet-stream', // dopuszcza .EXE
                          //'application/acrobat'));
    
    
    //Dodanie filtrów
    $dokument->addFilter(new Zend_Filter_StripTags());
    $dokument->addFilter(new Zend_Filter_HtmlEntities());
    $dokument->addFilter(new Zend_Filter_StringTrim());
    
    return $dokument;  
  
  }
  
   /**
   * Utworzenie pola dla tytułu dokumentu
   *
   * @return Zend_Form_Element_Textarea  
   **/  
  public function getDokumentTytulPoleText(){
  
    //Utworzenie objektu dla tytułu dokumentu
    $dokumentTytul = new Zend_Form_Element_Textarea('dokumentTytul');
    $dokumentTytul->setLabel('Tytuł dokumentu:');
    $dokumentTytul->setAttrib('COLS', '40');
    $dokumentTytul->setAttrib('ROWS', '5');
    $dokumentTytul->setAttrib('maxlength', '200');
    //Ustawienie pola jako wymagane
    $dokumentTytul->setRequired(true);
    
    //Dodanie mechanizmu sprawdzania poprawności
    $dokumentTytul->addValidator(new Zend_Validate_StringLength(1,200));
    
    
    //Dodanie filtrów
    $dokumentTytul->addFilter(new Zend_Filter_StripTags());
    $dokumentTytul->addFilter(new Zend_Filter_HtmlEntities());
    
    return $dokumentTytul;
  
  }
  
   /**
   * Utworzenie pola dla autora dokumentu
   *
   * @return Zend_Form_Element_Textarea  
   **/  
  public function getDokumentAutorPoleText(){
  
    //Utworzenie objektu dla tytułu dokumentu
    $dokumentAutor = new Zend_Form_Element_Textarea('dokumentAutor');
    $dokumentAutor->setLabel('Autor dokumentu:');
    $dokumentAutor->setAttrib('COLS', '40');
    $dokumentAutor->setAttrib('ROWS', '2');
    $dokumentAutor->setAttrib('maxlength', '200');
    //Ustawienie pola jako wymagane
    $dokumentAutor->setRequired(true);
    
    //Dodanie mechanizmu sprawdzania poprawności
    $dokumentAutor->addValidator(new Zend_Validate_StringLength(1,200));
    
    
    //Dodanie filtrów
    $dokumentAutor->addFilter(new Zend_Filter_StripTags());
    $dokumentAutor->addFilter(new Zend_Filter_HtmlEntities());
    
    return $dokumentAutor;
  
  }
  
   /**
   * Utworzenie pola dla przedmiotu dokumentu
   *
   * @return Zend_Form_Element_Text  
   **/  
  public function getDokumentPrzedmiotPoleText(){
  
    //Utworzenie objektu dla pola przdmiot
    $dokumentPrzedmiot = new Zend_Form_Element_Text('dokumentPrzedmiot');
    $dokumentPrzedmiot->setLabel('Przedmiot:');
    $dokumentPrzedmiot->setAttrib('maxlength', '150');

    //Dodanie mechanizmu sprawdzania poprawności
    $dokumentPrzedmiot->addValidator(new Zend_Validate_StringLength(0,150));
    
    //Dodanie filtrów
    $dokumentPrzedmiot->addFilter(new Zend_Filter_StripTags());
    $dokumentPrzedmiot->addFilter(new Zend_Filter_HtmlEntities());
    $dokumentPrzedmiot->addFilter(new Zend_Filter_StringTrim());
    
    return $dokumentPrzedmiot;  
  
  }
  
   /**
   * Utworzenie pola dla rodzaju dokumentu
   *
   * @return Zend_Form_Element_Select  
   **/  
  public function getDokumentRodzajPoleSelect(){
  
    $rodzaje = array('multiOptions' => array(
      '' => '--bez zmian--',
      'material_dyd' => "materiały dydaktyczne",
      'ksiazka' => "książki",
      'czasopismo' => "czasopisma",
      'wyklad' => "wykłady",
      'felieton' => "felietony", 
      'prezentacja' => "prezentacje"
    ));
    
    //Utworzenie objektu dla pola rodzaj
    $dokumentRodzaj = new Zend_Form_Element_Select('dokumentRodzaj', $rodzaje);
    $dokumentRodzaj->setLabel('Rodzaj:');
    
    return $dokumentRodzaj;  
  
  }
  
   /**
   * Utworzenie pola dla nr ISBN dokumentu
   *
   * @return Zend_Form_Element_Text  
   **/  
  public function getDokumentIsbnPoleText(){
  
    //Utworzenie objektu dla pola isbn
    $dokumentIsbn = new Zend_Form_Element_Text('dokumentIsbn');
    $dokumentIsbn->setLabel('Isbn:');
    $dokumentIsbn->setAttrib('maxlength', '14');

    //Dodanie mechanizmu sprawdzania poprawności
    $dokumentIsbn->addValidator(new Zend_Validate_Isbn(array(
      'separator' => '' )));
    
    //Dodanie filtrów
    $dokumentIsbn->addFilter(new Zend_Filter_StripTags());
    $dokumentIsbn->addFilter(new Zend_Filter_HtmlEntities());
    $dokumentIsbn->addFilter(new Zend_Filter_StringTrim());
    
    return $dokumentIsbn;  
  
  }
  
   /**
   * Utworzenie pola dla wydawnictwa dokumentu
   *
   * @return Zend_Form_Element_Text  
   **/  
  public function getDokumentWydawnictwoPoleText(){
  
    //Utworzenie objektu dla pola wydawnictwo
    $dokumentWydawnictwo = new Zend_Form_Element_Text('dokumentWydawnictwo');
    $dokumentWydawnictwo->setLabel('Wydawnictwo:');
    $dokumentWydawnictwo->setAttrib('maxlength', '100');

    //Dodanie mechanizmu sprawdzania poprawności
    $dokumentWydawnictwo->addValidator(new Zend_Validate_StringLength(0,100));
    
    //Dodanie filtrów
    $dokumentWydawnictwo->addFilter(new Zend_Filter_StripTags());
    $dokumentWydawnictwo->addFilter(new Zend_Filter_HtmlEntities());
    $dokumentWydawnictwo->addFilter(new Zend_Filter_StringTrim());
    
    return $dokumentWydawnictwo;  
  
  }
  
   /**
   * Utworzenie pola dla roku wydania dokumentu
   *
   * @return Zend_Form_Element_Text  
   **/  
  public function getDokumentRokWydaniaPoleText(){
  
    //Utworzenie objektu dla pola rok wydania
    $dokumentRokWydania = new Zend_Form_Element_Text('dokumentRokWydania');
    $dokumentRokWydania->setLabel('Rok wydania:');
    $dokumentRokWydania->setAttrib('maxlength', '4');

    //Dodanie mechanizmu sprawdzania poprawności
    $dokumentRokWydania->addValidator(new Zend_Validate_Date(array(
      'format' => 'yyyy')));
    
    //Dodanie filtrów
    $dokumentRokWydania->addFilter(new Zend_Filter_StripTags());
    $dokumentRokWydania->addFilter(new Zend_Filter_HtmlEntities());
    $dokumentRokWydania->addFilter(new Zend_Filter_StringTrim());
    
    return $dokumentRokWydania;  
  
  }
  
   /**
   * Utworzenie pola dla tagów dokumentu
   *
   * @return Zend_Form_Element_Textarea  
   **/  
  public function getDokumentTagiPoleText(){
  
    //Utworzenie objektu dla pola tagi
    $dokumentTagi = new Zend_Form_Element_Textarea('dokumentTagi');
    $dokumentTagi->setLabel('Tagi:');
    $dokumentTagi->setAttrib('maxlength', '150');
    $dokumentTagi->setAttrib('COLS', '40');
    $dokumentTagi->setAttrib('ROWS', '8');

    //Dodanie mechanizmu sprawdzania poprawności
    $dokumentTagi->addValidator(new Zend_Validate_StringLength(0,150));
    
    //Dodanie filtrów
    $dokumentTagi->addFilter(new Zend_Filter_StripTags());
    $dokumentTagi->addFilter(new Zend_Filter_HtmlEntities());
    $dokumentTagi->addFilter(new Zend_Filter_StringTrim());
    
    return $dokumentTagi;  
  
  }
  
   /**
   * Utworzenie pola dla opisu dokumentu
   *
   * @return Zend_Form_Element_Textarea  
   **/  
  public function getDokumentOpisPoleText(){
  
    //Utworzenie objektu dla pola opis
    $dokumentOpis = new Zend_Form_Element_Textarea('dokumentOpis');
    $dokumentOpis->setLabel('Opis:');
    $dokumentOpis->setAttrib('maxlength', '300');
    $dokumentOpis->setAttrib('COLS', '40');
    $dokumentOpis->setAttrib('ROWS', '8');

    //Dodanie mechanizmu sprawdzania poprawności
    $dokumentOpis->addValidator(new Zend_Validate_StringLength(0,300));
    
    //Dodanie filtrów
    $dokumentOpis->addFilter(new Zend_Filter_StripTags());
    $dokumentOpis->addFilter(new Zend_Filter_HtmlEntities());
    $dokumentOpis->addFilter(new Zend_Filter_StringTrim());
    
    return $dokumentOpis;  
  
  }
  
   /**
   * Utworzenie pola dla dostępności dokumentu
   *
   * @return Zend_Form_Element_Select  
   **/  
  public function getDokumentDostepnoscPoleSelect(){
  
    $tryb = array('multiOptions' => array(
      '' => '--bez zmian--',
      'czyt' => 'tylko odczytywanie',
      'pob' => 'odczytywanie i pobieranie',
      'brak' => 'plik zablokowany'
    )); 
    
    //Utworzenie objektu dla pola rodzaj
    $dokumentDostepnosc = new Zend_Form_Element_Select('dokumentDostepnosc', $tryb);
    $dokumentDostepnosc->setLabel('Dostępność:');
    
    return $dokumentDostepnosc;  
  
  }
  
   /**
   * Utworzenie pola dla usuwania dokumentu
   *
   * @param $dokumentId identyfikator dokumentu
   * @param $klasaStylu klasa stylu CSS do określenia sposobu prezentacji w widoku
   *         
   * @return Zend_Form_Element_Checkbox  
   **/  
  public function getDokumentUsunCheckbox($dokumentId, $klasaStylu){
  
    $checkbox = new Zend_Form_Element_Checkbox($dokumentId);
    $checkbox->addDecorator('label', array('class' => $klasaStylu ));
    
    return $checkbox;
      
  
  }
  
   /**
   * Utworzenie pola dla przechodzenia do strony czytanego dokumentu
   *
   * @return Zend_Form_Element_Text  
   **/  
  public function getNrStronyPoleText(){
  
    //Utworzenie objektu dla nr strony dokumentu
    $nrStrony = new Zend_Form_Element_Text('nrStrony');
    //$nrStrony->setLabel('');
    $nrStrony->setAttrib('maxlength', '4');
    $nrStrony->setAttrib('size', '3');
    $nrStrony->setAttrib('id', 'nrStronyPole');
    
    //Dodanie mechanizmu sprawdzania poprawności
    //$nrStrony->addValidator(new Zend_Validate_StringLength(1,4));
    
    //Dodanie filtrów
    $nrStrony->addFilter(new Zend_Filter_Int());
    
    return $nrStrony;  
  
  }
  
  
  
  /*****************************************************************************
   *                                                                           *
   *                    Pola dla elementów Wyszukiwania                        *
   *                                                                           *
   ****************************************************************************/    
  
   /**
   * Utworzenie pola dla wyszukiwania dokumentów
   *
   * @return Zend_Form_Element_Text  
   **/  
  public function getWyszukajDokumentPoleText(){
    
    //Utworzenie objektu dla nazwy Użytkownika
    $wyszukajDokument = new Zend_Form_Element_Text('wyszukajDokument');
    //$wyszukajDokument->setLabel('');
    $wyszukajDokument->setAttrib('maxlength', '100');
    $wyszukajDokument->setAttrib('size', '70');
    
    //Dodanie mechanizmu sprawdzania poprawności
    $wyszukajDokument->addValidator(new Zend_Validate_StringLength(3,100));
    
    //Dodanie filtrów
    $wyszukajDokument->addFilter(new Zend_Filter_StripTags());
    $wyszukajDokument->addFilter(new Zend_Filter_HtmlEntities());
    
    return $wyszukajDokument;
  
  }
  
   /**
   * Utworzenie pola dla wyszukiwania Użytkowników
   *
   * @return Zend_Form_Element_Text  
   **/  
  public function getWyszukajUzytkownikaPoleText(){
    
    //Utworzenie objektu dla nazwy Użytkownika
    $wyszukajUzytkownika = new Zend_Form_Element_Text('wyszukajUzytkownika');
    $wyszukajUzytkownika->setAttrib('maxlength', '100');
    $wyszukajUzytkownika->setAttrib('size', '70');
    
    //Dodanie mechanizmu sprawdzania poprawności
    $wyszukajUzytkownika->addValidator(new Zend_Validate_StringLength(3,100));
    
    //Dodanie filtrów
    $wyszukajUzytkownika->addFilter(new Zend_Filter_StripTags(array('encoding' => 'UTF-8')));
    $wyszukajUzytkownika->addFilter(new Zend_Filter_HtmlEntities(array('encoding' => 'UTF-8')));
    
    return $wyszukajUzytkownika;
  
  }
  
  
  
  
  /*****************************************************************************
   *                                                                           *
   *                    Pola dla elementów Poczty                              *
   *                                                                           *
   ****************************************************************************/              
  
   /**
   * Utworzenie pola dla adresata wiadomości
   *
   * @param $odbiorcy tablica zawierająca dane odbiorców w postaci: id => login
   *        
   * @return Zend_Form_Element_Select  
   **/  
  public function getListaOdbiorcowPoleSelect(array $odbiorcy){ 
    
    $odb = array('multiOptions' => $odbiorcy);
    
    //Utworzenie objektu dla listy wyboru adresata
    $listaOdbiorcow = new Zend_Form_Element_Select('odbiorcy', $odb);
    $listaOdbiorcow->setLabel('Odbiorca wiadomości:');
    $listaOdbiorcow->setRequired(true);
    
    return $listaOdbiorcow;  
  
  }

   /**
   * Utworzenie pola dla tematu wiadomości
   *        
   * @return Zend_Form_Element_Text  
   **/  
  public function getTematWiadomoscPoleText(){
  
    //Utworzenie objektu dla tematu wiadomości
    $tematWiad = new Zend_Form_Element_Text('tematWiadomosci');
    $tematWiad->setLabel('Temat');
    $tematWiad->setAttrib('maxlength', '80');
    $tematWiad->setAttrib('size', '50');
    $tematWiad->setRequired(true);

    //Dodanie mechanizmu sprawdzania poprawności
    $tematWiad->addValidator(new Zend_Validate_StringLength(4,98));
    
    //Dodanie filtrów
    $tematWiad->addFilter(new Zend_Filter_StripTags(array('encoding' => 'UTF-8')));
    $tematWiad->addFilter(new Zend_Filter_HtmlEntities(array('encoding' => 'UTF-8')));
    $tematWiad->addFilter(new Zend_Filter_StringTrim(array('encoding' => 'UTF-8')));
    
    return $tematWiad;  
  
  }
  
   /**
   * Utworzenie pola dla treści wiadomości
   *        
   * @return Zend_Form_Element_Textarea  
   **/  
  public function getTrescWiadomoscPoleText(){
  
    //Utworzenie objektu dla treści wiadomości
    $trescWiad = new Zend_Form_Element_Textarea('trescWiadomosci');
    $trescWiad->setLabel('Treść wiadomości');
    $trescWiad->setAttrib('COLS', '50');
    $trescWiad->setAttrib('ROWS', '10');
    $trescWiad->setAttrib('maxlength', '998');
    $trescWiad->setRequired(true);

    //Dodanie mechanizmu sprawdzania poprawności
    $trescWiad->addValidator(new Zend_Validate_StringLength(4,998));
    
    //Dodanie filtrów
    $trescWiad->addFilter(new Zend_Filter_StripTags(array('encoding' => 'UTF-8')));
    $trescWiad->addFilter(new Zend_Filter_HtmlEntities(array('encoding' => 'UTF-8')));
    $trescWiad->addFilter(new Zend_Filter_StringTrim(array('encoding' => 'UTF-8')));
    
    return $trescWiad;  
  
  }
  
   /**
   * Utworzenie pola dla usuwania odbiorców
   *
   * @param $odbiorcaId identyfikator odbiorcy do usunięcia
   * @param $etykieta nazwa etykiety elementu: login odbiorcy
   * @param $klasaStylu nazwa klasy stylu CSS
   *             
   * @return Zend_Form_Element_Checkbox
   * @return false     
   **/  
  public function getListaOdbiorcowUsunCheckbox($odbiorcaId, $etykieta, $klasaStylu){
  
    if(!empty($odbiorcaId) && !empty($etykieta) && !empty($klasaStylu) ){
      $checkbox = new Zend_Form_Element_Checkbox($odbiorcaId);
      $checkbox->setLabel($etykieta);
      $checkbox->addDecorator('label', array('class' => $klasaStylu ));
    
      return $checkbox;
    }else{  
    
      return false;
    
    }
    
      
  }
  


}

?>
