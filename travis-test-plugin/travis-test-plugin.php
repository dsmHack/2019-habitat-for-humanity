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
        add_action('user_register', array(__CLASS__, 'update_user_hours'));
        add_action('wp_login', array(__CLASS__, 'update_user_hours'));
        add_action('wp_loaded', array(__CLASS__, 'update_user_hours'));
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
            // Menu slug (?page=travis-test-import)
            'travis-test-import',
            // On success callback
            array(__CLASS__, 'add_credential_settings')
        );
    }

    // Adds the HTML for changing the Salesforce credentials.
    public function add_credential_settings() {
        echo "hey";
        // todo Write the HTML for changing the Salesforce credentials.
        // todo Write the credentials to a table?
    }

    // Updates the user's hours by hitting a Salesforce api (see fetch_hours).
    // Those hours are written to the user's myCRED row.
    public static function update_user_hours() {
        $user = wp_get_current_user($login);
        if (is_null($user) or empty($user->id)) {
            // If the user object doesn't have an id, we can't proceed.
            return;
        }

        $previous_date = TravisTest_Plugin::fetch_previous_date($user->id);
        $current_date = TravisTest_Plugin::today();

        if ($previous_date == $current_date) {
            // Do not do anything if the user is already up to date.
            return;
        }

        $hours = TravisTest_Plugin::fetch_hours($user->user_email, $previous_date, $current_date);
        TravisTest_Plugin::write_mycred($user->id, $hours);
        update_user_meta($user->id, 'last_fetch_date', $current_date);
    }

    // Fetches the last known start date for the user.
    public static function fetch_previous_date($user_id) {
        $previous_date = get_user_meta($user_id, 'last_fetch_date', true);
        if (empty($previous_date) ) {
            // See the function [today] for the format used.
            $previous_date = '2019-01-01';
        }
        return $previous_date;
    }

    // Returns the hours the user has worked between the two dates.
    public static function fetch_hours($email, $start_date, $end_date) {
        // todo Make a GET request to salesforce and get the hours. Return them.
        return 1;
    }

    // Adds the given hours to the given user id in the myCRED table.
    public static function write_mycred($user_id, $hours) {
        $mycred = mycred('points');
        $mycred->add_creds(
            'add_hours',
            $user_id,
            12.5 * $hours,
            'add volunteer hours'
        );
    }

    // Returns today's date. It's important to use this when referencing
    // today's date as this format is what is persisted in the user meta.
    public static function today() {
        return date('Y-m-d');
    }
}

TravisTest_Plugin::init();