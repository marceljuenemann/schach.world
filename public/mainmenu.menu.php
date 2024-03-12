<?
$nsvMainmenu = array (

"NSV" => array (
	"nsv/",
	(bool) strstr ( $_SERVER ['PHP_SELF'], "/archiv/" ),

	"Neuigkeiten" => "",
	"Vorstand" => "vorstand/",
	"Kongress" => "kongress/",
	"Satzung und Ordnungen" => "ordnungen/",
	"Mitgliederverwaltung" => "mitgliederverwaltung/",

	"Ruhmeshalle" => "ruhmeshalle/",
	"Ehrentafel" => "ehrentafel/",
	"Vorstandschronik" => "vorstandschronik/",
	"Verbandsnachrichten" => "verbandsnachrichten/"
),

"Ligen" => array (
	"goto/nsvligen",
	(bool) strstr ( $_SERVER ['PHP_SELF'], "/verband/Ligenuebersicht.php" ) ||  (bool) strstr ( $_SERVER ['PHP_SELF'], "/ligen/" ),
	"&Uuml;bersicht" => "alle-ligen/",
	"Landesliga Nord" => "goto/lln",
	"Landesliga S&uuml;d" => "goto/lls",
	"Verbandsliga Nord" => "goto/vln",
	"Verbandsliga West" => "goto/vlw",
	"Verbandsliga Ost" => "goto/vlo",
	"Verbandsliga S&uuml;d" => "goto/vls",

	"Pokal-Meisterschaft" => "ligen/pokal/",
	"Frauen Landesliga" => "ligen/fll/",
	"Jugendligen" => "ligen/nsj/"
),

"Vorstand" => array (
	"vorstand/",
	(bool) strstr ( $_SERVER ['REQUEST_URI'], "/vorstand/" )
),

"Vereine" => array (
	"vereine/",
	(bool) strstr ( $_SERVER ['REQUEST_URI'], "/vereine/" )
),

"Termine" => array (
	"termine/",
	(bool) strstr ( $_SERVER ['PHP_SELF'], "/termine/" )
),

"Ausbildung" => array (
	"kategorie/ausbildung/",
	false
),

"Frauen" => array (
	"kategorie/frauenschach/",
	false
),

"Jugend" => array (
	"goto/jugend",
	false
),

"Senioren" => array (
	"goto/Senioren",
	false
)
);
?>

