<?php

function display_management_page() {
    if ($_GET['reauth'] == "true") {
        $auth_url = "https://login.salesforce.com"
        . "/services/oauth2/authorize?response_type=code&client_id="
        . get_option("ttp_client_id") . "&redirect_uri=" . urlencode("https://store.gdmhabitat.org/wp-admin/tools.php?page=travis-test-import&oauth_callback=true");

        header('Location: ' . $auth_url);
    }

    if (session_status() == PHP_SESSION_NONE) {
        session_start();
    }

    if ($_GET['oauth_callback'] == "true") {
        require_once "oauth_callback.php";
        handle_oauth_callback();
    }

    if (isset($_SESSION['access_token'])) {
        update_option("ttp_access_token", $_SESSION['access_token']);
    }
    if (isset($_SESSION['refresh_token'])) {
        update_option("ttp_refresh_token", $_SESSION['refresh_token']);
    }
    if (isset($_SESSION['instance_url'])) {
        update_option("ttp_instance_url", $_SESSION['instance_url']);
    }
    if (isset($_POST['ttp_client_id'])) {
        update_option("ttp_client_id", $_POST["ttp_client_id"]);
    }
    if (isset($_POST['ttp_client_secret'])) {
        update_option("ttp_client_secret", $_POST["ttp_client_secret"]);
    }
?>

<h1>SalesForce + myCRED integration details</h1>
<form action="" method="POST">
<table>
    <tr>
        <td>Client ID</td>
        <td><input type="text" name="ttp_client_id" value="<?php echo get_option("ttp_client_id"); ?>"/></td>
    </tr>
    <tr>
        <td>Client Secret</td>
        <td><input type="text" name="ttp_client_secret" value="<?php echo get_option("ttp_client_secret"); ?>"/></td>
    </tr>
</table>
<button type="submit">Save</button>
</form>

<table>
    <tr>
        <td>Access token</td><td><?php echo get_option("ttp_access_token"); ?></td>
    </tr>
    <tr>
        <td>Refresh token</td><td><?php echo get_option("ttp_refresh_token"); ?></td>
    </tr>
    <tr>
        <td>Instance URL</td><td><?php echo get_option("ttp_instance_url"); ?></td>
    </tr>
</table>

<p>Click <a href='tools.php?page=travis-test-import&reauth=true'>here</a> to re-authenticate with SalesForce.</p>
    
<?
}
?>
