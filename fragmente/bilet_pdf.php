<?php 

//require('../FPDF/makefont/makefont.php');

//MakeFont('E:\\matematica\\ID 2023\\Anul 2\\Sem 1\\DAW - Dezvoltarea aplicatiilor web\\Proiect\\FPDF\\blinkmacsystemfont-medium.ttf','cp1252');
 //exit;

require('../FPDF/fpdf.php');
$dim =22;
$pdf = new FPDF('P','mm','A4');
$pdf->AddPage();
$pdf->AddFont('Limelight-Regular','','Limelight-Regular.php');
$pdf->AddFont('blinkmacsystemfont-bold','','blinkmacsystemfont-bold.php');
$pdf->AddFont('blinkmacsystemfont-medium','','blinkmacsystemfont-medium.php');

//-------------------------------- logo si firma
$pdf->SetFont('Limelight-Regular','',27);

$pdf->SetXY(55,25); 
$pdf->Cell(100,10,'Teatrul de opera',0,0,'C');

$pdf->SetFont('Limelight-Regular','',10);
$pdf->SetXY(55,$pdf->GetY()+10); 
$pdf->Cell(100,6,'Un teatru de opera imaginar.',0,0,'C');

$pdf->Image('imagini/cantaret128.png', 33, 22, $dim ,$dim );
$pdf->Image('imagini/cantareata128.png', 155, 22, $dim ,$dim );

$pdf->SetLineWidth(1);
$ton = 150;
$pdf->SetDrawColor($ton,$ton,$ton);
$pdf->Line(35,47,176,47);
$pdf->SetLineWidth(1);

$pdf->SetLineWidth(.1);
$linie=0;
$ton = 240;
$x=35;
$pdf->SetFont('blinkmacsystemfont-medium','',20);
$pdf->SetXY(35,$pdf->GetY()+20);
$pdf->Cell(140,6,'Bilet de spectacol',$linie,1,'C');

$pdf->SetFont('blinkmacsystemfont-bold','',12);
$pdf->SetXY($x,$pdf->GetY()+10);
$data_spect= strtotime($row['data_spectacol']);
$data_txt = data_romaneasca(date('d',$data_spect), date('m',$data_spect), date('Y',$data_spect));
$pdf->Cell(140,6,$data_txt,$linie);

$pdf->SetFont('blinkmacsystemfont-bold','',18);
$pdf->SetXY($x,$pdf->GetY()+7);
$pdf->MultiCell(140,6,$row['titlu'],$linie,1);

$pdf->SetFont('blinkmacsystemfont-bold','',14);
$y = $pdf->GetY()+3;
$pdf->SetXY($x,$y);
$pdf->Cell(20,6,'Locuri:',$linie);

$locuri = $row['lista_locuri'];
$locuri = str_replace(',',', ', $locuri );

$pdf->SetFont('blinkmacsystemfont-medium','',12);
$pdf->SetXY(52,$y);
$pdf->MultiCell(123,6,$locuri,$linie);

$y = $pdf->GetY()+15;
$pdf->SetFont('blinkmacsystemfont-medium','',20);
$pdf->SetXY(35,$y);
$pdf->Cell(140,6,'SCENA',$linie,1,'C');

//--------------------------------------print locuri sala
$y = $pdf->GetY()+3;
$pdf->SetFont('blinkmacsystemfont-bold','',12);
$x = 35 + (( 140 - 120 ) / 2); 
$pdf->SetXY($x,$y);
$vect = explode (', ',$locuri);
$x=$pdf->GetX(); 
$y=$pdf->GetY();
for ($i = 1 ; $i <= 100 ; $i++) {
	if (in_array($i, $vect)) {
		$pdf->SetFillColor(1,1,1);
		$pdf->SetTextColor(250,250,250);
	} else { 
		$pdf->SetFillColor($ton,$ton,$ton); 
		$pdf->SetTextColor($ton+50,$ton+50,$ton+50);
	}
	
	if ($i % 10 == 1 && $i >1) {
		$y += 8;
		$x -= 109;
	} elseif ($i % 10 != 1) { 
		$x += 11 + ($i % 10 == 4 || $i % 10 == 8 ? 5 : 0) ;
		}
	$pdf->SetXY($x,$y);
	$pdf->Cell(10,7,$i ,$linie,'','C',1);
}

$pdf->SetTextColor(0, 0, 0);
$pdf->SetXY(70,$pdf->GetPageHeight()-50);
$pdf->SetFont('blinkmacsystemfont-medium','',14);
$pdf->Cell(25,6,'Cod bilet:',$linie);

$cod = str_pad($row['id_rezervare'],10,'0', STR_PAD_LEFT).str_pad($row['cod_control'],4,'0', STR_PAD_LEFT);
$pdf->SetFont('Courier','',14);
$pdf->Cell(60,6,$cod ,$linie);


$pdf->SetLineWidth(1);
$ton = 150;
$pdf->SetDrawColor($ton,$ton,$ton);
$pdf->Line(35,$pdf->GetPageHeight()-33,176,$pdf->GetPageHeight()-33);

$pdf->SetFont('Limelight-Regular','',10);
$pdf->SetXY(35,$pdf->GetPageHeight()-30);
$pdf->Cell(140,6,'Teatrul de opera',0,0,'C');



$pdf->Output('I', 'Teatrul de opera - '.$row['titlu'].' - '.$data_txt.'.pdf');

?>
