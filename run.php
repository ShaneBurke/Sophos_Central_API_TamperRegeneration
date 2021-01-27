<?php
$ini = parse_ini_file('config.ini');
//First get the token
logMsg("Getting token from Sophos Central API...");
$ch = curl_init();

curl_setopt($ch, CURLOPT_URL, 'https://id.sophos.com/api/v2/oauth2/token');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, "grant_type=client_credentials&client_id=".$ini['client_id']."&client_secret=".$ini['client_secret']."&scope=token");

$headers = array();
$headers[] = 'Content-Type: application/x-www-form-urlencoded';
curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

$result = curl_exec($ch);
if (curl_errno($ch)) {
    logMsg('Error:' . curl_error($ch));
}

$jwt = json_decode($result);

$token = $jwt->{'access_token'};

logMsg("Token obtained!");

curl_close($ch);

//Now that we have a token get the tenantid
logMsg("Getting tenant id from Sophos Central API...");
$url = "https://api.central.sophos.com/whoami/v1";

$request_headers = array(
    "Authorization: Bearer " . $token
);


$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_HTTPHEADER, $request_headers);

$season_data = curl_exec($ch);

if (curl_errno($ch)) {
    logMsg("Error: " . curl_error($ch));
    exit();
}

curl_close($ch);
$tenant = json_decode($season_data, true); 
	
$tenantid = $tenant['id'];
$dataregion = $tenant['apiHosts']['dataRegion'];
	
logMsg("The tenant id is " . $tenantid . ".");

logMsg("The dataregion is " . $dataregion . ".");

//Now that we have the tenantid and the token we can query the API
logMsg("Doing initial query to gather endpoint IDs...");

$url = $dataregion . "/endpoint/v1/endpoints?pageTotal=true&pageSize=500";

$request_headers = array(
	"X-Tenant-ID: " . $tenantid,
    "Authorization: Bearer " . $token
);

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_HTTPHEADER, $request_headers);

$season_data = curl_exec($ch);

if (curl_errno($ch)) {
    logMsg("Error: " . curl_error($ch));
    exit();
}

curl_close($ch);
	
$tenant = json_decode($season_data, true);

//now that we have the first page of endpoints we can query all the pages and store in an array.

$computers = array();

try {
	foreach ($tenant["items"] as $value) {

		array_push($computers, $value["id"]);
	}
} catch (Exception $e) {
    logMsg('Caught exception: ' . $e->getMessage());
}

$pages = $tenant["pages"]["total"] - 1;
$nextkey = $tenant["pages"]["nextKey"];

logMsg("First set of endpoint data pulled and found " . $pages . " remaining pages.");

for ($x = $pages; $x > 0; $x--) {
	logMsg("Pulling another page... (pages remaining " . $x . ")");
	$url = $dataregion . "/endpoint/v1/endpoints?pageSize=500&pageFromKey=" . $nextkey;

    $request_headers = array(
		"X-Tenant-ID: " . $tenantid,
        "Authorization: Bearer " . $token
	);

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $request_headers);

    $season_data = curl_exec($ch);

	
    if (curl_errno($ch)) {
        logMsg("Error: " . curl_error($ch));
        exit();
    }

    curl_close($ch);
	
    $tenant = json_decode($season_data, true);

	//now that we have the first page of endpoints we can query all the pages and store in an array
	try {
		foreach ($tenant["items"] as $value) {
		
			array_push($computers, $value["id"]);
	}
	} catch (Exception $e) {
		logMsg('Caught exception: '.$e->getMessage());
	}

	if($x > 1){
		$nextkey = $tenant["pages"]["nextKey"];
	} else {
		$nextkey = null;
	}
}

logMsg("We found " . count($computers) . " total endpoints.");

logMsg("Resetting tamper protection on all devices...");

if($ini['regentamper'] == true){
	logMsg("Commands set to include tamper regeneration.");
} else {
	logMsg("Commands WILL NOT include tamper regeneration.");
}

if($ini['enabletamper'] == true){
	logMsg("Commands set to enable tamper if disabled.");
} else {
	logMsg("Commands WILL NOT enable tamper if disabled.");
}

foreach($computers as $value){
	logMsg("Sending command for device ID " . $value . "...");
	
	$url = $dataregion . "/endpoint/v1/endpoints/".$value."/tamper-protection";
	
	$data = array(
		'regeneratePassword' => $ini['regentamper'],
		'enabled' => $ini['enabletamper']
	);
 
	$payload = json_encode($data);

    $request_headers = array(
					"X-Tenant-ID: " . $tenantid,
                    "Authorization: Bearer " . $token,
					"Content-Type: application/json",
					'Content-Length: ' . strlen($payload)
                );

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $request_headers);
	curl_setopt($ch, CURLOPT_POST, true);
	curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
	

    $season_data = curl_exec($ch);


    if (curl_errno($ch)) {
        logMsg("Error: " . curl_error($ch));
        exit();
    }

    curl_close($ch);
	
    $tenant = json_decode($season_data, true);
	
	if($tenant['enabled'] == 'true'){
		logMsg("Command successful");
	} else {
		logMsg("ERROR: " . $season_data);
	}
}

logMsg("Task is complete.");

function logMsg($msg){
	$ini = parse_ini_file('config.ini');
	$logname='/logs/TamperRegenLog_'.date('m-d-Y').'.log';
	$date = new DateTime();
	$date->setTimezone(new DateTimeZone($ini['timezone']));
	$date = $date->format("m/d/y h:i:s");
	$this_directory = dirname(__FILE__);
	$fp = fopen($this_directory . $logname, "a+");
	fwrite($fp, $date . ": " . $msg . PHP_EOL); 
	fclose($fp);;
}
?>