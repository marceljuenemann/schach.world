<?
/* Bibliothek für kurze URLs
 *
 * Funktionen, um kürzere URLs (vor allem in eMails) zu ermöglichen.
 *
 * @copyright Copyright (c) 2006-2010, Marcel Jünemann
 * @version 0.8.0 (2010/7)
 * @license GNU Public License v3
 * @author Marcel Jünemann <mail@marcel-juenemann.de>
 *
 * @package schach-ergebnisdienst
 * @subpackage libs
 */

/* TYPEN VON TINYURLs
 * 1-PIDauth            Ergebniseingabe (4 Stellen PID)
 * 2-MIDauth            Mannschaftsdaten (4 Stellen MID)
 * 3-sid                Staffelleiter-Login
 *
 *
 */

function SED_TINYURL_parse ()
{
    global $globals;

    switch ( $_GET ['type'] )
    {
        case 1:
            $_GET ['pid'] = substr ( $_GET ['p1'], 0, 4 );
            $_GET ['pid'] = base_convert ( $_GET ['pid'], 36, 10 );
            $_GET ['auth'] = substr ( $_GET ['p1'], 4 );
            require_once ( "turnier.inc.php" );
            header ( "Location: $globals[httppath]$prefs[directory]/?m=eingabe&pid=$_GET[pid]&auth=$_GET[auth]" );
            exit;

            $globals ['mod'] = "eingabe";
            break;

        case 2:
            $_GET ['mid'] = substr ( $_GET ['p1'], 0, 4 );
            $_GET ['mid'] = base_convert ( $_GET ['mid'], 36, 10 );
            $_GET ['auth'] = substr ( $_GET ['p1'], 4 );
            require_once ( "turnier.inc.php" );
            header ( "Location: $globals[httppath]$prefs[directory]/?m=mannschaftsdaten&mid=$_GET[mid]&auth=$_GET[auth]" );
            exit;

            $globals ['mod'] = "mannschaftsdaten";
            break;

        case 3:
            $_GET ['staffel'] = $_GET ['p1'];
            require_once ( "turnier.inc.php" );
            header ( "Location: $globals[httppath]$prefs[directory]/?m=startseite&staffel=$_GET[staffel]" );
            exit;
            break;
    }
}

function SED_TINYURL_Paarung ( $pid )
{
    global $globals;
    require_once ( "auth.inc.php" );
    $auth = SED_MD5_PID ( $pid );
    $pid = base_convert ( $pid, 10, 36 );
    $pid = str_repeat ( "0", 4-strlen($pid) ) . $pid;
    return "$globals[httppath]"."1/$pid$auth";
}

function SED_TINYURL_Mannschaftsdaten ( $mid )
{
    global $globals;
    require_once ( "auth.inc.php" );
    $auth = SED_MD5_MID ( $mid );
    $mid = base_convert ( $mid, 10, 36 );
    $mid = str_repeat ( "0", 4-strlen($mid) ) . $mid;
    return "$globals[httppath]"."2/$mid$auth";
}

function SED_TINYURL_Login ( $sid )
{
    global $globals;
    require_once ( "auth.inc.php" );
    return "$globals[httppath]"."3/$sid";
}


?>
