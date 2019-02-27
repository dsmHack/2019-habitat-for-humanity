<?php

function display_management_page($plugin) {
    if ($_GET['reauth'] == "true") {
        $auth_url = "https://login.salesforce.com"
        . "/services/oauth2/authorize?response_type=code&client_id="
        . get_option("ttp_client_id") . "&redirect_uri=" . urlencode("https://store.gdmhabitat.org/wp-admin/tools.php?page=store-credit-calculator&oauth_callback=true");

        header('Location: ' . $auth_url);
    }

    if (session_status() == PHP_SESSION_NONE) {
        session_start();
    }

    if ($_GET['oauth_callback'] == "true") {
        require_once "oauth_callback.php";
        handle_oauth_callback();
    }

    if ($_GET['refresh_access_token'] == "true") {
        require_once "refresh_access_token.php";
        manual_refresh_access_token();
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
<div style="max-width: 50rem">
<h1>Store Credit Calculator</h1>

<p>This tool was initially created at the 2019 dsmHack.  The plugin will function automatically, it 
populates user's myCred balance by querying SalesForce for new volunteer hours.  This page is only 
for debugging issues or re-authenticating with SalesForce if necessary.</p>

<h2>Connected App Settings</h2>
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
<button type="submit">Update</button>
</form>

<h2>Debug Tools</h2>
<h4>Lookup a User's Last Fetched Date</h4>

<p>The plugin records when it last updated each user so when the user returns to the site it can 
calculate the difference and add any volunteer hours since last time.  This tool lets you look 
up the user's recorded last fetch time, as well as modify it (this should be done for testing only)</p>

<form action="" method="POST">
Email to look up: <input type="text" name="lookup_user_by_email" value="<?php echo $_POST['lookup_user_by_email']; ?>" /> <button type="submit">look up</button></br>
</form>
<?php
if (isset($_POST['lookup_user_by_email']) || isset($_POST['user_id_to_override'])) {
    if (isset($_POST['user_id_to_override'])) {
        update_user_meta($_POST['user_id_to_override'], 'last_fetch_date', $_POST['override_last_fetch']);
        $looked_up_user = get_user_by("id", $_POST['user_id_to_override']);
    } else {
        $looked_up_user = get_user_by("email", $_POST['lookup_user_by_email']);
    }
    if ($looked_up_user) {
        $last_fetched = $looked_up_user->get('last_fetch_date');
        if ($last_fetched == null) {
            $last_fetched = "<i>never</i>";
        }
    } else {
        $last_fetched = "<i>no user with that email was found</i>";
    }
    
    echo "User credits were last fetched at: " . $last_fetched . ".<br/>";
    ?>
<form action="" method="POST">
Manually override last fetch to: <input type="text" name="override_last_fetch" />
<input type="hidden" name="user_id_to_override" value="<?php echo $looked_up_user->id; ?>"/>
<button type="submit">OVERRIDE (this is not undoable!)</button>
</form>
    <?php
    if (isset($_POST['user_id_to_override'])) {
        echo "</br><b>Successfully overrode user's last_fetch_date.</b><br/>";
    }
}
?>

<h4>Query Volunteer Hours</h4>

<p>This tool lets you test the query capability by providing a user email, start and stop time.  
The tool will tell you how many volunteer hours were recorded for that email between those dates.</p>

<form action="" method="POST">
<table>
    <tr>
        <td>
            Email
        </td>
        <td>
            <input type="text" name="query_email" value="<?php echo $_POST['query_email']; ?>" />
        </td>
    </tr>
    <tr>
        <td>
            Start Date (e.g. 2019-01-31)
        </td>
        <td>
            <input type="text" name="query_start_date" value="<?php echo $_POST['query_start_date']; ?>" />
        </td>
    </tr>
    <tr>
        <td>End Date (e.g. 2019-02-28)</td>
        <td><input type="text" name="query_end_date" value="<?php echo $_POST['query_end_date']; ?>" />
    </tr>
</table>
<button type="submit">Query</button>
</form>
<?php if (isset($_POST['query_email'])) {?>
<p>
    User <?php echo $_POST['query_email']; ?> hours between <?php echo $_POST['query_start_date']; ?> and <?php echo $_POST['query_end_date']; ?> are <b><?php echo $plugin->fetch_hours($_POST['query_email'], $_POST['query_start_date'], $_POST['query_end_date']); ?></b>.
</p>
<?php } ?>

<h4>OAuth Configuration (read only)</h4>
<table>
    <tr>
        <td>Access token</td><td><input readonly value="<?php echo get_option("ttp_access_token"); ?>" /></td>
    </tr>
    <tr>
        <td>Refresh token</td><td><input readonly value="<?php echo get_option("ttp_refresh_token"); ?>" /></td>
    </tr>
    <tr>
        <td>Instance URL</td><td><input readonly value="<?php echo get_option("ttp_instance_url"); ?>" /></td>
    </tr>
</table>

<h4>Reauthenticate with SalesForce</h4>
<p>Click <a href='tools.php?page=store-credit-calculator&refresh_access_token=true'>here</a> to manually update the access_token using the existing refresh_token.</p>
<p>Click <a href='tools.php?page=store-credit-calculator&reauth=true'>here</a> to re-establish an OAuth connection with SalesForce.  This will create a new refresh_token and access_token.</p>

</div>
<?
}
?>
