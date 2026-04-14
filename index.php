<?php

header('Access-Control-Allow-Origin: *');//this states thats any website can request data from this script
header('Content-Type: application/json'); //this tells the  receiving computer that its receiving data in json format

function respond(int $statusCode, array $body): void //respond is reusable tool (a function) designed to send the final answer back to whoever asked for it.
{
    http_response_code($statusCode); //sets the official "status" of the request. If you send 404, the browser knows the page is missing. If you send 200, it knows everything is "OK."
    echo json_encode($body);//This line translates your PHP list into a JSON string and "echoes" (prints) it out so the requester can read it.
    exit;
}

function errorResponse(int $code, string $message): void //another function so when using our status code anytime the condition is meant itwill use this function for the format its going to give our response
{
    respond($code, ['status' => 'error', 'message' => $message]);
}

// --- Validate query parameter ---
if (!isset($_GET['name']) || $_GET['name'] === '') { //if not set or empty, then we will send an error response with a 400 status code and a message saying "Missing or empty name parameter"
    errorResponse(400, 'Missing or empty name parameter');
}

$name = $_GET['name'];//stores the fetched name in $name variable

if (!is_string($name)) { //if what was inputted in name is not a string, then we will send an error response with a 422 status code and a message saying "name must be a string"
    errorResponse(422, 'name must be a string');
}

// --- Call Genderize API ---
$url = 'https://api.genderize.io?' . http_build_query(['name' => $name]); //builds the url used to call genderize.com so it will be https://api.genderize.io?name=habiba if name is habiba

$ctx = stream_context_create(['http' => ['timeout' => 5]]);//creating a timeout of 5 seconds for the API call. This means if the API doesn't respond within 5 seconds, it will stop waiting and move on to the next step (which is to handle the error).
$raw = @file_get_contents($url, false, $ctx); //used to call the API and get the raw response. The "@" symbol is used to suppress any warnings that might occur if the API call fails (like if the server is down or there's a network issue). If the call fails, $raw will be set to false, which we check for in the next step.
//$url means the api endpoint we want to call, false means we don't want to use the include path, and $ctx is the context we created with the timeout settings.

if ($raw === false) {//if $raw is false, it means the API call failed, so we will send an error response with a 502 status code and a message saying "Failed to reach upstream API"
    errorResponse(502, 'Failed to reach upstream API');
}

$apiData = json_decode($raw, true);//convert json into a PHP array. The second parameter "true" tells json_decode to return an associative array instead of an object. If the JSON is invalid or there's an issue with decoding, $apiData will be set to null, which we check for in the next step.

if (!is_array($apiData)) {//Check if the apiData is not an array, which would indicate that the JSON was invalid or there was an issue with decoding. If this is the case, we will send an error response with a 502 status code and a message saying "Invalid response from upstream API"
    errorResponse(502, 'Invalid response from upstream API');
}

// --- Handle Genderize edge cases ---
if (empty($apiData['gender']) || empty($apiData['count'])) {//if empty, it means that the API couldn't provide a prediction for the given name (either because the name is very uncommon or because the API doesn't have enough data on it). In this case, we will send an error response with a 200 status code (indicating that the request was successful, but we just don't have data) and a message saying "No prediction available for the provided name"
    errorResponse(200, 'No prediction available for the provided name');
}

// --- Extract & process ---
$gender = $apiData['gender'];
$probability = (float) $apiData['probability'];
$sampleSize = (int) $apiData['count'];//These lines extract the relevant data from the API response and convert them to the appropriate types. $gender is stored as a string, $probability is converted to a float (decimal number), and $sampleSize is converted to an integer (whole number).

$isConfident = ($probability >= 0.7 && $sampleSize >= 100);//This line checks if the probability is 70% or higher and if the sample size is at least 100. If both conditions are met, it sets $isConfident to true, indicating that we can be reasonably

$processedAt = gmdate('Y-m-d\TH:i:s\Z');
//stores the current date and time in a standardized format (ISO 8601) in UTC timezone. This is useful for keeping track of when the data was processed, especially if you want to log it or include it in the response.

respond(200, [
    'status' => 'success',
    'data' => [
        'name' => strtolower($name),
        'gender' => $gender,
        'probability' => $probability,
        'sample_size' => $sampleSize,
        'is_confident' => $isConfident,
        'processed_at' => $processedAt,
    ],
]);