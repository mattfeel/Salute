<?php
$this->load->helper('actions');
$this->load->helper('table_result');

/**
 * @param
 *   $list_name		The name of the list
 *   $list			The result array from the database
 *   $status		Must be one of:
 * 
 * @note For now we are assuming that a controller asks a list where
 * ALL the tuples are of the same status.
 * */

echo '<h2>'.$list_name.'</h2>';

// Id of the table
$table['table-name'] = 'mydoctors-table';

// Names of the headers in the table
$table['th'] = array('Account Id', 'First Name', 'Last Name', 'Specialty', 'Select');

// Classes for columns (order matters)
$table['th_class'] = array('id_keeper', '', '', '', '');
$table['td_class'] = array('id_keeper', '', '', '', '');

// All the results from the database
$table['tuples'] = $list;

// Attributes to display
$table['attr'] = array('account_id', 'first_name', 'last_name', 'specialization', 'actions');


// The only actions available it to select an hcp using a radio button
for ($i = 0; $i < count($table['tuples']); $i++) {
	
	$table['tuples'][$i]['actions'] = '<input type="radio" name="hcp_id" value="'.$table['tuples'][$i]['account_id'].'" />';
}

echo '<form method="post" action="/refers/create_referral_do1" id="pick_hcp">';
view_table($table);

echo 	'<br />';
echo 	'<p>';
echo		'<input type="submit" name="submit" value="Submit" class="submit-button" />';
echo		'<input type="reset" />';
echo 	'</p>';
echo '</form>';

?>