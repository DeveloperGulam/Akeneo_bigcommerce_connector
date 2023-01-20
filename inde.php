<?php

     // Let's create a `callback.php` file
    
     require_once __DIR__ . '/vendor/autoload.php';
    
     $oauthClientId = '9_1wsysfycdduswcc0cgwokkwkc48kwos8kw04wo48w0goo40kgw';
     $oauthClientSecret = 'acnknsqf9uwo88w4kg4cgog8scco8ocokw0kcks8sgkkgsg48';
    //  $generateTokenUrl = '/connect/apps/v1/oauth2/token';
     
    //  session_start();
     
    //  // We check if the received state is the same as in the session, for security.
    //  $sessionState = $_SESSION['oauth2_state'] ?? '';
    //  $state = $_GET['state'] ?? '';
    //  if (empty($state) || $state !== $sessionState) {
    //      exit('Invalid state');
    //  }
     
    //  $authorizationCode = $_GET['code'] ?? '';
    //  if (empty($authorizationCode)) {
    //      exit('Missing authorization code');
    //  }
     
    //  $pimUrl = $_SESSION['pim_url'] ?? '';
    //  if (empty($pimUrl)) {
    //      exit('No PIM url in session');
    //  }
     
    //  // Generate code for token request
    //  $codeIdentifier = bin2hex(random_bytes(30));
    //  $codeChallenge = hash('sha256', $codeIdentifier . $oauthClientSecret);
     
    //  // Build form data to post
    //  $accessTokenRequestPayload = [
    //      'client_id' => $oauthClientId,
    //      'code_identifier' => $codeIdentifier,
    //      'code_challenge' => $codeChallenge,
    //      'code' => $authorizationCode,
    //      'grant_type' => 'authorization_code',
    //  ];
     
    //  // If you haven't set your client yet, please install Guzzle by following the official documentation:
    //  // https://docs.guzzlephp.org/en/stable/overview.html#installation
    //  $client = new GuzzleHttp\Client(['base_uri' => $pimUrl]);
     
    //  // Make an authenticated call to the API
    //  $accessTokenUrl = $pimUrl . $generateTokenUrl;
    //  $response = $client->post($accessTokenUrl, ['form_params' => $accessTokenRequestPayload]);
     
    //  // Convert json response to array
    //  $contents = json_decode($response->getBody()->getContents(), true);
     
    //  var_export($contents);
    
    // require_once __DIR__ . '/vendor/autoload.php';
    
    $clientBuilder = new \Akeneo\Pim\ApiClient\AkeneoPimClientBuilder('https://zorang-e19b8cc18d.trial.akeneo.cloud/');
    $client = $clientBuilder->buildAuthenticatedByPassword($oauthClientId, $oauthClientSecret, 'zorangbigcommerceconnect_8674', '0ab963dac');
    // $client->getToken();
    // $client->getRefreshToken();

    // $product = $client->getProductUuidApi()->get('1cf1d135-26fe-4ac2-9cf5-cdb69ada0547');
    // echo $product['uuid'];
    // $category = $client->getCategoryApi()->get('master');
    
    // $products = $client->getProductApi()->all(50);
    // foreach ($products as $product) {
    //     // do your stuff here
    //     echo $product['uuid'];
    // }
    // $product = $client->getProductApi()->get('top');
    // echo "<pre>";
    // print_r($product);
    try {
        $client->getProductUuidApi()->create('1cf1d135-26fe-4ac2-9cf5-cdb69ada0547');
    } catch (UnprocessableEntityHttpException $e) {
        // do your stuff with the exception
        $requestBody = $e->getRequest()->getBody();
        $responseBody = $e->getResponse()->getBody();
        $httpCode = $e->getCode(); 
        $errorMessage = $e->getMessage(); 
        $errors = $e->getResponseErrors();
        foreach ($e->getResponseErrors() as $error) {
            // do your stuff with the error
            echo $error['property'];
            echo $error['message'];
        }
    }
    // echo $product['identifier']; // display "top"