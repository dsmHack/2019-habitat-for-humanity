<?php
/**
 *  Plugin Name: Volunteer Hours to Points Import
 */
echo "<h1>Hello world!</h1>";

add_action('admin_menu', 'hours_import_plugin_setup_menu');

function hours_import_plugin_setup_menu()
{
    add_management_page(
        'Volunteer Hours Import',
        'Volunteer Hours Import',
        // "import" - user must have permissions to import to access this page
        'import',
        'gdm-habitat-hours-import',
        'volunteer_hours_import_page_init'
    );
}

function volunteer_hours_import_page_init()
{
    echo '<h2>Import volunteer hours from a CSV file</h2>
            <form method="post" action="" enctype="multipart/form-data">
            <table class="form-table">
			<tr valign="top">
				<th scope="row"><label for="users_csv">CSV file</label></th>
				<td>
					<input type="file" id="users_csv" name="users_csv" value="" class="all-options" /><br />
				</td>
			</tr>
		</table>
		<p class="submit">
		 	<input type="submit" class="button-primary" value="Import" />
		</p>
	</form>';
}

class HoursImport_Plugin
{
    // holds singleton instance
    static $instance = false;


    public static function init()
    {
        // do something?
    }
}
