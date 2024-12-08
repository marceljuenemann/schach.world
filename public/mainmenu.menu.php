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
	"Einladungsturnier 2024" => "/einladungsturnier/",
	"Jubil&auml;umsgrandprix 2024" => "2024/11/nsv-jubilaeumsgrandprix-im-egor-bogdanov-ulf-stoy-sowie-jannes-haverlandt-gewinnen-ihre-gruppen/",
	"D&auml;hne-Pokal 2024" => "2024/11/daehne-pokal-sebastian-mueer-gewinnt-finale/",
	"Bulletmeisterschaft 2024" => "2024/11/2-offene-niedersaechsische-bulletmeisterschaft-in-kooperation-mit-der-csa/",
	"Blitzeinzelmeisterschaft 2024" => "2024/10/ausschreibung-landesblitzeinzelmeisterschaft-2024/",
	"Blitzmannschaftsmeisterschaft 2024" => "2024/10/landesblitzmannschaftsmeisterschaft-2024-ausschreibung/",
	"Hochschulmeisterschaft 2024" => "2024/10/3-offene-niedersaechsische-hochschulmeisterschaft-2024-ausschreibung/",
	"LEM 2025" => "/2024/09/lem-2025-ausschreibung-und-anmeldungen/",
),

"Referate" => array (
	"vorstand/",
	(bool) strstr ( $_SERVER ['PHP_SELF'], "/vorstand/" ),
	"Spielgeschehen" => "kategorie/spielgeschehen/",
	"Ausbildung" => "kategorie/ausbildung/",
	"Frauen" => "kategorie/frauenschach/",
	"Problemschach" => "kategorie/problemschach/",
	"Jugend" => "goto/jugend",
	"Senioren" => "goto/Senioren",
),

"100-Jahre NSV" => array (
	"nsv-jubilaeum-100-jahre-nsv/",
	(bool) strstr ( $_SERVER ['PHP_SELF'], "/nsv-jubilaeum-100-jahre-nsv/" ),
),


);
?>

