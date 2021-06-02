<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Admin extends CI_Controller {
	function __construct() {
		parent::__construct();

	}

	public function index()
	{
		$this->load->view('admin');
	}

	public function firestore()
	{
		$this->load->view('admin_firestore');
	}
}
