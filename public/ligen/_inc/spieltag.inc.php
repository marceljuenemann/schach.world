<?
/* Backend zur Spieltag-Anzeige
 * 
 * Dieses Skript berechnet alle Daten, die bei der Spieltag-Ansicht
 * dargestellt werden und kümmert sich auch um das cachen dieser Daten.
 * 
 * @copyright Copyright (c) 2006-2010, Marcel Jünemann
 * @version 0.8.0 (2010/7)
 * @license GNU Public License v3
 * @author Marcel Jünemann <mail@marcel-juenemann.de>
 * 
 * @package schach-ergebnisdienst
 * @subpackage spieltag
 */

  /*
  ---Parameter---
    turnier
    staffel
    runde
    result (Array als Referenz, in das die Ergebnisse gespeichert werden)
    tabelle_kreuz (bool)
    tabelle_links (bool)

  ---Gibt das folgende Array zurück:---
    turnierid
    turniername
    staffelid
    staffelname
    datum
    sl_name
    sl_telefon
    sl_email
    bemerkung
    timestamp
    paarungen (!)
      id
      m1
      m2
      mid1
      mid2
      erg1
      erg2
      bemerkung
      timestamp
      datum
      ausrichter
      ausrichterId      
      kampflos
      paarungen (!)
        id
        s1nachname
        s1vorname
        s1titel
        s2nachname
        s2vorname
        s2titel
        s1fullname
        s2fullname
        sid1
        sid2
        s1pass
        s2pass
        dwz1
        dwz2
        erg1
        erg2
    nachmeldungen (!)
      id
      nachname
      vorname
      titel
      mid
      mannschaft
      geburt
      dwz
      passnr
      berechtigtAb
      fullname
    vorschau (!)
      mid1
      mid2
      mannschaft1
      mannschaft2
      verlegung
    vorschautermin
  */
  require_once ( "cache.inc.php" );

function Spieltag ( $turnier, $staffel, $runde, &$result, $dummy1 = 0, $dummy2 = 0 )
{
    global $globals;
    global $prefs;
    $result = array ();

    // Simple Parameter Überprüfung
    if ( $turnier != $globals ['tid'] || isset ( $globals ['staffeln'][$staffel] ) == false || is_numeric ( $runde ) == false )
    return false;

    // Im Cache?
    if ( $result = SED_Cache::load ( SED_Cache::SPIELTAG, $runde, $staffel ) )
        return true;
        
    // Ansonsten neu generieren
    else
    {
        // Informationen und Einstellungen über die Staffel abfragen
        $infos = mysql_fetch_array ( mysql_query ( "SELECT * FROM viewStaffeln WHERE id=$staffel", $globals ['db'] ), MYSQL_ASSOC );
        if ( !is_array ( $infos ) ) return false;

        // Die ersten Daten setzen
        $result ['turnierid'] = $infos ['turnier'];
        $result ['turniername'] = $prefs ['name'];
        $result ['staffelid'] = $infos ['id'];
        $result ['staffelname'] = $infos ['name'];
        $result ['datum'] = SED_GetTermin ( $runde, $staffel );
        $result ['sl_name'] = $infos ['staffelleiter'];
        $result ['sl_telefon'] = $infos ['telefon'];
        $result ['sl_email'] = $infos ['email'];

        // Spieltag-Bemerkung abfragen
        $result ['bemerkung'] = "";
        $result ['timestamp'] = 0;
        $temp = mysql_query ( "SELECT text, UNIX_TIMESTAMP(timestamp) FROM bemerkungen WHERE staffel=$staffel AND runde=$runde LIMIT 1", $globals ['db'] );
        if ( mysql_num_rows ( $temp ) > 0 && $tmp = mysql_fetch_array ( $temp ) ){
            $result ['bemerkung'] = $tmp [0];
            $result ['timestamp'] = $tmp [1];
        }

        // Paarungen
        $i = 0;
        $temp = mysql_query ( "SELECT p.id, p.mannschaft1 as mid1, p.mannschaft2 as mid2, p.erg1, p.erg2, p.bemerkung, DATE_FORMAT(vt.termin,'%d.%m.%Y') as datum, UNIX_TIMESTAMP(p.timestamp) timestamp, vt.ausrichter as ausrichterId FROM paarungen as p INNER JOIN viewTermine vt ON p.id=vt.paarung WHERE p.staffel=$staffel AND p.runde=$runde", $globals ['db'] );
        while ( $result ['paarungen'][$i] = mysql_fetch_array ( $temp, MYSQL_ASSOC ) )
        {
            // Mannschaft und Ergebnisse
            $result ['paarungen'][$i]['m1'] = $globals ['teams'][$result ['paarungen'][$i]['mid1']];
            $result ['paarungen'][$i]['m2'] = $globals ['teams'][$result ['paarungen'][$i]['mid2']];
            $result ['paarungen'][$i]['erg1'] = SED_Ergebnis ( $result ['paarungen'][$i]['erg1'] );
            $result ['paarungen'][$i]['erg2'] = SED_Ergebnis ( $result ['paarungen'][$i]['erg2'] );

            // Spielverlegung und Ausrichter
            if ( $result ['paarungen'][$i]['datum'] == "24.12.2020" )
                $result ['paarungen'][$i]['datum'] = "(unbekannt)";
            $result ['paarungen'][$i]['ausrichter'] = $globals ['teams'][$result ['paarungen'][$i]['ausrichterId']];

            // Timestamp des gesamten Spieltages
            if ( $result ['paarungen'][$i]['timestamp'] > $result ['timestamp'] )
                $result ['timestamp'] = $result ['paarungen'][$i]['timestamp'];

            // Spielerpaarungen
            $j = 0;
            $kampflos = 1;
            $temp2 = mysql_query ( "SELECT s1.nachname as s1nachname, s1.vorname as s1vorname, TRIM(s1.titel) as s1titel, s1.brettnr as s1pass, s2.nachname as s2nachname, s2.vorname as s2vorname, s2.titel as s2titel, s2.brettnr as s2pass, sp.spieler1 as sid1, sp.spieler2 as sid2, IF(s1.dwz=0,'',s1.dwz) as dwz1, IF(s2.dwz=0,'',s2.dwz) as dwz2, sp.ergebnis1 as erg1, sp.ergebnis2 as erg2 FROM spielerpaarungen as sp LEFT JOIN spieler as s1 ON s1.id=sp.spieler1 LEFT JOIN spieler as s2 ON s2.id=sp.spieler2 WHERE paarung=".$result ['paarungen'][$i]['id']." ORDER BY brett", $globals ['db'] );
            while ( $result ['paarungen'][$i]['paarungen'][$j] = mysql_fetch_array ( $temp2, MYSQL_ASSOC ) )
            {
                // Vollständige Namen zusammensetzen
                $result ['paarungen'][$i]['paarungen'][$j]['s1fullname'] = SED_Spielername ( $result ['paarungen'][$i]['paarungen'][$j], "s1" );
                $result ['paarungen'][$i]['paarungen'][$j]['s2fullname'] = SED_Spielername ( $result ['paarungen'][$i]['paarungen'][$j], "s2" );
                
                // Kampflos überprüfen
                if ( $kampflos && $result ['paarungen'][$i]['paarungen'][$j]['erg1'] != "+" && $result ['paarungen'][$i]['paarungen'][$j]['erg1'] != "-" )
                    $kampflos = 0;
                ++$j;
            }          
            array_pop ( $result ['paarungen'][$i]['paarungen'] ); // das letzte element ist einfach false, wegen mysql_fetch_array. entfernen
            $result ['paarungen'][$i]['kampflos'] = $kampflos;
            ++$i;
        }
        array_pop ( $result ['paarungen'] ); // s. oben, auch wegen false durch mysql_fetch_array

        // Nachmeldungen
        {
            // Abfrage
            $result ['nachmeldungen'] = array ();
            $i = 0;
            $temp = mysql_query ( "SELECT id, nachname, vorname, titel, mannschaft as mid, geburt, dwz, brettnr as passnr, nmR as berechtigtAb FROM spieler WHERE nmSid=$staffel AND (nmR=$runde OR nmR=$runde+1) ORDER BY mid, brettnr, id", $globals ['db'] );
            if ( $temp && mysql_num_rows ( $temp ) > 0 && $infos ['showNachmeldungen'] )
            {
                while ( $result ['nachmeldungen'][$i] = mysql_fetch_array ( $temp, MYSQL_ASSOC ) )
                {
                    $result ['nachmeldungen'][$i]['fullname'] = SED_Spielername($result ['nachmeldungen'][$i]);
                    $result ['nachmeldungen'][$i]['mannschaft'] = $globals ['teams'][$result ['nachmeldungen'][$i]['mid']];
                    ++$i;
                }
                array_pop ( $result ['nachmeldungen'] );
            }
        }

        // Spieltag Vorschau
        $result ['vorschautermin'] = SED_GetTermin ( $runde + 1, $staffel );
        $rsrc = mysql_query ( "SELECT mannschaft1 as mid1, mannschaft2 as mid2, DATE_FORMAT(termin,'%d.%m.%Y') verlegung FROM paarungen WHERE staffel=$staffel and runde=$runde+1", $globals ['db'] );
        if ( $rsrc && mysql_num_rows ( $rsrc ) /* && $staffel ['infos'] */ )
        {
            for ( $i = 0; $temp = mysql_fetch_array ( $rsrc, MYSQL_ASSOC ); ++$i )
            {
                $result ['vorschau'][$i] = $temp;
                $result ['vorschau'][$i]['mannschaft1'] = $globals ['teams'][$result ['vorschau'][$i]['mid1']];
                $result ['vorschau'][$i]['mannschaft2'] = $globals ['teams'][$result ['vorschau'][$i]['mid2']];
            }
        }
        else
            $result ['vorschau'] = false;

        // Im Cache speichern
        SED_Cache::cache ( $result, SED_Cache::SPIELTAG, $runde, $staffel, $turnier );
        return true;
    }
}
?>
