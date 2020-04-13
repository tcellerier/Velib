<?php $start_time = microtime(true); 

require_once 'mysql_login.php';
$conn = new mysqli($hn, $un, $pw, $db);
if ($conn->connect_error) die($conn->connect_error);

/******** Lecture du formulaire ********/
if (isset($_POST['type'])) $type = $_POST['type'];
else                       $type = "available_bikes";

if (isset($_POST['stationcode'])) { 
  $stationcode = $_POST['stationcode'];
}
elseif (isset($_GET['stationcode'])) {
  $stationcode = $_GET['stationcode'];
}
else {
  $stationcode = "18045";
}

if ($type ==  "available_bikes")
  $type_field = "num_bikes_mechanical+num_bikes_ebike";
else
  $type_field = "num_docks_available";

$query = "SELECT name, capacity, is_installed, is_returning, is_renting  from stations_info where stationcode=\"".$stationcode."\" LIMIT 1";
$result = $conn->query($query);
$row = $result->fetch_array(MYSQLI_ASSOC);
extract($row);

if ($is_installed == 1 or $is_returning == 1 or $is_renting == 1) {
  if ($is_installed == 1) $station_info = "Station déployée, ";
  else if ($is_installed == 0)  $station_info = "Station en cours de déploiement, ";

  if ($is_returning == 1) $station_info = $station_info."peut recevoir des vélos ";
  else if ($is_returning == 0) $station_info = $station_info."ne peut pas recevoir des vélos ";

  if ($is_renting == 1)  $station_info = $station_info."et peut louer des vélos";
  else if ($is_renting == 0) $station_info = $station_info."et ne peut pas louer des vélos";
}
else {
  $station_info = "";  
}




// On check si on doit mettre une valeur par défaut
$default_value = 1;

// Lecture des données lastweekdays
if (isset($_POST['lastweekdays'])) {
  $lastweekdays = $_POST['lastweekdays'];
  $default_value = 0;
}
elseif (isset($_GET['lastweekdays'])) {
  $lastweekdays = $_GET['lastweekdays'];
  $default_value = 0;
}
else {
  $lastweekdays = 0;
}

// Lecture des données lastdays
if (isset($_POST['lastdays'])) {
  $lastdays = $_POST['lastdays'];
  $default_value = 0;
}
elseif (isset($_GET['lastdays'])) {
  $lastdays = $_GET['lastdays'];
  $default_value = 0;
}
else {
  $lastdays = 0;
}


// Basé sur la fonction SQL dayofweek() qui retourne 1 à 7 du dimanche au samedi
if (isset($_POST['day']))  {
  $day = implode(", ", $_POST['day']);
  $default_value = 0;
}
else {
  $day = date('w') + 1;
}

// Mois de selection
if (isset($_POST['yearmonth'])) {
  $yearmonth = $_POST['yearmonth'];
  $yearmonth_sql = "\"".implode("\", \"", $_POST['yearmonth'])."\"";
  $default_value = 0;
}
else if ($lastweekdays == 0 and $lastdays == 0) {
  $yearmonth[0] = date('Y-n');
  $yearmonth_sql = "\"".$yearmonth[0]."\"";
}
else {
  $yearmonth = "";
}


// On définit la valeur par défaut
if ($default_value == 1) {
  $lastdays = 5;
}




/********************************/

?><html>
<head>
  <title>Velib Paris - by Thomas</title>
  <style type="text/css">
    table.center {
      margin-left:auto; 
      margin-right:auto;
    }
    td {
      vertical-align:top;
      text-align:center;
    }
    select {
      vertical-align:top;
      text-align:center;
    }
  </style>
</head>
<body style="background-color:white">

<script type="application/javascript" src="jquery.min.js"></script>
<script type="application/javascript" src="highcharts.js"></script>

<div id='container' style='min-width: 600px; height: 600px; margin: 0 auto'></div>

<br>
<form method='post' action='' enctype='multipart/form-data'>
<table class='center'>
  <tr>
    <td>
      <label><input onChange="javascript:submit();" type='radio' name='type' value='available_bikes'<?php if ($type == "available_bikes") echo " checked='checked'"; ?>> Velibs disponibles</label><br>
      <label><input onChange="javascript:submit();" type='radio' name='type' value='available_docks'<?php if ($type == "available_docks") echo " checked='checked'"; ?>> Places disponibles</label>
    </td>
    <td>
      <select name='stationcode' onChange="javascript:submit();">
<?php 

$query = "SELECT name, stationcode from stations_info ORDER BY name";
$result = $conn->query($query);
if (!$result) die($conn->error);

$rows = $result->num_rows;
for ($j = 0 ; $j < $rows ; ++$j)
{
  $result->data_seek($j);
  $row = $result->fetch_array(MYSQLI_ASSOC);

  echo "\t\t\t<option value=" . $row['stationcode'];
  if ($stationcode == $row['stationcode']) echo " selected";
  echo ">". $row['name']."</option>\r\n";
}
         ?>
      </select>
    </td>
    <td><input type='submit' value='Actualiser graphique'></td>
  </tr>
  <tr>
    <td colspan=3>
      
      <table>
        <tr>
          <td>
            <select name='day[]' onChange="this.form.elements['lastweekdays'].value='0';this.form.elements['lastdays'].value='0';javascript:submit()" size='7' multiple='multiple'>
            <option value='2'<?php if (!isset($_POST['day']) and date('w')==1 or $_POST['day'][0]==2 or $_POST['day'][1]==2 or $_POST['day'][2]==2 or $_POST['day'][3]==2 or $_POST['day'][4]==2 or $_POST['day'][5]==2 or $_POST['day'][6]==2) echo " selected"; ?>>Lundi</option>
            <option value='3'<?php if (!isset($_POST['day']) and date('w')==2 or $_POST['day'][0]==3 or $_POST['day'][1]==3 or $_POST['day'][2]==3 or $_POST['day'][3]==3 or $_POST['day'][4]==3 or $_POST['day'][5]==3 or $_POST['day'][6]==3) echo " selected"; ?>>Mardi</option>
            <option value='4'<?php if (!isset($_POST['day']) and date('w')==3 or $_POST['day'][0]==4 or $_POST['day'][1]==4 or $_POST['day'][2]==4 or $_POST['day'][3]==4 or $_POST['day'][4]==4 or $_POST['day'][5]==4 or $_POST['day'][6]==4) echo " selected"; ?>>Mercredi</option>
            <option value='5'<?php if (!isset($_POST['day']) and date('w')==4 or $_POST['day'][0]==5 or $_POST['day'][1]==5 or $_POST['day'][2]==5 or $_POST['day'][3]==5 or $_POST['day'][4]==5 or $_POST['day'][5]==5 or $_POST['day'][6]==5) echo " selected"; ?>>Jeudi</option>
            <option value='6'<?php if (!isset($_POST['day']) and date('w')==5 or $_POST['day'][0]==6 or $_POST['day'][1]==6 or $_POST['day'][2]==6 or $_POST['day'][3]==6 or $_POST['day'][4]==6 or $_POST['day'][5]==6 or $_POST['day'][6]==6) echo " selected"; ?>>Vendredi</option>
            <option value='7'<?php if (!isset($_POST['day']) and date('w')==6 or $_POST['day'][0]==7 or $_POST['day'][1]==7 or $_POST['day'][2]==7 or $_POST['day'][3]==7 or $_POST['day'][4]==7 or $_POST['day'][5]==7 or $_POST['day'][6]==7) echo " selected"; ?>>Samedi</option>
            <option value='1'<?php if (!isset($_POST['day']) and date('w')==0 or $_POST['day'][0]==1 or $_POST['day'][1]==1 or $_POST['day'][2]==1 or $_POST['day'][3]==1 or $_POST['day'][4]==1 or $_POST['day'][5]==1 or $_POST['day'][6]==1) echo " selected"; ?>>Dimanche</option>
            </select>

          </td>
          <td>   
          <select name='yearmonth[]' id="yearmonth" onChange="this.form.elements['lastweekdays'].value='0';this.form.elements['lastdays'].value='0';javascript:submit()" size='6' multiple='multiple'>
          <?php 

          $query = "SELECT distinct concat(YEAR(last_update), '-', MONTH(last_update)) as update_yearmonth, YEAR(last_update) as update_year, MONTH(last_update) as update_month from stations_usage where stationcode=\"".$stationcode."\"  order by 2 desc, 3 desc limit 15";
          $result = $conn->query($query);
          if (!$result) die($conn->error);

          $rows = $result->num_rows;
          for ($j = 0 ; $j < $rows ; ++$j)
          {
            $result->data_seek($j);
            $row = $result->fetch_array(MYSQLI_ASSOC);

            $yearmonth_is_selected = 0;
            foreach ($yearmonth as $yearmonth_key => $yearmonth_value) {
 
              if ($yearmonth_value == $row['update_yearmonth']) {
                $yearmonth_is_selected = 1;
              }
            }

            if ($row['update_month'] == 1) $month_display = "Janvier";
            else if ($row['update_month'] == 2) $month_display = "Février";
            else if ($row['update_month'] == 3) $month_display = "Mars";
            else if ($row['update_month'] == 4) $month_display = "Avril";
            else if ($row['update_month'] == 5) $month_display = "Mail";
            else if ($row['update_month'] == 6) $month_display = "Juin";
            else if ($row['update_month'] == 7) $month_display = "Juillet";
            else if ($row['update_month'] == 8) $month_display = "Août";
            else if ($row['update_month'] == 9) $month_display = "Septembre";
            else if ($row['update_month'] == 10) $month_display = "Octobre";
            else if ($row['update_month'] == 11) $month_display = "Novembre";
            else if ($row['update_month'] == 12) $month_display = "Décembre";

            echo "\t\t\t<option value=" . $row['update_yearmonth'];
            if ($yearmonth_is_selected == 1) echo " selected";
            echo ">".$month_display." ". $row['update_year']."</option>\r\n";
          } 
         ?>

          </select>
          </td>
          <td style="text-align:left">
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;ou&nbsp;&nbsp;&nbsp;&nbsp;
      <select name='lastdays' onChange="this.form.elements['lastweekdays'].value='0';document.getElementById('yearmonth').options.selectedIndex = -1;javascript:submit()" size='1'>
        <option value='0'<?php if ($lastdays==0) echo " selected"; ?>></option>
        <option value='1'<?php if ($lastdays==1) echo " selected"; ?>>1</option>
        <option value='2'<?php if ($lastdays==2) echo " selected"; ?>>2</option>
        <option value='3'<?php if ($lastdays==3) echo " selected"; ?>>3</option>
        <option value='4'<?php if ($lastdays==4) echo " selected"; ?>>4</option>
        <option value='5'<?php if ($lastdays==5) echo " selected"; ?>>5</option>
        <option value='6'<?php if ($lastdays==6) echo " selected"; ?>>6</option>
        <option value='7'<?php if ($lastdays==7) echo " selected"; ?>>7</option>
        <option value='8'<?php if ($lastdays==8) echo " selected"; ?>>8</option>
        <option value='9'<?php if ($lastdays==9) echo " selected"; ?>>9</option>
        <option value='10'<?php if ($lastdays==10) echo " selected"; ?>>10</option>
        <option value='11'<?php if ($lastdays==11) echo " selected"; ?>>11</option>
        <option value='12'<?php if ($lastdays==12) echo " selected"; ?>>12</option>
        <option value='13'<?php if ($lastdays==13) echo " selected"; ?>>13</option>
        <option value='14'<?php if ($lastdays==14) echo " selected"; ?>>14</option>
        <option value='21'<?php if ($lastdays==21) echo " selected"; ?>>21</option>
        <option value='28'<?php if ($lastdays==28) echo " selected"; ?>>28</option>
            </select> derniers jours
            <br><br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;ou&nbsp;&nbsp;&nbsp;&nbsp;
      <select name='lastweekdays' onChange="this.form.elements['lastdays'].value='0';document.getElementById('yearmonth').options.selectedIndex = -1;javascript:submit()" size='1'>
        <option value='0'<?php if ($lastweekdays==0) echo " selected"; ?>></option>
        <option value='1'<?php if ($lastweekdays==1) echo " selected"; ?>>1</option>
        <option value='2'<?php if ($lastweekdays==2) echo " selected"; ?>>2</option>
        <option value='3'<?php if ($lastweekdays==3) echo " selected"; ?>>3</option>
        <option value='4'<?php if ($lastweekdays==4) echo " selected"; ?>>4</option>
        <option value='5'<?php if ($lastweekdays==5) echo " selected"; ?>>5</option>
        <option value='6'<?php if ($lastweekdays==6) echo " selected"; ?>>6</option>
        <option value='7'<?php if ($lastweekdays==7) echo " selected"; ?>>7</option>
        <option value='8'<?php if ($lastweekdays==8) echo " selected"; ?>>8</option>
        <option value='9'<?php if ($lastweekdays==9) echo " selected"; ?>>9</option>
        <option value='10'<?php if ($lastweekdays==10) echo " selected"; ?>>10</option>
        <option value='11'<?php if ($lastweekdays==11) echo " selected"; ?>>11</option>
        <option value='12'<?php if ($lastweekdays==12) echo " selected"; ?>>12</option>
        <option value='13'<?php if ($lastweekdays==13) echo " selected"; ?>>13</option>
        <option value='14'<?php if ($lastweekdays==14) echo " selected"; ?>>14</option>
        <option value='15'<?php if ($lastweekdays==15) echo " selected"; ?>>15</option>
        <option value='16'<?php if ($lastweekdays==16) echo " selected"; ?>>16</option>
        <option value='17'<?php if ($lastweekdays==17) echo " selected"; ?>>17</option>
        <option value='18'<?php if ($lastweekdays==18) echo " selected"; ?>>18</option>
        <option value='19'<?php if ($lastweekdays==19) echo " selected"; ?>>19</option>
        <option value='20'<?php if ($lastweekdays==20) echo " selected"; ?>>20</option>
            </select> derniers mêmes jours de semaine
  
          </td>
        </tr>
      </table>

    </td>

  </tr>

</table>
</form>


<?php

// mode sélection par 'n' derniers jours
if ($lastdays > 0)
  $query = "SELECT DATE(last_update) as update_date, HOUR(last_update) as update_hour, MINUTE(last_update) as update_minute, ".$type_field." as bikes from stations_usage where stationcode=\"".$stationcode."\" and date(last_update) between CURRENT_DATE - INTERVAL ".$lastdays." DAY and CURRENT_DATE - INTERVAL 1 DAY ORDER BY 1 DESC, 2, 3";
// mode sélection par 'n' derniers mêmes jours de semaine (on exlut le dernier jour qu'on affichera en gras après)
else if ($lastweekdays > 0)
  $query = "SELECT DATE(last_update) as update_date, HOUR(last_update) as update_hour, MINUTE(last_update) as update_minute, ".$type_field." as bikes from stations_usage where stationcode=\"".$stationcode."\" and date(last_update) between CURRENT_DATE - INTERVAL ".$lastweekdays." WEEK and CURRENT_DATE - INTERVAL 1 DAY and DAYOFWEEK(last_update) = DAYOFWEEK(CURRENT_DATE) ORDER BY 1 DESC, 2, 3";
// Série des jours dans le mode sélection par jour/mois/année
else
  $query = "SELECT DATE(last_update) as update_date, HOUR(last_update) as update_hour, MINUTE(last_update) as update_minute, ".$type_field." as bikes from stations_usage where stationcode=\"".$stationcode."\" and DAYOFWEEK(last_update) in (".$day.") and CONCAT(YEAR(last_update), '-', MONTH(last_update)) in (".$yearmonth_sql.") ORDER BY 1 DESC, 2, 3";
 


$result = $conn->query($query);
if (!$result) die($conn->error);
$rows = $result->num_rows;
for ($j = 0 ; $j < $rows ; ++$j)
{
  $result->data_seek($j);
  $row = $result->fetch_array(MYSQLI_ASSOC);

  $rows_by_day[$row['update_date']][$j]['update_hour'] = $row['update_hour'];
  $rows_by_day[$row['update_date']][$j]['update_minute'] = $row['update_minute'];
  $rows_by_day[$row['update_date']][$j]['bikes'] = $row['bikes'];
}


// Série du jour pour le mode sélection par 'n' derniers jours de semaine
if ($lastweekdays > 0 OR $lastdays > 0)  
  $query = "SELECT update_hour, update_minute, ROUND(AVG(bikes),1) as bikes from (SELECT DATE(last_update) as update_date, HOUR(last_update) as update_hour, FLOOR(MINUTE(last_update) / 15) * 15 as update_minute, AVG(".$type_field.") as bikes from stations_usage where stationcode=\"".$stationcode."\" and date(last_update) = CURRENT_DATE GROUP BY 1,2,3) MAIN group by 1,2 ORDER BY 1,2";
// Série des moyennes dans le mode sélection par jour/mois/année
else
  $query = "SELECT update_hour, update_minute, ROUND(AVG(bikes),1) as bikes from (SELECT DATE(last_update) as update_date, HOUR(last_update) as update_hour, FLOOR(MINUTE(last_update) / 15) * 15 as update_minute, AVG(".$type_field.") as bikes from stations_usage where stationcode=\"".$stationcode."\" and DAYOFWEEK(last_update) in (".$day.") and CONCAT(YEAR(last_update), '-', MONTH(last_update)) in (".$yearmonth_sql.") GROUP BY 1,2,3) MAIN group by 1,2 ORDER BY 1,2";


$result = $conn->query($query);
if (!$result) die($conn->error);
$rows = $result->num_rows;
for ($j = 0 ; $j < $rows ; ++$j)
{
  $result->data_seek($j);
  $rows_avg[$j] = $result->fetch_array(MYSQLI_ASSOC);
}


// Série des jours
$stations_data = "";
$i=0;
foreach ($rows_by_day as $key_date => $value_date)
{ 
  if ($i > 0) $stations_data .= ",";
  $stations_data .= "{
            name: \"". date_format(date_create_from_format('Y-m-d',$key_date), 'D M j Y ') . "\",
            lineWidth: 1,
            data: [\r\n\t\t\t\t";

  $j=0;
  foreach ($value_date as $key_min => $value_min)
  {
    if ($j> 0) $stations_data .= ",\r\n\t\t\t\t";
    $stations_data .= "[Date.UTC(1985, 4, 1, ".$value_min['update_hour'].", ".$value_min['update_minute']."), ".$value_min['bikes']."]";
    $j++;
  }
  $stations_data .= "\r\n\t\t\t]\r\n\t\t}";
  $i++;
}

$is_data = $stations_data;  // Variable to check if we have data


// Serie des moyennes ou du jour selon le mode d'affichage
if ($stations_data != "") $stations_data .= ",";
if ($lastweekdays > 0 or $lastdays > 0)
  $stations_data .= "{
          name: \"Aujourd'hui\",
          lineWidth: 3,
          data: [\r\n\t\t\t\t";
else
  $stations_data .= "{
          name: \"Moyenne\",
          lineWidth: 3,
          data: [\r\n\t\t\t\t";

$j=0;
foreach ($rows_avg as $key_avg => $value_avg)
{
  if ($j> 0) $stations_data .= ",\r\n\t\t\t\t";
  $stations_data .="[Date.UTC(1985, 4, 1, ".$value_avg['update_hour'].", ".$value_avg['update_minute']."), ".$value_avg['bikes']."]";
  $is_data .= $stations_data;  // Variable to check if we have data
  $j++;
}
$stations_data .= "\r\n\t\t\t]\r\n\t\t}";


$result->close();
$conn->close();

?>




<script type="text/javascript">

// Verification qu'on a des données à afficher
is_data = <?php echo strlen($is_data)  ?>;


if (is_data > 0) { 

  d = new Date();
  $(function () {
      $('#container').highcharts({
          chart: {
              type: 'spline'
          },
          title: {
              text: 'Velib Paris'
          },
          subtitle: {
              text: "Station <?php echo $name ."  -  Capacité ". $capacity ." places<br>". $station_info; ?>"
          },
          xAxis: {
              type: 'datetime',
              dateTimeLabelFormats: { // empeche l'affichage des jours
                  minute: '%H:%M',
                  hour: '%H:%M',
                  day: '%H:%M'

              },
              title: {
                  text: 'Heure du jour'
              },
              plotLines: [{
                  color: '#C8515F', 
                  width: 2,
                  value: Date.UTC(1985, 4, 1,  d.getHours(), d.getMinutes()) // Position, you'll have to translate this to the values on your x axis
              }]
          },
          yAxis: {
              title: {
                  text: '<?php if ($type == "available_bikes") echo "Velibs"; else echo "Places"; ?> disponibles'
              },
              min: 0,
              allowDecimals: false
          },
          tooltip: {
              headerFormat: '<b>{series.name}</b><br>',
              pointFormat: '{point.x:%H:%M} -> {point.y}/<?php echo $capacity; ?> velibs'
          },

          plotOptions: {
              series: {
                  animation: false
              },
              spline: {
                  marker: {
                      enabled: false
                  }
              }
          },

          series: [ <?php  echo $stations_data; ?>]
      });
  });
}

else {
  $( "#container" ).height(50);
  $( "#container" ).html("<div style='text-align:center;color:red'><br>Pas de données sur la période sélectionnée</div>")
}
</script>



<div style='text-align:center; font-size:9pt'>
<?php 
$end_time = microtime(true);
$creationtime = ($end_time - $start_time);
printf("Page created in %.4f seconds", $creationtime);
?>
</div>

</body>
</html>
