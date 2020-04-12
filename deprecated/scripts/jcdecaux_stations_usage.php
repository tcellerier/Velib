<?php

/*
   Script de rafraichissement de l'utilisation des stations (velib disponibles / places disponibles)
   A exécuter toutes les x minutes 
*/

// Appel à l'API Velib
$api_call = file_get_contents("https://api.jcdecaux.com/vls/v1/stations?contract=Paris&apiKey=XXXXXXX");


// Décodage JSON
$obj = json_decode($api_call);


require_once '/volume1/web/mysql_login.php';
$conn = new mysqli($hn, $un, $pw, $db);
if ($conn->connect_error) die($conn->connect_error);

$i_ok = 0;
$i_ko = 0;

foreach($obj as $station)
{

	// Last update aroundi à la minute inférieur
	$query = "INSERT INTO stations_usage VALUES (". $station->{'number'}. ", \"".date("Y-m-d H:i:s", floor($station->{'last_update'} / (1000 * 60)) * 60)."\", ".$station->{'available_bike_stands'}.", ".$station->{'available_bikes'}.")";
	$result = $conn->query($query);
	if (!$result) 
	{
		echo "Database access failed for station ". $station->{'number'}. " : " . $conn->error. " \n";
		$i_ko++;
	}
	else
		$i_ok++;
}

echo "\nNouvelles données pour " . $i_ok ." stations \n";
echo "Données non mises à jour pour " . $i_ko ." stations ('last_update' arrondi à la minute déjà existant)\n";

?>
