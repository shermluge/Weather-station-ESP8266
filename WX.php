<?php
/*
   Post WX from 8266 12E or 12F
   
   Updated by Sherman Stebbins exports to data.txt
   11-12-17
   
   Updated By Sherman Stebbins - exports to mysql now as well.
   after a few days, I will have it only go to mysql (if later code for
   index.php goes well)
   12-13-17
*/
$same=0;
$goingUp=1;
$goingDown=2;
$prevPress = 0.0;
$pressRate = 0.0;

date_default_timezone_set("America/Los_Angeles");
$TimeStamp = date("Y/m/d H:i:s");
   if( $_REQUEST["TempF"] ){
      echo " The Temp is: ". $_REQUEST['TempF']. " F<br />";
      echo " The Temp is: ". $_REQUEST['TempC']. " C<br />";
      echo " The Index is: ". $_REQUEST['TempFi']. " Fi<br />";
      echo " The Index is: ". $_REQUEST['TempCi']. " Ci<br />";
      echo " The Humidity is: ". $_REQUEST['Humidity']. " %<br />";
      echo " The Pressure is: ". $_REQUEST['pressure']. " %<br />";
      echo " The TempBmp is: ". $_REQUEST['tempBmp']. " %<br />";
      echo " The volt is: ". $_REQUEST['volt']. " %<br />";
      echo " The rpm is: ". $_REQUEST['windspeed']. " %<br />";
   }
$tempF = $_REQUEST['TempF'];
$tempC = $_REQUEST['TempC'];
$tempFi = $_REQUEST['TempFi'];
$tempCi = $_REQUEST['TempCi'];
$humidity = $_REQUEST['Humidity'];
$pressure = $_REQUEST['pressure'];
$tempBmp = $_REQUEST['tempBmp'];
$volt = $_REQUEST['volt'];
$light = $_REQUEST['light'];
$windspeed = $_REQUEST['windspeed'];

if($tempF != "nan"){
    $WriteMyRequest=  $TimeStamp. "," . $tempF . "," . $tempC .",". $tempFi .",". $tempCi . "," . $humidity 
    . "," . $pressure . "," . $tempBmp . "," . $volt .  "," . $light . ",". $windspeed . "\n";
   file_put_contents('data.txt', $WriteMyRequest, FILE_APPEND);
}else{
    $WriteMyRequest= $TimeStamp . "," . $tempF . "," . $humidity . "," . $tempBmp . "," . $volt . ",Failed\n";
    file_put_contents('failed.txt', $WriteMyRequest,FILE_APPEND);
}


$avg = 0;
$file = file('data.txt');
for($i=24;$i>=12;$i--){
	$line[$i] = $file[count($file) - $i];
	$line = $file[count($file) - $i];
	$pieces = explode(",", $line);
	$pressInches = number_format($pieces[6] * 0.0002952998,4);
	$avg= $avg + $pieces[6];
}
$prevPress=$avg/12;

$avg = 0;
$fileout = "debug.txt";
for($i=12;$i>=1;$i--){
	$line[$i] = $file[count($file) - $i];
	$line = $file[count($file) - $i];
	$pieces = explode(",", $line);
	$avg= $avg + $pieces[6];
}

$avg=$avg/12;
$var_string =  $TimeStamp ."," . $tempF . "~" . $avg . "," . $pressRate . "!" . $prevPress .",". $pieces[6]. "," . $i. "\n";
file_put_contents($fileout, $var_string, FILE_APPEND | LOCK_EX);


//////Start of SQL///////
$servername = "mysql.hostinger.com";
$username = "mysql_user_name";
$password = "password";
$dbname = "Data_base_name";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);
// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
} 
$sql = "INSERT INTO `wx` (`id`, `location`, `date`, `tempf`, `tempc`, `tempfi`, `tempci`, `humidity`, `pressure`, 
`TempBmpC`, `windspeed`, `windDir`,`volt`,`light`) 
VALUES (NULL, 'BackYard', CURRENT_TIMESTAMP, '$tempF', '$tempC', '$tempFi', '$tempCi', '$humidity', '$pressure', 
'$tempBmp', '$windspeed','0','$volt','$light')";

if ($conn->query($sql) === TRUE) {
    echo "New record created successfully";
} else {
    echo "Error: " . $sql . "<br>" . $conn->error;
}
$conn->close();
//////end of SQL///////

if($avg==$prevPress){
		$pressRate = $same;
}else if ($avg > $prevPress){
		$pressRate = $goingUp;
}else{
		$pressRate = $goingDown;
}
$avg = number_format($avg * 0.00031764553693764569533801318419059,4);
$fp = fopen('../family/wx/temp.html', 'w');
$var_string = "<html>," . $tempF . "~" . $avg . ",</html>" . $pressRate . "!" . $prevPress . $pieces[6];
fwrite($fp, $var_string );
fclose($fp);

?>
