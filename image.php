<?php

$curl = curl_init();

curl_setopt_array($curl, array(
  CURLOPT_URL => 'https://zorang-e19b8cc18d.trial.akeneo.cloud/api/rest/v1/media-files/8/6/3/c/863c0470d4189d6573fe5f3882b4962e691f59aa_grey_tshirt.jpg/download',
  CURLOPT_RETURNTRANSFER => true,
  CURLOPT_ENCODING => '',
  CURLOPT_MAXREDIRS => 10,
  CURLOPT_TIMEOUT => 0,
  CURLOPT_FOLLOWLOCATION => true,
  CURLOPT_SSL_VERIFYPEER => false,
  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
  CURLOPT_CUSTOMREQUEST => 'GET',
  CURLOPT_HTTPHEADER => array(
    'Authorization: Bearer ZDNjNjExZmVhZWE3Njk1M2JlZmFhYjNhNWNlMGJmODA5MDEwMzRjMjJlYTBkNzZlOGE3YjhjMTRiMWFlNmU5NA'
  ),
));

$response = curl_exec($curl);

curl_close($curl);
$save_directory = 'product_images/';
file_put_contents($save_directory . 'image.jpg', $response);
echo "Image downloaded and saved to " . $save_directory;die;
// echo "<pre>";
//     print_r(json_decode($response, true));die;
// echo $response;
// echo '<img src="'.$response.'" />';
