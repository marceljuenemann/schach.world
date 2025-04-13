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

"Turniere" => array (
	"nsv-grandprix/",
	(bool) strstr ( $_SERVER ['PHP_SELF'], "/nsv-grandprix/" ),
	"NSV-Grandprix" => "/nsv-grandprix/",
	"100 Jahre NSV" => "nsv-jubilaeum-100-jahre-nsv/",
	"LEM 2025" => "2025/01/lem-2025-abschlussbericht/",
	"Schnellschacheinzelm. 2025" => "2025/02/nsv-schnellschach-einzelmeisterschaft-anderter-open-2025-ausschreibung/",
	"Pokalmannschaftsm. 2024/25" => "2025/02/pokalmannschaftsmeisterschaft-2024-25-ausschreibung/",
	"Frauenmeisterschaften 2025" => "2025/02/frauenschachpower-pur/",
	"Frauenblitzschach 2025" => "2025/01/niedersaechsische-frauenblitzeinzelmeisterschaften-2025-ausschreibung/",
	"Frauenschnellschach 2025" => "2025/01/niedersaechsische-frauenschnellschachmeisterschaft-2025-ausschreibung/",
	"Probleml&ouml;semeisterschaft 2025" => "2024/12/offene-niedersaechsische-problemloesemeisterschaft-ausschreibung-4/",
),

"Referate" => array (
	"vorstand/",
	(bool) strstr ( $_SERVER ['PHP_SELF'], "/vorstand/" ),
	"Spielgeschehen" => "kategorie/spielgeschehen/",
	"Ausbildung" => "kategorie/ausbildung/",
	"Leistungssport" => "kategorie/leistungssport/",
	"Frauen" => "kategorie/frauenschach/",
	"Problemschach" => "kategorie/problemschach/",
	"Jugend" => "goto/jugend",
	"Senioren" => "goto/Senioren",
),


);
?>

