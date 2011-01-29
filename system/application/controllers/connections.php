<?php
class Connections extends Controller {

	function __construct(){
		parent::Controller();
		$this->load->library('ajax');
		$this->load->library('auth');
	}


	function index() {
		show_error('Direct access to this resource is forbidden', 500);
		return;
	}

	function list_doctors()	{
		$this->auth->check_logged_in();		

		$this->load->model('connections_model');
		
		if ($this->auth->get_type() === 'patient') {
			$results = $this->connections_model->list_my_doctors($this->auth->get_account_id()); 

			$this->ajax->view(array(
				$this->load->view('mainpane/mydoctors', array('hcp_list' => $results) , TRUE),
				$this->load->view('sidepane/patient-profile', '', TRUE)
			));
		} 

		else {
			$results = $this->connections_model->list_my_colleagues($this->auth->get_account_id()); 
			$this->ajax->view(array(
				$this->load->view('mainpane/mydoctors', array('hcp_list' => $results) , TRUE),
				$this->load->view('sidepane/doctor-profile', '', TRUE)
			));	
		}
	}

	function list_patients()
	{
		$this->auth->check_logged_in();
		
		if ($this->auth->get_type() !== 'doctor'){
			show_error($this->load->view('errors/not_hcp', '', TRUE));
			return;
		}

		$this->load->model('connections_model');
		$results = $this->connections_model->list_my_patients($this->auth->get_account_id()); 

		/*$results = array(
			array('first_name' => 'Mario', 'last_name' => 'Rossi', 'specialty' => 'Murderer'),
			array('first_name' => 'Matteo', 'last_name' => 'Brucato', 'specialty' => 'Surgeon')
		); /** @todo Remove this and uncomment above, when Model is available */
		

		$this->ajax->view(array(
			$this->load->view('mainpane/mypatients', array('pat_list' => $results) , TRUE),
			$this->load->view('sidepane/doctor-profile', '', TRUE)
		));
	}

	// Request a connection ( aka request to be friends with a doctor )
	function request($id = NULL)
	{
		if ( $id == NULL ){
			show_error('No doctor_id specified.', 500);
			return;
		}

		$this->auth->check_logged_in();

		$this->load->model('hcp_model');		
		$results = $this->hcp_model->get_doctor(array($id));
		
		$this->load->model('connections_model');
		/*@todo: How to get Timestamp? */		
		if ( $this->auth->get_type() === 'doctor' ){		
			$check = $this->connections_model->add_doctor_doctor(array(
										$this->auth->get_account_id(),
										$id
										));
		}

		else if ( $this->auth->get_type() === 'patient' ){		
			$check = $this->connections_model->add_patient_doctor(array(
										$this->auth->get_account_id(),
										$id
										));
		}

		else {
			show_error('Unknown Error.', 500);
			return;
		}
		
		if (! $check) {
			show_error('Connection already requested');
			return;
		}

		$this->load->library('email');
		
		$config['mailtype'] = 'html';
		$this->email->initialize($config);
		$this->email->from($this->auth->get_email());
//		$this->email->to($results['email']);
		$this->email->to('nadahashem@gmail.com');
		$this->email->subject('New Connection Request');
		
		
		// @todo: Fix the body -- need to put a link to 'accept' connection, connections/accept/requester_id
		$this->email->message(
			'You have a new connection request from '.
			$this->auth->get_first_name().' '.$this->auth->get_last_name().
			'. Click <a href="https://'.$_SERVER['SERVER_NAME'].'/connections/accept/'.$this->auth->get_account_id().'/'.$id.'">here</a> to accept.');

		// @todo: Pop-up : are you sure you want to send?
		$this->email->send();
	}

	// Accept request ; Note: only Doctors do this 
	function accept($requester_id, $my_id) // this needs to take in an account id as an argument. 
	{
		$this->auth->check_logged_in();
		
		if ($this->auth->get_account_id() != $my_id) {
			show_error('You are not the receiver for this request');
			return;
		}
		
		$this->load->model('connections_model');
		$this->load->model('hcp_model');
		$this->load->model('patient_model');
		
		if ($this->patient_model->is_patient(array($requester_id))) {
			$this->connections_model->accept_patient_doctor(array('patient_id' => $requester_id, 'hcp_id' => $this->auth->get_account_id() ));
		}

		else if ($this->hcp_model->is_doctor(array($requester_id))) {
			$this->connections_model->accept_doctor_doctor(array('hcp_id' => $requester_id, 'this_hcp_id' => $this->auth->get_account_id() ));
		}

		else {
			show_error('The requester id does not match any id in the database', 500);
			return;
		}
	}

	// destroy connection (un-friend someone)
	function destroy($id)
	{
		$this->auth->check_logged_in();
		
		$this->load->model('connections_model');

		if ( $this->auth->get_type() === 'patient' ){
			$delete = $this->connections_model->remove_pd_connection(array('patient_id' => $this->auth->get_account_id() , 'hcp_id' => $id )); 
		}
		else if ( $this->auth->get_type() === 'doctor' ){
			$delete = $this->connections_model->remove_dd_connection(array('this_hcp_id' => $this->auth->get_account_id() , 'hcp_id' => $id )); 
		}

		else {
			show_error('Unknown Error.', 500);
			return;
		}
		/*	
		if ($delete){
			echo "Success";
		}
		else {
			echo "Unable to remove connection";
		}
		*/
	}

	/* Fancy Feature: list all my pending connections
	function pending() 
	{
		// Case 1: Doctor's Incoming Doctor Requests
		// Case 2: Doctor's Outgoing Doctor Requests
		// Case 3: Doctor's Incoming Patient Requests
		// Case 4: Patients's Outgoing Doctor Requests
	}*/


}
?>
