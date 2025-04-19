<?php

require_once ( "login.inc.php" );

echo '<h2>SWI-Format</h2>';

$league = SED_Bridge()->league;
echo "<a href='/ligen/{$league->path}/swi/'>Alle Staffeln als .zip</a><br />";
foreach ( $league->divisions as $division ) {
  echo "<a href='/ligen/{$league->path}/{$division->path()}/swi/'>{$division->name}</a><br />";
}
