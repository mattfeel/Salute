<?php
/**
 * @file groups_model.php
 * @brief Model to manage groups
 *
 * @defgroup mdl Models
 * @ingroup mdl
 * @{
 */

class Groups_model extends Model {
	function __construct() {
		parent::Model();
		$this->load->database();
	}

	/**
	 * Create a Group
	 * @param $inputs
	 *   Is of the form: array(creator's account_id,name,type,description,privacy)
	 * @return
	 * 		-1 if query error
	 * 		0 if insert was successful
	 * @todo
	 * 		make it return the group tuple to print to screen?
	 * */
	function create($inputs){
	
		$sql = "INSERT INTO groups (account_id,name,description,public_private,group_type)
				VALUES (?,?,?,?,?)";
		$query = $this->db->query($sql, $inputs);
		if ($this->db->trans_status() === FALSE)
			return -1;
		return 0;
		/*	
		$sql = "SELECT group_id
			FROM groups
			WHERE ? ;
		$query = $this->db->query($sql, $inputs['?']);
		
		if ($this->db->trans_status() === FALSE)
			return -1;
			
		return $query->result_array();	*/
	}


	/**
	 * Edit a Group
	 * @param $inputs
	 *   Is of the form: array(name,description,public_private,group_type,group_id)
	 * @return
	 * 		-1 in case of error in a update
	 * 		1 otherwise
	 * */
	function edit($inputs){
		$sql = "UPDATE groups
				SET name = ?,  description = ?, public_private = ?, group_type = ?
				WHERE group_id = ?";
		$query = $this->db->query($sql, $inputs);
		if ($this->db->trans_status() === FALSE)
			return -1;
		return 1;
	}

	
	/**
	 * Delete a Group
	 * 
	 * @param $group_id
	 *   Is of the form: array($group_id)
	 * @return
	 *   -1 if error in delete
	 *   1 if group was properly deleted
	 * */
	function delete($group_id){
		$sql = "DELETE FROM groups
				WHERE group_id = ?";
		$query = $this->db->query($sql, $group_id);
		if ($this->db->trans_status() === FALSE)
			return -1;
		return 1;
	}

	
	/**
	 * Join a Group
	 * 
	 * @param $account_id, $group_id
	 *   Is of the form: array($account_id,$group_id)
	 * @return
	 * */
	function join($account_id,$group_id){}
	
	/**
	 * Leave a Group
	 * 
	 * @param $account_id, $group_id
	 *   Is of the form: array($account_id,$group_id)
	 * @return
	 * */	
	function leave($account_id,$group_id){}
	
	/**
	 * Lists all Groups
	 * @param
	 * @return
	 * */
	function list_all_groups(){}

	/**
	 * Lists all my groups
	 * 
	 * @param $account_id
	 *   Is of the form: array($account_id)
	 * @return
	 *  -1 in case of error in a query
	 *   Array of all groups account_id is a member of 
	 *   empty array() if none
	 * */
	function list_my_groups($account_id) {

		$sql = "SELECT g.*
				FROM groups g
				WHERE g.account_id = ?";

		$query = $this->db->query($sql, array($account_id));
		
		if ($this->db->trans_status() === FALSE)
			return -1;
			
		if ($query->num_rows() > 0)
			return $query->result_array();
		
		return array();
	}
	
	/**
	 * Lists all members in a group
	 * 
	 * @param $group_id
	 *   Is of the form: array($group_id)
	 * @return
	 *  -1 in case of error in a query
	 *   Array of all members in group
	 *   empty array() if none
	 * */
	function list_members($group_id){}
	
	function is_member($account_id,$group_id){}
	
	function get_member($account_id,$group_id){}
	
	function update_member($account_id,$group_id,$permission_number){}
	
}
/**@}*/
?>
