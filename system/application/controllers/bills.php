<?php
/**
 * @file bills.php
 * @brief Controller to handle bills
 *
 * @defgroup ctr Controllers
 * @ingroup ctr
 * @{
 */

class Bills extends Controller {

	function __construct(){
		parent::Controller();
		$this->load->library('ui');
		$this->load->library('auth');
		$this->load->model('bills_model');
	}

	/**
	 * Default function call
	 * @return  redirects to all() function
	 * */ 
	function index(){
		$this->auth->check_logged_in();
		$this->all();
	}
  	
	/**
	 * Lists all bills of the current patient/hcp
	 * @return
	 *		if successful, hcp-list all bills you have issued to your patients, set side panel to hcp view
	 * 		if successful, patient- list all the bills HCPs have issued to you, set side panel to patient view
	 * 		if neither patient nor hcp, show error message
	 * 		if any database query errors, show error message
	**/
	function all() {
		
		$check = $this->auth->check(array(auth::CurrLOG));
		if ($check !== TRUE) return;

		if($this->auth->get_type() === 'patient')
			$results = $this->bills_model->view_all(array($this->auth->get_account_id(),$this->auth->get_type()));		
		else if($this->auth->get_type() === 'hcp')
			$results = $this->bills_model->view_all(array($this->auth->get_account_id(),$this->auth->get_type()));
		else{
			$this->ui->set_error('Server Error', 'server');	
			return;		
		}
		if($results === -1) {
			$this->ui->set_query_error(); 
			return;
		}
		$mainview = $this->load->view('mainpane/lists/bills',
					array('list_name' => 'My Bills', 'list' => $results, 'show_total' => TRUE) , TRUE);
				
		$this->ui->set(array($mainview));
	}
	
	/**
	 * Lists current bills of the current patient/hcp
	 * @return
	 *		if successful, hcp-list current bills you have issued to your patients, set side panel to hcp view
	 * 		if successful, patient- list current the bills HCPs have issued to you, set side panel to patient view
	 * 		if neither patient nor hcp, show error message
	 * 		if any database query errors, show error message
	**/
	function current(){
		$check = $this->auth->check(array(auth::CurrLOG));
		if ($check !== TRUE) return;
		
		if($this->auth->get_type() === 'patient')
			$results = $this->bills_model->view_current(array($this->auth->get_account_id(),$this->auth->get_type()));
		else if($this->auth->get_type() === 'hcp')
			$results = $this->bills_model->view_current(array($this->auth->get_account_id(),$this->auth->get_type()));
		else{
			$this->ui->set_error('Server Error', 'server');	
			return;		
		}
		if($results === -1){
			$this->ui->set_query_error(); 
			return;
		}	
		$mainview = $this->load->view('mainpane/lists/bills', array('list_name' => 'My Current Bills', 'list' => $results, 'show_total' => FALSE) , TRUE);
		$this->ui->set(array($mainview));
	}

	/**
	 * Lists past bills of the current patient/hcp
	 * @return
	 *		if successful, hcp-list past bills you have issued to your patients, set side panel to hcp view
	 * 		if successful, patient- list past  the bills HCPs have issued to you, set side panel to patient view
	 * 		if neither patient nor hcp, show error message
	 * 		if any database query errors, show error message
	**/
	function past() {
		$check = $this->auth->check(array(auth::CurrLOG));
		if ($check !== TRUE) return;
		
		if($this->auth->get_type() === 'patient')
			$results = $this->bills_model->view_past(array($this->auth->get_account_id(),$this->auth->get_type()));
		else if($this->auth->get_type() === 'hcp')
			$results = $this->bills_model->view_past(array($this->auth->get_account_id(),$this->auth->get_type()));
		else{
			$this->ui->set_error('Server Error', 'server'); 
			return;		
		}
		if($results === -1){
			$this->ui->set_query_error(); 
			return;
		}
		$mainview = $this->load->view('mainpane/lists/bills', array('list_name' => 'My Past Bills', 'list' => $results, 'show_total' => FALSE) , TRUE);
		$this->ui->set(array($mainview));
	}

	/**
	 * Allows HCPs to bill their patients
	 * @attention
	 * 		only HCPs can issue a bill
	 * 		only patient can receive a bill
	 * 		HCP must have connection to a patient to issue bill* 		
	 * @return
	 *		error if non-HCP is calling function
	 * 		error if non-patient is being issued bill or if patient doesnt exist
	 * 		error if HCP is not connected to patient
	 * 		error upon database query errors
	 * 		success: redirect to a form page for HCP to input bill data
	**/
	function issue($patient_id = NULL){
		$this->load->model('patient_model');
		$check = $this->auth->check(array(auth::CurrLOG, auth::CurrHCP, auth::PAT, $patient_id, auth::CurrCONN, $patient_id ));
		if ($check !== TRUE) return;
		
		$results = $this->patient_model->get_patient(array($patient_id));if ($results === -1){
				$this->ui->set_query_error(); 
				return;
		}
		$this->ui->set(array($this->load->view('mainpane/forms/issue_bill', array('results' => $results), TRUE)));
		
		/*
		
		$this->auth->check_logged_in();
		$this->load->model('patient_model');
		$this->load->model('connections_model');
		if( $this->auth->get_type() === 'hcp' ){
			$results = $this->patient_model->get_patient(array($patient_id));	
			if ($results === -1){
				$this->ui->set_query_error(); 
				return;
			} elseif( sizeof($results) < 1 ){
				$this->ui->set_error('This patient does not exist!'); 
				return;
			} else{
				$info = $this->connections_model->is_connected_with( $patient_id, $this->auth->get_account_id() );
				if( $info === -1 ){
					$this->ui->set_query_error(); 
					return;
				}
				elseif( $info === FALSE ){
					$this->ui->set_error('You are not connected with this patient', 'Permission Denied.'); 
					return;
				}
				else{
					$this->ui->set(array(
							$this->load->view('mainpane/forms/issue_bill', array('results' => $results), TRUE)
						));
				}
			}										
		} else{
			$this->ui->set_error('Server Error', 'server'); 
			return;		
		}
		*/
	}
	
	/**
	 * allows bill info to be inputted into DB
	 * @attention
	 * 		only HCPs can issue a bill
	 * 		only patient can receive a bill
	 * 		HCP must have connection to a patient to issue bill		
	 * @return
	 *		error if non-HCP is calling function
	 * 		error if non-patient is being issued bill or if patient doesnt exist
	 * 		error if HCP is not connected to patient
	 * 		error upon database query errors
	 * 		success: redirect to a form page for HCP to input bill data
	 * @bug
	 * 		incorrent input types will produce DB error
	 * @todo
	 * 		uploading of a receipt file
	 * 		parsing of input, input validation
	**/
	function issue_new_bill() {
		$patient_id = $this->input->post('patient_id');
		$check = $this->auth->check(array(auth::CurrLOG, auth::CurrHCP, auth::PAT, $patient_id, auth::CurrCONN, $patient_id ));
		if ($check !== TRUE) return;
		$amount = $this->input->post('amount');
		$description = $this->input->post('descryption');
		$due_date = $this->input->post('due_date');
		$results = $this->bills_model->issue_bill(array($patient_id,$this->auth->get_account_id(),$amount,$description,$due_date));
		if( $results === -1 ){
			$this->ui->set_query_error(); 
			return;
		} 
		else {
			$this->ui->set_message('Successfully issued the bill.', 'Confirmation'); 
			return;
		}
		/*
		//logged in, currhcp, PAT, currCONN, 
		$this->load->model('bills_model');
		$this->load->model('connections_model');
		$this->load->model('patient_model');
		if( $this->auth->get_type() === 'hcp' ){
			$results = $this->patient_model->is_patient(array($this->input->post('patient_id')));	
			if( $results === -1 ){
				$this->ui->set_query_error(); 
				return;
			}
			if( $results === FALSE ){
				$this->ui->set_error('Patient does not exist!');
				return;
			} else{			
				$info = $this->connections_model->is_connected_with( $this->input->post('patient_id'), $this->auth->get_account_id() );
				if( $info === -1 ){
					$this->ui->set_query_error(); 
					return;
				} else if( $info === FALSE ){
					$this->ui->set_error('You are not connected with this patient.', 'Permission Denied'); 
					return;
				} else{
					$patient_id = $this->input->post('patient_id');
					$amount = $this->input->post('amount');
					$description = $this->input->post('descryption');
					$due_date = $this->input->post('due_date');
					$results = $this->bills_model->issue_bill(array($patient_id,$this->auth->get_account_id(),$amount,$description,$due_date));
					if( $results === -1 ){
						$this->ui->set_query_error(); 
						return;
					} else {
						$this->ui->set_message('Successfully issued the bill.', 'Confirmation'); 
						return;
					}
				}
			}
		} else
			$this->ui->set_error('Server Error', 'server');
		*/
	}
	
	/**
	 * allows HCP to delete bill
	 * @attention
	 * 		allows users to delete a bill
	 * 		patient can delete bill if inactive( paid or the doctor deleted it )
	 * 		doctor can delete bill at any time
	 * @param
	 * 		bill_id
	 * @return
	 *		error if patient tries to delete active bill
	 * 		error if anyone tried to delete an already deleted bill
	 * 		error if unauthorized to delete the bill(not your bill)
	 * 		error upon database query errors
	 * 		success: redirect to a success page message
	**/
	function delete($bill_id = NULL) {
		$check = $this->auth->check(array(
			auth::CurrLOG,
			auth::BILL_DELC, $bill_id));
		if ($check !== TRUE) return;
		
		if( $this->auth->get_type() === 'hcp' ){
			$results = $this->bills_model->delete_bill( array($bill_id, 'hcp'));
		}
		else {
			$results = $this->bills_model->delete_bill( array($bill_id, 'patient'));
		}
		
		if ($results === 0){
			$this->ui->set_message('Successfully deleted the bill.', 'Confirmation');
			$this->all();
		}
		else {
			$this->ui->set_query_error(); 
			return;
		}
	}
	
	/**
	 * allows patient to pay bill
	 * @attention
	 * 		only patient can pay a bill
	 * 		no actual method to pay a bill has been implemented
	 * 		patient can only pay if bill is active( uncleared and doctor hasn't deleted
	 * 
	 * @param
	 * 		bill_id
	 * 
	 * @return
	 *		error if non-patient is calling function
	 * 		error if trying to pay an inactive bill
	 * 		error upon database query errors
	 * 		success: changes attribute 'cleared' of type bool from false to true
	**/
	function pay($bill_id = NULL) {
		//logged in, currPAT, bill exists, is my bill, is active 
		$check = $this->auth->check(array(auth::CurrLOG, auth::CurrPAT, auth::BILL_PAYC, $bill_id ));
		if ($check !== TRUE) return;
		$results = $this->bills_model->pay_bill( $bill_id );
		if( $results === 0 ){
			$this->ui->set_message('Successfully paid the bill.', 'Confirmation'); 
			$this->all();
		}else{
			$this->ui->set_query_error(); 
			return;
		}
	}
}
/** @} */
?>
