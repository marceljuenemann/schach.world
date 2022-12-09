/* Ergebniseingabe: Javascript für Ergebnisbearbeitung
 * 
 * @copyright Copyright (c) 2006-2010, Marcel Jünemann
 * @version 0.8.0 (2010/7)
 * @license GNU Public License v3
 * @author Marcel Jünemann <mail@marcel-juenemann.de>
 * 
 * @package schach-ergebnisdienst
 * @subpackage eingabe
 */
  /*
    Kopiert von alleeing_eingabe!
  
    Beim Start
    - Fast alle Felder färben

    Bei Ergebnisauswahl
    - Gegnerergebnis setzen
    - Gesamtergebnis berechnen
  */

  var g_bretter = 8;
  var g_color = "#fff3ca";

  ///////////////////////////////
  // TOOLS
  ///////////////////////////////
    
  // Berechnet die Brettanzahl
  function SED_Brettzahl ()
  {
    for ( i = 1; i <= 1000; ++i )
    {
      if ( document.getElementsByName ( "ergheim" + i ).length < 1 )
      	return i - 1;
    }
  }

  // Gibt den Punktewert eines Ergebnisses zurück
  function SED_Ergebniswert ( erg )
  {
    switch ( erg )
    {
      case "0":   return 0;    break;
      case "½":   return 0.5;  break;
      case "1":   return 1;    break;
      case "-":   return 0;    break;
      case "+":   return 1;    break;
      case "?":   return 0;    break;
    }
  }
  
  // Gibt das Gegenteil eines Ergebniswertes zurück
  function SED_ErgebnisGegenteil ( erg )
  {
    switch ( erg )
    {
      case "0":   return "1";    break;
      case "½":   return "½";    break;
      case "1":   return "0";    break;
      case "-":   return "+";    break;
      case "+":   return "-";    break;
      case "?":   return "?";    break;
    }
  }
  
  // Setzt die Farbe bei einer Auswahlbox
  function SED_SetColor ( name )
  {
      document.getElementsByName ( name )[0].style.backgroundColor = g_color;
  }
  
  // Prüft, ob die Farbe gesetzt ist
  function SED_IsColor ( name )
  {
    if ( document.getElementsByName ( name )[0].style.backgroundColor )
      return true;
    else
      return false;
  }
      
  ///////////////////////////////
  // ERGEBNIS-BERECHNUNGEN ETC.
  ///////////////////////////////

  // Berechnet und setzt das Gesamtergebnis
  function SED_Gesamtergebnis ()
  {
    // Wurde nur das Heimergebnis von Hand gesetzt?
    if ( SED_IsColor ( "gesheim" ) && !SED_IsColor ( "gesgast" ) )
    {
        document.getElementsByName ( "gesgast" )[0].value =
            g_bretter - document.getElementsByName ( "gesheim" )[0].value;
    }
    
    // Wurde nur das Gastergebnis von Hand gesetzt?
    else if ( SED_IsColor ( "gesgast" ) && !SED_IsColor ( "gesheim" ) )
    {
        document.getElementsByName ( "gesheim" )[0].value =
            g_bretter - document.getElementsByName ( "gesgast" )[0].value;
    }    

    // Wenn nichts von Hand gesetzt wurde, Einzelergebnisse addieren
    else if ( !SED_IsColor ( "gesgast" ) && !SED_IsColor ( "gesheim" ) )
    {
        var heim = 0, gast = 0;
        for ( i = 1; i <= g_bretter; ++i )
        {
          heim += SED_Ergebniswert ( document.getElementsByName ( "ergheim" + i )[0].value );
          gast += SED_Ergebniswert ( document.getElementsByName ( "erggast" + i )[0].value );
        }
        document.getElementsByName ( "gesheim" )[0].value = heim;
        document.getElementsByName ( "gesgast" )[0].value = gast;   
    }    
  }

  // Setzt ein Einzelergebnis
  function SED_Einzelergebnis ( brett )
  {
    // Bisherige Eingaben abfragen
    erg1 = document.getElementsByName ( "ergheim"+brett )[0].value;
    erg2 = document.getElementsByName ( "erggast"+brett )[0].value;
    color1 = SED_IsColor ( "ergheim"+brett );
    color2 = SED_IsColor ( "erggast"+brett );
    spieler1 = document.getElementsByName ( "spiheim"+brett )[0].value;
    spieler2 = document.getElementsByName ( "spigast"+brett )[0].value;
      
    // Wurde nur das Heimergebnis von Hand gesetzt?
    if ( color1 && !color2 )
        erg2 = SED_ErgebnisGegenteil ( erg1 );
    
    // Wurde nur das Gastergebnis von Hand gesetzt?
    else if ( !color1 && color2 )
        erg1 = SED_ErgebnisGegenteil ( erg2 );

    // Wenn nichts von Hand gesetzt wurde...
    else if ( !color1 && !color2 ) 
    {
        // Ist nur die Heimmannschaft ein NULL-Spieler?
        if ( spieler1 == "x" && spieler2 != "x" )
            { erg1 = "+"; erg2 = "-" }
            
        // Ist nur die Gastmannschaft ein NULL-Spieler?
        else if ( spieler1 != "x" && spieler2 == "x" )
            { erg1 = "-"; erg2 = "+" }

        // Sind beide ein NULL-Spieler?
        else if ( spieler1 == "x" && spieler2 == "x" )
            { erg1 = "-"; erg2 = "-" }

        // Ansonsten nach DWZ schätzen
        else
        {
            // DWZ abfragen
            dwz1 = parseInt ( document.getElementsByName ( "dwz_"+spieler1 )[0].value );
            dwz2 = parseInt ( document.getElementsByName ( "dwz_"+spieler2 )[0].value );
         
            // DWZ-lose als 0 interpretieren
            if ( isNaN ( dwz1 ) ) dwz1 = 0;
            if ( isNaN ( dwz2 ) ) dwz2 = 0;
         
            // Bis 100 Punkte Unterschied ist remis
            if ( dwz1 > dwz2 + 100 )
                { erg1 = "1"; erg2 = "0"; } 
            else if ( dwz2 > dwz1 + 100 )
                { erg1 = "0"; erg2 = "1"; }
            else
                { erg1 = "½"; erg2 = "½"; }
        }
    }        

    // Ergebnis setzen
    document.getElementsByName ( "ergheim"+brett )[0].value = erg1;
    document.getElementsByName ( "erggast"+brett )[0].value = erg2;
  }

  // Wählt Spieler unterhalb der Auswahl entsprechend aus
  function SED_WaehleSpieler ( mannschaft, brett )
  {
    for ( i = brett; i <= g_bretter; ++i )
    {
      // Wenn der Spieler von Hand gesetzt wurde, abbrechen
      if ( SED_IsColor ( mannschaft+i ) )
        return;  
        
      // Wenn der vorherige NULL oder Nachmeldung war, muss der hier NULL werden
      if ( document.getElementsByName ( mannschaft + (i-1) )[0].value != "s" )
        document.getElementsByName ( mannschaft + i )[0].value = "x";
        
      // Ansonsten den nächsten in der Liste nehmen
      else if ( brett >= 2 )
        document.getElementsByName ( mannschaft + i )[0].selectedIndex =
          document.getElementsByName ( mannschaft + (i-1) )[0].selectedIndex + 1;
          
      // Ergebnis neu schätzen
      SED_Einzelergebnis ( i );
    }
  }
  
  
  ///////////////////////////////
  // EVENT-HANDLER
  ///////////////////////////////
  
  // Wenn ein Spieler ausgewählt wurde
  function SED_OnSelectSpieler ( mannschaft, brett )
  {
    // Nachmeldung... ausgewählt?
    obj = document.getElementsByName ( mannschaft+brett )[0];
    if ( obj.value == "n" )
    {
        // Nachnamen abfragen
        name = prompt ( "Wie lautet der Name des Spielers, den Sie nachmelden möchten?" );
        if ( name.length < 3 )
            return;
        
        // Spieler in Liste einfügen
        NeuerEintrag = new Option (name, "n"+name, false, true);
        obj.options [obj.length] = NeuerEintrag;
    }
  }

  // Wenn ein Einzelergebnis ausgewählt wurde
  function SED_OnSelectEinzelergebnis ( mannschaft, brett )
  {
    SED_SetColor ( mannschaft + brett );
    SED_Einzelergebnis ( brett );
    SED_Gesamtergebnis ();
  }
    
  // Wenn das Gesamtergebnis ausgewählt wurde
  function SED_OnSelectGesamtergebnis ( mannschaft )
  {
    SED_SetColor ( mannschaft );
    SED_Gesamtergebnis ();
  }
    
  ///////////////////////////////
  // START
  ///////////////////////////////

  function SED_Start ()
  {
    // Brettzahl berechnen
    g_bretter = SED_Brettzahl ();

    // Bis auf Gesamt- und Gastergebnisse alle Felder färben
    for ( i = 1; i <= g_bretter; ++i )
    {
        SED_SetColor ( "spiheim"+i );
        SED_SetColor ( "ergheim"+i );
        SED_SetColor ( "spigast"+i );
    }
  }
  SED_Start ();
