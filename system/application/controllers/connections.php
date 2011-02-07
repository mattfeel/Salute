<?php
/**
 * @file connections.php
 * @brief Controller to handle connections
 *
 * @defgroup ctr Controllers
 * @ingroup ctr
 * @{
 */

/**
 * Class Controller Connections
 * 
 * @test The whole class has been succesfully tested.
 * @bug No known bugs reported
 * */
class Connections extends Controller {

	function __construct() {
		parent::Controller();
		$this->load->library('ajax');
		$this->load->library('auth');
	}

	/**
	 * Default method
	 * @attention should never be accessible
	 * @return error
	 * */
	function index() {
		show_error('Direct access to this resource is forbidden', 500);
		return;
	}

	/**
	 * List all hcps that current user is connected with
	 * 
	 * @attention Available for both patients and hcps
	 * 
	 * @return: List view of hcps I'm connected with
	 * 
	 * @test Works fine
	 * */
	function myhcps() {
		$this->auth->check_logged_in();

		$this->load->model('connections_model');
		
		if ($this->auth->get_type() === 'patient') {
			$results  = $this->connections_model->list_my_hcps($this->auth->get_account_id()); 
			$sidepane = 'sidepane/patient-profile';
		}
		else if ($this->auth->get_type() === 'hcp'){
			$results  = $this->connections_model->list_my_colleagues($this->auth->get_account_id()); 
			$sidepane = 'sidepane/hcp-profile';
		}
		else {
			show_error('Internal server logic error.', 500);
			return;
		}
		
		switch ($results) {
			case -1:
				$mainview = 'Query error!';
				$sideview = '';
				break;
			default:
				$mainview = $this->load->view('mainpane/list_hcps',
					array('list_name' => 'My Hcps', 'list' => $results, 'status' => 'connected') , TRUE);
				$sideview = $this->load->view($sidepane, '', TRUE);
				break;
		}
		
		// Give results to the client
		$this->ajax->view(array($mainview,$sideview));
	}

	/**
	 * List all patients that current hcp is connected with
	 * 
	 * @attention Only available for hcps
	 * 
	 * @return: List view of patients
	 *
	 * @test Works fine
	 * */
	function mypatients()
	{
		$this->auth->check_logged_in();
		
		if ($this->auth->get_type() !== 'hcp'){
			show_error($this->load->view('errors/not_hcp', '', TRUE));
			return;
		}

		$this->load->model('connections_model');
		$res = $this->connections_model->list_my_patients($this->auth->get_account_id()); 

		// Switch the response from the model, to select the correct view
		switch ($res) {
			case -1:
				$mainview = 'Query error!';
				$sideview = '';
				break;
			default:
				$mainview = $this->load->view('mainpane/list_patients',
					array('list_name' => 'My Patients', 'list' => $res, 'status' => 'connected') , TRUE);
				$sideview = $this->load->view('sidepane/hcp-profile', '', TRUE);
				break;
		}
		
		// Give results to the client
		$this->ajax->view(array($mainview,$sideview));
	}
	
	/**
	 * Lists the pending connections that this user has initiated
	 * 
	 * @param
	 *   $direction
	 *   If it is 'out', it lists pending outgoing connections,
	 *   otherwise it lists pending incoming connections.
	 * 
	 * @attention Only hcps can see pending(in)
	 * 
	 * @test Tested in and out and also incorrect inputs.
	 * @test Tested pendings for both patients and hcps and worked fine
	 * */
	function pending($direction = 'out')
	{
		if ($direction == 'in') {
			if ($this->auth->get_type() === 'hcp') {
				$this->_pending_in();
			} else {
				show_error($this->load->view('errors/not_hcp', '', TRUE));
			}
		}
		else if ($direction == 'out') {
			$this->_pending_out();
		}
		else {
			show_error('Input not valid');
		}
	}
	
	/**
	 * Private function to list all pending outgoing connection requests
	 * 
	 * @note Lists only hcps
	 * @note This function is available for both patients and hcps
	 * 
	 * @test Tested!
	 * */
	function _pending_out()
	{
		$this->auth->check_logged_in();
		$this->load->model('connections_model');
		
		if ($this->auth->get_type() === 'hcp') {
			$res = $this->connections_model->pending_outgoing_hcps_4_a_hcp(array($this->auth->get_account_id())); 
			$sidepane = 'sidepane/hcp-profile';
		}
		else if ($this->auth->get_type() === 'patient') {
			$res = $this->connections_model->pending_outgoing_hcps_4_a_patient(array($this->auth->get_account_id())); 
			$sidepane = 'sidepane/patient-profile';
		}
		else {
			show_error('Internal server logic error.', 500);
			return;
		}

		// Switch the response from the model, to select the correct view
		switch ($res) {
			case -1:
				$mainview = 'Query error!';
				$sideview = '';
				break;
			default:
				$mainview = $this->load->view('mainpane/list_hcps',
					array('list_name' => 'Pending Outgoing Requests', 'list' => $res, 'status' => 'pending_out') , TRUE);
				$sideview = $this->load->view($sidepane, '', TRUE);
				break;
		}
		
		// Give results to the client
		$this->ajax->view(array($mainview,$sideview));
	}
	
	/**
	 * Private function that lists all pending connections that this 
	 * user has received (incoming)
	 * 
	 * @note Lists both requests from hcps and patients
	 * @note Available only for hcps, but the check is already done
	 * by the function pending()
	 * 
	 * @test Tested!
	 * */
	function _pending_in() 
	{
		$this->auth->check_logged_in();
		$this->output->enable_profiler(TRUE);
		$this->load->model('connections_model');
		
		if($this->auth->get_type() === 'hcp') {
			// Take pending incoming from other hcps
			$hcps = $this->connections_model->pending_incoming_hcps_4_a_hcp(array($this->auth->get_account_id())); 
			// And pending incoming from other patients
			$pats = $this->connections_model->pending_incoming_patients_4_a_hcp(array($this->auth->get_account_id())); 
		}
		else {
			/** @todo In the future, create a specific view for this
			 * kind of errors, anc call this view in all similar cases */
			show_error('Internal server logic error.');
			return;
		}
		
		if ($hcps == -1 || $pats == -1) {
			$mainview = 'Query error!';
			$sideview = '';
			return;
		}
		
		$mainview  = $this->load->view('mainpane/list_patients',
			array('list_name' => 'Pending Requests from Patients', 'list' => $pats, 'status' => 'pending_in') , TRUE);
		$mainview .= $this->load->view('mainpane/list_hcps',
			array('list_name' => 'Pending Requests from Hcps', 'list' => $hcps, 'status' => 'pending_in') , TRUE);
		$sideview = $this->load->view('sidepane/hcp-profile', '', TRUE);
		
		// Give results to the client
		$this->ajax->view(array($mainview,$sideview));
	}
	
	/**
	 * Request a new connection to a hcp.
	 * 
	 * @param
	 *   $id is the id of a hcp you want to connect to
	 * 
	 * @attention
	 *   Can be called by both patients and hcps, but a 
	 * hcp can only request for another hcp and a patient can 
	 * only request for a hcp.
	 * 
	 * @test Tested different inputs: nothing, string, invalid id
	 * */
	function request($id = NULL)
	{
		$this->auth->check_logged_in();
		$this->load->model('hcp_model');
		$this->load->model('connections_model');
		
		// Check if an account_id has been specified
		if ($id == NULL) {
			show_error('No hcp_id specified.');
			return;
		}
		
		// Check the input type
		if (! is_numeric($id)) {
			show_error('Invalid id type.');
			return;
		}
		
		// Check if the account_id specified refers to a hcp
		if (!$this->hcp_model->is_hcp(array($id))) {
			show_error('The id specified does not refer to an HCP.');
			return;
		}
		
		// Get all the hcp's info
		$results = $this->hcp_model->get_hcp(array($id));
		
		// If current user is a hcp
		if ($this->auth->get_type() === 'hcp') {
			$res = $this->connections_model->add_hcp_hcp(array(
										$this->auth->get_account_id(),
										$id
										));
		}
		// If current user is a patient
		else if ($this->auth->get_type() === 'patient') {
			$res = $this->connections_model->add_patient_hcp(array(
										$this->auth->get_account_id(),
										$id
										));
		}
		else {
			show_error('Internal server logic error.', 500);
			return;
		}
		
		// Switch the response from the model, to select the correct view
		switch ($res) {
			case -1:
				$mainview = 'Query error!';
				$sideview = '';
				break;
			case -3:
				$mainview = 'This connection has been already requested.';
				$sideview = '';
				break;
			default:
				$mainview = 'Your request has been submitted.';
				$sideview = '';
				$this->load->library('email');
				$config['mailtype'] = 'html';
				$this->email->initialize($config);
				//$this->email->from($this->auth->get_email());
				$this->email->from('salute-noreply@salute.com');
				//$this->email->to($results['email']);
				$this->email->to('mattfeel@gmail.com');
				$this->email->subject('New Connection Request');
				
				$this->email->message(
					'You have a new connection request from '.
					$this->auth->get_first_name().' '.$this->auth->get_last_name().
					'. Click <a href="https://'.$_SERVER['SERVER_NAME'].'/connections/accept/'.$this->auth->get_account_id().'/'.$id.'">here</a> to accept.');
				
				$this->email->send();
				break;
		}
		
		// Give results to the client
		$this->ajax->view(array($mainview,$sideview));
	}

	/** 
	 * Accept an existing connection request
	 * 
	 * @attention Only hcps can do this
	 * 
	 * @test Tested
	 * */
	function accept($requester_id = NULL) 
	{
		$this->auth->check_logged_in();
		$this->load->model('connections_model');
		$this->load->model('hcp_model');
		$this->load->model('patient_model');
		
		// Check if parameters are specified
		if ($requester_id == NULL) {
			show_error('ids not specified.', 500);
			return;
		}
		
		/* Check if the current user is the receiver
		if ($this->auth->get_account_id() != $my_id) {
			show_error('You are not the receiver for this request');
			return;
		}*/
		
		// Check if you are a hcp (only hcp can call this function)
		if ($this->auth->get_type() != 'hcp') {
			show_error('Sorry, only HCP can accept connection requests');
			return;
		}
		
		if ($this->patient_model->is_patient(array($requester_id))) {
			$res = $this->connections_model->accept_patient_hcp(array($requester_id, $this->auth->get_account_id()));
		}
		else if ($this->hcp_model->is_hcp(array($requester_id))) {
			$res = $this->connections_model->accept_hcp_hcp(array($requester_id, $this->auth->get_account_id()));
		}
		else {
			show_error('The requester id does not match any id in the database', 500);
			return;
		}
		
		// Switch the response from the model, to select the correct view
		switch ($res) {
			case -1:
				$mainview = 'Query error!';
				$sideview = '';
				break;
			case -2:
				$mainview = 'Connection does not exist.';
				$sideview = '';
				break;
			case -3:
				$mainview = 'This connection has already been accepted.';
				$sideview = '';
				break;
			default:
				$mainview = 'You have accepted the connection.';
				$sideview = '';
				break;
		}
		
		// Give results to the client
		$this->ajax->view(array($mainview,$sideview));
	}

	/**
	 * deletes connection (un-friend someone)
	 * @param
	 * 		id is the account_id of the hcp or patient the user would like to disconnect from
	 * @return 
	 * 		error 
	 * 			id not specified (the one to disconnect from)
	 * 			query fails
	 * 			connection doesnt exist
	 * 		success: deleted the connection
	 * 
	 * @test Tested
	 */
	function destroy($id = NULL)
	{
		$this->auth->check_logged_in();
		$this->output->enable_profiler(TRUE);
		
		if ($id == NULL) {
			show_error('id not specified.', 500);
			return;
		}
		
		$this->load->model('connections_model');
		$res = $this->connections_model->remove_connection($this->auth->get_account_id(), $id);
		
		/*if ($this->patient_model->is_patient(array($id))) {
			$res = $this->connections_model->remove_pd_connection(array($this->auth->get_account_id(), $id)); 
		}
		else if ($this->hcp_model->is_hcp(array($id))) {
			$res = $this->connections_model->remove_dd_connection(array($this->auth->get_account_id(), $id)); 
		}
		else {
			show_error('Internal Logic Error.', 500);
			return;
		}*/
		
		// Switch the response from the model, to select the correct view
		switch ($res) {
			case -1:
				$view = 'Query error!';
				break;
			case -2:
				$view = 'Connection does not exist.';
				break;
			default:
				$view = 'You have been disconnected from that health care provider.';
				break;
		}
		
		// Create final view for the user
		$this->ajax->view(array($view,''));
	}
	
	/**
	 * Removes a pending outgoing connection request
	 * @param
	 * 		id is the account_id of the hcp or patient the user would 
	 * 		like to cancel a connection request to
	 * @return 
	 * 		error 
	 * 			id not specified (the one to disconnect from)
	 * 			query fails
	 * 			connection doesnt exist
	 * 		success: deleted the connection
	 * 
	 * @attention The current user can cancel only requests that he/she
	 * personally made!
	 * @test Tested
	 */
	function cancel($id = NULL)
	{
		$this->auth->check_logged_in();
		$this->output->enable_profiler(TRUE);
		
		if ($id == NULL) {
			show_error('id not specified.', 500);
			return;
		}
		
		$this->load->model('connections_model');
		
		$conn = $this->connections_model->get_connection($this->auth->get_account_id(), $id);
		
		if ($conn === -1) {
			$res = -1;
		}
		else if ($conn === NULL) {
			$res = -2;
		}
		else if ($conn['requester_id'] = $this->auth->get_account_id()) {
			// If I requested this connection, I can cancel it
			$res = $this->connections_model->remove_pending($this->auth->get_account_id(), $id);
		} else {
			$res = -5;
		}
		
		// Switch the response from the model, to select the correct view
		switch ($res) {
			case -1:
				$view = 'Query error!';
				break;
			case -2:
				$view = 'Connection does not exist.';
				break;
			case -5:
				$view = 'This connection request has not been initiated by you.';
				break;
			default:
				$view = 'Your connection request has been canceled.';
				break;
		}
		
		// Create final view for the user
		$this->ajax->view(array($view,''));
	}
	
	/**
	 * Removes a pending incoming connection request
	 * @param
	 * 		id is the account_id of the hcp or patient the user would 
	 * 		like to cancel a connection request to
	 * @return 
	 * 		error 
	 * 			id not specified (the one to disconnect from)
	 * 			query fails
	 * 			connection doesnt exist
	 * 		success: deleted the connection
	 * 
	 * @attention The current user can cancel only requests that he/she
	 * personally received!
	 * 
	 * @test Tested
	 */
	function reject($id = NULL)
	{
		$this->auth->check_logged_in();
		$this->output->enable_profiler(TRUE);
		
		if ($id == NULL) {
			show_error('id not specified.', 500);
			return;
		}
		
		$this->load->model('connections_model');
		
		$conn = $this->connections_model->get_connection($this->auth->get_account_id(), $id);
		
		if ($conn === -1) {
			$res = -1;
		}
		else if ($conn === NULL) {
			$res = -2;
		}
		else if ($conn['accepter_id'] = $this->auth->get_account_id()) {
			// If I requested this connection, I can cancel it
			$res = $this->connections_model->remove_pending($this->auth->get_account_id(), $id);
		} else {
			$res = -5;
		}
		
		// Switch the response from the model, to select the correct view
		switch ($res) {
			case -1:
				$view = 'Query error!';
				break;
			case -2:
				$view = 'Connection does not exist.';
				break;
			case -5:
				$view = 'This connection request has been initiated by you.<br />
				Click here to <a href="/connections/cancel/'.$id.'">cancel this request</a>.';
				break;
			default:
				$view = 'This connection has been rejected.';
				break;
		}
		
		// Create final view for the user
		$this->ajax->view(array($view,''));
	}
}

/** @} */
?>
