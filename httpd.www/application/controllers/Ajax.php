<?php
	class Ajax extends CI_Controller{
		
		function __construct() {
			parent::__construct();
			//$this->load->model('App_model');
			$this->load->library('session');   
			$this->load->library('upload');
	    }
		
		
		public function index(){
			echo 'WELLCOME, ';
		}
		public function get_categories(){
		$mid = $this->input->post('mid');	
		$rows = $this->db->get_where('categories', array('main_categories_id'=>$mid))->result_array();
			if($rows){
				foreach($rows as $row){
					echo '<option value="'.$row['categories_id'].'">'.$row['name'].'</option>';
				}
				echo '<option value="15">Extra</option>';
			}else{
					echo '<option value="">No Record Found</option>';
			}
		}
		
		public function get_sub_categories(){
		$cid = $this->input->post('cid');	
		
		$this->db->where_in('categories_id', $cid);
        $rows = $this->db->get('sub_categories')->result_array();
			if($rows){
				echo '<option value="">Select Sub Category</option>';
				foreach($rows as $row){
					echo '<option value="'.$row['sub_categories_id'].'">'.$row['name'].'</option>';
				}
				echo '<option value="15">Extra</option>';
			}else{
					echo '<option value="">No Record Found</option>';
			}
		}
		
		public function update_access(){	
			$save_data['access'] = $this->input->post('access');
			$this->db->where('shows_id' , $this->input->post('sid'));
			$this->db->update('shows' , $save_data); 
		}
		
		public function update_screen(){
			if($this->input->post('access') == 'PC'){
				$save_data['is_pc'] = $this->input->post('status');
			}elseif($this->input->post('access') == 'Mobile'){
				$save_data['is_mobile'] = $this->input->post('status');
			}elseif($this->input->post('access') == 'Live'){
				$save_data['is_live'] = $this->input->post('status');
			}
			$this->db->where('shows_id' , $this->input->post('sid'));
			$this->db->update('shows' , $save_data); 
		}
		
		public function fake_update_access(){	
			$save_data['access'] = $this->input->post('access');
			$this->db->where('fake_shows_id' , $this->input->post('sid'));
			$this->db->update('fake_shows' , $save_data); 
		}
		
		public function update_corder(){	
		    $save_data['c_order'] = $this->input->post('order');
			$this->db->where('categories_id' , $this->input->post('id'));
			$this->db->update('categories' , $save_data); 
		}
		
		public function update_sorder(){	
			$save_data['s_order'] = $this->input->post('order');
			$this->db->where('sub_categories_id' , $this->input->post('id'));
			$this->db->update('sub_categories' , $save_data); 
		}
		
		public function update_border(){	
			$save_data['b_order'] = $this->input->post('order');
			$this->db->where('blogs_id' , $this->input->post('id'));
			$this->db->update('blogs' , $save_data); 
		}
		
		public function update_approval_status(){	
			$save_data['approval_status'] = $_REQUEST['status'];
			$this->db->where('users_id' , $_REQUEST['id']);
			$this->db->update('users' , $save_data); 
		}
		
		public function fake_update_screen(){
			if($this->input->post('access') == 'PC'){
				$save_data['is_pc'] = $this->input->post('status');
			}elseif($this->input->post('access') == 'Mobile'){
				$save_data['is_mobile'] = $this->input->post('status');
			}elseif($this->input->post('access') == 'Live'){
				$save_data['is_live'] = $this->input->post('status');
			}
			$this->db->where('fake_shows_id' , $this->input->post('sid'));
			$this->db->update('fake_shows' , $save_data); 
		}
		
		public function get_image_url(){
		$shows_id = $this->input->post('shows_id');	
		$rows = $this->db->get_where('shows', array('shows_id'=>$shows_id))->result_array();
		
			if($rows){
			    foreach($rows as $row){
				    echo base_url()."uploads/admin/shows/image/".$row['image'];
			    }
			}else{
					echo '<option value="">No Record Found</option>';
			}
		}
		
		public function get_video_images() {  // Use CodeIgniter input class to get the request parameter
		    $id = $_REQUEST['id'];
            $rows = $this->db->get_where('blogs', array('blogs_id' => $id))->result_array();
        
            if ($rows) {
                foreach ($rows as $row) {
                     $var = json_decode($row['v_image']);
                     echo json_encode($var);
                }
            } else {
                return json_encode([]);
            }
        }

		
		public function delete_v_image(){	
			$id = $_REQUEST['id'];
            $image = $_REQUEST['image'];
    
          unlink(base_url().'uploads/admin/blogs/'.$image);
          $rows = $this->db->get_where('blogs', array('blogs_id'=>$id))->result_array();
         
    	  if($rows){
    	      foreach($rows as $row){
    	          $imagedata = $row['v_image'];
                  $imagedata = json_decode($imagedata);
                  $variable = '';
                  for($i=0;$i<count($imagedata);$i++){
                      if($imagedata[$i] != $image){
                          if($i==0){
                              $variable = $imagedata[$i];
                          }else{
                              if($variable){
                                $variable .= ','.$imagedata[$i];
                              }else{
                                $variable = $imagedata[$i];
                              }
                          }
                      }
                  }
                 if($variable){
                    $Modeldata = array(
            
                        'v_image'    =>  json_encode(explode(',',$variable)),
              
                    );
                 }else{
                    $Modeldata = array(
            
                        'v_image'    =>  null,
              
                    );
                 }
           
                 
                  $this->db->where('blogs_id' , $this->input->post('id'));
			      $this->db->update('blogs' , $Modeldata); 
    	      }
    	  }
          
		}
	}
?>