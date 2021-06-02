<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Admin extends CI_Controller {

	public function index()
	{
		$this->load->view('admin');
	}

	public function firestore()
	{
		$this->load->view('admin_firestore');
	}
}
