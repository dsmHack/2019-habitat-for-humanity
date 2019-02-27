<?php
function manual_refresh_access_token() {
    echo "Updating access token...<br/>";

    refresh_access_token();

    header( 'Location: /wp-admin/tools.php?page=store-credit-calculator' );
    exit();
}

function refresh_access_token() {
    $token_url = "https://login.salesforce.com/services/oauth2/token";

    $params = "grant_type=refresh_token"
        . "&client_id=" . get_option("ttp_client_id")
        . "&client_secret=" . get_option("ttp_client_secret")
        . "&refresh_token=" . get_option("ttp_refresh_token");

    $curl = curl_init($token_url);
    curl_setopt($curl, CURLOPT_HEADER, false);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($curl, CURLOPT_POST, true);
    curl_setopt($curl, CURLOPT_POSTFIELDS, $params);

    $json_response = curl_exec($curl);

    $status = curl_getinfo($curl, CURLINFO_HTTP_CODE);

    if ( $status != 200 ) {
        die("Error: call to token URL $token_url failed with status $status, response $json_response, curl_error " . curl_error($curl) . ", curl_errno " . curl_errno($curl));
    }

    curl_close($curl);

    $response = json_decode($json_response, true);

    $access_token = $response['access_token'];
    $instance_url = $response['instance_url'];

    if (!isset($access_token) || $access_token == "") {
        die("Error - access token missing from response!");
    }

    if (!isset($instance_url) || $instance_url == "") {
        die("Error - instance URL missing from response!");
    }

    $_SESSION['access_token'] = $access_token;
    $_SESSION['instance_url'] = $instance_url;
}
?>
