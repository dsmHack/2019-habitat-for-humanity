<?php
/**
 *  Plugin Name: Volunteer Hours to Points Import
 */


class HoursImport_Plugin
{
    public static function init()
    {
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
            <table class="form-table">
            <form method="post" action="" enctype="multipart/form-data">
            <table class="form-table">
			<tr valign="top">
				<th scope="row"><label for="volunteer_hours_csv">CSV file</label></th>
				<td>
					<input type="file" id="volunteer_hours_csv" name="volunteer_hours_csv" /><br />
				</td>
			</tr>
        </table>' .
            '<input type="submit" class="button-primary" value="Import" name="Submit" />
	</form>';
    }

    public function process_csv()
    {
        if (!empty($_FILES['volunteer_hours_csv']['tmp_name'])) {
            // Setup settings variables
            $filename = $_FILES['users_csv']['tmp_name'];
            $results = self::import_csv($filename);
            echo $results;
        }
    }

    public function import_csv($filename)
    {
        //do import logic here
        return "super successful";
    }
}


HoursImport_Plugin::init();
