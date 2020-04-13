<?php
$start_time = microtime(true); 

/*
   Script de rafraichissement des infos de stations
   + nettoyage des données > 1 an

   A exécuter régulièrement. ex : 1 fois par jour 
*/



// ----  1/2 ----- Appel à l'API Velib OPENDATA 
$api_call = file_get_contents("https://velib-metropole-opendata.smoove.pro/opendata/Velib_Metropole/station_information.json");

// Décodage JSON
$obj = json_decode($api_call);

// Connexion à la base de données
require_once '/home/pi/www/velib/mysql_login.php';
$conn = new mysqli($hn, $un, $pw, $db);
if ($conn->connect_error) die($conn->connect_error);

$i_ko1 = 0; $i_cree1 = 0; $i_maj1 = 0;

// Pour chaque station du JSON
foreach($obj->{'data'}->{'stations'} as $obj_i)
{

	$stationcode = $obj_i->{'stationCode'};
	$capacity = $obj_i->{'capacity'};
	$name = $obj_i->{'name'};
	$lat = $obj_i->{'lat'};
	$lng = $obj_i->{'lon'};

	$query_check = "SELECT count(*) as nb_check from stations_info where stationcode=\"".$stationcode."\"";
	$result_check = $conn->query($query_check);
	if (!$result_check)
	{
		echo "Step 1/2 - Database access failed for station ".$stationcode. " : " . $conn->error ."\n";
		$i_ko1++;
	}
	else
	{

		// Si la station existe déjà en base, on la met à jour
		if ($result_check->fetch_assoc()['nb_check'] > 0)
		{
			$query = "UPDATE stations_info SET name=\"".$stationcode." - ".$name."\", gps_lat=".$lat.", gps_lng=".$lng.", capacity=".$capacity." WHERE stationcode=\"".$stationcode."\"";
			$result = $conn->query($query);
			
			// Si erreur connexion
			if (!$result) {
				$i_ko1++;
				echo "Step 1/2 - Failure ".$i_ko1." - Database update failed for station ". $stationcode. " : " . $conn->error . "\n";
			}

			// Si connexion OK
			else {
				$i_maj1++;
				echo "Step 1/2 - Update ".$i_maj1." - Station ". $stationcode."\n";
			}
		}

		// Si la station n'est pas en base, on la crée
		else {
			$query = "INSERT INTO stations_info (stationcode, name, capacity, gps_lat, gps_lng) VALUES (\"".$stationcode."\",  \"".$stationcode." - ".$name."\", ".$capacity.", ".$lat.", ".$lng.")";
			$result = $conn->query($query);
			
			// Si erreur connexion
			if (!$result)  {
				$i_ko1++;
				echo "Step 1/2 - Failure ".$i_ko1." - Database update failed for station ". $stationcode. " : " . $conn->error . "\n";
			}

			// Si connexion OK
			else {
				$i_cree1++;		
				echo "Step 1/2 - Creation ".$i_cree1." - Station ".$i_cree1.": ". $stationcode. "\n ";
			}

		}

	}

}





// ----  2/2 ----- Appel à l'API Velib OPENDATA 
// On récupère : is_installed, is_returning, is_renting, last_reported

$api_call = file_get_contents("https://velib-metropole-opendata.smoove.pro/opendata/Velib_Metropole/station_status.json");
echo "\n";

// Décodage JSON
$obj = json_decode($api_call);

// Connexion à la base de données
require_once '/home/pi/www/velib/mysql_login.php';
$conn = new mysqli($hn, $un, $pw, $db);
if ($conn->connect_error) die($conn->connect_error);

$i_ko2 = 0; $i_cree2 = 0; $i_maj2 = 0;

// Pour chaque station du JSON
foreach($obj->{'data'}->{'stations'}  as $obj_i)
{

	$last_reported = $obj_i->{'last_reported'};
	$stationcode = $obj_i->{'stationCode'};
	$is_installed = $obj_i->{'is_installed'}; // is_installed: variable binaire indiquant si la station est. La station a déjà été déployée (1) ou est encore en cours de déploiement (0) 
	$is_returning = $obj_i->{'is_returning'}; // is_returning: variable binaire indiquant si la station peut recevoir des vélos (is_renting=1 si le statut de la station est Operative)
	$is_renting = $obj_i->{'is_renting'}; // is_renting: variable binaire indiquant si la station peut louer des vélos (is_renting=1 si le statut de la station est Operative)
	$last_repoted_datetime = date('Y-m-d H:i:s', $last_reported);


	$query_check = "SELECT count(*) as nb_check from stations_info where stationcode=\"".$stationcode."\"";
	$result_check = $conn->query($query_check);
	if (!$result_check)
	{
		echo "Step 2/2 - Database access failed for station ".$stationcode. " : " . $conn->error ."\n";
		$i_ko2++;
	}
	else {

		// Si la station existe déjà en base, on la met à jour
		if ($result_check->fetch_assoc()['nb_check'] > 0) {
			$query = "UPDATE stations_info SET is_installed=".$is_installed.", is_returning=".$is_returning.", is_renting=".$is_renting.", last_reported=\"".$last_repoted_datetime."\" WHERE stationcode=\"".$stationcode."\"";
			$result = $conn->query($query);
			
			// Si erreur connexion
			if (!$result) {
				$i_ko2++;
				echo "Step 2/2 - Failure ".$i_ko2." - Database update failed for station ". $stationcode. " : " . $conn->error . "\n";
			}

			// Si connexion OK
			else {
				echo "Step 2/2 - Update ".$i_maj2." - Station ". $stationcode."\n";
				$i_maj2++;
			}
		}

		// Si la station n'est pas en base, on la crée
		else {
			$query = "INSERT INTO stations_info (stationcode, is_installed, is_returning, is_renting, last_reported) VALUES (\"".$stationcode."\", ".$is_installed.", ".$is_returning.", ".$is_renting.", \"".$last_repoted_datetime."\")";
			$result = $conn->query($query);
			
			// Si erreur connexion
			if (!$result) {
				$i_ko2++;
				echo "Step 2/2 - Failure ".$i_ko2." - Database update failed for station ". $stationcode. " : " . $conn->error . "\n";
			}

			// Si connexion OK
			else {
				$i_cree2++;
				echo "Step 2/2 - Creation ".$i_cree2." - Station ". $stationcode. "\n ";
			}

		}

	}

}


echo "\nEtape 1/2: \n". $i_cree1 ." stations créées\n";
echo $i_maj1 ." stations mises à jour\n";
echo $i_ko1 ." stations en erreur\n";


echo "\nEtape 2/2: \n". $i_cree2 ." stations créées\n";
echo $i_maj2 ." stations mises à jour\n";
echo $i_ko2 ." stations en erreur\n\n";


// Nettoyage des données > 13 mois pour chaque station
//    Tests le 27/01/2016 sur RPI2B : 614s en faisant un DELETE station par station, 372s en faisant un DELETE global
//                          Index sur le champ last_update couteux en espace disque, environ +50%
//    Test le 14/7/2016 : echec au bout de 20 min en faisant un DELETE global 

$dayweek = date('w');
$query = "SELECT stationcode from stations_info ORDER BY 1";
$result = $conn->query($query);
if (!$result) die($conn->error);

$rows = $result->num_rows;
for ($j = 0 ; $j < $rows ; ++$j)
{

 	$result->data_seek($j);
 	$row = $result->fetch_array(MYSQLI_ASSOC);

 	// Cleaning d'1/7 chaque jour de la semaine. Durée d'environ 5min avec RPI3 le 13/4/2020
 	if ($j % 7 == $dayweek) {
	  	$query_suppr = "DELETE from stations_usage where stationcode=\"".$row['stationcode']."\" and date(last_update) <= CURRENT_DATE - INTERVAL 13 MONTH";
		$result_suppr = $conn->query($query_suppr);
		if (!$result_suppr)
			echo "".$j." - Station ".$row['stationcode']." - Database access failed to clean data > 13 month old\n";
		else
			echo "".$j." - Station ".$row['stationcode']." - Data > 13 month old cleaned\n";
	}
	else {
		echo "".$j." - Station ".$row['stationcode']." - Skipped for today\n";
	} 

}




$end_time = microtime(true);
$creationtime = ($end_time - $start_time);
printf("Script executed in %.4f seconds \n", $creationtime);

?>
