<?php

// Require the Composer autoloader
require 'vendor/autoload.php';

// Use the Guzzle HTTP client library
use GuzzleHttp\Client;

// Set the base URL for the Akeneo API
$baseUrl = 'https://zorang-e19b8cc18d.trial.akeneo.cloud/';

// Set the product code for which you want to retrieve the variants
$productCode = 'shirt001';

// Create a new HTTP client
$client = new Client([
    'base_uri' => $baseUrl,
    'verify' => false
]);

// Set the API key for authentication
$apiKey = 'MDE1YWQ5NWZmNjFiYzEyODRhZTQyMGZlZjhmMjAyODkxYjg0N2NkMjBjOTdhZWVlOTI4YzZhYWE3MzQ2NGFkZA';

// Set the headers for the API request
$headers = [
    'Content-Type' => 'application/json',
    'Authorization' => 'Bearer ' . $apiKey
];

// Send a GET request to the API endpoint to retrieve the products
// $response = $client->request('GET', '/api/rest/v1/products', [
//     'headers' => $headers
// ]);

// Send a GET request to the API endpoint to retrieve the variants
$response = $client->request('GET', '/api/rest/v1/products/' . $productCode . '/variants', [
    'headers' => $headers
]);

// Get the response body as a JSON object
$products = json_decode($response->getBody());
echo "<pre>";
print_r($products);die;
// Loop through the products and print out their details
foreach ($products as $product) {
    echo $product->code . ': ' . $product->label . "\n";
}
