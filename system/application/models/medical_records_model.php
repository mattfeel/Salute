<?php
/**
 * @file medical_records_model.php
 * @brief Model to give access to the medical_records table in the database
 *
 * @defgroup mdl Models
 * @ingroup mdl
 * @{
 */

class Medical_records_model extends Model {
	
	function __construct() {
		parent::Model();
		$this->load->database();
	}
	
	/**
	 * Retreives top 5 records for a patient along with the patient information and the account information of the person that 
	 * upload the medical record
	 * 
	 * @params $inputs
	 *  Is of the form: array(patient_id)
	 * @return
	 * -1 in case of error in a query
	 * emtpy array if patient has no medical records
	 * */
	function list_recent_five($inputs){
		$sql = "(SELECT M.*, P.first_name AS pat_first_name, P.last_name AS pat_last_name, A.*, H.first_name, H.last_name
			FROM patient_account P, accounts A, hcp_account H, medical_record M
			WHERE M.patient_id = ? AND M.patient_id = P.account_id AND M.account_id = A.account_id AND A.account_id = H.account_id)
			UNION
			(SELECT M.*, P.first_name AS pat_first_name, P.last_name AS pat_last_name, A.*, P2.first_name , P2.last_name
			FROM patient_account P, patient_account P2, accounts A, medical_record M
			WHERE M.patient_id = ? AND M.patient_id = P.account_id AND M.account_id = A.account_id AND A.account_id = P2.account_id)";
				
		$query = $this->db->query($sql, array($inputs[0], $inputs[0]));
		
		if ($this->db->trans_status() === FALSE)
			return -1;
		if( $query->num_rows() > 0) {
			return $query->result_array();
		}
		return array();
	}
	
		/**
	 * Retreives all records for a patient along with the patient information and the account information of the person that 
	 * upload the medical record
	 * 
	 * @params $inputs
	 *  Is of the form: array(patient_id)
	 * @return
	 * -1 in case of error in a query
	 * emtpy array if patient has no medical records
	 * */
	function list_my_records($inputs){
		$sql = "
			SELECT * FROM (
			(SELECT M.*, P.first_name AS pat_first_name, P.last_name AS pat_last_name, A.*, H.first_name, H.last_name
			FROM patient_account P, accounts A, hcp_account H, medical_record M
			WHERE M.patient_id = ? AND M.patient_id = P.account_id AND M.account_id = A.account_id AND A.account_id = H.account_id)
			UNION
			(SELECT M.*, P.first_name AS pat_first_name, P.last_name AS pat_last_name, A.*, P2.first_name , P2.last_name
			FROM patient_account P, patient_account P2, accounts A, medical_record M
			WHERE M.patient_id = ? AND M.patient_id = P.account_id AND M.account_id = A.account_id AND A.account_id = P2.account_id)) AS T
			ORDER BY T.date_created DESC
			";
		
		$query = $this->db->query($sql, array($inputs[0], $inputs[0]));
		
		if ($this->db->trans_status() === FALSE)
			return -1;
		if( $query->num_rows() > 0) {
			return $query->result_array();
		}
		return array();
	}
	
	/**
	 * States wheather a medical record belongs to a patient
	 * 
	 * @param $inputs
	 *   Is of the form: array(patient_id, medical_rec_id)
	 * @return
	 *   Returns -1 if error in query
	 *   Returns TRUE if it is, FALSE otherwise
	 * *
	 function belongs_to($inputs){
	 
	 	$sql = "SELECT *
	 		FROM medical_record M
	 		WHERE M.patient_id = ? AND M.medical_rec_id = ?";
		$query = $this->db->query($sql, $inputs);
		if ($this->db->trans_status() === FALSE)
			return -1;	
		if( $query->num_rows() > 0){
			return true;
		}
		return false;
	 }*/
	
	
	/**
	 * List all information regarding a medical record
	 * 
	 * @param $inputs
	 *   Is of the form: array(medical_rec_id)
	 * @return
	 *   -1 if error in querry
	 *   empty array if medical record doesn't exist
	 *   Array with all the infomation regarding medical record with id medical_rec_id
	 * */
	function get_medicalrecord($inputs) {
	
		$sql = "SELECT *
			FROM medical_record M
			WHERE M.medical_rec_id = ?";
		$query = $this->db->query($sql, $inputs);
		if ($this->db->trans_status() === FALSE)
			return -1;	
		if( $query->num_rows() > 0){
			return $query->result_array();
		}
		return array();
	}
	
	/**
	 * Add a medical record.  If a doctor is adding it, it automatically gives him permission to view it
	 * 
	 * @param $inputs
	 *   Is of the form: array(patient_id, account_id (person adding), issue, suplementary_info, file_path)
	 * @return
	 *   -1 if error in insert
	 *   -2 cannot retrieve medical record id of last inserted tuple
	 *    <the new id inserted> if medical record was properly inserted
	 * */
	function add_medical_record($inputs){
	
		//$data = array( 'patient_id', 'account_id', 'issue', 'suplementary_info', 'description', 'file_path');
		//$this->db->insert('Medical_Records', $data);	
		
		$sql = "INSERT INTO medical_record (patient_id, account_id, issue, suplementary_info, description, file_name)
			VALUES (?, ?, ?, ?, ?, ?)";
		$query = $this->db->query($sql, $inputs);
		if ($this->db->trans_status() === FALSE)
			return -1;
		
		//get the last medical_rec_id inserted 
		$sql = "select last_value from medical_record_medical_rec_id_seq";
		$query = $this->db->query($sql);
		if ($this->db->trans_status() === FALSE)
			return -1;
		
		if ($query->num_rows() > 0) {
			$res = $query->result_array();
			return $res[0]['last_value'];
		} else {
			return -2;
		}
	}
	
	/**
	 * Delete a medical record
	 * 
	 * @param $inputs
	 *   Is of the form: array(medical_rec_id)
	 * @return
	 *   -1 if error in delete
	 *   1 if medical record was properly deleted
	 * */
	function delete_medical_record($inputs){
		$sql = "DELETE FROM medical_record
			WHERE medical_rec_id = ?";
		$query = $this->db->query($sql, $inputs);
		if ($this->db->trans_status() === FALSE)
			return -1;
		return 1;
	}
	
	/**
	 * Allows an account_id permission to see medical record
	 * 
	 * @param $inputs
	 *   Is of the form: array(medical_rec_id, account_id)
	 * @return
	 *  -1 if query error
	 *  -2 if account already has permission for that med rec
	 *  1 if permission inserted
	 * 
	 * @note We are forcing permission only to other doctors
	 * */
	function allow_permission($inputs){
		//$data = array( 'medical_rec_id' => $inputs[0], 'account_id' => $inputs[1]);
		//$this->db->insert( 'Permissions', $data);
		
		//$sql = "SELECT *
		//	FROM accounts
		//	WHERE account_id = ?";
		//$query = $this->db->query($sql, array($inputs[1]));
		//if ($this->db->trans_status() === FALSE)
		//	return -1;
		//if ($query->num_rows() <= 0) {
		//	return -2;
		//}
		
		// Check if account has permission
		$is_allowed = $this->is_account_allowed($inputs);
		if ($is_allowed === TRUE) return -2;
		
		$sql = "INSERT INTO permission (medical_rec_id, account_id, date_created)
			VALUES (?, ?, current_date)";
		$query = $this->db->query($sql, $inputs);
		if ($this->db->trans_status() === FALSE)
			return -1;
		return 1;
	
	}
	
	/**
	 * Give permissions to all $from_id's medical records to $to_id
	 * 
	 * @note This function ignores query errors
	 * */
	function give_all_ones_medrec_to($from_id, $to_id) {
		$sql = "SELECT M.*
			FROM medical_record M
			WHERE M.patient_id = ?";
		$query = $this->db->query($sql, array((int)$from_id));
		if ($query->num_rows() <= 0) return;
		$meds = $query->result_array();
		foreach ($meds as $m) {
			$sql = "INSERT INTO permission (medical_rec_id, account_id, date_created)
				VALUES (?, ?, current_date)";
			$query = $this->db->query($sql, array((int)$m['medical_rec_id'], (int)$to_id));
		}
	}
	
	/**
	 * Add permission to see $mid to all accounts connected with $pid
	 * with a high level connection (1 or 3).
	 * */
	function add_permissions_to_high_levels($mid, $pid) {
		$sql = "SELECT * FROM connections WHERE sender_id = ? AND (sender_level = '1' OR sender_level = '3')";
		$query = $this->db->query($sql, array((int)$pid));
		if ($this->db->trans_status() === FALSE)
			return -1;
		if ($query->num_rows() > 0) {
			$res = $query->result_array();
			foreach ($res as $r) {
				$this->allow_permission(array($mid, $r['receiver_id']));
			}
		}
		
		$sql = "SELECT * FROM connections WHERE receiver_id = ? AND (receiver_id = '1' OR receiver_id = '3')";
		$query = $this->db->query($sql, array((int)$pid));
		if ($this->db->trans_status() === FALSE)
			return -1;
		if ($query->num_rows() > 0) {
			$res = $query->result_array();
			foreach ($res as $r) {
				$this->allow_permission(array($mid, $r['sender_id']));
			}
		}
	}
	
	/**
	 * Removes the ability for an account_id to view a medical record
	 * 
	 * @param $inputs
	 *   Is of the form: array(medical_rec_id, account_id)
	 * @return
	 *   -1 if query error
	 *   -2 if there no permission to delete (account already has no perm)
	 *   1 if it deletes it
	 * */
	function delete_permission($inputs) {
		//$this->db->delete('Permissions', array( 'medical_rec_id' => $inputs[0], 'account_id' => $inputs[1]);
		
		// Check if account has permission
		$is_allowed = $this->is_account_allowed($inputs);
		if ($is_allowed === FALSE) return -2;
		
		$sql = "DELETE FROM permission
			WHERE medical_rec_id = ? AND account_id = ?";
		$query = $this->db->query($sql, $inputs);
		if ($this->db->trans_status() === FALSE)
			return -1;
		return 1;
	}
	
	/**
	 * Determines if a medical record can be viewed by another account
	 * 
	 * @param $inputs
	 *   Is of the form: array(medical_rec_id, account_id)
	 * @return
	 *   True or Flase
	 *   -1 if error in query
	 * */
	function is_account_allowed($inputs){
		$sql = "SELECT *
			FROM permission P
			WHERE P.medical_rec_id = ? AND P.account_id = ?";
		$query = $this->db->query($sql, $inputs);
		if ($this->db->trans_status() === FALSE)
			return -1;
		if ($query->num_rows() > 0)
			return TRUE;
		return FALSE;
	}
	
	
	
	/**
	 * Get all the HCPs that have access to this
	 * medical record.
	 * 
	 * @param $mid
	 * 		A medical record id
	 * @return
	 * 		-1 in case of query error
	 * 		an array with all the allowed accounts
	 * 		an empty array if no allowed account is present
	 * */
	function get_medrec_allowed_hcps($mid) {
		$sql = "
			SELECT H.*, M.medical_rec_id
			FROM   medical_record M, hcp_account H, permission P, accounts A
			WHERE  M.medical_rec_id = ? AND M.medical_rec_id = P.medical_rec_id
				   AND P.account_id = A.account_id AND A.account_id = H.account_id";
		$query = $this->db->query($sql, $mid);
		if ($this->db->trans_status() === FALSE)
			return -1;
		if ($query->num_rows() <= 0)
			return array();
		return $query->result_array();

	}
	
	/**
	 * Get all the patients that have access to this
	 * medical record.
	 * 
	 * @param $mid
	 * 		A medical record id
	 * @return
	 * 		-1 in case of query error
	 * 		an array with all the allowed accounts
	 * 		an empty array if no allowed account is present
	 * */
	function get_medrec_allowed_patients($mid) {
		$sql = "
			SELECT H.*, M.medical_rec_id
			FROM   medical_record M, patient_account H, permission P, accounts A
			WHERE  M.medical_rec_id = ? AND M.medical_rec_id = P.medical_rec_id
				   AND P.account_id = A.account_id AND A.account_id = H.account_id";
		$query = $this->db->query($sql, $mid);
		if ($this->db->trans_status() === FALSE)
			return -1;
		if ($query->num_rows() <= 0)
			return array();
		return $query->result_array();

	}
	
	
	/**
	 * Gives a hcp permission to view all of a patients medical records
	 * 
	 * @param $inputs
	 *   Is of the form: array(patient_id, hcp_id)
	 * @return
	 *   -1 if error in query
	 *   -3 if the connection does not exist
	 *    0 if everything goes fine
	 * */
	function set_all_allowed($inputs){
		
		//first see if the connection exists
		$sql = "SELECT * 
			FROM p_d_connection
			WEHRE patient_id = ? AND hcp_id = ?";
		$query = $this->db->query( $sql, $inputs);
		if ($this->db->trans_status() === FALSE)
			return -1;
		if ( $query->num_rows() < 1 )
			return -3;
			
		//find all of that patients medical records
		$sql = "SELECT M.medical_rec_id
			FROM medical_records M
			WHERE M.patient_id = ?";
		$query = $this->db->query($sql, array($inputs[0]));
		if ($this->db->trans_status() === FALSE)
			return -1;	
	
		$result = $query->result_array();
		
		//allow the hcp to view those medical records
		foreach( $result as $value){
			
			$sql = "INSERT INTO permission (medical_rec_id, account_id)
				VALUES (?, ?)";
			$query = $this->db->query($sql, array($value, $inputs[1]));
			
			if ($this->db->trans_status() === FALSE)
				return -1;
		}
		
		return 0;
	}
	
	
	/**
	 * Does not allow a hcp to view any of the patients medical records
	 * 
	 * @param $inputs
	 *   Is of the form: array(patient_id, hcp_id)
	 * @return
	 *   -1 if error in query
	 *    0 if everything goes fine
	 * */
	function set_all_hidden($inputs){
		
		//find all of that patients medical records
		$sql = "SELECT M.medical_rec_id
			FROM medical_records M
			WHERE M.patient_id = ?";
		$query = $this->db->query($sql, array($inputs[0]));
		if ($this->db->trans_status() === FALSE)
			return -1;
		
		$result = $query->result_array();
		
		//delete that tuple from the permission table	
		foreach ( $query as $value) {
			
			$sql = "DELETE FROM permission	
				WHERE medical_rec_id = ? AND account_id = ?";
			$query = $this->db->query($sql, array($value, $inputs[1]));
			
			if ($this->db->trans_status() === FALSE)
				return -1;
		}
		
		return 0;
	}
	
	
	/**
	 * Lists all of the medical records a hcp can view for a certain patient
	 * 
	 * @param $inputs
	 *   Is of the form: array(patient_id, hcp_id)
	 * @return
	 *   -1 if error in query
	 *    Array with all of the medical records
	 * 
	 * @bug It seems that gives back medical records to doctors even if
	 * they are not connected... see my e-mail about it.... (matteo)
	 * */
	 function get_patient_records($inputs) {
		$sql = "(SELECT M.*, P.first_name AS pat_first_name, P.last_name AS pat_last_name, A.*, H.first_name, H.last_name, PR.type
			FROM patient_account P, accounts A, hcp_account H, medical_record M, permission PR
			WHERE M.patient_id = ? AND P.account_id = M.patient_id AND M.medical_rec_id = PR.medical_rec_id 
			  AND PR.account_id = ? AND M.account_id = A.account_id AND A.account_id = H.account_id)
			UNION
			(SELECT M.*, P.first_name AS pat_first_name, P.last_name AS pat_last_name, A.*, P2.first_name, P2.last_name, PR.type
			FROM patient_account P, accounts A, hcp_account H, medical_record M, permission PR, patient_account P2
			WHERE M.patient_id = ? AND M.patient_id = P.account_id AND M.medical_rec_id = PR.medical_rec_id
			  AND PR.account_id = ? AND M.account_id = A.account_id AND A.account_id = P2.account_id)";
			
		$query = $this->db->query($sql, array($inputs[0], $inputs[1], $inputs[0], $inputs[1]));
		
		if ($this->db->trans_status() === FALSE)
			return -1;
		
		if ($query->num_rows() > 0)
			return $query->result_array();

		return array();	
	}
	
		/**
	 * Lists all of the medical records a hcp can view for a certain patient
	 * 
	 * @param $inputs
	 *   Is of the form: array(patient_id, hcp_id)
	 * @return
	 *   -1 if error in query
	 *    Array with all of the medical records
	 * 
	 * @bug It seems that gives back medical records to doctors even if
	 * they are not connected... see my e-mail about it.... (matteo)
	 * */
	 function get_patient_records_top_five($inputs) {
		$sql = "((SELECT M.*, P.first_name AS pat_first_name, P.last_name AS pat_last_name, A.*, H.first_name, H.last_name, PR.type
			FROM patient_account P, accounts A, hcp_account H, medical_record M, permission PR
			WHERE M.patient_id = ? AND P.account_id = M.patient_id AND M.medical_rec_id = PR.medical_rec_id 
			  AND PR.account_id = ? AND M.account_id = A.account_id AND A.account_id = H.account_id)
			UNION
			(SELECT M.*, P.first_name AS pat_first_name, P.last_name AS pat_last_name, A.*, P2.first_name, P2.last_name, PR.type
			FROM patient_account P, accounts A, hcp_account H, medical_record M, permission PR, patient_account P2
			WHERE M.patient_id = ? AND M.patient_id = P.account_id AND M.medical_rec_id = PR.medical_rec_id
			  AND PR.account_id = ? AND M.account_id = A.account_id AND A.account_id = P2.account_id))
			  ORDER BY date_created desc
			  LIMIT 5";
			
		$query = $this->db->query($sql, array($inputs[0], $inputs[1], $inputs[0], $inputs[1]));
		
		if ($this->db->trans_status() === FALSE)
			return -1;
		
		if ($query->num_rows() > 0)
			return $query->result_array();

		return array();	
	}
	/*
	function get_records_top_five($inputs) {
		$sql = "((SELECT M.*, P.first_name AS pat_first_name, P.last_name AS pat_last_name, A.*, H.first_name, H.last_name, PR.type
			FROM patient_account P, accounts A, hcp_account H, medical_record M, permission PR
			WHERE M.patient_id = ? AND P.account_id = M.patient_id AND M.medical_rec_id = PR.medical_rec_id 
			  AND PR.account_id = ? AND M.account_id = A.account_id AND A.account_id = H.account_id)
			UNION
			(SELECT M.*, P.first_name AS pat_first_name, P.last_name AS pat_last_name, A.*, P2.first_name, P2.last_name, PR.type
			FROM patient_account P, accounts A, hcp_account H, medical_record M, permission PR, patient_account P2
			WHERE M.patient_id = ? AND M.patient_id = P.account_id AND M.medical_rec_id = PR.medical_rec_id
			  AND PR.account_id = ? AND M.account_id = A.account_id AND A.account_id = P2.account_id))
			  ORDER BY date_created desc
			  LIMIT 5";
			
		$query = $this->db->query($sql, array($inputs[0], $inputs[1]));
		
		if ($this->db->trans_status() === FALSE)
			return -1;
		
		if ($query->num_rows() > 0)
			return $query->result_array();

		return array();	
	}*/
}
/** @} */
?>
