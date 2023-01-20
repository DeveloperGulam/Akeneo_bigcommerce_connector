<?php

// require the Guzzle HTTP client library
require 'vendor/autoload.php';

use GuzzleHttp\Client;

// create a new Guzzle HTTP client
// $client = new Client();
$client = new Client([
    'verify' => false,
]);

// set the base URL for the Akeneo API
$baseUrl = 'https://zorang-e19b8cc18d.trial.akeneo.cloud';

// set the API credentials
$clientId = '9_1wsysfycdduswcc0cgwokkwkc48kwos8kw04wo48w0goo40kgw';
$secret = 'acnknsqf9uwo88w4kg4cgog8scco8ocokw0kcks8sgkkgsg48';
$username = 'zorangbigcommerceconnect_8674';
$password = '0ab963dac';
$base4encode = base64_encode($clientId . $secret);
// $aut_headers = [
//     'Authorization' => 'Basic ' . $base4encode,
//     'Content-Type' => 'application/json',
//     'Accept' => 'application/json',
// ];
// authenticate with the Akeneo API
// $response = $client->post($baseUrl . '/api/oauth/v1/token', [
//     'headers' => $aut_headers,
//     'form_params' => [
//         'grant_type' => 'password',
//         'username' => $username,
//         'password' => $password,
//     ],
// ]);
// $response = $client->post($baseUrl . '/api/oauth/v1/token', [
//     'form_params' => [
//         'grant_type' => 'refresh_token',
//         'client_id' => $clientId,
//         'client_secret' => $secret,
//     ],
// ]);

// decode the response to get the access token
// $responseData = json_decode($response->getBody(), true);
// echo $accessToken = $responseData['access_token'];die;

$accessToken = 'ZmU3MWRkMTRhNjY5MzdkNTg4MWU4MzI5M2IyZjNiOTk1MTNhN2EzM2QyNjIzZTFhZDhjYWNjMzk3MWE0Yzc5OQ';
// set the headers for the API request
$headers = [
    'Authorization' => 'Bearer ' . $accessToken,
    'Content-Type' => 'application/json',
    'Accept' => 'application/json',
];

// make a GET request to the products endpoint
$response = $client->get($baseUrl . '/api/rest/v1/products', [
    'headers' => $headers,
]);

// decode the response to get the list of products
$products = json_decode($response->getBody(), true);
echo "<pre>";
print_r($products);die;
// iterate through the products and print their attributes
foreach ($products as $product) {
    echo $product['identifier'] . "\n";
    echo "  Color: " . $product['color'] . "\n";
    echo "  Size: " . $product['size'] . "\n";
}

