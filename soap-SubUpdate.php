<?php

require_once("exacttarget_soap_client.php");
require_once("config.php");
require_once("FuelAPI-Platform.php");

// Initalize the session
if(!isset($_SESSION)) {session_start();}

// The Internal OAuth Token is needed for Email SOAP API, this is different from the OAuth Token used for REST
$token = $_SESSION['internalOauthToken'];

// The ID of the subscriber that we are updating
$subscriberID = $_REQUEST['id'];	

// The status we are setting this subscriber to
$subscriberStatus = $_REQUEST['status'];	

// Call the Fuel API Rest service for Endpoints to make sure we hit the correct SOAP endpoint
$soapURL = getSoapURLFromPlatform();

try{

	$client = new ExactTargetSoapClient('etframework.wsdl', array('trace'=>1));
	$client->__setLocation($soapURL);

	/* Set username and password here when passing an AuthToken, username and password are required but should be set to "*" */
	$client->username = '*';
	$client->password = '*';
	$client->authtoken = $token;

	$subscriber = new ExactTarget_Subscriber();
	// Set the ID on the Subscriber object so we have a unique identifier for which record is being updated
	$subscriber->ID = $subscriberID;
	// Set the Status on the Subscriber object with the new value
	$subscriber->Status = $subscriberStatus;	

	// The line below makes the HTTP request to ExactTarget with the SOAP payload
	$subObject = new SoapVar($subscriber, SOAP_ENC_OBJECT, 'Subscriber', "http://exacttarget.com/wsdl/partnerAPI");				
	
	$request = new ExactTarget_UpdateRequest();
	// We don't need any additional options for this call so set it to an empty array
	$request->Options = array();
	// Pass in the Subscriber object in an array using the Object property
	$request->Objects = array($subObject);

	// The line below makes the HTTP request to ExactTarget with the SOAP payload
	$results = $client->Update($request);
	
	// Return a true if successful and false if the status was anything outside of "OK"
	if ($results->OverallStatus == 'OK'){
		return true;
	} else {
		return false;
	}
} 
catch (SoapFault $e) 
{
	// If Exception occured then record was not updated, return false
	print_r(false);
}	


?>
