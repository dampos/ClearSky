<?php 

function getForecast($airport, $day, $month, $year, $hour)
{
        // create curl resource 
        $ch = curl_init(); 
		/*$airport = $_GET['airport'];
		$day = $_GET['day'];
		$month = $_GET['month'];
		$year = $_GET['year'];
		$hour = $_GET['hour'];*/
		
        // set url 
        curl_setopt($ch, CURLOPT_URL, "http://api.wunderground.com/api/9956ca6fe6bc6618/hourly10day/q/" . $airport . ".json"); 

        //return the transfer as a string 
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); 

        // $output contains the output string 
        $output = json_decode(curl_exec($ch)); 
		//print_r($output);
		//exit;
		foreach($output->hourly_forecast as $forecast)
		{
			if(
			$forecast->FCTTIME->hour == $hour && 
			$forecast->FCTTIME->mday == $day && 
			$forecast->FCTTIME->mon == $month && 
			$forecast->FCTTIME->year == $year)
			{
				
				//echo $forecast->condition . ';' . $forecast->temp->metric . ';' . $forecast->wspd->metric . ';';
			}
		}
		
        // close curl resource to free up system resources 
        curl_close($ch);
		
		return array(
		'condition' =>$forecast->condition,
		'temp'=> $forecast->temp->metric,
		'wind'=> $forecast->wspd->metric, 
		
		);
}
?>