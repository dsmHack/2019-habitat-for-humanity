<?php
/**
 *  Plugin Name: Travis Test
 *  Author: Travis Sanderson (travis.sanderson@workiva.com)
 *
 */

class TravisTest_Plugin {
    // Registers the WordPress callbacks.
    public static function init() {
        add_action('admin_menu', array(__CLASS__, 'handle_admin_menu'));
        // add_action('user_register', 'handle_register_user');
        // add_action('wp_login', 'handle_user_login');
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
            array(__CLASS__, 'add_credential_settings')
        );
    }

    // Adds the HTML for changing the Salesforce credentials.
    public function add_credential_settings() {
        echo "hey!";
        // todo Write the HTML for changing the Salesforce credentials.
        // todo Write the credentials to a table?
    }

    // Callback for when a user registers.
    public function handle_register_user() {
        $user = wp_get_current_user();

        // todo
        // requests all hours this email has worked since today's date
        // records the the date on this user's metainfo

        echo '
        <script>
            console.log("user registered: ' . $user->user_email . '");
        </script>
        ';
    }

    public function handle_user_login() {
        $user = wp_get_current_user();

        // todo
        // requests all hours this email has worked since this user's metainfo

        echo '
        <script>
            console.log("user logged in: ' . $user->user_email . '");
        </script>
        ';
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

TravisTest_Plugin::init();