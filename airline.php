<?php 
include('weather.php');
function printDate($date)
{
	return sprintf('%02d', $date['day']) . '/' . sprintf('%02d', $date['month']) . '/' . $date['year'] . ' ' . sprintf('%02d', $date['hour']) . ':' . sprintf('%02d', $date['minute']); 
}

		$appId= '1ab0640d';
		$appKey='080a6d2f286b11871516850a3a1c43b1';
		
		
		$carrier = substr($_GET['id'], 0, 2);
		$id = substr($_GET['id'],2);
		
		$day = $_GET['d'];
		$month = $_GET['m'];
		$year = $_GET['y'];
		
		
        // create curl resource 
        $ch = curl_init(); 
		
		
		
		
        // set url 
		$url = "https://api.flightstats.com/flex/flightstatus/rest/v2/json/flight/status/" . $carrier . "/" . $id .  "/dep/". $year . "/" . $month .  "/" . $day . "?appId=1ab0640d&appKey=080a6d2f286b11871516850a3a1c43b1&utc=false";
        
		curl_setopt($ch, CURLOPT_URL, $url); 
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        //return the transfer as a string 
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); 

        // $output contains the output string 
        $output = json_decode(curl_exec($ch)); 
		
		//print_r($output);
		
		foreach($output->flightStatuses as $flight)
		{
			if(isset($flight->departureAirportFsCode))
			{
				$scheduledDep = date_parse($flight->operationalTimes->publishedDeparture->dateLocal);
				$scheduledArr = date_parse($flight->operationalTimes->publishedArrival->dateLocal);
				
				$estimatedDep = date_parse($flight->operationalTimes->estimatedGateDeparture->dateLocal);
				$estimatedArr = date_parse($flight->operationalTimes->estimatedGateArrival->dateLocal);
				
				foreach($output->appendix->airports as $airports)
				{
					if($airports->fs == $flight->departureAirportFsCode)
					{
						$from = $airports->city . ', ' . $airports->countryName . ' (' . $airports->fs . ')';
						
						$weatherDep = getForecast($flight->departureAirportFsCode, $scheduledDep['day'], $scheduledDep['month'], $scheduledDep['year'], $scheduledDep['hour']);
						if($weatherDep['wind'] > 50 or $weatherDep['condition'] == 'Fog')
						{
							$status = 'Possible delay on take off.';
						}
					}
					
					if($airports->fs == $flight->arrivalAirportFsCode)
					{
						$to = $airports->city . ', ' . $airports->countryName . ' (' . $airports->fs . ')';
						
						$weatherArr = getForecast($flight->arrivalAirportFsCode, $scheduledArr['day'], $scheduledArr['month'], $scheduledArr['year'], $scheduledArr['hour']);
						if($weatherArr['wind'] > 50 or $weatherArr['condition'] == 'Fog')
						{
							$status .= 'Possible delay or diversion on landing.';
						}
					}
				}
				
				if(!isset($status))
				{
					$status = ' ';
				}
				
				if($flight->delays->departureGateDelayMinutes > 0)
				{
					$status .= 'Delay on take off: ' . $flight->delays->departureGateDelayMinutes . '.';
				}
						
				if($flight->delays->arrivalGateDelayMinutes > 0)
				{
					$status .= 'Delay on arrival: ' . $flight->delays->arrivalGateDelayMinutes . '.';
				}
				
				if($flight->status == 'L')
				{
					$status = 'Landed';
				}
			}
			else
			{
				echo "404"; exit;
			}
		}
		
		/*echo $from . ';' . $to . ';' . $status . ';' . printDate($estimatedDep) .  ';' . printDate($estimatedArr) . ';';
		
		foreach($weatherDep as $we)
		{
			echo $we . ';';
		}
		foreach($weatherArr as $we)
		{
			echo $we . ';';
		}*/
		
		$out = array(
		'from' => $from, 
		'to' => $to,
		'status' => $status,
		'estimatedDep'=> $estimatedDep,
		'estimatedArr' => $estimatedArr,
		'weatherDep'	=> $weatherDep,
		'weatherArr'	=> $weatherArr
		);
		
		echo json_encode($out);
		
		//print_r($weatherDep) . '\n' . print_r($weatherArr); 
		
		
        // close curl resource to free up system resources 
        curl_close($ch);      
?>