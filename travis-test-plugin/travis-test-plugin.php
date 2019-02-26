<?php
/**
 *  Plugin Name: Travis Test
 *  Author: Travis Sanderson (travis.sanderson@workiva.com)
 *
 */

class TravisTest_Plugin {
    public function __construct() {
        add_action('admin_menu', array( $this, 'handle_admin_menu'));
        add_action('plugins_loaded', array( $this, 'plugins_loaded')); 
    }

    // Adds this plugin to the Tools WordPress section.
    public function handle_admin_menu() {
        add_management_page(
            // Page title
            'Travis Test',
            // Menu title
            'Travis Test',
            // Capability requirements
            'import',
            // Menu slug ????
            'travis-test-import',
            // On success callback
            array($this, 'display_management_page')
        );
    }

    public function plugins_loaded() {
        // runs on every page after plugins have loaded
    }

    // Adds the HTML for changing the Salesforce credentials.
    public function display_management_page() {
        // TODO: This is probably not the right place to read out of the session
        session_start();
        if (isset($_SESSION['access_token'])) {
            update_option("ttp_access_token", $_SESSION['access_token']);
        }
        if (isset($_SESSION['refresh_token'])) {
            update_option("ttp_refresh_token", $_SESSION['refresh_token']);
        }

        echo "<a href='/wp-content/plugins/travis-test-plugin/oauth.php' target='_blank'>Re-authenticate</a><br/>";
        echo "Access token: " . get_option("ttp_access_token") . "<br/>";
        echo "Refresh token: " . get_option("ttp_refresh_token") . "<br/>";
    }

    // Fetches the hours for the given user for the given start date and end date.
    //
    // This will update the user's points in myCRED and the last time this was called.
    public function log_hours($user, $start_date, $end_date) {

        // todo
        // make GET request
        // send request
        // write result to myCRED

    }

    // Adds the given hours to the given user id in the myCRED table.
    public function write_mycred($user_id, $hours) {
        $mycred = mycred('points');
        $mycred->add_creds(
            'add_hours',
            $user_id,
            12.5 * $hours,
            'add volunteer hours'
        );
    }
}

$TravisTestPlugin = new TravisTest_Plugin();
