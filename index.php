<html>
    <head> <meta http-equiv="refresh" content="60" /> </head>
    <body style="background-color:LightGray;">
<p class="highlight" class="bold">Prototype#3  temperature(duel), humidity, pressure, and light levels -- Out Side:</p>

<?php 
////////////////////////////////////////////////////////////
date_default_timezone_set("America/Los_Angeles");
$servername = "mysql.hostinger.com";
$username = "user";
$password = "pass";
$dbname = "dbname";
$paddingBuff=0;

   $same = 0;
	$goingUp = 1;
	$goingDown = 2;

	// Create connection
	$conn = new mysqli($servername, $username, $password, $dbname);
	
	// Check connection
	if ($conn->connect_error) {
		die("Connection failed: " . $conn->connect_error);
	} 
	$sql = "SELECT * FROM wx ORDER BY id DESC LIMIT 1";
	$result = $conn->query($sql);
	if ($result->num_rows > 0) {
		while($row = $result->fetch_assoc()) {
			$lastRecord=$row["id"];
			$humidity=$row["humidity"]+5;
			if($humidity>100)$humidity=100;
			$currentPacal = number_format($row["pressure"],4);
			$pressInches = number_format($row["pressure"] * 0.00031764553693764569533801318419059,4);
			$tempAvg = ($row["tempc"]+$row["TempBmpC"])/2; //avg of both sensors.	
			$dewPoint = number_format($tempAvg-((100 -($humidity))/5),2);
			$newDate=date("m/d/Y H:i:s ", strtotime('-8 hours', strtotime($row["date"])));
			$volt=$row["volt"];
			echo "Last Reading: <p style=\"font-size: 140%\">" . $newDate . "</p>"; // piece1
			echo "<p>Voltage: " . $volt . "</p>";
			echo "<p class=\"highlight\">".  $tempAvg . " C    /   DewPoint " . $dewPoint . " C </p>";
			$tempAvgF = $tempAvg * 1.8 + 32;
			$tempAvgD = $dewPoint * 1.8 +32;
			$tempSpread = $tempAvgF - $tempAvgD;
			$cloudBase=number_format($tempSpread/4.4*1000,0);
			$light=$row["light"];
			$revolutions=$row["windspeed"]; //revolutions per 6 seconds as set in code
		}
	} else {
		echo "0 results";
	}
	$conn->close();
	
///////////////////////////////////////////////////////////////
///  ATTENTION: MODIFIES HUMIDITY DUE TO CALIBRATION WITH DHT22
	//$pieces[5]=$pieces[5]+10;
	//if($pieces[5]>100)$pieces[5]=100;
///  
///////////////////////////////////////////////////////////////
//	$timeRecorded = explode(" ",pieces[0]);
//	$zTime = explode(":", timeRecorded[1]);
//	$zDate = explode("\/", )
	//echo "KFUK " .  000000KT 10SM "
	$number = range(110000,1,7.1428);
	$test=.001;
   $test=($number[130])/100;
   $tempSpread = number_format($tempSpread,2);
   
   //Light map funtion:
   	function mapNumber($xx, $in_min, $in_max, $out_min, $out_max)
    	{
        return ($xx - $in_min) * ($out_max - $out_min) / ($in_max - $in_min) + $out_min;
    	}
    	if($light<900) {
    		$lux = number_format($lux = mapNumber($light,0,900,80000,900),0);
    	}else {
    		$lux = number_format($lux = mapNumber($light,900,19000,1000,0),0);
    	}
	//$circ = .0011938052;
	//$circ = .0007539862;
	//$rpm = (($revolutions/2)/6)*60; //maxTime is 5
    //$kph = $rpm * $circ *60;
	
	//works much better and is actual math..
	$circ=97.34; //centimeters for 1 revolution
	$kph = ($circ * $revolutions *10*60)/100000;//10 is for 6*10 for 1 minute, 60 is for minutes in hour 100000cm per 1km
	$mph =number_format($kph/1.609344,2); 
	echo "<span >
	<style>
	  /* table#t01,th,td {
	        align: center;
	    }*/</style>";
	    
	echo "<p class=\"highlight\"><table style=\"font-size: 140%\"><tr><th>Temp:</th><th>DewPoint:</th><th>Temp<br>Spread:</th><th>Humidity:</th>
	<th>Pressure:</th><th>Est.<br>Cloud Base:</th><th>Light<br>(in lux):</th><th>Wind<br>Speed mph</th></tr>
	<tr><td align=center>".$tempAvgF . " F</td><td align=center>" . $tempAvgD . " F</td><td align=center>$tempSpread</td>
	<td align=center>" . $humidity . " %</td><td align=center>" . $pressInches . " in</td><td align=center>$cloudBase</td>
	<td align=center>$lux</td><td>" . $mph . "</td></tr></table>
	</p></span><br>Last 24 hrs of readings(12 Measures per hour)<br>\n";
	  
	//class=\"highlight\" class=\"bold\ style=\"font-size: 140%\"
	/*echo "<style>
	
	table, td, th {
    border: 1px solid black;
}*/
	echo "<style>
	
	td, th {
    border: 1px solid black;
    padding: 1px;
    
}

table {
  border-collapse: collapse;
 /* width: 100px;*/
  height: 100px;
  background-color: white;
  display: inline-block;
}

th {
    height: 50px;
}
</style>\n";
    echo "<hr>
    <font size=\"+2\">Agerage Temps:</font><br>\n"; 
	echo "<table style=\"font-size: 10%\">\n";
	$value=NULL;
	$valueh=NULL;
	$timelabel=NULL;
	$thm=NULL;
	$avg=0;
	$avgh=0.0;
	$counttmp=0;
	if($tempAvgF<12){
	    $paddingBuff = 280;
	}else if($tempAvgF>=12 && $tempAvgF<32){
	    $paddingBuff = 80;
	}else if($tempAvgF>=32 && $tempAvgF<70){
	    $paddingBuff = -50;
	}else{
	    $paddingBuff - -220;
	}
	/////////Temperature//////////////////////////////////////////////////////////////////////////
	// Create connection
	$conn = new mysqli($servername, $username, $password, $dbname);
	// Check connection
	if ($conn->connect_error) {
		die("Connection failed: " . $conn->connect_error);
	} 
	$sql = "SELECT * FROM wx WHERE date > DATE_SUB(NOW(), INTERVAL 23 HOUR)";
	$result = $conn->query($sql);
	$prevHour = 99;
	if ($result->num_rows > 0) {
		//echo "<table><tr><th>Date</th><th>Light</th><th>Humidity</th></tr>";
		// output data of each row
		$totalHours=0;
		while($row = $result->fetch_assoc()) {
			$newDate=date("m/d/Y H:i:s ", strtotime('-8 hours', strtotime($row["date"])));
			$newHour=date("H", strtotime('-8 hours', strtotime($row["date"])));
			if($prevHour == 99)$prevHour=$newHour;
			if($prevHour != $newHour && $row["humidity"] != 0 ){
				$avg=$avg/$counttmp;
				$value[$totalHours]=number_format($avg,2);
				$avg=0;
				$counttmp=0;
				$blockHours[$totalHours]=$prevHour;
				$prevHour=$newHour;
				//$blockHours[$totalHours]=$newHour;
				$totalHours++;
			}else if($row["humidity"] != 0 ){
				$counttmp++;
				$avg=$avg+$row["tempf"];
			}
			//echo "<tr><td>".$newDate. "</td><td>".$newHour. "</td><td>".$row["light"]."</td><td>".$row["humidity"]."</td></tr>";
		}
		if($counttmp>0){
			$avg=$avg/$counttmp;
			$value[$totalHours]=number_format($avg,2);	
			$blockHours[$totalHours]=$newHour;
		}
			//echo "</table>";
	} else {
		echo "0 results";
	}
	//echo "<br>total hours: ".$totalHours."<br>";
	for($i=0;$i<$totalHours;$i++){
		echo "<table style=background:linear-gradient(-360deg,blue,yellow);width:42px;height:";
		echo ($value[$i] * 5) + $paddingBuff;
		echo "px;background-color:red;border-collapse:collapse;display:inline-block;>
               <tr>
               <th style=\"width:42px;text-align:top;\">";
        echo $value[$i];
        echo "</th></tr>
              <tr style=\"height:";
        echo ($value[$i]*5) + $paddingBuff - 25;
        echo "px\"><td valign=\"bottom\";>";
        echo $blockHours[$i] . "</td>";
        echo "</tr></table>\n";
	}
	$conn->close();

///////////////End of Temp////////////////////////////////////////////////

	////////////Humidity//////////////////////////////////////////////////
	// Create connection
	echo "<br><br><hr><br><font size=\"+2\">Humidity:</font><br>";
	$conn = new mysqli($servername, $username, $password, $dbname);
	// Check connection
	if ($conn->connect_error) {
		die("Connection failed: " . $conn->connect_error);
	} 
	$sql = "SELECT * FROM wx WHERE date > DATE_SUB(NOW(), INTERVAL 23 HOUR)";
	$result = $conn->query($sql);
	$prevHour = 99;
	$counttmp=0;
	$avg = 0;
	if ($result->num_rows > 0) {
		$totalHours=0;
		while($row = $result->fetch_assoc()) {
			$newDate=date("m/d/Y H:i:s ", strtotime('-8 hours', strtotime($row["date"])));
			$newHour=date("H", strtotime('-8 hours', strtotime($row["date"])));
			if($prevHour == 99)$prevHour=$newHour;
			if($prevHour != $newHour && $row["humidity"] != 0 ){
				$avg=$avg/$counttmp;
				$value[$totalHours]=number_format($avg,2);
				$avg=0;
				$counttmp=0;
				$blockHours[$totalHours]=$prevHour;
				$prevHour=$newHour;
				//$blockHours[$totalHours]=$newHour;
				$totalHours++;
			}else if($row["humidity"] != 0){
				$counttmp++;
				$avg=$avg+$row["humidity"];
			}			
		}
		if($counttmp>0){
			$avg=$avg/$counttmp;
			$value[$totalHours]=number_format($avg,2);	
			$blockHours[$totalHours]=$newHour;
		}		
	} else {
		echo "0 results";
	}
	//echo "<br>total hours: ".$totalHours."<br>";
	$paddingBuff = 0;
	for($i=0;$i<$totalHours;$i++){
		echo "<table style=background:linear-gradient(-360deg,blue,green);width:42px;height:";
		echo ($value[$i] * 2)+$paddingBuff;
		echo "px;background-color:red;border-collapse:collapse;display:inline-block;>
              <tr>
              <th style=width:42px;text-align:top;>";
        echo $value[$i];
        echo "</th></tr>
              <tr style=\"height:";
        echo ($value[$i]*2)+$paddingBuff - 20;
        echo "px\"><td valign=\"bottom\";>";
        echo $blockHours[$i] . "</td>";
        echo "</tr></table>\n";
	}
	//echo "</td></tr></table>";
	$conn->close();
	
	echo "<br><br><br>";
    //echo "<span style=\"font-size: 100%\">";
	//echo "<p class=\"highlight\">24 Hour index</p>";
	//echo "</span>";
//////End HUMIDITY/////////////////////////////////////////////////////////////////////


//////////Pressure/////////////////////////////////////////////////////////////////////

	// Create connection
	$conn = new mysqli($servername, $username, $password, $dbname);
	// Check connection
	if ($conn->connect_error) {
		die("Connection failed: " . $conn->connect_error);
	} 
	$sql = "SELECT * FROM wx WHERE date > DATE_SUB(NOW(), INTERVAL 23 HOUR)";
	$result = $conn->query($sql);
	$avg = 0;
	$prevAvg = 0;
	$upOrDown = 0;
	$prevDay =0;
	$dayDiff = 0;
	$prevHour = 99;
	$newHour=0;
	$counttmp=0;
	$totalHours=0;
	$prevPress=0;
	if ($result->num_rows > 0) {
		//echo "<table><tr><th>Date</th><th>Light</th><th>Humidity</th></tr>";
		// output data of each row
		$totalHours=0;
		while($row = $result->fetch_assoc()) {
			$newDate=date("m/d/Y H:i:s ", strtotime('-8 hours', strtotime($row["date"])));
			$newHour=date("H", strtotime('-8 hours', strtotime($row["date"])));
			if($prevHour == 99)$prevHour=$newHour;
			if($prevHour != $newHour && $row["humidity"] != 0 ){
				$avg=$avg/$counttmp;
				//$avg = number_format($avg * 0.00031764553693764569533801318419059,2);
				if($avg==$prevPress){
					$pressRate = $same;
				}else if ($avg > $prevPress){
					$pressRate = $goingUp;
				}else{
					$pressRate = $goingDown;
				}
				
				$value[$totalHours]=$avg;
				//$prevPress = $avg;
				$avg=0;
				$counttmp=0;
				$blockHours[$totalHours]=$prevHour;
				$prevHour=$newHour;
				//$blockHours[$totalHours]=$newHour;
				$totalHours++;
				
			}else if ($row["humidity"] != 0 ){
				$counttmp++;
				$avg=$avg+$row["pressure"];
				
			}			
		}
		if($counttmp>0){
			$avg=$avg/$counttmp;
			//$avg = number_format($avg * 0.00031764553693764569533801318419059,2);
			if($avg==$prevPress){
				$pressRate = $same;
			}else if ($avg > $prevPress){
				$pressRate = $goingUp;
			}else{
				$pressRate = $goingDown;
			}
			$value[$totalHours]=$avg;
			$blockHours[$totalHours]=$newHour;
		}
			//echo "</table>";
	} else {
		echo "0 results";
	}
	echo "<hr><br><font size=\"+2\">Pressure in last 24 hrs:</font><br>";
	for($i=0;$i<$totalHours;$i++){
		if($value[$i]>$prevAvg){
			$upOrDown++;
		}else if($value[$i]<$prevAvg){
			$upOrDown--;
		}
		$prevAvg = $value[$i];
		$avg = number_format($value[$i] * 0.00031764553693764569533801318419059,2);
		echo "<table style=background:linear-gradient(-360deg,blue,red);width:42px;height:";
		echo $upOrDown*10 +220;
		echo "px;background-color:red;border-collapse:collapse;display:inline-block;>
             <tr>
             <th style=width:42px;text-align:top;>";
        echo $avg;
        echo "</th></tr>
             <tr style=\"height:";
        echo $upOrDown*10 + 200;
        echo "px\"><td valign=\"bottom\";>";
        echo $blockHours[$i] . "</td>";
        echo "</tr></table>\n";		
	}
	$conn->close();
/////////end of pressure/////////////////////////////////////////////////////////////////////////////////

	echo "<br><br><br><hr><br><font size=\"+2\">Light in LUX:</font><br>";
	$avg = 0;
	$prevDay =0;
	$dayDiff = 0;
//////////Light/////////////////////////////////
	// Create connection
	$conn = new mysqli($servername, $username, $password, $dbname);
	// Check connection
	if ($conn->connect_error) {
		die("Connection failed: " . $conn->connect_error);
	} 
	$sql = "SELECT * FROM wx WHERE date > DATE_SUB(NOW(), INTERVAL 23 HOUR)";
	$result = $conn->query($sql);
	$avg = 0;
	$prevAvg = 0;
	$prevDay =0;
	$dayDiff = 0;
	$prevHour = 99;
	$newHour=0;
	$counttmp=0;
	$totalHours=0;
	$prevPress=0;
	if ($result->num_rows > 0) {
		// output data of each row
		$totalHours=0;
		while($row = $result->fetch_assoc()) {
			$newDate=date("m/d/Y H:i:s ", strtotime('-8 hours', strtotime($row["date"])));
			$newHour=date("H", strtotime('-8 hours', strtotime($row["date"])));
			if($prevHour == 99)$prevHour=$newHour;
			if($prevHour != $newHour && $row["humidity"] != 0 ){
				$avg=$avg/$counttmp;
				if($avg<900) {
					$avg =  mapNumber($avg,0,900,80000,900);
				}else {
					$avg =  mapNumber($avg,900,19000,1000,0);
				}
				$value[$totalHours]=$avg;
				$avg=0;
				$counttmp=0;
				$blockHours[$totalHours]=$prevHour;
				$prevHour=$newHour;
				$totalHours++;				
			}else if ($row["humidity"] != 0 ){
				$counttmp++;
				$avg=$avg+$row["light"];				
			}			
		}
		if($counttmp>0){
			$avg=$avg/$counttmp;
			if($avg==$prevPress){
				$pressRate = $same;
			}else if ($avg > $prevPress){
				$pressRate = $goingUp;
			}else{
				$pressRate = $goingDown;
			}
			$value[$totalHours]=$avg;
			$blockHours[$totalHours]=$newHour;
		}
			
	} else {
		echo "0 results";
	}
	echo "<br><br>";
	for($i=0;$i<$totalHours;$i++){
	
		$avg = $value[$i];
		$height = $avg;
		if($avg>1 && $avg<100){
			$height = 70;			
		}
		if($avg>100&&$avg<200){
			$height = 100;
		}
		if($avg>200&&$avg<300){
			$height = 130;
		}
		if($avg>300&&$avg<500){
			$height = 160;
		}
		if($avg>500&&$avg<800){
			$height = 190;
		}
		if($avg> 800 && $avg< 1100){
			$height = 220;
		}
		if($avg>1100 && $avg<1400){
			$height = 250;
		}
		if($avg>1400 && $avg<3000){
			$height = 280;
		}
		if($avg>3000 && $avg<10000){
			$height = 310;
		}
		if($avg>10000 && $avg<20000){
			$height = 340;
		}
		if($avg>20000 && $avg<50000){
			$height = 370;
		}
		if($avg>50000){
			$height = 400;
		}
		echo "<table style=background:linear-gradient(-360deg,purple,orange);width:42px;height:";
		echo $height;
		echo "px;background-color:red;border-collapse:collapse;display:inline-block;>
             <tr>
             <th style=\"font-size: 14px;width:42px;text-align:top;\">";
        echo number_format($avg,0);
        echo "</th></tr>
             <tr style=height:";
        echo $height-20;
        echo "px;><td valign=\"bottom\";>";
        echo $blockHours[$i] . "</td>";
        echo "</tr></table>\n";		
	}
	$conn->close();
	
/////////////end of light////////////////////////////////////////////////////////////////////////////////////////
///////////////////////Wind://////////////////////////////////////////////////////////////////////////
	$avg = 0;
	$prevDay =0;
	$dayDiff = 0;
	$paddingBuff = 60;
	// Create connection
	$conn = new mysqli($servername, $username, $password, $dbname);
	// Check connection
	if ($conn->connect_error) {
		die("Connection failed: " . $conn->connect_error);
	} 
	$sql = "SELECT * FROM wx WHERE date > DATE_SUB(NOW(), INTERVAL 23 HOUR)";
	$result = $conn->query($sql);
	$prevHour = 99;
	if ($result->num_rows > 0) {
		//echo "<table><tr><th>Date</th><th>Light</th><th>Humidity</th></tr>";
		// output data of each row
		$totalHours=0;
		while($row = $result->fetch_assoc()) {
			$newDate=date("m/d/Y H:i:s ", strtotime('-8 hours', strtotime($row["date"])));
			$newHour=date("H", strtotime('-8 hours', strtotime($row["date"])));
			if($prevHour == 99)$prevHour=$newHour;
			if($prevHour != $newHour && $row["humidity"] != 0 ){
				$avg=$avg/$counttmp;
				$value[$totalHours]=number_format($avg,2);
				$avg=0;
				$counttmp=0;
				$blockHours[$totalHours]=$prevHour;
				$prevHour=$newHour;
				$totalHours++;
			}else if($row["humidity"] != 0 ){
				$counttmp++;
				$avg=$avg+$row["windspeed"];
			}
			//echo "<tr><td>".$newDate. "</td><td>".$newHour. "</td><td>".$row["light"]."</td><td>".$row["humidity"]."</td></tr>";
		}
		if($counttmp>0){
			$avg=$avg/$counttmp;
			$value[$totalHours]=number_format($avg,2);	
			$blockHours[$totalHours]=$newHour;
		}
			//echo "</table>";
	} else {
		echo "0 results";
	}
	echo "<br><br><br><hr><br><font size=\"+2\">Wind Speed:</font><br>";
	for($i=0;$i<$totalHours;$i++){
		echo "<table style=background:linear-gradient(-360deg,gray,white);width:42px;height:";
		echo ($value[$i]*5) + $paddingBuff;
		echo "px;background-color:red;border-collapse:collapse;display:inline-block;>
               <tr>
               <th style=\"width:42px;text-align:top;\">";
        echo $value[$i];
        echo "</th></tr>
              <tr style=\"height:";
        echo ($value[$i]*5) + $paddingBuff - 25;
        echo "px\"><td valign=\"bottom\";>";
        echo $blockHours[$i] . "</td>";
        echo "</tr></table>\n";
	}
	$conn->close();

///////////////End of Wind////////////////////////////////////////////////

echo "<br><br><br><br><hr><br>Pascal:" .$currentPacal."<br>\n";
	if($pressRate == $same){
		echo "<br>Pressure since last hour staying the same<br>";
	}else if($pressRate == $goingUp){
		echo "<br>Pressure since last hour going up, improving<br>";
	}else{
		echo "<br>Pressure since last hour going down, getting worse<br>";
	}
echo "<br>Rate of increase or decrease (24 max, -24 min), 
closer to 0 means not much change:<font size=\"+2\"> " . $upOrDown . "</font>\n";
if($upOrDown == 0){
		echo "<br>24 hr Pressure staying the same<br>";
	}else if($upOrDown > 0 && $upOrDown < 10){
		echo "<br>24 hr Pressure going up, improving<br>";
	}else if($upOrDown >= 10 && $pressRate == $goingUp){
		echo "<br>24 hr Pressure going up, is greatly improving<br>";
	}else if($upOrDown >= 10 && $pressRate != $goingUp){
		echo "<br>24 hr Pressure going up, is greatly improving<br>";
	}else{
		echo "<br>24 hr Pressure going down, getting worse<br>";
	}
echo "<hr><br>Total records recorded: " . $lastRecord . "<br>\n";
echo "<br> Updated 01/05/18 - converted to all mysql now.\n";
echo "<br> Updated 01/22/18 - Added Lux and Wind graphs and fixed formating issues (still a few more to go).";
echo "<hr><br><a href=\"layout.jpg\">Layout photo</a>\n";
echo "<br><a href=\"wxStation.jpg\">WX Station photo (prior to set up with case and wind speed)</a>\n";
	
	

/////////////////////////////////////////////////////////////////////////////
?>
</body></html>
