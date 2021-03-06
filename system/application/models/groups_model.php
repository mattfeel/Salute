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
	 *	Is of the form: array(creator's account_id,name,type,description,privacy)
	 *	public_private = 0: public , 1: private 
	 * @return
	 * 		-1 if query error
	 * 		-2 if group_id dne
	 * 		group_id if all ok.
	 * */
	function create($inputs){
	
		$sql = "INSERT INTO groups (account_id,name,description,public_private,group_type)
				VALUES (?,?,?,?,?)";
		$query = $this->db->query($sql, $inputs);
		if ($this->db->trans_status() === FALSE)
			return -1;
	
		$sql = "select last_value from groups_group_id_seq";
		$query = $this->db->query($sql);
		if ($this->db->trans_status() === FALSE)
			return -1;
			
		if ($query->num_rows() > 0) {
			$res = $query->result_array();
			$group_id = $res[0]['last_value'];
		} else 
			return -2;
			
		return $group_id;
	}


	/**
	 * Edit a Group
	 * @param $inputs
	 *   Is of the form: array(name,description,public_private,group_type,group_id)
	 * @return
	 * 		-1 in case of error in a update
	 * 		1 otherwise
	 * */
	function edit_group($inputs){
		$sql = "UPDATE groups
				SET name = ?,  description = ?
				WHERE group_id = ?";
		$query = $this->db->query($sql, $inputs);
		if ($this->db->trans_status() === FALSE)
			return -1;
		return 0;
	}

	
	/**
	 * Delete a Group
	 * 
	 * @param $group_id
	 *   Is of the form: array($group_id)
	 * @return
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
	 * Delete an invitation
	 * 
	 * @param $group_id
	 *   Is of the form: invitee id, group id
	 * @return
	 *   1 if group was properly deleted
	 * */
	function delete_invitation($invitee, $gid) {
		$sql = "DELETE FROM invite
				WHERE group_id = ? AND invitee_id = ?";
		$query = $this->db->query($sql, array($gid, $invitee));
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
	 * 		-1 already error
	 * 		-2 user is already a member
	 * 		0 if all is well
	 * */
	function join($account_id,$group_id){
	
		// Check if the member exists
		$check = $this->is_member($account_id,$group_id);
		if ($check === -1) return -1;
		if ($check === TRUE) return -2;
	
		// Now, add the member
		$sql = "INSERT INTO is_in(account_id,group_id)
				VALUES(?,?)";
	
		$query = $this->db->query($sql, array($account_id,$group_id));
	
		if ($this->db->trans_status() === FALSE) return -1;
	
		return 0; // Success
	}
	
	/**
	 * Leave a Group
	 * 
	 * @param $account_id, $group_id
	 *   Is of the form: array($account_id,$group_id)
	 * @return
	 * 		-1 if query error
	 * 		-2 if membership doesn't exist
	 * 		0 if all is well
	 * */
	function remove($account_id,$group_id){
		
		// Check if the member exists
		$check = $this->is_member($account_id,$group_id);
		if ($check === -1) return -1;
		if ($check === FALSE) return -2;
	
		// Now, delete the member
		$sql = "DELETE FROM is_in
				WHERE account_id = ? AND group_id = ?";
	
		$query = $this->db->query($sql, array($account_id,$group_id));
	
		if ($this->db->trans_status() === FALSE) return -1;
	
		return 0; // Success
	}

	function get_group($group_id){
		
		$sql = "SELECT *
				FROM groups
				WHERE group_id = ? ";
		
		$query = $this->db->query($sql, array($group_id));
		
		if ($this->db->trans_status() === FALSE)
			return -1;
		
		// If found, return it
		if ($query->num_rows() > 0) {
			$array = $query->result_array();
			return $array[0];	
		}
		return NULL;
	}

	
	/**
	 * Lists all Groups ; excluding hcp only groups
	 * @param none
	 * @return
	 * 		-1 if query error
	 * 		empty array
	 * 		array of groups
	 * */
	function list_all_groups_for_pats(){
		
		$sql = "SELECT * FROM groups WHERE public_private = '0' AND group_type <> '1' ";

		$query = $this->db->query($sql);
		
		if ($this->db->trans_status() === FALSE) 
			return -1;
			
		if ($query->num_rows() > 0)
			return $query->result_array();
		
		return array();
	}
	/**
	 * Lists all Groups ; excluding patient only groups
	 * @param none
	 * @return
	 * 		-1 if query error
	 * 		empty array
	 * 		array of groups
	 * */
	function list_all_groups_for_hcps(){
		
		$sql = "SELECT * FROM groups WHERE public_private = '0' AND group_type <> '0' ";

		$query = $this->db->query($sql);
		
		if ($this->db->trans_status() === FALSE) 
			return -1;
			
		if ($query->num_rows() > 0)
			return $query->result_array();
		
		return array();
	}


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

		/*$sql = "SELECT g.* 
				FROM is_in i, groups g 
				WHERE g.account_id = ? AND i.account_id = ?";
		*/
		$sql = "SELECT * FROM groups WHERE group_id IN (SELECT group_id FROM is_in WHERE account_id = ?)";
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
	 * 	-2 in case of invalid type
	 *   Array of all members in group
	 *   empty array() if none
	 * */
	function list_members($group_id){
		
		$sql = "SELECT *
				FROM is_in 
				WHERE group_id = ? ";
								
		$query = $this->db->query($sql, array($group_id));
		
		if ($this->db->trans_status() === FALSE)
			return -1;
			
		if ($query->num_rows() > 0)
			return $query->result_array();
		
		return array();
	}

	function list_my_invites($invitee_id){
	
		$sql = "SELECT * FROM groups WHERE group_id IN (SELECT group_id FROM invite WHERE invitee_id = ? )";
		
		$query = $this->db->query($sql,array($invitee_id));
		
		if ($this->db->trans_status() === FALSE)
			return -1;
			
		if ($query->num_rows() > 0)
			return $query->result_array();
		
		return array();
	}
	function list_my_invites_top_five($invitee_id){
	
		$sql = "SELECT * FROM groups WHERE group_id IN (SELECT group_id FROM invite WHERE invitee_id = ? )
				ORDER BY name
				LIMIT 5";
		
		$query = $this->db->query($sql,array($invitee_id));
		
		if ($this->db->trans_status() === FALSE)
			return -1;
			
		if ($query->num_rows() > 0)
			return $query->result_array();
		
		return array();
	}
	
	function is_member($account_id,$group_id){
	
		$sql = "SELECT *
				FROM is_in 
				WHERE account_id = ? AND group_id = ?";

		$query = $this->db->query($sql, array($account_id,$group_id));
		
		if ($this->db->trans_status() === FALSE)
			return -1;
			
		return ($query->num_rows() > 0);
	}
	
	function can_delete($account_id,$group_id){
		$sql = "SELECT *
				FROM is_in 
				WHERE permissions = '3' AND account_id = ? AND group_id = ?";

		$query = $this->db->query($sql, array($account_id,$group_id));
		
		if ($this->db->trans_status() === FALSE)
			return -1;
			
		return ($query->num_rows() > 0);
	}
	
	function get_member($account_id,$group_id){
		
		$sql = "SELECT *
				FROM is_in
				WHERE account_id = ? AND group_id = ?";
		
		$query = $this->db->query($sql, array($account_id,$group_id));
		
		if ($this->db->trans_status() === FALSE)
			return -1;
		
		// If found, return it
		if ($query->num_rows() > 0) {
			$array = $query->result_array();
			return $array[0];	
		}
		return NULL;
	}
	
	function edit_member($account_id,$group_id,$permissions){
	
		$sql = "UPDATE is_in SET permissions = ? WHERE account_id = ? AND group_id = ?";
		
		$this->db->query($sql,array($permissions,$account_id,$group_id));
		
		if ($this->db->trans_status() === FALSE)
			return -1; // query error
		return 0;
	}
	
	function invite($inviter_id,$invitee_id,$group_id){
	
		$sql = "INSERT INTO invite(inviter_id, invitee_id,group_id) VALUES(?,?,?) ";
		
		$this->db->query($sql,array($inviter_id,$invitee_id,$group_id));
		if ($this->db->trans_status() === FALSE)
			return -1; // query error
		return 0;
	}

	function is_invited($invitee_id,$group_id){
		
		$sql = "SELECT *
				FROM invite
				WHERE invitee_id = ? AND group_id = ?";

		$query = $this->db->query($sql, array($invitee_id,$group_id));
		
		if ($this->db->trans_status() === FALSE)
			return -1;
			
		return ($query->num_rows() > 0);
	}
	
}
/**@}*/
?>
