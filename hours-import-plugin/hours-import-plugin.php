<?php
/**
 *  Plugin Name: Volunteer Hours to Points Import
 *  Author: Travis Smith (travis.smith@workiva.com)
 *
 */

// To get this import to work properly, first cd into plugin's directory and run the following:
//
// php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
// php -r "if (hash_file('sha384', 'composer-setup.php') === '48e3236262b34d30969dca3c37281b3b4bbe3221bda826ac6a9a62d6444cdb0dcd0615698a5cbe587c3f0fe57a54d8f5') { echo 'Installer verified'; } else { echo 'Installer corrupt'; unlink('composer-setup.php'); } echo PHP_EOL;"
// php composer-setup.php
// php -r "unlink('composer-setup.php');"
// php composer.phar require automattic/woocommerce
//
// More information about composer can be found at https://getcomposer.org/download/
require __DIR__ . '/vendor/autoload.php';
use Automattic\WooCommerce\Client;

// WooCommerce -> Settings -> Advanced -> REST API -> Permissions: Read/Write -> Generate API key
// These keys were generated from my local environment, and will need to be modified for the correct environment
const CONSUMER_KEY = "ck_401ee38fe27f4419a6840b3c0b248444fbbcd770";
const CONSUMER_SECRET_KEY = "cs_d56b356d4e9d46eb1576fdc5609e1394214ae130";

const URL = "http://localhost:8888/wordpress/";

class User
{
    // The user's first name for the WooCommerce customer account.
    //
    // This is pulled from the Salesforce CSV.
    public $first_name = '';

    // The user's last name for the WooCommerce customer account.
    //
    // This is pulled from the Salesforce CSV.
    public $last_name = '';

    // The sum of hours worked (pulled from the Salesforce CSV).
    public $hours_worked = 0;

    // The user's email address for the WooCommerce customer account.
    //
    // This is pulled from the Salesforce CSV.
    public $email = '';

    // The user's id.
    //
    // This is pulled from WooCommerce. A map of ID to Points is stored in MyCred
    public $id = '';

    // Gets the payload for creating a WooCommerce account as a batch response.
    function get_woocommerce_create_payload() {
        return [
            'email' => $this->email,
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
        ];
    }
}

class HoursImport_Plugin
{
    public static function init()
    {
        date_default_timezone_set('America/Chicago');
        add_action('admin_menu', array(__CLASS__, 'hours_import_plugin_setup_menu'));
        add_action('init', array(__CLASS__, 'process_csv'));
    }

    public function hours_import_plugin_setup_menu()
    {
        add_management_page(
            'Volunteer Hours Import',
            'Volunteer Hours Import',
            // "import" - user must have permissions to import to access this page
            'import',
            'gdm-habitat-hours-import',
            array(__CLASS__, 'volunteer_hours_import_page_init')
        );
    }

    public function volunteer_hours_import_page_init()
    {
        echo '<h2>Import volunteer hours from a CSV file</h2>

            <h3>Instructions:</h3>
            <ol>
                <li>Click link to Salesforce report below</li>
                <li>Set beginning date of Salesforce report filter to the "Last upload performed at" date below</li>
                <li>Set end date of Salesforce report filter to the current day</li>
                <li>Click "Export Details" on Salesforce report. Set "Export File Format" to "Comma Delimited (.csv)"</li>
                <li>Click "Export"</li>
                <li>Browse for the CSV file that downloaded from Salesforce and click "Import"</li>
            </ol>

            <table class="form-table">
            <table class="form-table">
            <tr>
                <th scope="row">
                    Last upload performed at:
                </th>
                <td>' .
            get_option('volunteer_hour_last_upload_date') .
            '</td>
            </tr>
            <tr>
                <th scope="row">
                    Link to Salesforce Report:
                </th>
                <td>
                <a href="' . get_option('volunteer_hour_salesforce_report_link') . '">' .
            get_option('volunteer_hour_salesforce_report_link') .
            '</a>
                </br>
                <a onclick="showSFLinkEditForm()">(edit)</a>
                <div id="SFLinkEditForm" style="display: none">
                <form method="post" action="" enctype="application/x-www-form-urlencoded">
                    <input type="text" id="sf-link" name="sf-link-to-report" value="https://..... "/>    
                    <input type="submit" class="button" value="Update" name="Update" />
                </form>
                </div>
                </td>
            </tr>
            <form method="post" action="" enctype="multipart/form-data">
			<tr valign="top">
				<th scope="row"><label for="volunteer_hours_csv">CSV file</label></th>
                <td>
					<input type="file" accept=".csv" id="volunteer_hours_csv" name="volunteer_hours_csv" /><br />
				</td>
            </tr>
            <tr>
                <td>
                    <input type="submit" class="button-primary" value="Import" name="Submit" />      
                </td>
            </tr>
            </form>
            </table>' .
            '
    ';

        echo '
        <script>
        function showSFLinkEditForm() {
            var x = document.getElementById("SFLinkEditForm");
            if (x.style.display === "none") {
              x.style.display = "block";
            } else {
              x.style.display = "none";
            }
          }
        </script>
    
    ';
    }

    public function process_csv() {
        if (isset($_POST['sf-link-to-report'])) {
            $sf_url = $_POST['sf-link-to-report'];
            update_option('volunteer_hour_salesforce_report_link', $sf_url);
        }

        if (!empty($_FILES['volunteer_hours_csv']['tmp_name'])) {
            $filename = $_FILES['volunteer_hours_csv']['tmp_name'];

            $csv_file = fopen($filename, 'r');
            $csv_emails_to_users = HoursImport_Plugin::flatten_csv($csv_file); // returns {email:User}
            $woocommerce_emails_to_ids = HoursImport_Plugin::get_all_woo_commerce_users_ids(); // returns {emails:user_id}

            $emails_to_new_users = [];
            foreach ($csv_emails_to_users as $csv_email => $csv_user) {
                if (!array_key_exists($csv_email, $woocommerce_emails_to_ids)) {
                    $emails_to_new_users[$csv_email] = $csv_user;
                }
            }

            $new_woocommerce_emails_to_ids = HoursImport_Plugin::batch_create_woo_commerce_users($emails_to_new_users);

            foreach ($csv_emails_to_users as $csv_email => $csv_user) {
                $csv_user->id = $woocommerce_emails_to_ids[$csv_email];
                if (is_null($csv_user->id)) {
                    $csv_user->id = $new_woocommerce_emails_to_ids[$csv_email];
                }
            }

            HoursImport_Plugin::add_hours_to_mycred($csv_emails_to_users);
            HoursImport_Plugin::set_last_upload_date();

            echo "success";
        }
    }

    // Takes in a csv_file and returns map of emails to User objects containing the number of hours worked.
    public static function flatten_csv($csv_file) {
        $emails_to_users = [];
        $first_name_header_field_index = 1;
        $last_name_header_field_index = 2;
        $hours_worked_index = 7;
        $email_index = 8;

        $header_row = true;

        while (($row = fgetcsv($csv_file, 1000, ",")) !== FALSE) {
            if ($header_row) {
                $header_row = false;
                continue;
            }
            $email = preg_replace('/\s+/' , '', $row[$email_index]);
            // If the user doesn't have an email, we can't create an account for them
            if ($email == "") {
                continue;
            }

            // If we've already parsed this user, just add their hours worked.
            $hours_worked = (float) preg_replace('/\s+/' , '', $row[$hours_worked_index]);
            if (array_key_exists($email, $emails_to_users)) {
                $user = $emails_to_users[$email];
                $user->hours_worked = $user->hours_worked + $hours_worked;
                continue;
            }

            // If they're a new user, add them to the array
            $last_name = preg_replace('/\s+/' , '', $row[$last_name_header_field_index]);
            $first_name = preg_replace('/\s+/' , '', $row[$first_name_header_field_index]);

            $user = new User();
            $user->email = $email;
            $user->hours_worked = $hours_worked;
            $user->last_name = $last_name;
            $user->first_name = $first_name;
            $emails_to_users[$email] = $user;
        }

        return $emails_to_users;
    }

    public static function add_hours_to_mycred($emails_to_users) {
        // This creates a myCRED instance for the "points" currency type.
        $mycred = mycred('points');

        foreach ($emails_to_users as $user) {
            $mycred->add_creds(
                'csv_import',
                $user->id,
                HoursImport_Plugin::convert_hours_to_points($user->hours_worked),
                'import volunteer hours'
            );
        }
    }

    // Writes the csv to the myCREDs table.
    public static function write_csv_to_my_creds($hours_array, $emailsToUserIDs) {
        // This creates a myCRED instance for the "points" currency type.
        $mycred = mycred('points');

        // We then iterate over our CSV (that has been converted to an array).
        // The first four elements in the array are headers, so we can safely ignore them.
        for ($i = 4; $i < count($hours_array); $i += 4) {
            // $i + 1 (timestamp for hours) and $i + 3 (blank) are ignored.
            $email = preg_replace('/\s+/', '', $hours_array[$i]); // strip whitespace on email (newlines from parse)
            $hours = $hours_array[$i + 2];

            // Convert the email to a WooCommerce user id.
            // If the conversion fails, we can create a new account for the user.
            // This gives us the user's new id, allowing the process to continue.
            $user_id = $emailsToUserIDs[$email];
            if (is_null($user_id)) {
                $user_id = HoursImport_Plugin::create_woo_commerce_user_id($email);
                $emailsToUserIDs[$email] = $user_id;
            }

            // todo Determine if add_creds is still okay to call even if $user_id isn't in the table.
            $mycred->add_creds(
                'csv_import',
                $user_id,
                HoursImport_Plugin::convert_hours_to_points($hours),
                'import volunteer hours'
            );
        }
    }

    // Creates a customer account in the WooCommerce table with the given email.
    // The id is then returned.
    public static function create_woo_commerce_user_id($email) {
        // https://woocommerce.github.io/woocommerce-rest-api-docs/#create-a-customer
        $woocommerce = new Client(
            URL,
            CONSUMER_KEY,
            CONSUMER_SECRET_KEY,
            [
                'wp_api' => true,
                'version' => 'wc/v3'
            ]
        );
        $data = [
            'email' => $email,
        ];
        $response = $woocommerce->post('customers', $data);
        return $response->id;
    }

    // Creates a batch of WooCommerce accounts when given a map of emails to User objects.
    //
    // Returns a map of new user emails to ids
    public static function batch_create_woo_commerce_users($emails_to_new_users) {
        $emails_to_user_ids = [];
        foreach ($emails_to_new_users as $email => $new_user) {
            $user_id = HoursImport_Plugin::create_woo_commerce_user_id($email);
            $new_user->id = $user_id;
            //$emails_to_user_ids[$email] = $user_id
        }
        return $emails_to_user_ids;
    }

    // Converts the given hours to "points", where "points" is the currency users
    // see when attempting to purchase products.
    public static function convert_hours_to_points($hours) {
        return 12.5 * $hours;
    }

    // Gets a map of customer emails to user ids from the WooCommerce table
    public static function get_all_woo_commerce_users_ids() {
        $customers = get_users([
            'role' => 'Customer',
        ]);

        foreach ($customers as $customer) {
            $email = $customer->user_email;
            $user_id = $customer->id;
            $emailsToUserIDs[$email] = $user_id;
        }
        return $emailsToUserIDs;
    }

    // Writes the current date to the database.
    public static function set_last_upload_date() {
        $current_date = date('m/d/y h:i:s a');
        update_option('volunteer_hour_last_upload_date', $current_date . " " . date_default_timezone_get(), get, true);
    }
}


HoursImport_Plugin::init();
