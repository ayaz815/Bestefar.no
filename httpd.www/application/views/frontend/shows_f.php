<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
<?php

		$conditions = '1=1';
		$access='';
		if($param2 > 0){
			$conditions .= ' AND sub_categories_id='.$param2;
		
		}if($param1 > 0){
			$conditions .= ' AND categories_id='.$param1;
			
		}
		$users_id = isset($_SESSION['users_id']) ? $_SESSION['users_id'] : null;
		if($users_id){ //show him extra shows ----------------------------------------------------------
			
			$this->db->select('*');
            $this->db->select('shows_id');
            $this->db->from('shows_extra');
            $this->db->where('users_id',$users_id);
            $extra_shows  = $this->db->get()->result_array();
            
            
			$user = $user = $this->Admin_model->get_row_id('users',$users_id);
			$access = $user->membership;
			$status = $user->status;
			
			if($status == 'Inactive'){  
			    if($_SESSION['approval_status'] == 1){
				    $conditions .= ' AND (access="Demo" OR access="Info")';
			    }else{
			        $conditions .= ' AND access="Info"';
			    }
			//	$link = base_url().'home/show_details/'.$show['shows_id'];
				
			}elseif($status == 'Active'){
			    if($_SESSION['approval_status'] == 1){
			       
				    $conditions .= ' AND (access="Demo" OR access="Info" OR access="Extended")';
			    }else{
			        $conditions .= ' AND (access="Demo" OR access="Info")';
			    }
			//	$link = base_url().'home/show_details/'.$show['shows_id'];
			}
			if($extra_shows){
			    
			    foreach($extra_shows as $exs){
			    
			        $this->db->select('*');
                    $this->db->select('shows_id');
                    $this->db->from('shows');
                    $this->db->where('shows_id',$exs['shows_id']);
                    $this->db->where('categories_id',$param1);
                    $shows_exist = $this->db->get()->result_array();
                    if($shows_exist){
                        $conditions .= ' OR (shows_id='.$exs['shows_id'].')';
                    }
			        
			    
			    }
			    
			}
			$this->db->select('*');
			$this->db->select('shows.shows_id AS shows_id,');
			$this->db->from('shows');
			$this->db->where($conditions);
			//$this->db->join('shows_extra', 'shows_extra.shows_id = shows.shows_id', 'inner');
			//$this->db->join('shows_extra', 'shows_extra.shows_id = shows.shows_id', 'left');
			$shows  = $this->db->get()->result_array(); 
		}else{
		  //  if($_SESSION['approval_status'] == 1){
			    $conditions .= ' AND (access="Demo" OR access="Info" OR access="Extended")';
		  //  }else{
		  //      $conditions .= ' AND access="Info"';
		  //  }
		    $link = 'href="#"  data-toggle="modal" data-target="#myModal"';
		    $shows = $this->Admin_model->get_data_conditions('shows', $conditions)->result_array();
		}
		
		$conditions2 = '';
		if($param1 > 0){
			$conditions2 = 'categories_id='.$param1;
		}if($param2 > 0){
			$conditions2 .= ' AND sub_categories_id='.$param2;
		}
		$fake_shows = $this->Admin_model->get_data_conditions('fake_shows', $conditions2)->result_array();
		
		
		?>
 
	<style>
	.close_btn{
    	background: url(<?php echo base_url(); ?>assets/frontend/images/Lukk.png) no-repeat !important;
    	cursor: pointer  !important;
    	border: none !important;
    	background-size: 100% !important;
        width: 120px !important;
        height: 50px; 
    }
	@media only screen and (min-width: 1025px) {
		.category_title{
		    margin-top: -2px;
		}
	}
	@media only screen and (min-width: 900px) {
		.show_row{
			margin-left: -15px !important;
			margin-right: -14px !important;
		}
	}
	@media only screen and (max-width: 900px) {
		.show_row{
			margin-left: -10px !important;
			margin-right: 0px !important;
		}.show_col{
			padding-left: 10px !important;
			padding-right: 0px !important;
		}
		.img2{
			left: 10px!important;
		}
		.category_title{
		    margin-left: -1px;
		    margin-top: 12px !important;
		}
		#top_column{
    	    padding-right: 0px;
            padding-left: 10px;
    	}
	}
	@media only screen and (min-width: 1025px) {
        #myModal{
    	    margin-left: 100px;
    	}
    }
    @media screen and (max-width: 1024px) and (min-width: 768px) {
        #myModal{
    	    margin-left: -3%;
    	}
    }
    @media (max-width:768px){
    	.show_mb_1{
    		margin-right:1px;
    	}
    	#top_column{
    	    padding-right: 0px;
            padding-left: 10px;
    	}
    }
	
	.img1{
		margin-bottom:15px;/*position:absolute;z-index:1;*/
	}
	.img2{
		margin-bottom:15px;top: 0;
		position:absolute;
		left: 15px;
		width: 40%;
		}
	
	</style>
	<div id="content" class="site-content">
		<div id="content" class="elementor elementor-16 elementor-14">
			<div id="content" class="elementor-section elementor-top-section elementor-element elementor-element-60622e5 elementor-section-boxed elementor-section-height-default elementor-section-height-default">
				<div class="elementor-container elementor-column-gap-default">
				 <!------------>


	<div class="elementor-column elementor-col-100 elementor-top-column elementor-element elementor-element-c9a3d65" data-id="c9a3d65" data-element_type="column" style="margin-top:5px;">
		<div class="elementor-widget-wrap elementor-element-populated">
								
			<section class="elementor-section elementor-inner-section elementor-element elementor-element-a473ad5 elementor-section-full_width elementor-section-height-default elementor-section-height-default" data-id="a473ad5" data-element_type="section">
				<div class="elementor-container elementor-column-gap-default">
					<div class="elementor-column elementor-col-100 elementor-inner-column elementor-element elementor-element-bcd4cfc" data-id="bcd4cfc" data-element_type="column">
						<div class="elementor-widget-wrap">
						<!------------>
						
		
						<!------------>
							<div class="container- show_mb_1" style="margin-left:-1px;">
								<div class="row show_row" style="">
									<div class="col-md-12" id="top_column">
								 
										<h2 class="elementor-heading-title elementor-size-default category_title"  style="font-family: agency_fbregular;font-size: 27px;">
                                    	    <a href="<?php echo base_url(); ?>/home/shows/<?php echo $param1; ?>"  style="text-decoration:none;color:black;">
                                    		    <?php echo $this->Admin_model->get_tc('categories',$param1, 'name'); ?>
                                    		    	<?php if($param2  > 0){ ?>
                                    		    <i class="fa fa-play" style="color: #808080a6;font-size: 16px;"></i>
                                    		    	<?php } ?>
                                    		</a>
                                    		<?php if($param2  > 0){ ?>
                                    		 <a href="<?php echo base_url().'/home/shows/'. $param1.'/'.$param2 ; ?>" style="text-decoration:none;color:black;">
                                    		    <?php echo $this->Admin_model->get_tc('sub_categories',$param2 , 'name'); ?> 
                                    		</a>
                                    		<?php } ?>
                                    		
                                    		
                                    	</h2>
                                    	<?php if($categories_data[0]['image']){?>
                                    	<img src="<?php echo base_url(); ?>uploads/admin/shows/image/<?php echo $categories_data[0]['image']; ?>" class="img-responsive img1" style="">
                                    	<?php }?>
								 
									</div>
									<?php 
									
										foreach($shows as $show){ 
										    //	 if(isset($_SESSION['users_id'])){
										    	    $link = ' href="'.base_url().'home/show_details/'.$show['shows_id'].'" ';
										    //	}
									?>
									<div class="col-md-6 col-sm-6 col-xs-6 show_col">
										<a <?php echo $link; ?>>
										<img src="<?php echo base_url(); ?>uploads/admin/shows/image/<?php echo $show['image']; ?>" class="img-responsive img1" style="">
										
										<?php if($show['access'] =='Demo' ){?>
										<img src="<?php echo base_url(); ?>assets/frontend/images/demo.png" class="img-responsive img2" style="">
										<?php } ?>
										<?php if($show['access'] =='Info' ){?>
										<img src="<?php echo base_url(); ?>assets/frontend/images/info.png" class="img-responsive img2" style="">
										<?php } ?>
										</a>
									</div>
									<?php } ?>
								
									<?php 
									if($users_id){
									    
									     $shows_extra = $this->Admin_model->get_data_conditions('shows_extra', 'users_id="$users_id"')->result_array();
									     
									     
										 foreach($shows_extra as $show_e){ 
										    $link = ' href="'.base_url().'home/show_details/'.$show_e['shows_id'].'" ';
										    
									?>
    									<div class="col-md-6 col-sm-6 col-xs-6 show_col">
    										<a <?php echo $link; ?>>
    										<img src="<?php echo base_url(); ?>uploads/admin/shows/image/<?php echo $show_e['image']; ?>" class="img-responsive img1" style="">
    										</a>
    									</div>
    									<?php } ?>
									<?php } ?>
									
									
									
									<?php 
								// 	if($_SESSION['approval_status'] == 1 && $_SESSION['approval_status'] == 1 && $status == 'Active'){
										foreach($fake_shows as $show_f){ 
										$link_f = base_url().'home/show_details_2/'.$show_f['fake_shows_id'];
										
									?>
									<div class="col-md-6 col-sm-6 col-xs-6 show_col">
										<a href="<?php echo $link_f; ?>">
										<img src="<?php echo base_url(); ?>uploads/admin/shows/image/<?php echo $show_f['image']; ?>" class="img-responsive img1" style="">
										</a>
									</div>
									<?php 
										    
								// 		}
									} ?>
									
									
									
									
								</div>
								<?php if(empty($shows) && empty($fake_shows)){ ?>
									<h4>Ingen Data Tilgjengelig</h4>
									<br/><br/><br/><br/><br/><br/><br/><br/><br/><br/><br/><br/><br/><br/><br/><br/><br/>
									<?php } ?>
							</div>
						</div>
					</div>
				</div>
		</section>
				
		</div>
	</div>
	
	
	<!------------>
				</div>
			</div>
		</div>
	</div><!-- #content -->
	
	
		<!-- Modal -->
<div id="myModal" class="modal fade" role="dialog">
  <div class="modal-dialog">

    <!-- Modal content-->
    <div class="modal-content"style="background-color: #ffc000;">
      <div class="modalheader">
        <!--<button type="button" class="close" data-dismiss="modal">&times;</button>-->
        <center><h1 class="modal-title" style="font-family: agency_fbregular;margin-top:30px;"><b>Vi beklager</b></h1></center>
      </div>
      <div class="modalbody" align="center">
        <h3 style="font-family: agency_fbregular;margin-top: 6px;">Tilgangen gjelder kun for abonnenter, men.. <br/> dersom du registrerer deg, får du tilgang til demonstrasjonene</h3>
      </div>
      <div class="modalfooter" align="center">
        <button type="button"  class="btn btn-default close_btn" data-dismiss="modal" style="margin:0px 0px 24px 0px"></button>
      </div>
    </div>

  </div>
</div>
 