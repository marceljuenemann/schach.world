<?
/* Spieltag: Export Schnittstelle
 * 
 * Aufruf über ...&ausgabe=phparray. Format s. inc/spieltag
 * 
 * @copyright Copyright (c) 2006-2010, Marcel Jünemann
 * @version 0.8.0 (2010/7)
 * @license GNU Public License v3
 * @author Marcel Jünemann <mail@marcel-juenemann.de>
 * 
 * @package schach-ergebnisdienst
 * @subpackage spieltag
 */

  $data = array ();
  if ( Spieltag ( $globals ['tid'], $_GET ['staffel'], $_GET ['r'], $data, false, false ) )
    $data ['tabelle'] = Tabelle ( $data ['staffelid'], $_GET ['r'], false, 1 );

  echo serialize ( $data );

  exit;
?>
