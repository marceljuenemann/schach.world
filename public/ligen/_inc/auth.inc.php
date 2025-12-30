<?php
/* Authentifizierungs Bibliothek
 * 
 * Diese Bilbliothek stellt vor allem Funktionen zur Verfügung, die
 * verschiedene MD5-Hashes erstellen können.
 * 
 * @copyright Copyright (c) 2006-2010, Marcel Jünemann
 * @version 0.8.0 (2010/7)
 * @license GNU Public License v3
 * @author Marcel Jünemann <mail@marcel-juenemann.de>
 * 
 * @package schach-ergebnisdienst
 * @subpackage libs
 */

	function SED_MD5_MID ( $mid )
	{
		return SED_MD5 ( "MID$mid" );
	}
	
	function SED_MD5_PID ( $pid )
	{
		return SED_MD5 ( "PID$pid" );
	}
	
    function SED_MD5_TL ()
    {
        global $prefs;
        return SED_MD5 ( "TL$prefs[leiter]" );
    }
    
	function SED_MD5 ( $str )
	{
		global $globals;
		return substr ( md5 ( "$str$globals[salt]" ), 0, $globals ['md5-length'] ); 
	}
  
	// Generiert eine zehnstelliges Passwort
	function SED_GeneratePassword ( $length = 10 )
	{
		$rnd = "";
		for ( $i = 0; $i < $length; ++$i )
		 $rnd .= base_convert ( rand ( 1, 30 ), 10, 32 );
		return $rnd;
	}
?>
