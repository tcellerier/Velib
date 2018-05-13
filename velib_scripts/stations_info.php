<?php
$start_time = microtime(true); 

/*
   Script de rafraichissement des infos de stations
   + nettoyage des données > 1 an

   A exécuter régulièrement. ex : 1 fois par jour 
*/


// Appel à l'API Velib
$api_call = file_get_contents("https://www.velib-metropole.fr/webapi/map/details?gpsTopLatitude=50&gpsTopLongitude=5&gpsBotLatitude=40&gpsBotLongitude=2&zoomLevel=15");


// Décodage JSON
$obj = json_decode($api_call);


// Connexion à la base de données
require_once '/home/pi/www/velib/mysql_login.php';
$conn = new mysqli($hn, $un, $pw, $db);
if ($conn->connect_error) die($conn->connect_error);

$i_ko = 0;
$i_cree = 0;
$i_maj = 0;

// Pour chaque station du JSON
foreach($obj as $obj_i)
{

	$station = $obj_i->{'station'};
	$gps = $station->{'gps'};

	$query_check = "SELECT count(*) as nb_check from stations_info where code=\"".$station->{'code'}."\"";
	$result_check = $conn->query($query_check);
	if (!$result_check)
	{
		echo "Database access failed for station ". $station->{'code'}. " : " . $conn->error ."\n";
		$i_ko++;
	}
	else
	{

		// Si la station existe déjà en base, on la met à jour
		if ($result_check->fetch_assoc()['nb_check'] > 0)
		{
			$query = "UPDATE stations_info SET name=\"".$station->{'code'}." - ".$station->{'name'}."\", gps_lat=".$gps->{'latitude'}.", gps_lng=".$gps->{'longitude'}.", nbDock=".$obj_i->{'nbDock'}.", nbEDock=".$obj_i->{'nbEDock'}.", state=\"". $station->{'state'} ."\"  WHERE code=\"".$station->{'code'}."\"";
			$result = $conn->query($query);
			
			// Si erreur connexion
			if (!$result) 
			{
				echo "Database update failed for station ". $station->{'code'}. " : " . $conn->error . "\n";
				$i_ko++;
			}

			// Si connexion OK
			else
			{
				echo "Update station ". $station->{'code'}."\n";
				$i_maj++;
			}
		}

		// Si la station n'est pas en base, on la crée
		else
		{
			$query = "INSERT INTO stations_info VALUES (\"".$station->{'code'}."\", ".$gps->{'latitude'}.", ".$gps->{'longitude'}.", \"". $station->{'state'} ."\", \"".$station->{'code'}." - ".$station->{'name'}."\", \"\", ".$obj_i->{'nbDock'}.", ".$obj_i->{'nbEDock'}.")";
			$result = $conn->query($query);
			
			// Si erreur connexion
			if (!$result) 
			{
				echo "Database update failed for station ". $station->{'code'}. " : " . $conn->error . "\n";
				$i_ko++;
			}

			// Si connexion OK
			else
			{
				echo "Create station ". $station->{'code'}. "\n ";
				$i_cree++;
			}

		}

	}

}

echo "\n". $i_cree ." stations créées\n";
echo $i_maj ." stations mises à jour\n";
echo $i_ko ." stations en erreur\n\n";



// Nettoyage des données > 13 mois pour chaque station
//    Tests le 27/01/2016 : 614s en faisant un DELETE station par station, 372s en faisant un DELETE global
//                          Index sur le champ last_update couteux en espace disque, environ +50%
//    Test le 14/7/2016 : echec au bout de 20 min en faisant un DELETE global 
$query = "SELECT code from stations_info where 1 ORDER BY 1";
$result = $conn->query($query);
if (!$result) die($conn->error);

$rows = $result->num_rows;
for ($j = 0 ; $j < $rows ; ++$j)
{
 	$result->data_seek($j);
 	$row = $result->fetch_array(MYSQLI_ASSOC);

  	$query_suppr = "DELETE from stations_usage where code=\"".$row['code']."\" and date(last_update) <= CURRENT_DATE - INTERVAL 13 MONTH";
	$result_suppr = $conn->query($query_suppr);
	if (!$result_suppr)
		echo "Station ".$row['code']." - Database access failed to clean data > 13 month old\n";
	else
		echo "Station ".$row['code']." - Data > 13 month old cleaned\n";
}




$end_time = microtime(true);
$creationtime = ($end_time - $start_time);
printf("Script executed in %.4f seconds \n", $creationtime);

?>