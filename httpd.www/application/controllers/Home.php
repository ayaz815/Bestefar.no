<?php
	class Home extends CI_Controller{
		
		function __construct() {
			parent::__construct();
			//$this->load->model('App_model');
			$this->load->library('session');   
			$this->load->library('upload');
			
	    }
		
		
		public function index(){
		    $this->db->select('visitors');                  // Select all columns
            $this->db->from('visitors');              // Specify the table
            $this->db->order_by('id', 'DESC');        // Order by 'id' in descending order
            $this->db->limit(1);                      // Limit the result to the last record
            $query = $this->db->get(); 
            $visitor_count = $query->row()->visitors;
            
            if($visitor_count > 0){
                $visitor_count+=1;
            }else{
                $visitor_count = 1;
            }
		    $save_data['visitors'] = 1;
		    $this->db->insert('visitors' , $save_data);
		  //  $this->db->where('id' , 1);
// 			$this->db->update('visitors' , $save_data);
			$data['system_setting'] = $this->Admin_model->get_row_id('system_setting', 1);
			//$data['competitions'] 	= $this->Admin_model->get_data_asc('competitions');
			
			$data['page_title'] = 'Home';
			$data['page_name'] = 'home';
			
			$blogsn = $this->Admin_model->get_data_conditions('blogs', ' home_page=1');
// 			echo "<pre>";
// 			print_r($blogsn);
// 			exit;
			if($blogsn->num_rows() > 0){
				$link = base_url().'home/blog_details/'.$blogsn->row()->blogs_id;
				redirect($link , 'refresh');
			}else{
			    
				$this->load->view('frontend/home');
			}
			
// 			$this->load->view('frontend/home');
		}
		
		public function registration_msg($param=''){
			$data['system_setting'] = $this->Admin_model->get_row_id('system_setting', 1);
			$data['response'] = $param;
			$data['page_title'] = 'Home';
			$data['page_name'] = 'registration_msg';
			$this->load->view('frontend/index',$data);
		}

		// The "Spørreleker" catalog: UI-only for now, using hardcoded sample rows
		// straight from the mockup while the real backend is built separately.
		public function sporreleker(){
			$quiz_type = $this->input->get('type');
			$sort      = $this->input->get('sort') ?: 'date';
			$dir       = ($this->input->get('dir') == 'asc') ? 'asc' : 'desc';

			$quizzes = array(
				array('quiz_type' => 'AudioQuiz',    'name' => 'Kassisk musikk',  'question_count' => 16, 'date' => '2026-04-12', 'info' => ''),
				array('quiz_type' => 'BildeQuiz',    'name' => 'Byer i Europa',   'question_count' => 16, 'date' => '2026-04-18', 'info' => ''),
				array('quiz_type' => 'TekstQuiz',    'name' => 'Sitater',         'question_count' => 32, 'date' => '2026-04-24', 'info' => ''),
				array('quiz_type' => 'BilderMusikk', 'name' => 'Ansikter',        'question_count' => 56, 'date' => '2026-04-29', 'info' => ''),
			);

			if($quiz_type){
				$quizzes = array_values(array_filter($quizzes, function($q) use ($quiz_type){
					return $q['quiz_type'] == $quiz_type;
				}));
			}

			$columns = array('date' => 'date', 'category' => 'quiz_type', 'name' => 'name', 'count' => 'question_count');
			$sort_key = isset($columns[$sort]) ? $columns[$sort] : 'date';
			$sort_dir = (strtolower($dir) == 'asc') ? 1 : -1;
			usort($quizzes, function($a, $b) use ($sort_key, $sort_dir){
				if($a[$sort_key] == $b[$sort_key]){ return 0; }
				return ($a[$sort_key] < $b[$sort_key] ? -1 : 1) * $sort_dir;
			});

			$data['system_setting'] = $this->Admin_model->get_row_id('system_setting', 1);
			$data['quizzes']        = $quizzes;
			$data['quiz_type']      = $quiz_type;
			$data['sort']           = $sort;
			$data['dir']            = $dir;
			$data['page_title']     = 'Spørreleker';
			$data['page_name']      = 'sporreleker';
			$this->load->view('frontend/index', $data);
		}

    	public function blogs($param1 = '', $param2 = '') {
    	    $users_id = isset($_SESSION['users_id']) ? $_SESSION['users_id'] : null;
    	    $blog_ids = null;
    	    if($users_id){
    	       $user = $user = $this->Admin_model->get_row_id('users',$users_id);
    	       $blog_ids = json_decode($user->blog_id);
    	    }
    	    
    	    $data['param1'] = $param1;
    	    $data['param2'] = $param2;
    	    $blogsn = $this->Admin_model->get_data_conditions('blogs', ' home_page=1');
    	 
    	    if($blogsn->num_rows() > 0 && $param1 == '' && $param1 == ''){
				$link = base_url().'home/blog_details/'.$blogsn->row()->blogs_id;
				redirect($link , 'refresh');
			}else{
            $data['system_setting'] = $this->Admin_model->get_row_id('system_setting', 1);
            if (isset($_SESSION['users_id'])) {
                $status = "(status='Active' OR status='Draft')";
                } else {
                    $status = "(status='Active')";
                }
            
            $data['category']     = [];
            $data['sub_category'] = [];

            if ($param2 > 0) {

                $param2 = intval($param2);

                $this->db->select('*');
                $this->db->from('categories');
                $this->db->where('categories_id',$param1);
                $data['category'] = $this->db->get()->result_array();

                $this->db->select('*');
                $this->db->from('sub_categories');
                $this->db->where('sub_categories_id',$param2);
                $this->db->where('categories_id',$param1);
                $data['sub_category'] = $this->db->get()->result_array();
               
                $this->db->select('*');
                $this->db->from('blogs');
                $this->db->where("JSON_SEARCH(sub_categories_id, 'one', '$param2') IS NOT NULL", null, false);
                $this->db->where($status);
                if($blog_ids != null){
                    $this->db->or_where_in ('blogs_id',$blog_ids);
                }
                $this->db->where('paid',0);
                $data['blogs'] = $this->db->get()->result_array();
                
             
                // $data['blogs'] = $this->Admin_model->get_data_conditions_orderwise(
                //     'blogs', 
                //     "sub_categories_id = " . $param2 . " " . $status, 
                //     'b_order'
                // )->result_array();
            } else {
            // ini_set('display_errors', 1);
            // ini_set('display_startup_errors', 1);
            // error_reporting(E_ALL);
                $param1 = intval($param1); 
                $this->db->select('*');
                $this->db->from('categories');
                $this->db->where('categories_id',$param1);
                $data['category'] = $this->db->get()->result_array();
                //  echo "<pre>";
                // print_r($data['category']);
                // exit;
                $this->db->select('*');
                $this->db->from('blogs');
                $this->db->where("JSON_SEARCH(categories_id, 'one', '$param1') IS NOT NULL", null, false);
                $this->db->where($status);
                if($blog_ids != null){
                    $this->db->or_where_in ('blogs_id',$blog_ids);
                }
                $this->db->where('paid',0);
                $data['blogs'] = $this->db->get()->result_array();
            }
        
            $data['page_title'] = 'Home';
            $data['page_name'] = 'blogs_f';
            $this->load->view('frontend/index', $data);
			}
        }


		public function omideen($param=''){
			$data['system_setting'] = $this->Admin_model->get_row_id('system_setting', 1);
			//$data['response'] = $param;
			$data['blogs'] = $this->Admin_model->get_data_desc('blogs');
			$data['page_title'] = 'Home';
			$data['page_name'] = 'omideen';
			$this->load->view('frontend/index',$data);
		}

		public function blog_details($param=''){
		    $blogsn = $this->Admin_model->get_data_conditions('blogs', ' home_page=1');
		       		
    	    if($blogsn->num_rows() > 0 && $param == ''){
    	        
			    $data['system_setting'] = $this->Admin_model->get_row_id('system_setting', 1);
    			$data['blog'] = $this->Admin_model->get_row_id_active('blogs', $blogsn->row()->blogs_id);
    			
    			//$data['response'] = $param;
    			$data['blogs'] = $this->Admin_model->get_data_desc_active('blogs');
    			$data['page_title'] = 'Home';
    			$data['page_name'] = 'blog_details';
    // 			echo "<pre>"; print_r($data); die;
    			$this->load->view('frontend/index',$data);
                	
			}else{
			    $data['system_setting'] = $this->Admin_model->get_row_id('system_setting', 1);
    			$data['blog'] = $this->Admin_model->get_row_id_active('blogs', $param);
    			
    			//$data['response'] = $param;
    			$data['blogs'] = $this->Admin_model->get_data_desc_active('blogs');
    			$data['page_title'] = 'Home';
    			$data['page_name'] = 'blog_details';
    // 			echo "<pre>"; print_r($data); die;
    			$this->load->view('frontend/index',$data);
			}
			
		}
		 
		public function shows($param1='',$param2=''){
			$data['system_setting'] = $this->Admin_model->get_row_id('system_setting', 1);
		    if($param2>0){
		        $this->db->select('image');
    			$this->db->from('sub_categories');
    		    $this->db->where('sub_categories_id',$param2);
    			$query = $this->db->get(); 
    			$data['categories_data'] =  $query->result_array();
		        
		    }else{
		        $this->db->select('image');
    			$this->db->from('categories');
    		    $this->db->where('categories_id',$param1);
    			$query = $this->db->get(); 
    			$data['categories_data'] =  $query->result_array();
		    }
			
			
			
			$data['page_name'] = 'shows';
			$data['param1'] = $param1;
			$data['param2'] = $param2;
			$data['page_title'] = 'Home';
			$data['page_name'] = 'shows_f';
			$this->load->view('frontend/index',$data);
		}

		public function show_details($param=''){
		  //  $this->load->library('user_agent');
		  //  if($_SESSION['approval_status'] == 1){
		  //      $data['system_setting'] = $this->Admin_model->get_row_id('system_setting', 1);
    // 			//$data['response'] = $param;
    // 			$data['show'] = $this->Admin_model->get_row_id('shows', $param);
    // 			$data['page_title'] = 'Home';
    // 			$data['page_name'] = 'show_details';
    // 			$this->load->view('frontend/index',$data);
		  //  }else{
		        
		  //      $this->db->select('*');
    // 			$this->db->from('shows');
    // 			$conditions = 'shows_id='.$param;
    // 			$conditions .= ' AND access="Info"';
    // 			$this->db->where($conditions);
    // 			$shows  = $this->db->get()->result_array();
    		    
    // 			if($shows){
    // 			    $data['system_setting'] = $this->Admin_model->get_row_id('system_setting', 1);
    //     			//$data['response'] = $param;
    //     			$data['show'] = $this->Admin_model->get_row_id('shows', $param);
    //     			$data['page_title'] = 'Home';
    //     			$data['page_name'] = 'show_details';
    // 			    $this->load->view('frontend/index',$data);
    			    
    // 			}else{
    // 			     redirect($this->agent->referrer());
    // 			}
		  //  }
		  
	        $data['system_setting'] = $this->Admin_model->get_row_id('system_setting', 1);
			//$data['response'] = $param;
			$data['show'] = $this->Admin_model->get_row_id('shows', $param);
			$data['page_title'] = 'Home';
			$data['page_name'] = 'show_details';
			$this->load->view('frontend/index',$data);
			
		    
		}
		 
		public function show_details_2($param=''){
			$data['system_setting'] = $this->Admin_model->get_row_id('system_setting', 1);
			//$data['response'] = $param;
			$data['show'] = $this->Admin_model->get_row_id('fake_shows', $param);
			$data['page_title'] = 'Home';
			$data['page_name'] = 'fake_show_details';
			$this->load->view('frontend/index',$data);
		}
		 
		public function yellow($param=''){
			$data['system_setting'] = $this->Admin_model->get_row_id('system_setting', 1);
			$data['page_title'] = 'Home';
			$data['page_name'] = 'yellow';
			$this->load->view('frontend/index',$data);
		}
		 
		public function blue($param=''){
			$data['system_setting'] = $this->Admin_model->get_row_id('system_setting', 1);
			$data['page_title'] = 'Home';
			$data['page_name'] = 'blue';
			$this->load->view('frontend/index',$data);
		}
		 
		public function save_cart(){
			$this->Admin_model->cart_data_saved();
		}
		
		
		
		public function check(){
		 exit;
		}
		
	

		
		
	}
?>