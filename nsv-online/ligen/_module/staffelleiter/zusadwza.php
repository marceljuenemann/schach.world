<?
/* SL-Bereich: DWZ-Auswertung für Elobase
 *
 * @copyright Copyright (c) 2006-2010, Marcel Jünemann
 * @version 0.8.0 (2010/7)
 * @license GNU Public License v3
 * @author Marcel Jünemann <mail@marcel-juenemann.de>
 *
 * @package schach-ergebnisdienst
 * @subpackage staffelleiter
 */
    require_once ( "login.inc.php" );
?>

<h2>SWI-Format (f&uuml;r Elobase)</h2>
<a href='?m=export&format=swi&zip=true'>Alle Staffeln als .zip</a><br />
<?
  foreach ( $globals ['staffeln'] as $k => $v )
    echo "<a href='?m=export&format=swi&staffel=$k'>$v</a><br />";
?>

<h2>MLF-Format</h2>
<a href='?m=export&format=mlf&zip=true'>Alle Staffeln als .zip</a><br />
<?
  foreach ( $globals ['staffeln'] as $k => $v )
    echo "<a href='?m=export&format=mlf&staffel=$k'>$v</a><br />";
?>
