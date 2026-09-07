<?php
 
//push Notification 

	class Admin_model extends CI_Model{
		function __construct() {			
			parent::__construct();
			$this->load->database();
			$this->output->set_header('Pragma: no-cache');
			$this->load->helper('date');
		}
		
		function admin_login_varify(){
			$username= $this->input->post('username');
			$password= $this->input->post('password');
			
		    $this->db->select();
			$this->db->from('admin');
			$this->db->where('username',$username);
			$this->db->where('password',$password);
			$query = $this->db->get(); 
			if($query->num_rows()== 1){
			  return $query->row();
			}else{ 
			  return false;
			}	
		}
		
		function user_login_varify(){
			$username= $this->input->post('email');
			$password= $this->input->post('password');
			
		    $this->db->select();
			$this->db->from('users');
			$this->db->where('email',$username);
			$this->db->where('password',$password);
			$query = $this->db->get(); 
			if($query->num_rows()== 1){
			  return $query->row();
			}else{ 
			  return false;
			}	
		}
		
		function activate_me($id){
		    $save_data['email_varified'] = 1;
			$this->db->where('users_id' , $id);
			$this->db->update('users' , $save_data); 
		}
		
		function get_total_rows($table){
		    $this->db->select();
			$this->db->from($table);
			$query = $this->db->get(); 
			return $query->num_rows();
		}
		
		
		function get_data_desc($table){
		    $this->db->select();
			$this->db->from($table);
		    $this->db->order_by($table.'_id','DESC');
			$query = $this->db->get(); 
			return $query->result_array();
		}
		
		function get_data_desc_active($table){
		    $this->db->select();
			$this->db->from($table);
			$this->db->where("status","Active");
		    $this->db->order_by($table.'_id','DESC');
			$query = $this->db->get(); 
			return $query->result_array();
		}
		
		function get_data_asc($table){
		    $this->db->select();
			$this->db->from($table);
		    $this->db->order_by($table.'_id','ASC');
			$query = $this->db->get(); 
			return $query->result_array();
		}
		
		
		function get_data_orderwise($table,$order){
		    $this->db->select();
			$this->db->from($table);
			$this->db->where("status","Active");
		    $this->db->order_by($order,'DESC');
			$query = $this->db->get(); 
			return $query->result_array();
		}
		
		function get_data_id($table, $id){
		    $this->db->select();
			$this->db->from($table);
			$this->db->where($table.'_id',$id);
			$query = $this->db->get(); 
			return $query;
		}
		
		function get_data_conditions($table, $conditions){ 
		    $this->db->select();
			$this->db->from($table);
			$this->db->where($conditions);
			$query = $this->db->get(); 
			return $query;
		}
		
		function get_data_conditions_orderwise($table, $conditions, $order){ 
		    $this->db->select();
			$this->db->from($table);
			$this->db->order_by($order,'ASC');
			$this->db->where($conditions);
			$query = $this->db->get(); 
			return $query;
		}
		
		function get_tc($table, $id, $coloumn){
		    // If id is a JSON array, extract the first value
		    if(is_string($id) && substr(trim($id),0,1) === '['){
		        $decoded = json_decode($id, true);
		        $id = (!empty($decoded) && is_array($decoded)) ? $decoded[0] : $id;
		    }
		    $this->db->select();
			$this->db->from($table);
			$this->db->where($table.'_id', $id);
			$query = $this->db->get();
			$row = $query->row();
			return ($row && isset($row->$coloumn)) ? $row->$coloumn : '';
		}
		
		function delete_data($table, $id){
			$this->db->where($table.'_id', $id);
			$this->db->delete($table);
		}
		
		function get_row_id($table, $id){
		    $this->db->select();
			$this->db->from($table);
			$this->db->where($table.'_id',$id);
			$query = $this->db->get()->row(); 
			return $query;
		}
		
		function get_row_id_active($table, $id){
		    
		    $this->db->select();
			$this->db->from($table);
			$this->db->where($table.'_id',$id);
			if(isset($_SESSION['users_id'])){
			    $status = array('Active', 'Draft');    
			}else{
			    $status = array('Active');
			}
            $this->db->where_in('status', $status);
			$query = $this->db->get()->row(); 
			return $query;
		}
		
		function get_array_id($table, $id){
		    $this->db->select();
			$this->db->from($table);
			$this->db->where($table.'_id',$id);
			$query = $this->db->get()->result_array(); 
			return $query;
		}
		
 		function update_admin_profile(){
		    $save_data['name'] = $this->input->post('name');
			$save_data['phone'] = $this->input->post('phone');
			$save_data['email'] = $this->input->post('email');
			$save_data['username'] = $this->input->post('username');
			$save_data['password'] = $this->input->post('password');
			$save_data['address'] = $this->input->post('address');
			if(!empty($_FILES['user_image']['name'])){
				$file_name = 'admin_'.$this->session->userdata('admin_id').'.jpg';
				$path_to_file = 'uploads/admin/'.$file_name;
				move_uploaded_file($_FILES['user_image']['tmp_name'], $path_to_file);
				$save_data['user_image'] = $file_name;
			}
			$this->db->where('admin_id' , $this->session->userdata('admin_id'));
			$this->db->update('admin' , $save_data); 
			$this->session->set_userdata('name',$save_data['name']);
		}
		
		
		function update_system_setting(){
		    $save_data['system_name'] = $this->input->post('system_name');
			$save_data['address'] = $this->input->post('address');
			$save_data['phone'] = $this->input->post('phone');
			$save_data['email'] = $this->input->post('email');
			if(!empty($_FILES['system_image']['name'])){
				//$file_name = time() . '-' .$_FILES['system_image']['name'];
				$file_name = 'system_image.jpg';
				$path_to_file = 'uploads/admin/'.$file_name;
				move_uploaded_file($_FILES['system_image']['tmp_name'], $path_to_file);
				$save_data['system_image'] = $file_name;
			}
			$this->db->where('system_setting_id' , $this->input->post('system_setting_id'));
			$this->db->update('system_setting' , $save_data); 
		}
		
		
		function add_new_show(){
		    
			//echo $device = $this->input->post('device'); 
			if($this->input->post('pc')){
				$save_data['is_pc'] = 'Yes';
			}if($this->input->post('mobile')){
				$save_data['is_mobile'] = 'Yes';
			}if($this->input->post('live')){
				$save_data['is_live'] = 'Yes';
			}
			//if($device)
		    $save_data['name'] = $this->input->post('name');
			$save_data['categories_id'] = $this->input->post('categories_id');
			$save_data['sub_categories_id'] = $this->input->post('sub_categories_id');
			$save_data['access'] = $this->input->post('access');
			$save_data['status'] = 'Active';
			$save_data['date'] = date('Y-m-d H:i:s');
			$save_data['description'] = $this->input->post('description');
			//$save_data['link'] = $this->input->post('link');
			/* --- Start 1-- */
			$folder_name = time() . '_f';
			mkdir("uploads/admin/shows/folder/".$folder_name); 
			mkdir("uploads/admin/shows/folder/".$folder_name."/data");
			 /* --- End 1 -- */
			if(!empty($_FILES['image']['name'])){
				$file_name = time() . '-' .$_FILES['image']['name'];
				$file_name = $file_name ;
				$path_to_file = 'uploads/admin/shows/image/'.$file_name;
				move_uploaded_file($_FILES['image']['tmp_name'], $path_to_file);
				$save_data['image'] = $file_name;
			}if(!empty($_FILES['pdf']['name'])){
				$file_name = time() . '-' .$_FILES['pdf']['name'];
				 $file_name = $file_name ; 
				$path_to_file = 'uploads/admin/shows/pdf/'.$file_name;
				move_uploaded_file($_FILES['pdf']['tmp_name'], $path_to_file);
				$save_data['pdf'] = $file_name; 
			}
			/* --- Start 2-- */
			if(!empty($_FILES['html']['name'])){
				//$file_name = time() . '-' .$_FILES['html']['name'];
				$file_name = $_FILES['html']['name'];
				$file_name = $file_name ;
				$path_to_file = 'uploads/admin/shows/folder/'.$folder_name.'/'.$file_name;
				move_uploaded_file($_FILES['html']['tmp_name'], $path_to_file);
				$save_data['html'] = $file_name; 
			}if(!empty($_FILES['folder']['name'])){
				
				foreach($_FILES['folder']['name'] as $i => $name)
				{
					$path_to_file = 'uploads/admin/shows/folder/'.$folder_name."/data/".$name;
					if(strlen($_FILES['folder']['name'][$i]) > 1){
						move_uploaded_file($_FILES['folder']['tmp_name'][$i],$path_to_file);
					}
				}
				$save_data['folder'] = $folder_name; 
			} 
			/* --- End 2-- */
			$this->db->insert('shows' , $save_data);  
		}
		
		
		function shows_update(){
			//echo $device = $this->input->post('device'); 
			if($this->input->post('pc')){ $save_data['is_pc'] = 'Yes'; }else{ $save_data['is_pc'] = 'No'; }
			if($this->input->post('mobile')){ $save_data['is_mobile'] = 'Yes'; }else{ $save_data['is_mobile'] = 'No'; }
			if($this->input->post('live')){ $save_data['is_live'] = 'Yes'; }else{ $save_data['is_live'] = 'No'; }
			
			//if($device)
		    $save_data['name'] = $this->input->post('name');
			$save_data['categories_id'] = $this->input->post('categories_id');
			$save_data['sub_categories_id'] = $this->input->post('sub_categories_id');
			$save_data['access'] = $this->input->post('access');
			//$save_data['status'] = 'Active';
			//$save_data['date'] = date('Y-m-d H:i:s');
			$save_data['description'] = $this->input->post('description');
			$save_data['link'] = $this->input->post('link');
			/* --- Start 1-- */
			$folder_name = time() . '_f';
			mkdir("uploads/admin/shows/folder/".$folder_name); 
			mkdir("uploads/admin/shows/folder/".$folder_name."/data");
			 /* --- End 1-- */
			if(!empty($_FILES['image']['name'])){
				$file_name = time() . '-' .$_FILES['image']['name'];
				$file_name = $file_name ;
				$path_to_file = 'uploads/admin/shows/image/'.$file_name;
				move_uploaded_file($_FILES['image']['tmp_name'], $path_to_file);
				$save_data['image'] = $file_name;
			}if(!empty($_FILES['pdf']['name'])){
				$file_name = time() . '-' .$_FILES['pdf']['name'];
				 $file_name = $file_name ; 
				$path_to_file = 'uploads/admin/shows/pdf/'.$file_name;
				move_uploaded_file($_FILES['pdf']['tmp_name'], $path_to_file);
				$save_data['pdf'] = $file_name; 
			}
			/* --- Start 2-- */
			if(!empty($_FILES['html']['name'])){
				//$file_name = time() . '-' .$_FILES['html']['name'];
				$file_name = $_FILES['html']['name'];
				$file_name = $file_name ;
				$path_to_file = 'uploads/admin/shows/folder/'.$folder_name.'/'.$file_name;
				move_uploaded_file($_FILES['html']['tmp_name'], $path_to_file);
				$save_data['html'] = $file_name; 
			}/* if(!empty($_FILES['folder']['name'])){
				
				foreach($_FILES['folder']['name'] as $i => $name)
				{
					$path_to_file = 'uploads/admin/shows/folder/'.$folder_name."/data/".$name;
					if(strlen($_FILES['folder']['name'][$i]) > 1){
						move_uploaded_file($_FILES['folder']['tmp_name'][$i],$path_to_file);
					}
				}
				$save_data['folder'] = $folder_name; 
			} */
			/* --- End 2-- */
			$this->db->where('shows_id' , $this->input->post('shows_id'));
			$this->db->update('shows' , $save_data);  
		}
		
    	function update_show_folder() {
    	$shows_data = $this->get_data_id('shows',$this->input->post('shows_id'));
    	$shows_data = $shows_data->result_array();
    	
        $folder_name = time() . '_f';
        $base_path = "uploads/admin/shows/folder/" . $folder_name;
        // $data_path = $base_path . "/data";
        $zip_path = $base_path . "/zip";
        // $delete_folder = "uploads/admin/shows/folder/".$shows_data[0]['folder'];
        // $this->delete_folder($delete_folder);
        
      
        // Create necessary directories
        // if (!mkdir($base_path, 0777, true) || !mkdir($data_path, 0777, true) || !mkdir($zip_path, 0777, true)) {
        //     error_log("Failed to create directories.");
        //     die("Failed to create directories.");
        // }
        
        if (!mkdir($base_path, 0777, true) || !mkdir($zip_path, 0777, true)) {
            error_log("Failed to create directories.");
            die("Failed to create directories.");
        }
        
    
        // Initialize save_data array
        $save_data = [];
    
        // Handle HTML file upload
        if (!empty($_FILES['html']['name'])) {
            $file_name = basename($_FILES['html']['name']);
            $path_to_file = $base_path . '/' . $file_name;
    
            if (move_uploaded_file($_FILES['html']['tmp_name'], $path_to_file)) {
                $save_data['html'] = $file_name;
            } else {
                error_log("Failed to upload HTML file.");
                echo "Failed to upload HTML file.<br>";
            }
        }
        
        
    
        // Handle folder files upload
        if (!empty($_FILES['folder']['name'][0])) {
            
            foreach ($_FILES['folder']['name'] as $i => $name) {
                
                // if (strlen($name) > 1) {
                    $path_to_file = $zip_path . '/' . basename($name);
    
                //     // Log the file type
                    $file_type = mime_content_type($_FILES['folder']['tmp_name'][$i]);
                //     error_log("Attempting to upload file: $name with MIME type: $file_type");
    
                    if (move_uploaded_file($_FILES['folder']['tmp_name'][$i], $path_to_file)) {
                        echo "Uploaded: " . $name . "<br>";
                    } else {
                        error_log("Failed to upload file: " . $name);
                        echo "Failed to upload file: " . $name . "<br>";
                    }
                // }
            }
            $save_data['folder'] = $folder_name;
        }
        
        $this->extract_zip($path_to_file, $base_path);
        $this->delete_folder($zip_path);
        // Update the database
        $this->db->where('shows_id', $this->input->post('shows_id'));
        if (!$this->db->update('shows', $save_data)) {
            error_log("Failed to update the database.");
            die("Failed to update the database.");
        }
    
        echo "Files uploaded and database updated successfully.";
    }

    function extract_zip($path_to_file, $base_path)
    {
        
        $zipFilePath = $path_to_file;
        $extractToPath = $base_path;
        if (file_exists($zipFilePath)) {

            $zip = new ZipArchive;

            if ($zip->open($zipFilePath) === TRUE) {

                $zip->extractTo($extractToPath);
                $zip->close();

                echo 'ZIP file extracted successfully.';
            } else {
                echo 'Failed to open the ZIP file.';
            }
        } else {
            echo 'ZIP file does not exist.';
        }
    }
    
    function delete_folder($folder_path)
    {
        // Check if the folder exists
        if (is_dir($folder_path)) {
        
            // Get all files and folders inside the directory
            $files = array_diff(scandir($folder_path), array('.', '..'));
        
            // Loop through each item in the directory
            foreach ($files as $file) {
                $file_path = $folder_path . '/' . $file;
        
                // If it's a directory, delete recursively
                if (is_dir($file_path)) {
                    delete_folder($file_path); // Recursive call
                } else {
                    // It's a file, so delete it
                    if (!unlink($file_path)) {
                        echo "Failed to delete file: $file_path";
                        return false;
                    }
                }
            }
        
            // Once all files/subdirectories are deleted, remove the main directory
            if (!rmdir($folder_path)) {
                echo "Failed to remove directory: $folder_path";
                return false;
            }
        
            echo "Folder deleted successfully!";
            return true;
        } else {
            echo "Folder does not exist: $folder_path";
            return false;
        }
    }

		
		
		function add_new_fake_show(){
			//echo $device = $this->input->post('device'); 
			if($this->input->post('pc')){
				$save_data['is_pc'] = 'Yes';
			}if($this->input->post('mobile')){
				$save_data['is_mobile'] = 'Yes';
			}if($this->input->post('live')){
				$save_data['is_live'] = 'Yes';
			}
			//if($device)
		    $save_data['name'] = $this->input->post('name');
			$save_data['categories_id'] = $this->input->post('categories_id');
			$save_data['sub_categories_id'] = $this->input->post('sub_categories_id');
		//	$save_data['access'] = $this->input->post('access');
			$save_data['date'] = date('Y-m-d H:i:s');
			$save_data['description'] = $this->input->post('description');
			
			if(!empty($_FILES['image']['name'])){
				$file_name = time() . '-' .$_FILES['image']['name'];
				$file_name = $file_name ;
				$path_to_file = 'uploads/admin/shows/image/'.$file_name;
				move_uploaded_file($_FILES['image']['tmp_name'], $path_to_file);
				$save_data['image'] = $file_name;
			}
		
			$this->db->insert('fake_shows' , $save_data);  
		}
		
		function fake_shows_update(){
			//echo $device = $this->input->post('device'); 
			if($this->input->post('pc')){ $save_data['is_pc'] = 'Yes'; }else{ $save_data['is_pc'] = 'No'; }
			if($this->input->post('mobile')){ $save_data['is_mobile'] = 'Yes'; }else{ $save_data['is_mobile'] = 'No'; }
			if($this->input->post('live')){ $save_data['is_live'] = 'Yes'; }else{ $save_data['is_live'] = 'No'; }
			
			//if($device)
		    $save_data['name'] = $this->input->post('name');
			$save_data['categories_id'] = $this->input->post('categories_id');
			$save_data['sub_categories_id'] = $this->input->post('sub_categories_id');
			$save_data['access'] = $this->input->post('access');
			//$save_data['status'] = 'Active';
			//$save_data['date'] = date('Y-m-d H:i:s');
			$save_data['description'] = $this->input->post('description');
			if(!empty($_FILES['image']['name'])){
				$file_name = time() . '-' .$_FILES['image']['name'];
				$file_name = $file_name ;
				$path_to_file = 'uploads/admin/shows/image/'.$file_name;
				move_uploaded_file($_FILES['image']['tmp_name'], $path_to_file);
				$save_data['image'] = $file_name;
			}
			$this->db->where('fake_shows_id' , $this->input->post('fake_shows_id'));
			$this->db->update('fake_shows' , $save_data);  
		}
		
		
		function add_new_blog(){
			if($this->input->post('home_page')){ $save_data['home_page'] = '1'; }else{ $save_data['home_page'] = '0'; }
			if($this->input->post('category_list')){ $save_data['category_list'] = '1'; }else{ $save_data['category_list'] = '0'; }
			
		    $save_data['title'] = $this->input->post('title');
			
			//$save_data['description'] = $this->input->post('description');
			$save_data['status'] = $this->input->post('status');
			$save_data['description'] = str_replace("ckeditor/uploads/",base_url()."assets/admin/ckeditor/uploads/",$this->input->post('description'));
			$save_data['writer'] = $this->input->post('writer');
			$save_data['background_color'] = $this->input->post('background_color');
			$save_data['categories_id'] = json_encode($this->input->post('categories_id'));
			$save_data['sub_categories_id'] = json_encode($this->input->post('sub_categories_id'));
			$save_data['main_categories_id'] = $this->input->post('main_categories_id');
			$save_data['caption'] = $this->input->post('caption');
			$save_data['date'] = date('Y-m-d H:i:s');
			if($this->input->post('author_date') == 'on'){
			    $save_data['author_date'] = 1;
			}else{
			    $save_data['author_date'] = 0;
			}
			if(!empty($_FILES['f_image']['name'])){
				$file_name = time() . '-' .$_FILES['f_image']['name'];
				$file_name = $file_name ;
				$path_to_file = 'uploads/admin/blogs/'.$file_name;
				move_uploaded_file($_FILES['f_image']['tmp_name'], $path_to_file);
				$save_data['f_image'] = $file_name;
			}if(!empty($_FILES['c_image']['name'])){
				$file_name = time() . '-' .$_FILES['c_image']['name'];
				$file_name = $file_name ;
				$path_to_file = 'uploads/admin/blogs/'.$file_name;
				move_uploaded_file($_FILES['c_image']['tmp_name'], $path_to_file);
				$save_data['c_image'] = $file_name; 
				$save_data['cover_url'] = $this->input->post('cover_url');
			}
			if(!empty($_FILES['v_image'])){
			    foreach($_FILES['v_image']['name'] as $k=>$files){
			  
			        $file_name = time() . '-' .$files;
    				$file_name = $file_name ;
    				$path_to_file = 'uploads/admin/blogs/'.$file_name;
    				move_uploaded_file($_FILES['v_image']['tmp_name'][$k], $path_to_file);
    				$save_v_images[] = $file_name; 
			    }
				$save_data['v_image'] = json_encode($save_v_images); 
			}
			//$this->db->where('system_setting_id' , $this->input->post('system_setting_id'));
			$this->db->insert('blogs' , $save_data);  
		}
		
		
		function blogs_update(){
			if($this->input->post('home_page')){ $save_data['home_page'] = '1'; }else{ $save_data['home_page'] = '0'; }
			if($this->input->post('category_list')){ $save_data['category_list'] = '1'; }else{ $save_data['category_list'] = '0'; }
		
		    $save_data['status'] = $this->input->post('status');
		    $save_data['title'] = $this->input->post('title');
		    $save_data['caption'] = $this->input->post('caption');
			$stest = str_replace(base_url()."assets/admin/c","c",$this->input->post('description'));
			$save_data['description'] = str_replace("ckeditor/uploads/",base_url()."assets/admin/ckeditor/uploads/",$stest);
			$save_data['writer'] = $this->input->post('writer');
			$save_data['background_color'] = $this->input->post('background_color');
			$save_data['categories_id'] = json_encode($this->input->post('categories_id'));
			$save_data['sub_categories_id'] = json_encode($this->input->post('sub_categories_id'));
			$save_data['cover_url'] = $this->input->post('cover_url');
			if($this->input->post('author_date') == 'on'){
			    $save_data['author_date'] = 1;
			}else{
			    $save_data['author_date'] = 0;
			}
			
		
			$save_v_images = array();
			//$save_data['date'] = date('Y-m-d H:i:s');
			if(!empty($_FILES['f_image']['name'])){
				$file_name = time() . '-' .$_FILES['f_image']['name'];
				$file_name = $file_name ;
				$path_to_file = 'uploads/admin/blogs/'.$file_name;
				move_uploaded_file($_FILES['f_image']['tmp_name'], $path_to_file);
				$save_data['f_image'] = $file_name;
			}if(!empty($_FILES['c_image']['name'])){
				$file_name = time() . '-' .$_FILES['c_image']['name'];
				$file_name = $file_name ;
				$path_to_file = 'uploads/admin/blogs/'.$file_name;
				move_uploaded_file($_FILES['c_image']['tmp_name'], $path_to_file);
				$save_data['c_image'] = $file_name; 
				
			}
			if(!empty($_FILES['v_image']['name'][0])){
			    
			    foreach($_FILES['v_image']['name'] as $k=>$files){
			  
			        $file_name = time() . '-' .$files;
    				$file_name = $file_name ;
    				$path_to_file = 'uploads/admin/blogs/'.$file_name;
    				move_uploaded_file($_FILES['v_image']['tmp_name'][$k], $path_to_file);
    				$save_v_images[] = $file_name; 
			    }
				$save_data['v_image'] = json_encode($save_v_images); 
			}
			$this->db->where('blogs_id' , $this->input->post('blogs_id'));
			$this->db->update('blogs' , $save_data);  
			
			if($save_data['home_page'] == '1'){
				$save_data2['home_page'] = '0';
				$this->db->where('blogs_id !=' , $this->input->post('blogs_id'));
				$this->db->update('blogs' , $save_data2);
			}
		}
		
		function main_category_update(){
			$save_data['name'] = $this->input->post('name');
			$this->db->where('main_categories_id' , $this->input->post('main_categories_id'));
			$this->db->update('main_categories' , $save_data); 
		}
		
		function add_new_category(){
			$save_data['name'] = $this->input->post('name');
			if(!empty($_FILES['f_image']['name'])){
				$file_name = time() . '-' .$_FILES['f_image']['name'];
				$file_name = $file_name ;
				$path_to_file = 'uploads/admin/shows/image/'.$file_name;
				move_uploaded_file($_FILES['f_image']['tmp_name'], $path_to_file);
				$save_data['image'] = $file_name;
			}
			$save_data['main_categories_id'] = $this->input->post('main_categories_id');
			$save_data['date'] = date('Y-m-d H:i:s');
			$this->db->insert('categories' , $save_data); 
		}
		
		function category_update(){
		    
			$save_data['name'] = $this->input->post('name');
			$save_data['main_categories_id'] = $this->input->post('main_categories_id');
			if(!empty($_FILES['f_image']['name'])){
			   
			    $old_image = $this->input->post('old_image');
				$file_name = time() . '-' .$_FILES['f_image']['name'];
				$file_name = $file_name ;
				$path_to_file = 'uploads/admin/shows/image/'.$file_name;
				$deleted_to_file = 'uploads/admin/shows/image/'.$old_image;
				if (file_exists($deleted_to_file)) {
			        unlink($deleted_to_file);
			    }
		
				move_uploaded_file($_FILES['f_image']['tmp_name'], $path_to_file);
				$save_data['image'] = $file_name;
			}
			//$save_data['date'] = date('Y-m-d H:i:s');
			$this->db->where('categories_id' , $this->input->post('categories_id'));
			$this->db->update('categories' , $save_data); 
		}
		
		function add_new_sub_category(){
			$save_data['name'] = $this->input->post('name');
			$save_data['main_categories_id'] = $this->input->post('main_categories_id');
			if(!empty($_FILES['f_image']['name'])){
				$file_name = time() . '-' .$_FILES['f_image']['name'];
				$file_name = $file_name ;
				$path_to_file = 'uploads/admin/shows/image/'.$file_name;
				move_uploaded_file($_FILES['f_image']['tmp_name'], $path_to_file);
				$save_data['image'] = $file_name;
			}
			$save_data['categories_id'] = $this->input->post('categories_id');
			$save_data['date'] = date('Y-m-d H:i:s');
			$this->db->insert('sub_categories' , $save_data); 
		}
		
		function sub_category_update(){
			$save_data['name'] = $this->input->post('name');
			$save_data['categories_id'] = $this->input->post('categories_id');
			if(!empty($_FILES['f_image']['name'])){
			   
			    $old_image = $this->input->post('old_image');
				$file_name = time() . '-' .$_FILES['f_image']['name'];
				$file_name = $file_name ;
				$path_to_file = 'uploads/admin/shows/image/'.$file_name;
				$deleted_to_file = 'uploads/admin/shows/image/'.$old_image;
				if (file_exists($deleted_to_file)) {
			        unlink($deleted_to_file);
			    }
		
				move_uploaded_file($_FILES['f_image']['tmp_name'], $path_to_file);
				$save_data['image'] = $file_name;
			}
			//$save_data['date'] = date('Y-m-d H:i:s');
			$this->db->where('sub_categories_id' , $this->input->post('sub_categories_id'));
			$this->db->update('sub_categories' , $save_data); 
		}
		function update_user_access(){
			$extras =$this->input->post('shows_id');
			$users_id =$this->input->post('users_id');
			if(isset($extras)){
				foreach($extras as $extra){
					$exist = $this->Admin_model->get_data_conditions('shows_extra', ' users_id='.$users_id.' AND shows_id='.$extra)->num_rows();
					if($exist ==0 ){
						$save_show['users_id'] = $this->input->post('users_id');
						$save_show['shows_id'] = $extra;
						$this->db->insert('shows_extra' , $save_show); 
					}
					
				}
			}else{
			    $this->db->where('users_id', $users_id);
			    $this->db->delete('shows_extra');
			}
			if($this->input->post('status') == 'Active'){
			    $save_data['membership_start_data'] = date('Y-m-d H:i:s');
			}
			
			$save_data['key_person'] = $this->input->post('key_person');
			$save_data['blog_id'] = json_encode($this->input->post('blogs_id'));
			//$save_data['membership'] = $this->input->post('membership');
			$save_data['notes'] = $this->input->post('notes');
			$save_data['status'] = $this->input->post('status');
			$this->db->where('users_id' , $this->input->post('users_id'));
			$this->db->update('users' , $save_data); 
			
		}
		
		function add_new_user(){
			$exist = $this->db->get_where('users', array('email'=>$this->input->post('email')))->num_rows();
			if($exist == 0){
			    $this->load->library('email');
				$save_data['institute'] = $this->input->post('institute');
				$save_data['address'] = $this->input->post('address');
				$save_data['postal1'] = $this->input->post('postal1');
				$save_data['postal2'] = $this->input->post('postal2');
				$save_data['web'] = $this->input->post('web');
				$save_data['projector'] = $this->input->post('projector');
				$save_data['fname'] = $this->input->post('fname');
				$save_data['lname'] = $this->input->post('lname');
				$save_data['phone'] = $this->input->post('phone');
				$save_data['mobile'] = $this->input->post('mobile');
				//$save_data['position'] = $this->input->post('position');
				$save_data['email'] = $this->input->post('email');
				$save_data['password'] = $this->input->post('password');
				$save_data['status'] = 'Inactive';
				$save_data['date'] = date('Y-m-d H:i:s');
				$this->db->insert('users' , $save_data); 
				$this->db->select();
    			$this->db->from('admin_emails');
    			$this->db->where("status","1");
    			$admin_email_data = $this->db->get(); 
    			$admin_email_data = $admin_email_data->result_array();
    			
    			$user_emails = [];
    			
    			foreach($admin_email_data as $data){
    			    $user_emails[] = $data['email'];
    			}
    // 			$user_emails[] ='shaheryarbhatti881@gmail.com';
				// $email_body = $this->load->view('frontend/registration_email', $save_data, TRUE);
				$config = array(
                    'protocol'  => 'smtp',
                    'smtp_host' => env('SMTP_HOST', 'mailout.one.com'),
                    'smtp_port' => (int) env('SMTP_PORT', 587),  // You can also use 465 for SSL/TLS
                    'smtp_user' => env('SMTP_USER', ''), // Your email address
                    'smtp_pass' => env('SMTP_PASS', ''),
                    'smtp_crypto' => env('SMTP_CRYPTO', 'tls'),  // For port 587 use 'tls', for port 465 use 'ssl'
                    'mailtype'  => 'html',   // IMPORTANT: Ensure it's set to HTML
                    'charset'   => 'utf-8',
                    'wordwrap'  => TRUE
                );
        
                $this->load->library('email', $config);
                $this->email->set_newline("\r\n");
                $message = 'Hei, og..
                <br>
                            takk for registreringen din.
                            <br>
                            Du vil høre fra oss når vi har åpnet tilgangen til
                            <br>
                            demonstrasjonene.
                            <br>
                            <br>
                            <br>
                            Med vennlig hilsen
                            <br>
                            <img src="https://bestefar.no/assets/frontend/images/logo/Logo-1.jpg" width="140px" height="40px"/>
                            <br>
                            <a href="https://bestefar.no/">www.bestefar.no</a>';
                
                // Set email parameters
                $this->email->from(env('SMTP_FROM_EMAIL', 'support@bestefar.no'), env('SMTP_FROM_NAME', 'Bestefar.no'));
                // $this->email->to($user_emails);
                $this->email->to($this->input->post('email'));
                $this->email->set_header('Content-Type', 'text/html');
                $this->email->subject('Bekreftelse på registrering');
                $this->email->message($message);
                
                if ($this->email->send()) {
                    // echo 'Email sent successfully';
                } else {
                    show_error($this->email->print_debugger());
                }
                if($user_emails){
                    
                    $email_body = 'Ny bruker er registrert på Bestefar.no<br><br><br>';
                    $email_body .= '<strong>Institusjon/forening:</strong>  ' . htmlspecialchars($this->input->post('institute')) . '<br>'; 
                    $email_body .= '<strong>Poststed:</strong>  ' . htmlspecialchars($this->input->post('postal1')) . ' ' . htmlspecialchars($this->input->post('postal2')) . '<br>';
                    $email_body .= '<strong>Kontaktperson:</strong>  ' . htmlspecialchars($this->input->post('fname')) . ' ' . htmlspecialchars($this->input->post('lname')) . '<br>'; 
                   
                    // Set email parameters
                    $this->email->from(env('SMTP_FROM_EMAIL', 'support@bestefar.no'), env('SMTP_FROM_NAME', 'Bestefar.no'));
                    $this->email->to($user_emails);
                    $this->email->set_header('Content-Type', 'text/html');
                    $this->email->subject('NY REGISTRERING');
                    $this->email->message($email_body);

                    if ($this->email->send()) {
                        // echo 'Email sent successfully';
                    } else {
                        show_error($this->email->print_debugger());
                    } 
                }
				return 'success';
			}else{
				return 'already_exist'; 
			}
		}
		
		function register_user(){
			$exist = $this->db->get_where('users', array('email'=>$this->input->post('email')))->num_rows();
		    $save_data['firstname'] = $this->input->post('name');
		    $save_data['email'] = $this->input->post('email');
		    $save_data['password'] = $this->input->post('password');
		    $save_data['status'] = 'Active';
		    $save_data['date'] = date('Y-m-d H:i:s');
			
			if($exist == 0){
				$this->db->insert('users' , $save_data); 
			}else{
				$this->db->where('email' , $this->input->post('email'));
				$this->db->update('users' , $save_data); 
			}
		}
		
		function blog_publish_unpublish($id){
		    $blog = $this->db->where('blogs_id',$id)->get("blogs")->row_array();
		    $status = $blog['status'] ==  'Draft' ? 'Active' : 'Draft';  
		    $this->db->where('blogs_id',$id)->update("blogs",['status'=>$status]);
		}
		
		function blog_paid($id){
		    $blog = $this->db->where('blogs_id',$id)->get("blogs")->row_array();
		    $status = $blog['paid'] ==  0 ? 1 : 0;  
		    $this->db->where('blogs_id',$id)->update("blogs",['paid'=>$status]);
		}
		
		function get_data_orderwise_unpublished($table,$order){
		    $this->db->select();
			$this->db->from($table);
			$this->db->where("status","Draft");
		    $this->db->order_by($order,'ASC');
			$query = $this->db->get(); 
			return $query->result_array();
		}
		
		function get_data_desc_preview($table){
		    $this->db->select();
			$this->db->from($table);
			$this->db->where("status","Draft");
		    $this->db->order_by($table.'_id','DESC');
			$query = $this->db->get(); 
			return $query->result_array();
		}
		function get_row_id_preview($table, $id){
		    $this->db->select();
			$this->db->from($table);
			$this->db->where($table.'_id',$id);
			$this->db->where('status','Draft');
			$query = $this->db->get()->row(); 
			return $query;
		}
		
		function add_new_admin_email(){
			$save_data['email'] = $this->input->post('email');
			$save_data['status'] = $this->input->post('status');
			$save_data['created_at'] = date('Y-m-d H:i:s');
			$this->db->insert('admin_emails' , $save_data); 
		}
		
		function update_admin_email(){
		    
		    $save_data['email'] = $this->input->post('email');
			$save_data['status'] = $this->input->post('status');
			$save_data['created_at'] = date('Y-m-d H:i:s');
			$this->db->where('admin_emails_id' , $this->input->post('admin_emails_id'));
			$this->db->update('admin_emails' , $save_data); 
		}
	}
?>