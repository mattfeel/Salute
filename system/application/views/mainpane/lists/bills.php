<?php
$this->load->helper('actions');
$this->load->helper('table_result');

/**
 * @param
 *   $list_name		The name of the list
 *   $list			The result array from the database
 *   $status		Must be one of:
 * 					"connected", "pending_in", "pending_out" or 
 * 					"not_connected". If not, it assumes "not_connected"
 * 
 * @note For now we are assuming that a controller asks a list where
 * ALL the tuples are of the same status.
 * */


echo '<h2 class="table-header">'.$list_name.'</h2>';

// Id of the table
$table['table-name'] = 'mybills-table';

// Names of the headers in the table
$table['th'] = array( 'First Name', 'Last Name', 'Amount', 'Description', 'Due date', 'Date posted', 'Status', 'Actions');

// Classes for columns (order matters)
$table['th_class'] = array( '', '', '', '', '', '', '', '');
$table['td_class'] = array( '', '', '', '', '', '', '', '');

// All the results from the database
$table['tuples'] = $list;

// Attributes to display
$table['attr'] = array( 'first_name', 'last_name', 'amount', 'descryption', 'due_date','creation_date', 'cleared', 'actions');
$total = 0;

for ($i = 0; $i < count($table['tuples']); $i++) {
	
	if( $this->auth->get_type() === 'patient' ){
		if( $table['tuples'][$i]['cleared'] === 't' &&
			$table['tuples'][$i]['hcp_kept'] === 't' ){
				$table['tuples'][$i]['cleared'] = 'paid';
				$actions = array('delete-bill');			
		}
		else if( $table['tuples'][$i]['cleared'] === 't' &&
				$table['tuples'][$i]['hcp_kept'] === 'f' ){
					$table['tuples'][$i]['cleared'] = 'paid';
					$actions = array('delete-bill');
		}
		else if( $table['tuples'][$i]['cleared'] === 'f' &&
				$table['tuples'][$i]['hcp_kept'] === 't' ){
					$table['tuples'][$i]['cleared'] = 'unpaid';
					$total += $table['tuples'][$i]['amount'];
					$actions = array('pay-bill');
				
		}
		else if( $table['tuples'][$i]['cleared'] === 'f' &&
				$table['tuples'][$i]['hcp_kept'] === 'f' ){
					$table['tuples'][$i]['cleared'] = 'void';
					$actions = array('delete-bill');
		}
	}
	else{
		if( $table['tuples'][$i]['cleared'] === 't' ){
				$table['tuples'][$i]['cleared'] = 'paid';
				$actions = array('delete-bill');			
		}
		else if( $table['tuples'][$i]['cleared'] === 'f' ){
					$table['tuples'][$i]['cleared'] = 'unpaid';
					$total += $table['tuples'][$i]['amount'];
					$actions = array('delete-bill');
		}
	}
		
	$table['tuples'][$i]['actions'] = '<ul>';
	$table['tuples'][$i]['actions'] .= get_action_strings($actions, $list[$i]);
	$table['tuples'][$i]['actions'] .= '<ul>';
}	
view_table($table);
if( $show_total === TRUE ){
	echo '<br /><div style="font-size: 1.1em;"><b>Total due:</b> $'.$total.'</div>';
}
	





?>

