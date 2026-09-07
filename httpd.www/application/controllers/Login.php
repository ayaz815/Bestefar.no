<?php
	class Login extends CI_Controller{
		
		function __construct() {
			parent::__construct();
			/* $this->load->model('App_model');
			$this->load->library('session');   
			$this->load->library('upload'); */
			$this->output->set_header('Cache-Control: no-store, no-cache, must-revalidate, post-check=0, pre-check=0');
			$this->output->set_header('Pragma: no-cache');
	    }
		
		public function index($param=''){
			$data['login_failed'] 				= $param;
			$data['page_name'] 				= 'login';
			$this->load->view('frontend/index', $data);
		}
		
		public function signup($param1=''){
			
			$data['page_name'] 				= 'register';
			$this->load->view('frontend/index', $data);
		}
		public function registration_msg($param1=''){
			
			$data['response'] = $param1;
			$data['page_name'] = 'registration_msg';
			$this->load->view('frontend/index', $data);
		}
		
		public function register($param1=''){
			if($param1 == 'create'){
			  
				$response = $this->Admin_model->add_new_user();
				$this->session->set_flashdata('msg_success', ' Data Saved successfully') ;
				redirect(base_url(). '/login/registration_msg/'.$response, 'refresh');
			}
			$data['response'] = $param1;
			$data['page_name'] 				= 'register';
			$this->load->view('frontend/index', $data);
		}
		
		public function test($param1=''){
			$data['page_name'] 				= 'register_3';
			$data['response'] = $param1;
			$this->load->view('frontend/index', $data);
		}
		
		public function admin_login_varify(){
			$answer = $this->Admin_model->admin_login_varify();  
			if($answer){		 
				//Make Session	
				$this->session->set_userdata('admin_login','1');
				$this->session->set_userdata('admin_id',$answer->admin_id);
				$this->session->set_userdata('role_name','admin'); 
				$this->session->set_userdata('name',$answer->name);
				$this->session->set_flashdata('msg_success', ' Wellcome '.$this->session->userdata('name')) ;
				redirect(strtolower($this->session->userdata('role_name')) . '/dashboard', 'refresh');			
			}else{ 
				$this->session->set_flashdata('msg_success', ' Invalid Username and Password.');
				redirect('admin', 'refresh');
			}
		}
		
		public function user_login_varify(){
			$answer = $this->Admin_model->user_login_varify();  
			if($answer){		 
				//Make Session
				$data = array(
                    'last_visit' => date('Y-m-d'),
                    // Add more columns and values as needed
                );
                $this->db->where('users_id ', $answer->users_id);
                $this->db->update('users', $data);
				$this->session->set_userdata('user_login','1');
				$this->session->set_userdata('users_id',$answer->users_id);
				$this->session->set_userdata('approval_status',$answer->approval_status);
				//$this->session->set_userdata('role_name','user'); 
				$this->session->set_userdata('name',$answer->firstname);
				$this->session->set_flashdata('msg_success', ' Wellcome '.$this->session->userdata('name')) ;
				redirect('home', 'refresh');			
			}else{ 
				//$this->session->set_flashdata('msg_error', 'Ugyldig brukernavn og passord.');
				redirect('login/index/failed', 'refresh');	
			}
		}
		
		public function register_user(){
			$this->Admin_model->register_user();
			$this->session->set_flashdata('msg_success', ' You can register successfully '.$this->session->userdata('name')) ;
			redirect('home', 'refresh');
		}
		
		public function logout(){
		  $this->session->sess_destroy();
		  redirect('admin', 'refresh');
		}
		
		public function logout_user(){
		  $this->session->sess_destroy();
		  redirect('home', 'refresh');
		}
	}
?>