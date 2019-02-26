<?php
/**
 *  Plugin Name: Volunteer Hours to Points Import
 *  Author: Travis Smith (travis.smith@workiva.com)
 *
 */

class HoursImport_Plugin {
    // Registers the WordPress callbacks.
    public static function init() {
        add_action('admin_menu', array(__CLASS__, 'handle_admin_menu'));
        add_action('wp_login', array(__CLASS__, 'handle_user_login'));
    }

    // Adds this plugin to the Tools WordPress section.
    public function handle_admin_menu() {
        add_management_page(
            // Page title
            'Volunteer Hours Watcher',
            // Menu title
            'Volunteer Hours Watcher',
            // Capability requirements
            'import',
            // Menu slug (?page=gdm-habitat-hours-import)
            'gdm-habitat-hours-import',
            // On success callback
            array(__CLASS__, 'add_credential_settings')
        );
    }

    // Adds the HTML for changing the Salesforce credentials.
    public function add_credential_settings() {
        // todo Write the HTML for changing the Salesforce credentials.
        // todo Write the credentials to a table?
    }

    // On user login, we fetch the current user with the login parameter.
    // The user's hours will be fetched by Salesforce and added to mycred.
    public static function handle_user_login($login) {
        $user = get_userdatabylogin($login);
        $start_date = HoursImport_Plugin::fetch_start_date($user);
        $end_date = date('Y-M-D');

        $hours = HoursImport_Plugin::fetch_hours($user->user_email, $start_date, $end_date);
        HoursImport_Plugin::write_mycred($user->id, $hours);
    }

    // Fetches the last known start date for the user.
    public static function fetch_start_date($user_id) {
        $start_date = get_user_meta($user_id, 'last_fetch_date', true);
        if (is_null($start_date)) {
            $start_date = '2019-Jan-01';
        }
        return $start_date;
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

        update_user_meta($user_id, 'last_fetch_date', today());
    }

    public static function today() {
        return date('Y-M-D');
    }
}

HoursImport_Plugin::init();
