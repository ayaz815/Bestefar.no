<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Admin extends CI_Controller {
	function __construct() {
		parent::__construct();
		//$this->load->database();
		//$this->load->library('session');
		$this->output->set_header('Cache-Control: no-store, no-cache, must-revalidate, post-check=0, pre-check=0');
		$this->output->set_header('Pragma: no-cache');
	}
	
	public function index(){
		if($this->session->userdata('admin_login') == 1){
			redirect(base_url().strtolower($this->session->userdata('role_name')) . '/dashboard', 'refresh');
		}
		
		$this->load->view('admin/login');
	}
	
	public function dashboard(){
		if($this->session->userdata('admin_login') != 1){
			redirect(base_url() . 'admin', 'refresh');
		}
		
		$data['page_title'] 			= 'Dashboard';
		$data['page_sub_title'] 		= '';
		$data['page_name'] 				= 'dashboard';
		$this->load->view(strtolower($this->session->userdata('role_name')) .'/index', $data);
	}
	
	public function system_setting($param1=''){
		if($this->session->userdata('admin_login') != 1){
			redirect(base_url().strtolower($this->session->userdata('role_name')), 'refresh');
		}
		if($param1 == 'update'){
			$response = $this->Admin_model->update_system_setting();
			$this->session->set_flashdata('msg_success', ' System Setting Update Successfully ') ;
			redirect(base_url().strtolower($this->session->userdata('role_name')) . '/system_setting', 'refresh');
		}
		$data['system_data'] 	= $this->Admin_model->get_data_id('system_setting', '1')->row();
		$data['page_title'] 	= 'System Setting';
		$data['page_sub_title'] = 'Update Your System Setting';
		$data['page_name'] = 'system_setting';
		$this->load->view(strtolower($this->session->userdata('role_name')) .'/index', $data);
	}
	
	public function myprofile($param1=''){
		if($this->session->userdata('admin_login') != 1){
			redirect(base_url().strtolower($this->session->userdata('role_name')), 'refresh');
		}
		if($param1 == 'update'){
			$response = $this->Admin_model->update_admin_profile();
			$this->session->set_flashdata('msg_success', ' My Profile Updated') ;
			redirect(base_url().strtolower($this->session->userdata('role_name')) . '/myprofile', 'refresh');
		}
		$data['profile_data'] 	= $this->Admin_model->get_data_id('admin', $this->session->userdata('admin_id'))->row();
		$data['page_title'] 	= 'My Profile';
		$data['page_sub_title'] = 'Update Your Profile';
		$data['page_name'] = 'myprofile';
		$this->load->view(strtolower($this->session->userdata('role_name')) .'/index', $data);
	}
	
	
	
	public function competitions_add($param1=''){
		if($this->session->userdata('admin_login') != 1){
			redirect(base_url().strtolower($this->session->userdata('role_name')), 'refresh');
		}
		if($param1 == 'create'){
			$response = $this->Admin_model->add_new_competition();
			$this->session->set_flashdata('msg_success', ' Data Saved successfully') ;
			redirect(base_url().strtolower($this->session->userdata('role_name')) . '/competitions_add', 'refresh');
		}
		$data['page_title'] 	= 'Competition Information';
		$data['page_sub_title'] = 'Add New Competition'; 
		$data['page_name'] = 'competitions_add';
		$this->load->view(strtolower($this->session->userdata('role_name')) .'/index', $data);
	}
	
	public function users($param1='', $param2=''){
		if($this->session->userdata('admin_login') != 1){
			redirect(base_url().'admin', 'refresh');
		}
		if($param1 == 'update_access'){
			$response = $this->Admin_model->update_user_access();
			$this->session->set_flashdata('msg_success', ' Data Updated successfully ') ;
			redirect(base_url().'admin/users', 'refresh');
		}if($param1 == 'delete'){
			$response = $this->Admin_model->delete_data('users', $param2);
			$this->session->set_flashdata('msg_success', ' Data Deleted successfully ') ;
			redirect(base_url().'admin/users', 'refresh');
		}if($param1 == 'create'){
			$response = $this->Admin_model->add_new_category();
			$this->session->set_flashdata('msg_success', ' Data Saved successfully') ;
			redirect(base_url().'admin/users', 'refresh');
		}
		
		$data['users'] 	= $this->Admin_model->get_data_desc('users');
		$data['page_title'] 	= 'Manage Users';
		$data['page_sub_title'] = 'Update, Delete, And View All Users';
		$data['page_name'] = 'users';
		$this->load->view('admin/index', $data);
	}
	
	
	public function main_categories($param1='', $param2=''){
		if($this->session->userdata('admin_login') != 1){
			redirect(base_url().'admin', 'refresh');
		} 
		if($param1 == 'update'){
			$response = $this->Admin_model->main_category_update();
			$this->session->set_flashdata('msg_success', ' Data Updated successfully ') ;
			redirect(base_url().'admin/main_categories', 'refresh');
		}
		
		$data['main_categories'] 	= $this->Admin_model->get_data_asc('main_categories');
		$data['page_title'] 	= 'Manage Main Categories';
		$data['page_sub_title'] = 'Update, Delete, And View All Main Categories';
		$data['page_name'] = 'main_categories';
		$this->load->view('admin/index', $data);
	}
		
	public function categories($param1='', $param2=''){
		if($this->session->userdata('admin_login') != 1){
			redirect(base_url().'admin', 'refresh');
		} 
		if($param1 == 'update'){
			$response = $this->Admin_model->category_update();
			$this->session->set_flashdata('msg_success', ' Data Updated successfully ') ;
			redirect(base_url().'admin/categories', 'refresh');
		}if($param1 == 'delete'){
			$response = $this->Admin_model->delete_data('categories', $param2);
			$this->session->set_flashdata('msg_success', ' Data Deleted successfully ') ;
			redirect(base_url().'admin/categories', 'refresh');
		}if($param1 == 'create'){
			$response = $this->Admin_model->add_new_category();
			$this->session->set_flashdata('msg_success', ' Data Saved successfully') ;
			redirect(base_url().'admin/categories', 'refresh');
		}
		
		$data['main_categories'] 	= $this->Admin_model->get_data_asc('main_categories');
		$data['categories'] 	= $this->Admin_model->get_data_asc('categories');
		$data['page_title'] 	= 'Manage Categories';
		$data['page_sub_title'] = 'Update, Delete, And View All Categories';
		$data['page_name'] = 'categories';
		$this->load->view('admin/index', $data);
	}
	
	public function sub_categories($param1='', $param2=''){
		if($this->session->userdata('admin_login') != 1){
			redirect(base_url().'admin', 'refresh');
		}
		if($param1 == 'update'){
			$response = $this->Admin_model->sub_category_update();
			$this->session->set_flashdata('msg_success', ' Data Updated successfully ') ;
			redirect(base_url().'admin/sub_categories', 'refresh');
		}if($param1 == 'delete'){
			$response = $this->Admin_model->delete_data('sub_categories', $param2);
			$this->session->set_flashdata('msg_success', ' Data Deleted successfully ') ;
			redirect(base_url().'admin/sub_categories', 'refresh');
		}if($param1 == 'create'){
			$response = $this->Admin_model->add_new_sub_category();
			$this->session->set_flashdata('msg_success', ' Data Saved successfully') ;
			redirect(base_url().'admin/sub_categories', 'refresh');
		}
		
		$data['main_categories'] 	= $this->Admin_model->get_data_asc('main_categories');
		$data['categories'] 	= $this->Admin_model->get_data_asc('categories');
		$data['sub_categories'] 	= $this->Admin_model->get_data_asc('sub_categories');
		$data['page_title'] 	= 'Manage Sub Categories';
		$data['page_sub_title'] = 'Update, Delete, And View All Sub Categories';
		$data['page_name'] = 'sub_categories';
		$this->load->view('admin/index', $data);
	}
	
	public function shows($param1='', $param2=''){
		if($this->session->userdata('admin_login') != 1){
			redirect(base_url().'admin', 'refresh');
		}
		if($param1 == 'update'){
			$response = $this->Admin_model->shows_update();
			$this->session->set_flashdata('msg_success', ' Data Updated successfully ') ;
			redirect(base_url().'admin/shows', 'refresh');
		}if($param1 == 'delete'){
			$response = $this->Admin_model->delete_data('shows', $param2);
			$this->session->set_flashdata('msg_success', ' Data Deleted successfully ') ;
			redirect(base_url().'admin/shows', 'refresh');
		}if($param1 == 'create'){
			$response = $this->Admin_model->add_new_show();
			$this->session->set_flashdata('msg_success', ' Data Saved successfully') ;
			redirect(base_url().'admin/shows', 'refresh');
		}if($param1 == 'update_show_folder'){
		    
			$response = $this->Admin_model->update_show_folder();
		
			$this->session->set_flashdata('msg_success', ' Data Saved successfully') ;
			redirect(base_url().'admin/shows', 'refresh');
		}
		
		$data['shows'] 	= $this->Admin_model->get_data_desc('shows');
		$data['page_title'] 	= 'Manage Shows';
		$data['page_sub_title'] = 'Update, Delete, And View All Shows';
		$data['page_name'] = 'shows';
		$this->load->view('admin/index', $data);
	}
	
		
	public function shows_add($param1='', $param2=''){
		if($this->session->userdata('admin_login') != 1){
			redirect(base_url().'admin', 'refresh');
		}
		if($param1 == 'create'){
			$response = $this->Admin_model->add_new_show();
			$this->session->set_flashdata('msg_success', ' Data Saved successfully') ;
			redirect(base_url().'admin/shows_add', 'refresh');
		}
		
		$data['categories'] 	= $this->Admin_model->get_data_conditions('categories', 'main_categories_id=2')->result_array();
		$data['page_title'] 	= 'Add New Show';
		$data['page_sub_title'] = 'Update, Delete, And View All Shows';
		//$data['page_name'] = 'shows_add';
		$data['page_name'] = 'shows_add_ispring';
		$this->load->view('admin/index', $data);
	}
	
	public function fake_shows($param1='', $param2=''){
		if($this->session->userdata('admin_login') != 1){
			redirect(base_url().'admin', 'refresh');
		}
		if($param1 == 'update'){
		   
			$response = $this->Admin_model->fake_shows_update();
			$this->session->set_flashdata('msg_success', ' Data Updated successfully ') ;
			redirect(base_url().'admin/fake_shows', 'refresh');
		}if($param1 == 'delete'){
			$response = $this->Admin_model->delete_data('fake_shows', $param2);
			$this->session->set_flashdata('msg_success', ' Data Deleted successfully ') ;
			redirect(base_url().'admin/fake_shows', 'refresh');
		}if($param1 == 'create'){
			$response = $this->Admin_model->add_new_show();
			$this->session->set_flashdata('msg_success', ' Data Saved successfully') ;
			redirect(base_url().'admin/fake_shows', 'refresh');
		}
		
		$data['shows'] 	= $this->Admin_model->get_data_desc('fake_shows');
		$data['page_title'] 	= 'Manage Fake Shows';
		$data['page_sub_title'] = 'Update, Delete, And View All Fake Shows';
		$data['page_name'] = 'fake_shows';
		$this->load->view('admin/index', $data);
	}
	
		
	public function fake_shows_add($param1='', $param2=''){
		if($this->session->userdata('admin_login') != 1){
			redirect(base_url().'admin', 'refresh');
		}
		if($param1 == 'create'){
			$response = $this->Admin_model->add_new_fake_show();
			$this->session->set_flashdata('msg_success', ' Data Saved successfully') ;
			redirect(base_url().'admin/fake_shows_add', 'refresh');
		}
		
		$data['categories'] 	= $this->Admin_model->get_data_conditions('categories', 'main_categories_id=2')->result_array();
		$data['page_title'] 	= 'Add New Fake Show';
		$data['page_sub_title'] = 'Update, Delete, And View All Shows';
		//$data['page_name'] = 'fake_shows_add';
		$data['page_name'] = 'fake_shows_add';
		$this->load->view('admin/index', $data);
	}
	
	
	public function blogs($param1='', $param2=''){
		if($this->session->userdata('admin_login') != 1){
			redirect(base_url().'admin', 'refresh');
		}
		if($param1 == 'update'){
			$response = $this->Admin_model->blogs_update(); 
			$this->session->set_flashdata('msg_success', ' Data Updated successfully ');
			redirect(base_url().'admin/blogs_edit/'.$this->input->post('blogs_id'), 'refresh');
		}if($param1 == 'delete'){
			$response = $this->Admin_model->delete_data('blogs', $param2);
			$this->session->set_flashdata('msg_success', ' Data Deleted successfully ') ;
			redirect(base_url().'admin/blogs', 'refresh');
		}if($param1 == 'create'){
			$response = $this->Admin_model->add_new_show();
			$this->session->set_flashdata('msg_success', ' Data Saved successfully') ;
			redirect(base_url().'admin/blogs', 'refresh');
		}
	    if($param1 == 'status'){
			$response = $this->Admin_model->blog_publish_unpublish($param2);
			$this->session->set_flashdata('msg_success', ' Status Changed successfully ') ;
			redirect(base_url().'admin/blogs', 'refresh');
		}
		if($param1 == 'paid_blog'){
			$response = $this->Admin_model->blog_paid($param2);
			$this->session->set_flashdata('msg_success', ' Status Changed successfully ') ;
			redirect(base_url().'admin/blogs', 'refresh');
		}
		
		$data['blogs'] 	= $this->Admin_model->get_data_orderwise('blogs','blogs_id');
		$data['page_title'] 	= 'Manage Blogs';
		$data['page_sub_title'] = 'Update, Delete, And View All Blogs';
		$data['page_name'] = 'blogs';
		$this->load->view('admin/index', $data);
	}
	
		
	public function blogs_edit($param1='', $param2=''){
	   
		if($this->session->userdata('admin_login') != 1){
			redirect(base_url().'admin', 'refresh');
		}
		
		//$data['blogs'] 	= $this->Admin_model->get_data_desc('blogs');
		$data['param2'] 	= $param1;
		$data['page_title'] 	= 'Manage Blogs';
		$data['page_sub_title'] = 'Update, Delete, And View All Blogs';
		$data['page_name'] = 'blogs_edit_page';
		$data['shows'] 	= $this->Admin_model->get_data_desc('shows');
	
		$this->load->view('admin/index', $data);
	}
	
				
	public function shows_edit($param1='', $param2=''){
		if($this->session->userdata('admin_login') != 1){
			redirect(base_url().'admin', 'refresh');
		}
		
		//$data['blogs'] 	= $this->Admin_model->get_data_desc('blogs');
		$data['param2'] 	= $param1;
		$data['page_title'] 	= 'Manage Shows';
		$data['page_sub_title'] = 'Update, Delete, And View All Shows';
		$data['page_name'] = 'shows_edit';
		$this->load->view('admin/index', $data);
	}
	
	public function fake_shows_edit($param1='', $param2=''){
		if($this->session->userdata('admin_login') != 1){
			redirect(base_url().'admin', 'refresh');
		}
		
		//$data['blogs'] 	= $this->Admin_model->get_data_desc('blogs');
		$data['param2'] 	= $param1;
		$data['page_title'] 	= 'Manage Shows';
		$data['page_sub_title'] = 'Update, Delete, And View All Shows';
		$data['page_name'] = 'fake_shows_edit';
		$this->load->view('admin/index', $data);
	}
	
	public function blogs_add($param1='', $param2=''){
		if($this->session->userdata('admin_login') != 1){
			redirect(base_url().'admin', 'refresh');
		}
		if($param1 == 'create'){
			$response = $this->Admin_model->add_new_blog();
			$this->session->set_flashdata('msg_success', ' Data Saved successfully') ;
			redirect(base_url().'admin/blogs_add', 'refresh');
		}
		
		$data['main_categories'] 	= $this->Admin_model->get_data_conditions('main_categories', ' main_categories_id != 2')->result_array();
		$data['sub_categories'] 	= $this->Admin_model->get_data_conditions('sub_categories', ' main_categories_id != 2')->result_array();
		$data['shows'] 	= $this->Admin_model->get_data_desc('shows');
// 		echo "<pre>";
// 		print_r($data['shows']);
// 		exit;
		$data['categories'] 	= $this->Admin_model->get_data_conditions('categories', ' main_categories_id != 2')->result_array();
		$data['page_title'] 	= 'Add New Show';
		$data['page_sub_title'] = 'Update, Delete, And View All Blogs';
		$data['page_name'] = 'blogs_add';
		$this->load->view('admin/index', $data);
	}
	
	public function tinymceUpload(){
	    // Handle image upload
        if (isset($_FILES['file'])) {
            $file = $_FILES['file'];
            $fileName = $file['name'];
            $fileTmpName = $file['tmp_name'];
            $fileError = $file['error'];
        
            // Handle upload errors
            if ($fileError === UPLOAD_ERR_OK) {
                // Move uploaded file to desired location
                move_uploaded_file($fileTmpName, 'uploads/' . $fileName);
                // Respond with the URL of the uploaded image
                echo json_encode(['location' => base_url().'uploads/' . $fileName]);
            } else {
                echo json_encode(['error' => 'Upload failed']);
            }
        } else {
            echo json_encode(['error' => 'No file uploaded']);
        }

	}
	
		public function unpublished_blogs($param1='', $param2=''){
		if($this->session->userdata('admin_login') != 1){
			redirect(base_url().'admin', 'refresh');
		}
		if($param1 == 'update'){
			$response = $this->Admin_model->blogs_update(); 
			$this->session->set_flashdata('msg_success', ' Data Updated successfully ') ;
			redirect(base_url().'admin/blogs_edit/'.$this->input->post('blogs_id'), 'refresh');
		}if($param1 == 'delete'){
			$response = $this->Admin_model->delete_data('blogs', $param2);
			$this->session->set_flashdata('msg_success', ' Data Deleted successfully ') ;
			redirect(base_url().'admin/blogs', 'refresh');
		}if($param1 == 'create'){
			$response = $this->Admin_model->add_new_show();
			$this->session->set_flashdata('msg_success', ' Data Saved successfully') ;
			redirect(base_url().'admin/blogs', 'refresh');
		}
	    if($param1 == 'status'){
			$response = $this->Admin_model->blog_publish_unpublish($param2);
			$this->session->set_flashdata('msg_success', ' Status Changed successfully ') ;
			redirect(base_url().'admin/blogs', 'refresh');
		}
		
		$data['blogs'] 	= $this->Admin_model-> get_data_orderwise_unpublished('blogs','b_order');
		$data['page_title'] 	= 'Manage Blogs';
		$data['page_sub_title'] = 'Update, Delete, And View All Blogs';
		$data['page_name'] = 'blogs';
		$this->load->view('admin/index', $data);
	}
	
    	public function preview_blogs($param){
    		$data['system_setting'] = $this->Admin_model->get_row_id('system_setting', 1);
    		$data['blog'] = $this->Admin_model->get_row_id_preview('blogs', $param);
    		//$data['response'] = $param;
    		$data['blogs'] = $this->Admin_model->get_data_desc_preview('blogs');
    		$data['page_title'] = 'home';
    		$data['page_name'] = 'preview_blogs';
    		$data['param2'] 	= $param;
    		$this->load->view('admin/preview_blogs',$data);
    	
    	}
    	
    	public function admin_emails($param1='', $param2=''){
    	    
    	if($this->session->userdata('admin_login') != 1){
    		redirect(base_url().'admin', 'refresh');
    	}
    	if($param1 == 'update'){
    		$response = $this->Admin_model->update_admin_email();
    		$this->session->set_flashdata('msg_success', ' Data Updated successfully ') ;
    		redirect(base_url().'admin/admin_emails', 'refresh');
    	}if($param1 == 'delete'){
    		$response = $this->Admin_model->delete_data('admin_emails', $param2);
    		$this->session->set_flashdata('msg_success', ' Data Deleted successfully ') ;
    		redirect(base_url().'admin/admin_emails', 'refresh');
    	}if($param1 == 'create'){
    		$response = $this->Admin_model->add_new_admin_email();
    		$this->session->set_flashdata('msg_success', ' Data Saved successfully') ;
    		redirect(base_url().'admin/admin_emails', 'refresh');
    	}
    	
    	$data['admin_emails'] 	= $this->Admin_model->get_data_desc('admin_emails');
    	$data['page_title'] 	= 'Manage Admin Emails';
    	$data['page_sub_title'] = 'Update, Delete, And View All Users';
    	$data['page_name'] = 'admin_email';
    	
    	$this->load->view('admin/index', $data);
    }
    
    public function create_admin_email(){
        if($this->session->userdata('admin_login') != 1){
    		redirect(base_url().'admin', 'refresh');
    	}
    	$data['page_title'] 	= 'Add New Email';
    	$data['page_sub_title'] = 'Create a New Email';
    	$data['page_name'] = 'create_admin_email';
    	
    	$this->load->view('admin/index', $data);
    }
    
    public function edit_admin_email($id){
        if($this->session->userdata('admin_login') != 1){
    		redirect(base_url().'admin', 'refresh');
    	}
    	$data['admin_email'] 	= $this->Admin_model->get_data_id('admin_emails',$id)->result_array();
    	
    // 	echo "<pre>";
    // 	print_r($data);
    // 	exit;
    	
    	$data['page_title'] 	= 'Edit Email';
    	$data['page_sub_title'] = 'Edit an Email';
    	$data['page_name'] = 'edit_admin_email';
    	
    	$this->load->view('admin/index', $data);
    }
    
    public function visitors($param1=''){
		if($this->session->userdata('admin_login') != 1){
			redirect(base_url().strtolower($this->session->userdata('role_name')), 'refresh');
		}
		
		
		$this->db->select('DATE(created_at) as visit_date, SUM(visitors) as total_visitors');
        $this->db->from('visitors');
        $this->db->group_by('visit_date'); // Group by the date part of created_at
        $this->db->order_by('visit_date', 'ASC'); // Optional: Order by date
        $query = $this->db->get();
        $visitor_data = $query->result_array();
        
        $this->db->select('id');
        $this->db->from('visitors');
        $query = $this->db->get();
        $total_record = $query->result_array();
        
        $data['dates'] = array_column($visitor_data, 'visit_date');
        $data['counts'] = array_column($visitor_data, 'total_visitors');
        $counter = 0;
        foreach($total_record as $visitor){
            $counter++;
        }
        $data['total_visitors'] = $counter;
		$data['page_title'] 	= 'Visitors';
		$data['page_sub_title'] = 'Website Visitors';
		$data['page_name'] = 'visitors';
		$this->load->view(strtolower($this->session->userdata('role_name')) .'/index', $data);
	}
	
	public function paid_status(){
	    
	}
		
	
	
	
}
