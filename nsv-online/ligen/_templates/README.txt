Schach-Ergebnisdienst
Copyright (C) 2006-2008 Marcel Jünemann <warumistkeinnamefrei@gmail.com>

This program is free software; you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation; either version 3 of the License, or (at your option) any later version.

This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.

You should have received a copy of the GNU General Public License along with this program; if not, see <http://www.gnu.org/licenses/>.

--------------------

Richtlinien zur Erstellung eines Templates
Letzte Änderung: 18.7.2010


1. Die Dateien start.php und end.php werden aufgerufen und müssen daher vorhanden sein.

2. Es müssen die folgenden CSS-Klassen definiert werden:
>> sed_only_screen, sed_only_print
>> table.sed_normal, table.sed_normal .l, [...] .c, [...] .r
>> table.sed_tabelle, table.sed_tabelle .l, [...] .c, [...] .r
>> td.sed_aufsteiger, td.sed_absteiger, td.sed_aufsteigerRelegation, td.sed_absteigerRelegation
>> span.sed_hl1, span.sed_hl2, span.sed_hl3
>> input.sed_submit
>> table.sed_home, table.sed_home td.l, table.sed_home td.r / für Startseite
>> fieldset.sed_admin_desk, table.sed_admin_desk, td.sed_admin_desk_icon
>> img.sed_admin_icon / für Admin-Bereich
>> hr.sed_hr / für Admin-Bereich
>> div.sed_infomeldung

3. Es müssen die Javascript-Funktionen SED_SendEmail, SED_CookieSetzen, SED_CookieLesen und dummy implementiert werden.

4. Im Head-Tag des HTML-Dokuments muss der Inhalt von $globals ['premod_headtag'] ausgegeben werden.

5. Wenn $prefs ['infomeldung'] gesetzt ist, muss diese ausgegeben werden.


Ansonsten bietet das System Narrenfreiheit. sehr wichtig bei der Templateerstellung sind sicher $globals und SED_GetMenue (). Optimalerweise benutzt man für die CSS-Klassen des Templates ein eigenes Präfix.

