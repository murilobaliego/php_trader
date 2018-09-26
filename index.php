<?php

//phpinfo();

$symbol = "VALE3.SA";
//$symbol = "PETR4.SA";

$url = "https://www.alphavantage.co/query?function=TIME_SERIES_INTRADAY&symbol=$symbol&interval=5min&outputsize=full&apikey=X2YNJWH7QSF0OY2C";

$data = get_data($url);
$data = $data['Time Series (5min)'];

$x=0;
foreach ($data as $key => $value) {
      if ($x == 0){
      	  $latest = $value['4. close'];
      	  $time = $key;
      }
      $x++;
    }


echo "<br>";

$time_pieces = explode(" ", $time);
$time_date = $time_pieces[0];
$time_hour = $time_pieces[1];
echo $time_date;

$url = "https://www.alphavantage.co/query?function=BBANDS&symbol=$symbol&interval=daily&time_period=60&series_type=close&nbdevup=3&nbdevdn=3&apikey=X2YNJWH7QSF0OY2C";
$data = get_data($url);
$data = $data['Technical Analysis: BBANDS'];
$data = $data["$time_date"];
echo "<BR>";
$upper_band = $data['Real Upper Band'];
$middle_band = $data['Real Middle Band'];
$lower_band = $data['Real Lower Band'];
echo "<br>Upper Band = ".$upper_band;
echo "<br>Middle Band = ".$middle_band;
echo "<br>Lower Band = ".$lower_band;
echo "<br>Latest = ".$latest;  

/* =============================================================================
                                    Functions
==============================================================================*/

function get_data($url){
	$crl = curl_init($url);
	curl_setopt($crl, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($crl, CURLOPT_TIMEOUT, 90);
	curl_setopt($crl, CURLOPT_HTTPHEADER, array('Cache-control: no-cache'));
	curl_setopt($crl, CURLOPT_HEADER, 0);
	
	$result = curl_exec($crl);
	
	$result = (array) json_decode($result, true);
	curl_close($crl);
	
	return $result;
}

?>
