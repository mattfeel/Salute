<?php
class Appointments extends Controller {

	function __constructor(){
		parent::Controller();
		$this->load->library('ajax');	
		//check if you're logged in	
	}

	// Default: load upcoming function
	function index()
	{}  	

	/* request an appointment */      	   
	function request()
	{}

	/* List all Appointments */
	function all()
	{}

	/* List Upcoming Appointments */
	function upcoming()
	{}

	/* List Past Appointments */
	function past()
	{}

	/*Cancel an existing appointment */	
	function cancel()
	{}
	
	/*Reschedule an existing appointment (date/time) */
	function reschedule()
	{}
	
}
?>