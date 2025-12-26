<?php
/* AJAX Modul
 * 
 * @copyright Copyright (c) 2006-2010, Marcel Jünemann
 * @version 0.8.0 (2010/7)
 * @license GNU Public License v3
 * @author Marcel Jünemann <mail@marcel-juenemann.de>
 * 
 * @package schach-ergebnisdienst
 * @subpackage ajax
 */
	$file = str_replace ( "..", "-", $_GET ['type'] );
	include ( "$globals[basedir]/_module/ajax/$file.php" );
?>
