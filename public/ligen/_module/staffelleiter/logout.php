<?
/* SL-Bereich: Logout
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

    // Sitzung beenden durch rücksetzen der Zeit
    mysql_query ( "UPDATE benutzer SET random='123', letzterzugriff=NOW()-100000 WHERE id=$admin[userid] LIMIT 1", $globals ['db'] );

    // Weiterleiten zu einer interessanteren Seite
    if ( $admin ["staffel"] )
        echo "<meta http-equiv='refresh' content='0;URL=?staffel=$admin[staffel]&r=' />";
    else
        echo "<meta http-equiv='refresh' content='0;URL=?' />";
?>
