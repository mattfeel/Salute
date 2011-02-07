<?php
/**
 * @file home.php
 * @brief Controller to handle access to the web application
 *
 * @defgroup ctr Controllers
 * @ingroup ctr
 * @{
 */

class Home extends Controller {

	function __construct() {
		parent::Controller();
		$this->load->library('ajax');
		$this->load->library('auth');
	}
	
	/**
	 * Default home 
	 * If not logged in load default welcome page and login side panel
	 * else already logged in, load their profile
	 * */
	function index()
	{
		// Not logged in
		if (!$this->auth->is_logged_in()) {
			$this->ajax->view(array(
				$this->load->view('mainpane/welcome', '', TRUE),
				$this->load->view('sidepane/login', '', TRUE)
			));
		}
		// Already logged in
		else {
			$this->ajax->redirect('/profile');
		}
	}

	/**
	 * Verifies user login
	 * @input -- email, password
	 * verify input, check authorization
	 * if login successful, store session info
	 * @return redirect to profile || error (if already logged in || authorization fails)
	 * */
	function login()
	{
		if ($this->auth->is_logged_in())
			$this->ajax->redirect('/profile');
		
		// get email & password
		$email = $this->input->post('email');
		$password = $this->input->post('password');
		
		if ($email == FALSE || $password == FALSE) {
			show_error('Access to this page not allowed', 500);
			return;
		}
		
		$this->load->model('login_model');
		$this->load->model('account_model');
		
		// verify login
		//returns array: of an array that has : 1st element = type, 2nd element= whole user's tuple
		$results = $this->login_model->authorize(array("email" => $email,"password" => $password));
				
		// login fails : error view
		if ($results === -1) {
			$this->ajax->view(array('Query error!',''));
		}
		else if (sizeof($results) == 0) {
			$this->ajax->view(array('',
				$this->load->view('sidepane/login_failed', '', TRUE)
			));
		} else if ($results === -1) {
			$this->ajax->view(array('Query Error!',''));
			return;
		}
		// login successful : store info for session id, go to user profile
		else {
			if ($results[0] != 'patient' && $results[0] != 'hcp') {
				$this->ajax->view(array(
					'',
					$this->load->view('sidepane/login_failed', '', TRUE)
				));
			} 
			$active_status = $this->account_model->is_active(array($results[1]["account_id"]));
			if ( $active_status === -1 ){
				show_error('Query Error',500);
				return;
			} else if ( $active_status === -4 ){
				$this->ajax->view('Sorry! That account does not exist.', '');
				return;
			} else if ( !$active_status ){
				$this->ajax->redirect('/settings/activate/'.$results[1]["account_id"]);
				return;
			}	
			$login_data = array(
				'account_id' => $results[1]["account_id"],
				'email' => $results[1]["email"],
				'type' => $results[0],
				'first_name' => $results[1]["first_name"],
				'last_name' => $results[1]["last_name"]
			);
			$this->session->set_userdata($login_data);
			$this->ajax->redirect('/profile');
			
		}
	}

	/**
	 * Logout User
	 * Clears current session info
	 * @return redirect to default page
	 * */
	function logout()
	{
		$this->session->sess_destroy();
		$this->ajax->redirect('/');
	}
	
	/**
	 * Loads view when user clicks 'Forgot Password'
	 * prompts for email address
	 * */
	function retrieve_password(){
				
		$this->ajax->view(array(
			$this->load->view('mainpane/forgot_password', '', TRUE),
			''
		));
	}
	
	/*
	 * retrieve_password
	 * @input -- email address
	 * @return send password via email to user, with link to login again. || error(invalid email)
	 * @testing
	 * 		working and tested.
	 * */
	function retrieve_password_do(){
		$email = $this->input->post('email');

		if ( $email == NULL ){
				show_error('Error: No email passed in.', 500);
				return;
		}
		$this->load->model('account_model');
		
		$result = $this->account_model->get_account(array($email)); 
		$password = $result[0]['password'];
		
		if ($password == NULL){
			show_error('Sorry, this email is not registered.', 500);
			return;
		}

		$this->load->library('email');
		$config['mailtype'] = 'html';
		$this->email->initialize($config);
		$this->email->from('salute-noreply@salute.com');
		$this->email->to($result[0]['email']);
		$this->email->subject('Password Retrieval');
		$this->email->message(
			'You have requested for retrieval of your password. Your password is:'.$password.' '.
			'Click <a href="https://'.$_SERVER['SERVER_NAME'].'/">here</a> to login.');

		$this->email->send();
		$this->ajax->view(array('Your password has been emailed to you.',''));
	}

	/*
	 * loads registration form
	 * */
	function register()
	{
		$this->ajax->view(array(
			$this->load->view('mainpane/registration', '', TRUE),
			$this->load->view('sidepane/default', '', TRUE)
		));
	}

	/*
	 * registers new user 
	 * @param
	 * 		takes in text from user(registration form)
	 * @return
	 * 		error 
	 * 			email || password is missing
	 *			add_account query error
	 * 			email is already registered
	 * 			type is not hcp nor patient
	 * 			account id is already registered in patient||hcp table
	 * 		confirmation email
	 * @error tests
	 * 		mandatory email/password field succes
	 * 		patient registration success
	 * 		hcp registration success 
	 * @attention dob is VERY sensitive...
	 * */
	function register_do($type = NULL)
	{
		if( $type == NULL || ( $type !== 'patient' && $type !== 'hcp' ) ){
			show_error('Invalid type.',500);
			return;
		}
		
		$email = $this->input->post('email');
		$password = $this->input->post('password');
	
		if($email == NULL || $password == NULL)	{
			show_error('Email and Password are mandatory to register.',500);
			return;
		}
		
		// Start a transaction now
		$this->db->trans_start();
		//$this->db->trans_begin();
		
		// load respective forms.
		$this->load->model('account_model');
		$result = $this->account_model->add_account(array('email' => $email, 'password' => $password)); 
		
		if($result === -1){
				$this->ajax->view(array('Query error (1)!',''));
				return;
		}
		
		$account_id = $result[0]['account_id'];
				
		if ($type === 'patient'){
			$input=array(
						$account_id,
						$this->input->post('firstname'),
						$this->input->post('lastname'),
						$this->input->post('middlename'),
						$this->input->post('ssn'),
						$this->input->post('dob'),
						$this->input->post('sex'),
						$this->input->post('tel'),
						$this->input->post('fax'),
						$this->input->post('address')
			);
		
			$this->load->model('patient_model');
			$res = $this->patient_model->register($input); 
		}
		else if ($type === 'hcp') {
			$input=array(
						$account_id,
						$this->input->post('firstname'),
						$this->input->post('lastname'),
						$this->input->post('middlename'),
						$this->input->post('ssn'),
						$this->input->post('dob'),
						$this->input->post('sex'),
						$this->input->post('tel'),
						$this->input->post('fax'),
						$this->input->post('spec'),
						$this->input->post('org'),
						$this->input->post('address')
			);
		
			$this->load->model('hcp_model');
			$res = $this->hcp_model->register($input); 
		}
		else
			show_error('Internal Server Error.', 500);
		if ( $res === -1 )
				$view = 'Query error!';
		else{
			$this->load->library('email');
			$config['mailtype'] = 'html';
			$this->email->initialize($config);
			$this->email->from('salute-noreply@salute.com');
			$this->email->to($email);
			$this->email->subject('Registration Confirmation');
			$this->email->message(
				'You have successfully registered with Salute!'.' '.
				'Click <a href="https://'.$_SERVER['SERVER_NAME'].'/">here</a> to login.');

			$this->email->send();
			$view = 'Congratulations, you are now registered. A confirmation email has been sent to you.'.
			' Click <a href="https://'.$_SERVER['SERVER_NAME'].'/">here</a> to login.';
		}
		
		// End transaction
		$this->db->trans_complete();
		//$this->db->trans_rollback();
		
		$this->ajax->view(array($view,''));
	}
}
/** @} */
?>
