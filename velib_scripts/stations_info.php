<?php
$start_time = microtime(true); 

/*
   Script de rafraichissement des infos de stations
   + nettoyage des données > 1 an

   A exécuter régulièrement. ex : 1 fois par jour 
*/


// Appel à l'API Velib
$api_call = file_get_contents("https://api.jcdecaux.com/vls/v1/stations?contract=Paris&apiKey=3496355ea83da762fa3cae313a27882b5b062bd7");


// Décodage JSON
$obj = json_decode($api_call);


// Connexion à la base de données
require_once '/volume1/web/mysql_login.php';
$conn = new mysqli($hn, $un, $pw, $db);
if ($conn->connect_error) die($conn->connect_error);

$i_ko = 0;
$i_cree = 0;
$i_maj = 0;

// Pour chaque station du JSON
foreach($obj as $station)
{

	$query_check = "SELECT count(*) as nb_check from stations_info where number=".$station->{'number'}." and contract_name = \"".$station->{'contract_name'}."\"";
	$result_check = $conn->query($query_check);
	if (!$result_check)
	{
		echo "Database access failed for station ". $station->{'number'}. " : " . $conn->error ."\n";
		$i_ko++;
	}
	else
	{

		$pos = $station->{'position'};

		// Si la station existe déjà en base, on la met à jour
		if ($result_check->fetch_assoc()['nb_check'] > 0)
		{
			$query = "UPDATE stations_info SET name=\"".$station->{'name'}."\", address=\"".$station->{'address'}."\", position_lat=".$pos->{'lat'}.", position_lng=".$pos->{'lng'}.", banking=\"".$station->{'banking'}."\", bonus=\"".$station->{'bonus'}."\", bike_stands=".$station->{'bike_stands'}.",  status=\"". $station->{'status'} ."\"  WHERE number=".$station->{'number'}." and contract_name = \"".$station->{'contract_name'}."\"";
			$result = $conn->query($query);
			
			// Si erreur connexion
			if (!$result) 
			{
				echo "Database update failed for station ". $station->{'number'}. " : " . $conn->error . "\n";
				$i_ko++;
			}

			// Si connexion OK
			else
			{
				echo "Update station ". $station->{'number'}."\n";
				$i_maj++;
			}
		}

		// Si la station n'est pas en base, on la crée
		else
		{
			$query = "INSERT INTO stations_info VALUES (".$station->{'number'}.", \"".$station->{'contract_name'}."\", \"".$station->{'name'}."\", \"".$station->{'address'}."\", ".$pos->{'lat'}.", ".$pos->{'lng'}.", \"".$station->{'banking'}."\", \"".$station->{'bonus'}."\", ".$station->{'bike_stands'}.", \"". $station->{'status'} ."\")";
			$result = $conn->query($query);
			
			// Si erreur connexion
			if (!$result) 
			{
				echo "Database update failed for station ". $station->{'number'}. " : " . $conn->error . "\n";
				$i_ko++;
			}

			// Si connexion OK
			else
			{
				echo "Create station ". $station->{'number'}. "\n ";
				$i_cree++;
			}

		}

	}

}

echo "\n". $i_cree ." stations créées\n";
echo $i_maj ." stations mises à jour\n";
echo $i_ko ." stations en erreur\n\n";



// Nettoyage des données > 1 an pour chaque station
//    Tests le 27/01/2016 : 614s en faisant un DELETE station par station, 372s en faisant un DELETE global
//                          Index sur le champ last_update couteux en espace disque, environ +50%
//    Test le 14/7/2016 : echec au bout de 20 min en faisant un DELETE global 
$query = "SELECT number from stations_info where contract_name=\"Paris\" ORDER BY 1";
$result = $conn->query($query);
if (!$result) die($conn->error);

$rows = $result->num_rows;
for ($j = 0 ; $j < $rows ; ++$j)
{
 	$result->data_seek($j);
 	$row = $result->fetch_array(MYSQLI_ASSOC);

  	$query_suppr = "DELETE from stations_usage where number=".$row['number']." and date(last_update) <= CURRENT_DATE - INTERVAL 1 YEAR";
	$result_suppr = $conn->query($query_suppr);
	if (!$result_suppr)
		echo "Station ".$row['number']." - Database access failed to clean data > 1 year old\n";
	else
		echo "Station ".$row['number']." - Data > 1 year old cleaned\n";
}




$end_time = microtime(true);
$creationtime = ($end_time - $start_time);
printf("Script executed in %.4f seconds \n", $creationtime);

?>