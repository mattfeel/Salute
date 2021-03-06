<?php
/**
 * @file patient_model.php
 * @brief Model to give access to the Patient table in the database
 *
 * @defgroup mdl Models
 * @ingroup mdl
 * @{
 */

class Patient_model extends Model {

	function __construct() {
		parent::Model();
		$this->load->database();
	}
	
	
	/**
	 * Gets all HCPs from the database, without any filter
	 * 
	 * @param $inputs
	 *   Is of the form: Does not take anything in
	 * @attention
	 *   THIS FUNCTION MUST BE REMOVED AND SUBSTITUTED BY A SEARCH
	 *   FUNCTION, THAT GETS DOCTORS THAT HAVE SPECIFIC VALUES
	 * @return
	 *  -1 in case of error in a query
	 *   Array with all the existing hcps in the database
	 *   empty array() if there are not any hcps in the database
	 * */
	function get_patients() {
		$sql = "SELECT * 
			FROM patient_account P, accounts A
			WHERE P.account_id = A.account_id and A.private = false AND A.account_id != ? ORDER BY P.first_name, P.last_name ASC";
		$query = $this->db->query($sql, array($this->auth->get_account_id()));
		
		if ($this->db->trans_status() === FALSE)
			return -1;
		if ($query->num_rows() > 0)
			return $query->result_array();
		
		return array();	
	}

	/**
	 * Checks if patient exists
	 * 
	 * @params $inputs
	 *  Is of the form: array(account_id)
	 * @return
	 * -1 in case of error in a query
	 * True if account_id is a patient
	 * False if not
	 * */
	function is_patient($inputs){
		
		$sql = "SELECT A.account_id
			FROM patient_account A
			WHERE A.account_id = ?";
		$query = $this->db->query($sql, $inputs);
		if ($this->db->trans_status() === FALSE)
			return -1;	
		if( $query->num_rows() > 0){
			return TRUE;
		}
		return false;

	}

	/**
	 * Gets patient info
	 * 
	 * @params $inputs
	 *  Is of the form: array(account_id)
	 * @return
	 * -1 in case of error in a query
	 * array with patient info if patient exists
	 * empty array if not
	 * */
	function get_patient($inputs) {
	
		$sql = "SELECT *
			FROM patient_account P
			WHERE P.account_id = ?";
		$query = $this->db->query($sql, $inputs);
		if ($this->db->trans_status() === FALSE)
			return -1;	
		if( $query->num_rows() > 0){
			$result = $query->result_array();
			return $result;
		}
		return array();
	}
	
	

	/**
	 * adds the patient to the Patient_Account
	 * 
	 * @params $inputs
	 *  Is of the form: array(account_id, first_name, last_name, middle_name, ssn, dbo, sex, tel_number, fax_number, address)
	 * @return
	 * -1 in case of error in a insert
	 * 1 otherwise
	 * */
	function register($inputs) {
	
		//$data = array( 'account_id' => $inputs[0], 'first_name' => $inputs[1], 'last_name' => $inputs[2], 'middle_name' => $inputs[3], 'ssn' => $inputs[4],  
			       //'dob' => $inputs[5],'sex' => $inputs[6], 'tel_number' => $inputs[7], 'fax_number' => $inputs[8], 'address' => $inputs[9]);
		//$this->db->insert('Patient_Account', $data);
		
		$sql = "INSERT INTO patient_account (account_id, first_name, last_name, middle_name, ssn, dob, sex, tel_number, fax_number, address)
			VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
		$query = $this->db->query($sql, $inputs);
		if ($this->db->trans_status() === FALSE)
			return -1;	
		return 1;
	}
	
	
	/**
	 * updates the Patient_Account table
	 * 
	 * @params $inputs
	 *  Is of the form: array(first_name, middle_name, last_name, dob, sex, tel_number, fax_number, address, account_id)
	 * @return
	 * -1 in case of error in a update
	 * 1 otherwise
	 * @bug will overwrite all info if anything left blank.
	 * */
	function update_personal_info($inputs){
	
		//$data = array( 'account_id' => $inputs[0], 'first_name' => $inputs[1], 'last_name' => $inputs[2], 'middle_name' => $inputs[3], 'tel_number' => $inputs[4], 
		               //'fax_number' => $inputs[5], 'address' => $inputs[6]);
		//$this->db->update('Patient_Account', $data, array('account_id' => $inputs[0]));
		
		$sql = "UPDATE patient_account
				SET first_name = ?,  middle_name = ?, last_name = ?, dob = ?, sex = ?, tel_number = ?, fax_number = ?, address = ?
				WHERE account_id = ?";
			
		/*$sql = "UPDATE patient_account
				SET first_name LIKE'%?', last_name LIKE'%?', middle_name LIKE'%?', 
					tel_number LIKE'%?', fax_number LIKE'%?', address LIKE'%?' 
				WHERE account_id = ?"; */
		$query = $this->db->query($sql, $inputs);
		if ($this->db->trans_status() === FALSE)
			return -1;
		return 1;
	}
}
/** @} */
?>
