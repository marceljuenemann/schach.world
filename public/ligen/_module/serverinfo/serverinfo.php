<?
/* Server Statistik
 * 
 * @copyright Copyright (c) 2006-2010, Marcel Jünemann
 * @version 0.8.0 (2010/7)
 * @license GNU Public License v3
 * @author Marcel Jünemann <mail@marcel-juenemann.de>
 * 
 * @package schach-ergebnisdienst
 * @subpackage main
 */
    header ( "Content-type: text/plain" );
    
echo "Schach-Ergebnisdienst Version $globals[systemversion]
Copyright (C) 2006-2010 Marcel Jünemann <mail@marcel-juenemann.de>
http://code.google.com/p/schach-ergebnisdienst/

Server-Info
Basis-Verzeichnis: $globals[httppath]
Webmaster: $globals[webmaster] <$globals[webmaster_mail]

Server-Statistik
Anzahl Turniere: ".reset(mysql_fetch_array(mysql_query("SELECT COUNT(*) FROM turniere")))."
Anzahl Staffeln: ".reset(mysql_fetch_array(mysql_query("SELECT COUNT(*) FROM staffeln")))."
Anzahl Mannschaften: ".reset(mysql_fetch_array(mysql_query("SELECT COUNT(*) FROM mannschaften")))."
Anzahl Spieler: ".reset(mysql_fetch_array(mysql_query("SELECT COUNT(*) FROM spieler")))."
Anzahl Partien: ".reset(mysql_fetch_array(mysql_query("SELECT COUNT(*) FROM spielerpaarungen")))."

This program is free software; you can redistribute it and/or modify it
under the terms of the GNU General Public License as published by the
Free Software Foundation; either version 3 of the License, or (at your
option) any later version.

This program is distributed in the hope that it will be useful, but 
WITHOUT ANY WARRANTY; without even the implied warranty of 
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General 
Public License for more details.

You should have received a copy of the GNU General Public License along 
with this program; if not, see <http://www.gnu.org/licenses/>.

";
?>


