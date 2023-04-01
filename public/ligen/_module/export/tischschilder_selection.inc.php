<form action="?" method="GET" target='_blank'>
<input type='hidden' name='m' value='export'>
<input type='hidden' name='format' value='tischschilder'>

Titel: <input type='text' size='50' name='titel' value='Jugend-Bundesliga in Lehrte'><br><br>
Datum: <input type='text' size='30' name='datum' value='18.-19. Mai 2019'><br><br>

<?php 
global $prefs;
$rsrc = mysql_query("select id, name from mannschaften where turnier = '$prefs[id]' order by name");
while ($team = mysql_fetch_array($rsrc)) {
	echo "<input type='submit' name='mid' value='$team[id]'> $team[name]<br><br>";
}
?>

</form>
