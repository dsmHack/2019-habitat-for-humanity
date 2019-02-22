<?php
/**
 *  Plugin Name: Volunteer Hours to Points Import
 *  Author: Travis Smith (travis.smith@workiva.com)
 *  
 */


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
					<input type="file" id="volunteer_hours_csv" name="volunteer_hours_csv" /><br />
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

    public function process_csv()
    {
        if (isset($_POST['sf-link-to-report'])) {
            $sf_url = $_POST['sf-link-to-report'];
            update_option('volunteer_hour_salesforce_report_link', $sf_url);
        }

        if (!empty($_FILES['volunteer_hours_csv']['tmp_name'])) {
            $filename = $_FILES['volunteer_hours_csv']['tmp_name'];
            $hours_csv_str = file_get_contents($filename);
            $hours_array = str_getcsv($hours_csv_str);

            HoursImport_Plugin::write_csv_to_my_creds($hours_array);

            HoursImport_Plugin::set_last_upload_date();

            echo "success";
        }
    }

    public static function write_csv_to_my_creds($hours_array) {
        $mycred = mycred('points');

        for ($i = 4; $i < count($hours_array); $i += 4) {
            // $i + 1 (timestamp for hours) and $i + 3 (blank) are ignored.
            $email = $hours_array[$i];
            $hours = $hours_array[$i + 2];

            if (!$mycred->exclude_user($email)) {
                $mycred->add_creds(
                    'csv_update',
                    HoursImport_Plugin::convert_email_to_id($email),
                    HoursImport_Plugin::convert_hours_to_points($hours),
                    'Points for hours worked'
                );
            }

            if ($i > 20) {
                break; // todo Remove once we have everything working.
            }
        }
    }

    public static function convert_email_to_id($email) {
        return $email; // todo Lookup the email and get its user id.
    }

    public static function convert_hours_to_points($hours) {
        return $hours; // todo Figure out the ratio of hours to points.
    }

    public static function set_last_upload_date()
    {
        $current_date = date('m/d/y h:i:s a');
        update_option('volunteer_hour_last_upload_date', $current_date . " " . date_default_timezone_get(), get, true);
    }
}


HoursImport_Plugin::init();
