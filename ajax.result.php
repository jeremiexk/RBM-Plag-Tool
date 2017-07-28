<?php

	require_once ( "API_SCRIPT/Client.php" ) ;
	$client = new Client('#####','Yh&_7s');
	$percentageCompleted = $client->getTextStatus($_GET["hash"])->getData();
    // Checking the percentage of processing completed from the API SErver
	$response = $client->getResponse();
	$headerJson = array( "header" => array ("percent" => intval($percentageCompleted) , "status"=> $response->getStatus() ) ) ;
	//header('Content-Type: application/json');
	
    // once the API Has completed the processing and analysing the data 
    // the result is echoed and displayed to the user
    if (intval ($percentageCompleted)>99) {
		$returnResult = $client->getResult($_GET["hash"])->getData() ;
		$returnResult = array_merge ( $returnResult , $headerJson ) ;
		$returnResult = json_encode ( $returnResult ) ;
		echo $returnResult ;
	} else {
		echo json_encode ( $headerJson ) ; // returning the string value read from the json array
	}
    
    
    
    