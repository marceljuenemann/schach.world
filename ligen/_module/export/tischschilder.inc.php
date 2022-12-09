<?
if (!isset($_GET['mid'])) {
  include('tischschilder_selection.inc.php');
  exit;
}

require_once('../../libs/tcpdf/config/lang/ger.php');
require_once('../../libs/tcpdf/tcpdf.php');

// create new PDF document
$pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

// remove default header/footer
$pdf->setPrintHeader(false);
$pdf->setPrintFooter(false);

// set default monospaced font
$pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

//set margins
$pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);

//set auto page breaks
$pdf->SetAutoPageBreak(FALSE);

//set image scale factor
$pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

//set some language-dependent strings
$pdf->setLanguageArray($l);

//////////////////////////

$teamName = reset(mysql_fetch_array(mysql_query("select d.Vereinname from dwz_vereine d join mannschaften m ON m.zps=d.ZPS where m.id='$_GET[mid]'")));
$rsrc = mysql_query("select zps from spieler where mannschaft = '$_GET[mid]' order by brettnr");
$players = array();
while ($row = mysql_fetch_array($rsrc)) {
	$players[] = $row['zps'];
}

/*
 header('Content-type: text/plain');
 print_r($teamname);
 print_r($players);
 exit();
*/

///////////////////////////////

$playerData = array();

foreach ($players as $player) {
	$tmp = explode('-', $player);
	count($tmp) == 2 or die("invalid zps ".$player);
	$zps = $tmp[0];
	$mglNr = $tmp[1];
	$rsrc = mysql_query($sql = "select * from dwz_spieler where ZPS='$zps' AND Mgl_Nr='$mglNr' LIMIT 1");
	echo mysql_error();
	$row = mysql_fetch_array($rsrc);

	$name = implode(' ', array_reverse(explode(',', $row['Spielername'])));
	if ($row['FIDE_Titel']) {
		if (in_array($row['FIDE_Titel'], array('WG', 'WI', 'WF', 'WC'))) {
			$row['FIDE_Titel'] .= 'M';
		}
		$name = $row['FIDE_Titel'] . ' ' . $name;
	}

	$rating = '';
	if ($row['DWZ']) {
		$rating = 'DWZ: ' . $row['DWZ'];
	}
	if ($row['FIDE_Elo']) {
		$rating .= '   ELO: ' . $row['FIDE_Elo'];
	}

	$data = array(
		'zps' => $player,
		'team' => utf8_encode($teamName),
		'name' => utf8_encode($name),
		'rating' => $rating
	);
	$playerData[] = $data;
}


/*
 header('Content-type: text/plain');
 print_r($playerData);
 exit();
*/

///////////////////////

foreach ($playerData as $data) {

	// add a page
	$pdf->AddPage();


	$h = 297;
	$y = ($h-2*15)/4.0*3;
	$pdf->Image('../_module/export/tischschilder-media/lehrte.png', 15, $y, 210-2*15, $h-20-$y, 'PNG', '', '', true, 150, '', false, false, 0, false, false, false);

	$txt = $_GET['titel'] ? $_GET['titel'] : 'Frauen-Bundesliga in Lehrte';
	$pdf->SetFont('helvetica', 'B', 14);
	$pdf->SetXY(60, $y + 9);
	$pdf->Cell(70, 0, $txt, 0, 1, 'C', 0, '', 0);

	$txt = $_GET['datum'] or '30.11. bis 01.12.2013';
	$pdf->SetFont('helvetica', '', 14);
	$pdf->SetXY(60, $y + 15);
	$pdf->Cell(70, 0, $txt, 0, 1, 'C', 0, '', 0);

	$txt = $data['name'];
	$pdf->SetFont('helvetica', 'B', 20);
	$pdf->SetXY(33, $y + 34);
	$pdf->Cell(144, 0, $txt, 0, 1, 'C', 0, '', 1);

	$txt = $data['team'];
	$pdf->SetFont('helvetica', 'B', 18);
	$pdf->SetXY(40, $y + 48);
	$pdf->Cell(130, 0, $txt, 0, 1, 'C', 0, '', 1);

	$txt = $data['rating'];
	$pdf->SetFont('helvetica', '', 16);
	$pdf->SetXY(15, $y + 57);
	$pdf->Cell(180, 0, $txt, 0, 1, 'C', 0, '', 0);
}


// ---------------------------------------------------------

//Close and output PDF document
$pdf->Output('tischschilder.pdf', 'I');
