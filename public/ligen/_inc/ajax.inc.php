<?php
/* AJAX Bibliothek
 * 
 * In dieser Datei wird die Klasse SED_AjaxRequest zur Verfügung
 * gestellt, mit der ein Javascript für eine Ajax-Anfrage erzeugt
 * werden kann.
 * 
 * @copyright Copyright (c) 2006-2010, Marcel Jünemann
 * @version 0.8.0 (2010/7)
 * @license GNU Public License v3
 * @author Marcel Jünemann <mail@marcel-juenemann.de>
 * 
 * @package schach-ergebnisdienst
 * @subpackage libs
 */

class SED_AjaxRequest {
    var $type;
    var $options = array ();
    var $onresult;
    
    function __construct ( $type ){
        $this->type = $type;
    }
    
    function setOption ( $name, $value ){
        $this->options [$name] = $value;
    }

    function onResult ( $code ){
        $this->onresult = str_replace ( "@RESULT@", "req.responseText", $code );
    }

    function getJavascript (){
        // Code vorbereiten
        $code_result = $this->onresult;
        $code_options = "";
        $code_url = "var url = '?m=ajax&type=".$this->type."'";

        // URL zusammensetzen
        foreach ( $this->options as $name=>$value ){
            $code_options .= "option_$name = $value;\n";
            $code_url .= "+'&$name='+encodeURIComponent(option_$name)";
        }

        return "
        {
            // URL zusammensetzen
            $code_options
            $code_url;

            // Ajax initialisieren
            var req = null;
            try { req = new XMLHttpRequest(); }
            catch (e) {
                try { req = new ActiveXObject('Msxml2.XMLHTTP'); }
                catch (e) {
                    try { req = new ActiveXObject('Microsoft.XMLHTTP'); }
                    catch ( failed ) { req = null; }
                }
            }
            if ( req == null ) return;

            // Anfrage abschicken
            req.open ( 'GET', url, true );

            // Bei Antwort folgendes ausführen:
            req.onreadystatechange = function (){
                if ( req.readyState == 4 && req.status == 200 )
                {
                    $code_result
                    return true;
                }
                return false;
            };

            // Endgültig senden
            req.setRequestHeader ( 'Content-Type', 'application/x-www-form-urlencoded' );
            req.send ( null );
        }";
    }
}
?>
