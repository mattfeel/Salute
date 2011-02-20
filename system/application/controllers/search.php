<?php
/**
 * @file search.php
 * @brief Controller to handle search requests
 *
 * @defgroup ctr Controllers
 * @ingroup ctr
 * @{
 */

class Search extends Controller {

	function __construct() {
		parent::Controller();
		$this->load->library('ui');
		$this->load->library('auth');
		$this->load->model('hcp_model');
		$this->load->model('connections_model');
	}

	/**
	 * Default to the advanced search
	 */
	function index() {
		$this->auth->check(array(auth::CurrLOG));
		if ($check !== TRUE) return;
		
		$this->ui->set(array(
			$this->load->view('mainpane/forms/search', '' , TRUE)
		));
	}

	/**
	 * Search for hcps in the database
	 * */
	function hcps() {
		$this->auth->check(array(auth::CurrLOG));
		if ($check !== TRUE) return;
		
		/** @todo Change this with the actual search! */
		$hcps = $this->hcp_model->get_hcps();
		
		for ($i = 0; $i < count($hcps); $i++) {
			if ($this->connections_model->is_connected_with(
				$this->auth->get_account_id(),
				$hcps[$i]['account_id']
			)) {
				$hcps[$i]['connected'] = TRUE;
			} else {
				$hcps[$i]['connected'] = FALSE;
			}
		}
		
		$this->ui->set(array(
			$this->load->view('mainpane/lists/allhcps', array('doc_list' => $hcps) , TRUE)
		));
	}

	/**
	 * Search for patients
	 * 
	 * @todo Not implemented yet
	 * */
	function patients(){ 
		$this->auth->check_logged_in();
	}

	/**
	 * Search for messages
	 * 
	 * @todo Not implemented yet
	 * */
	function messages() {
		$this->auth->check_logged_in();
	}

	/**
	 * Search for patients
	 * 
	 * @note if patient --> only search in my records
	 * @note if hcp --> only search in records i have access to
	 * @todo Not implemented yet
	 * */
	function medical_records() {
		$this->auth->check_logged_in();
	}

}
/** @} */
?>
