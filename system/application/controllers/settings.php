<?php
/**
 * @file settings.php
 * @brief Controller to manage user settings
 *
 * @defgroup ctr Controllers
 * @ingroup ctr
 * @{
 */

class Settings extends Controller {

	function __construct(){
		parent::Controller();
		$this->load->library('ui');
		$this->load->library('auth');
	}

	/**
	 * Default Settings View: lists possible actions:
	 * 		Change Password
	 * 		Change Email
	 * 		Deactivate
	 * */
	function index(){
		$this->auth->check_logged_in();
		
		$this->ui->set(array(
				$this->load->view('mainpane/settings', '', TRUE)
			));
	}
	
	/**
	 * Loads form  to let user change their password
	 * */
	function change_password(){
		$this->auth->check_logged_in();	
		
		$this->ui->set(array($this->load->view('mainpane/forms/change_password', '', TRUE)));
		
	}

	/**
	 * Changes user password
	 * @input new password
	 * @return error || email confirmation + success message
	 * */
	function change_password_do(){
			$this->auth->check_logged_in();	
			$this->load->model('account_model');		
			$password = $this->input->post('password');
			if ($password == NULL){
				$this->ui->set_error('Password Invalid','Missing Argument');
				return;
			}
			$check = $this->account_model->update_account(array($this->auth->get_account_id(),$this->auth->get_email(),$password));
			if ($check === -1){
				$this->ui->set_query_error();
				return;
			} else if ($check === -4){
				$this->ui->set_error('Account does not exist!');
				return;
			}
			
			$this->load->library('email');
			$config['mailtype'] = 'html';
			$this->email->initialize($config);
			$this->email->from('salute-noreply@salute.com');
			$this->email->to($this->auth->get_email());
			$this->email->subject('Your password has been changed.');
			$this->email->message(
				'Your password has been successfully changed. It is now: '.$password.'. '.
				'Click <a href="https://'.$_SERVER['SERVER_NAME'].'/">here</a> to login.');

			$this->email->send();
									
			$msg = 'Your password has been changed. A confirmation email has been sent for your records.';
			$this->ui->set_message($msg,'Confirmation');	
	}

	/**
	 * Loads form for user to change their email
	 * */
	function change_email(){
		$this->auth->check_logged_in();	
		
		$this->ui->set(array($this->load->view('mainpane/forms/change_email', '', TRUE)));
	}

	/**
	 * Changes user's email
	 * @param new email address
	 * @return error || confirmation email + success message
	 * */
	function change_email_do(){
			$this->auth->check_logged_in();	
			$this->load->model('account_model');		
			
			$email = $this->input->post('email');
			if ($email == NULL){
				$this->ui->set_error('Email Invalid','Missing Arguments');
				return;
			}	
			
			$password = $this->account_model->get_account($this->auth->get_email());
			if ($password == NULL){
				$this->ui->set_error('Failed to retrieve password.');
				return;
			} else if( $password === -1 ){
				$this->ui->set_query_error();
				return;
			}
			
			$check = $this->account_model->update_account(array($this->auth->get_account_id(),$email,$password[0]['password']));
			
			if ($check === -1){
				$this->ui->set_query_error();
				return;
			} else if ($check === -4){
					$this->ui->set_error('Account does not exist!');
					return;
			}
			
			$this->load->library('email');
			$config['mailtype'] = 'html';
			$this->email->initialize($config);
			$this->email->from('salute-noreply@salute.com');
			$this->email->to($email);
			$this->email->subject('Your password has been changed.');
			$this->email->message(
				'Your email has been successfully changed. It is now: '.$email.'. '.
				'Click <a href="https://'.$_SERVER['SERVER_NAME'].'/">here</a> to login.');

			$this->email->send();
			
			// Update session cookie
			$this->session->set_userdata(array('email' => $email));
			
			$this->ui->set_message('Your email has been changed. A confirmation has been sent to your email.','Confirmation');
			
	}
	
	/** 
	 * Deactivate Account
	 * @return Deactivation Confirmation + Email. || error
	 * @todo popup: are you sure?
	 **/ 
	function deactivate() {
		$this->auth->check_logged_in();
				
		$this->load->model('account_model');
		$check = $this->account_model->deactivate($this->auth->get_account_id());
		if ($check === -1){
			$this->ui->set_query_error();
			return;
		} else if ($check === -4){
			$this->ui->set_error('Account does not exist!');
			return;
		}
		
		$this->load->library('email');
		$config['mailtype'] = 'html';
		$this->email->initialize($config);
		$this->email->from('salute-noreply@salute.com');
		$this->email->to($this->auth->get_email());
		$this->email->subject('Account Deactivated.');
		$this->email->message('Your Account has been deactivated.');
		$this->email->send();
		
		$this->ui->set_message('Your account has been deactivated.','Confirmation');
		$this->session->sess_destroy();
	}
	
	/**
	 * Activate Account Prompt 
	 * loads a statement that user is deactive. Link to reactivate
	 * @param account_id -- the account_id of the user who tried to login but is deactive
	 * @todo popup: are you sure?
	 **/ 
	function activate($account_id){
		$msg = 'Your Account is de-active.'.	
		'Click <a href="https://'.$_SERVER['SERVER_NAME'].'/settings/activate_do/'.$account_id.
		'/ ">here</a> to reactivate.';
		$this->ui->set_message($msg);
	}
	
	/** 
	 * Activate Account
	 * @param account_id -- the account_id of the user who tried to login but is deactive
	 * @return error || Confirmation statement + Link to login. 
	 **/ 
	function activate_do($account_id){
		$this->load->model('account_model');
		$results = $this->account_model->activate(array($account_id));
		if ($results === -1){
			$this->ui->set_query_error();
			return;
		}
		$msg = 'Your Account has been reactivated. Click <a href="https://'.$_SERVER['SERVER_NAME'].'/">here</a> to login.';
		$this->ui->set_message($msg,'Confirmation');		
	}
}
/** @} */
?>
