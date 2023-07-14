<?
/* Graphische Oberfläche
 *
 * Dieses Skript berechnet, welches Template benutzt werden soll und
 * gibt dessen Anfang aus.
 *
 * @copyright Copyright (c) 2006-2010, Marcel Jünemann
 * @version 0.8.0 (2010/7)
 * @license GNU Public License v3
 * @author Marcel Jünemann <mail@marcel-juenemann.de>
 *
 * @package schach-ergebnisdienst
 * @subpackage main
 */

  global $globals, $prefs;

    // Template berechnen
    if ( !isset ( $globals ['templatedir'] ) )
    {
        $template = "nsv";
        if ( isset ( $prefs ['template'] ) ) {
            $template = $prefs ['template'];
        }
        if ( SED_IsNsv2020() ) {
            $template = 'nsv2020';
        }
        $globals ['templatedir'] = "$globals[basedir]/_templates/$template";
    }

    // Seitenheader ausgeben
    require_once ( "$globals[templatedir]/start.php" );


    // Gibt den Rest der Seite aus
    function SED_GUIclose ()
    {
        global $globals;
        global $prefs;
        require_once ( "$globals[templatedir]/end.php" );
    }

  // Gibt das Turniermenü als MySQL-Resource zurück
  function SED_GetMenue ()
  {
    global $globals;
    $rsrc = mysql_query ( "SELECT titel, url, neuesfenster, topnavigation FROM turniermenue WHERE turnier=$globals[tid] ORDER BY sortid", $globals ['db'] );
    if ( $rsrc && mysql_num_rows ( $rsrc ) )
      return $rsrc;
    return false;
  }

  // Wählt eine Option bei Selectstrings aus
  function SED_SelectOption ( $string, $id )
  {
    return str_replace ( "value='$id'", "value='$id' selected='selected'", $string );
  }

  // Schützt vor Spam-Robots
  function SED_Email ( $text, $mail = false, $maxchar = 200 )
  {
    static $cryptsalt;

    // Wenn Text=Email angegeben ist
    if ( !$mail ) $mail = $text;

    // Maximale Länge für den Namen?
    if ( strlen ( $text ) > $maxchar + 3 )
        $text = substr ( $text, 0, $maxchar )."...";

    // Key generieren
    if ( !$cryptsalt )
    {
      $cryptsalt = time ();
      for ( $i = 0; $i < rand ( 1, 5 ); ++$i )
       $cryptsalt .= base_convert ( rand ( 11, 36 ), 10, 32 );
    }

    // Mail verschlüsseln
    $cryptname = str_replace ( '@', "<span style='display: none'> @NSV-Spam-Schutz@ </span>@", $text );
    $cryptmail = str_replace ( '@', $cryptsalt, $mail );
    return "<a href=\"javascript: mail = '$cryptmail'; location = 'mailto:' + mail.replace ( /$cryptsalt/, '@' );\">$cryptname</a>";
  }


?>
