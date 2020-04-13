<?php
$start_time = microtime(true); 

/*
   Script de rafraichissement de l'utilisation des stations (velib disponibles / places disponibles)
   A exécuter toutes les x minutes 
*/

$timestamp_now = date("Y-m-d H:i:00");

// Appel à l'API Velib
$api_call = file_get_contents("https://velib-metropole-opendata.smoove.pro/opendata/Velib_Metropole/station_status.json");

// Décodage JSON
$obj = json_decode($api_call);

require_once '/home/pi/www/velib/mysql_login.php';
$conn = new mysqli($hn, $un, $pw, $db);
if ($conn->connect_error) die($conn->connect_error);

$i_ok = 0;
$i_ko = 0;

foreach($obj->{'data'}->{'stations'}  as $obj_i)
{

	//print_r($obj_i);
	$stationcode = $obj_i->{'stationCode'};
	$num_docks_available = $obj_i->{'numDocksAvailable'};
	$num_bikes_mechanical = $obj_i->{'num_bikes_available_types'}[0]->{'mechanical'};
	$num_bikes_ebike = $obj_i->{'num_bikes_available_types'}[1]->{'ebike'};

	// Last update aroundi à la minute inférieur
	$query = "INSERT INTO stations_usage(stationcode, last_update, num_bikes_mechanical, num_bikes_ebike, num_docks_available) VALUES (\"".$stationcode."\", \"".$timestamp_now."\", ".$num_bikes_mechanical.", ".$num_bikes_ebike.", ".$num_docks_available.")";
	//echo $query;
	$result = $conn->query($query);
	if (!$result) 
	{
		echo "Database access failed for station ". $stationcode. " : " . $conn->error. " \n";
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
