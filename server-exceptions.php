<?php
/**
 * @file
 * Shows example of getting 500's
 */
require_once __DIR__ . "/vendor/autoload.php";

use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Exception\ServerException;
use Monolog\Handler\FirePHPHandler;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;

$url = "https://data.dx.oregonstate.edu";

// Creating logger.
$logger = new Logger('guzzle-exception-example');
// Creating logger handlers;
$stream = new StreamHandler(__DIR__ . '/guzzle-exception-example.log', Logger::DEBUG);
$firephp = new FirePHPHandler();
// Registering handlers to logger stack.
$logger->pushHandler($stream);
$logger->pushHandler($firephp);
// Base url for API testing.
$client = new Client(['base_uri' => $url]);
try {
  $response = $client->get('/jsonapi/node/services', [
    'query' => [
      'filter' => [
        'status' => 1,
      ],
      'fields' => [
        'node--services' => 'id,title,field_exclude_trending,field_icon_name,field_service_category,field_affiliation,field_audience,field_service_synonyms,field_service_url,field_locations,field_it_system',
        'taxonomy_term--categories' => 'name',
        'taxonomy_term--audience' => 'name',
        'taxonomy_term--affiliation' => 'name',
        'taxonomy_term--locations' => 'name',
        'taxonomy_term--it_systems' => 'name',
      ],
      'include' => 'field_affiliation,field_audience,field_service_category,field_locations,field_it_system',
      'sort' => 'title',
      'page' => [
        'limit' => -50,
        'offset' => 0,
      ],
    ],
  ]);
}
catch (ConnectException $connectException) {
  // Catch the specific connect exception to log it differently.
  $logger->alert($connectException->getMessage());
}
catch (ClientException $clientException) {
  // catches client only exceptions, like 404's
  $logger->warning($clientException->getMessage());
}
catch (ServerException $serverException) {
  // Catches 500 errors specifically.
  $logger->error($serverException->getMessage());
}
catch (Exception $exception) {
  // Catchall if we didn't want to handle exceptions differently.
  // output to console for run.
  dump($exception->getMessage());
  dump($exception);
  // proper logging.
  $logger->warning($exception->getMessage());
  throw $exception;
}
// Check if we got a response and it as 200.
if (isset($response) && $response->getStatusCode() === 200) {
  $responseBody = json_decode($response->getBody(), TRUE);
  dump($responseBody);
  return;
}
// Dealt with all possible, problems, but didn't get back a good response.
else {
  dump("Didn't get a good response.");
  return;
}
