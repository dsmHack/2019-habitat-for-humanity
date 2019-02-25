<?php
session_start();

function get_hours($instance_url, $access_token) {
    $url = "$instance_url/services/data/v35.0/analytics/reports/00O61000004ZUOQ?includeDetails=true";

    $curl = curl_init($url);
    curl_setopt($curl, CURLOPT_HEADER, false);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($curl, CURLOPT_HTTPHEADER,
            array("Authorization: OAuth $access_token"));

    $json_response = curl_exec($curl);
    curl_close($curl);

    $response = json_decode($json_response, true);

    var_dump($response);
    $total_size = $response['totalSize'];

    echo "$total_size record(s) returned<br/><br/>";
    foreach ((array) $response['records'] as $record) {
        echo $record['Id'] . ", " . $record['FirstName'] . ", " . $record['TotalHours'] . "<br/>";
    }
    echo "<br/>";
}

// this is just an example of executing a SELECT query against SalesForce
function show_accounts($instance_url, $access_token) {
    $query = "SELECT Name, Id from Account LIMIT 100";
    $url = "$instance_url/services/data/v20.0/query?q=" . urlencode($query);

    $curl = curl_init($url);
    curl_setopt($curl, CURLOPT_HEADER, false);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($curl, CURLOPT_HTTPHEADER,
            array("Authorization: OAuth $access_token"));

    $json_response = curl_exec($curl);
    curl_close($curl);

    $response = json_decode($json_response, true);

    $total_size = $response['totalSize'];

    echo "$total_size record(s) returned<br/><br/>";
    foreach ((array) $response['records'] as $record) {
        echo $record['Id'] . ", " . $record['Name'] . "<br/>";
    }
    echo "<br/>";
}

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
        <title>REST/OAuth Example</title>
    </head>
    <body>
            <ul>
            <?php
            $access_token = $_SESSION['access_token'];
            $instance_url = $_SESSION['instance_url'];
            $refresh_token = $_SESSION['refresh_token'];

            echo "<li>Access token: " . $access_token . "</li>";
            echo "<li>Instance URL: " . $instance_url . "</li>"; 
            echo "<li>Refresh token: " . $refresh_token . "</li></ul>";

            if (!isset($access_token) || $access_token == "") {
                echo("no token");
                die("Error - access token missing from session!");
            }

            if (!isset($instance_url) || $instance_url == "") {
                echo("no instance");
                die("Error - instance URL missing from session!");
            }

            get_hours($instance_url, $access_token);
            ?>
    </body>
</html>