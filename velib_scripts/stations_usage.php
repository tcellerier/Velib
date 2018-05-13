<?php
$start_time = microtime(true); 

/*
   Script de rafraichissement de l'utilisation des stations (velib disponibles / places disponibles)
   A exécuter toutes les x minutes 
*/

$timestamp_now = date("Y-m-d H:i:00");

// Appel à l'API Velib
$api_call = file_get_contents("https://www.velib-metropole.fr/webapi/map/details?gpsTopLatitude=50&gpsTopLongitude=5&gpsBotLatitude=40&gpsBotLongitude=2&zoomLevel=15");


// Décodage JSON
$obj = json_decode($api_call);


require_once '/home/pi/www/velib/mysql_login.php';
$conn = new mysqli($hn, $un, $pw, $db);
if ($conn->connect_error) die($conn->connect_error);

$i_ok = 0;
$i_ko = 0;

foreach($obj as $obj_i)
{

	$station = $obj_i->{'station'};
	$gps = $station->{'gps'};

	$nb_velib = $obj_i->{'nbBike'}; // + $obj_i->{'nbBikeOverflow'}
	$nb_velib_electrique = $obj_i->{'nbEbike'}; // + $obj_i->{'nbEBikeOverflow'} 
	$nb_place_dispo = $obj_i->{'nbFreeDock'} + $obj_i->{'nbFreeEDock'};

	// Last update aroundi à la minute inférieur
	$query = "INSERT INTO stations_usage VALUES (\"".$station->{'code'}."\", \"".$timestamp_now."\", ".$nb_velib.", ".$nb_velib_electrique.", ".$nb_place_dispo.")";
	//echo $query;
	$result = $conn->query($query);
	if (!$result) 
	{
		echo "Database access failed for station ". $station->{'code'}. " : " . $conn->error. " \n";
		$i_ko++;
	}
	else
		$i_ok++;
}

echo "\nNouvelles données pour " . $i_ok ." stations \n";
echo "Données non mises à jour pour " . $i_ko ." stations\n";

$end_time = microtime(true);
$creationtime = ($end_time - $start_time);
printf("Script executed in %.4f seconds \n", $creationtime);
?>