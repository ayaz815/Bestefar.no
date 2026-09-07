<script src="<?php echo base_url(); ?>assets/admin/multi-select/multi-select.js"></script>
<link href="<?php echo base_url(); ?>assets/admin/multi-select/multi-select.css" rel="stylesheet"/>

<?php $user = $this->Admin_model->get_row_id('users',$param2); ?>
<?php $shows = $this->Admin_model->get_data_conditions('shows', 'access != "Demo" AND access != "Info"')->result_array(); ?>
<?php $blogs = $this->Admin_model->get_data_conditions('blogs', 'paid = 1')->result_array(); ?>
<?php 
$this->db->select('*');
$this->db->from('shows_extra');
$this->db->where('users_id',$param2);
$query = $this->db->get();
$user_shows=$query->result_array();
$temp_array = [];

foreach($user_shows as $us){
    $temp_array[]=$us['shows_id'];
}
?>
<style>
 .row_div{ margin-top:20px; border-bottom: 1px solid #d3d3d352; } 
 .chosen-container { width: 100% !important; }
</style>
<div class="contentpanel">
	<div class="row">
		<div class="col-md-12">
			<div class="panel panel-default">
				<div class="panel-heading">
					<center><h3 class="panel-title">User Information</h3></center><hr/>
				</div>
				<div class="panel-body">
					<center style="color:lightgrey"> Joining Date => <?= date('d-M-Y', strtotime($user->date)); ?></center>
					<br/>
					<div class="row ">
						
						<div class="col-md-6">
						 <h5><b>Subscriber</b></h5>  
							<div class="row row_div">
								<div class="col-md-4"> <b>Institution: </b></div>
								<div class="col-md-8"> <?= $user->institute; ?></div>
							</div>
							<div class="row row_div">
								<div class="col-md-4"> <b>Street Address</b></div>
								<div class="col-md-8"> <?= $user->address; ?></div>
							</div>
							<div class="row row_div">
								<div class="col-md-4"> <b>Postal Address : </b></div>
								<div class="col-md-8"> <?= $user->postal1; ?></div>
							</div>
							<div class="row row_div">
								<div class="col-md-4"> <b>Facebook/Web: </b></div>
								<div class="col-md-8"><?= $user->web; ?> </div>
							</div>
							<div class="row row_div">
								<div class="col-md-4"> <b>Projector : </b></div>
								<div class="col-md-8"><?= $user->projector; ?> </div>
							</div>
							
							<div class="row"> <div class="col-md-12"><br/> <h5><b>Contact Person</b></h5> </div> </div>
							<div class="row row_div">
								<div class="col-md-4"> <b>First Name(s): </b></div>
								<div class="col-md-8"> <?= $user->fname; ?></div>
							</div>
							<div class="row row_div">
								<div class="col-md-4"> <b>Last Name(s)</b></div>
								<div class="col-md-8"> <?= $user->lname; ?></div>
							</div>
							<div class="row row_div">
								<div class="col-md-4"> <b>Telephone : </b></div>
								<div class="col-md-8"> <?= $user->phone; ?></div>
							</div>
							<div class="row row_div">
								<div class="col-md-4"> <b>Phone : </b></div>
								<div class="col-md-8"> <?= $user->phone; ?></div>
							</div>
							<div class="row row_div">
								<div class="col-md-4"> <b>Mobile : </b></div>
								<div class="col-md-8"> <?= $user->mobile; ?></div>
							</div>
							<div class="row row_div">
								<div class="col-md-4"> <b>Email Address: </b></div>
								<div class="col-md-8"><?= $user->email; ?> </div>
							</div>
							<div class="row" style="margin-top:20px;">
								<div class="col-md-4"> <b> Password : </b></div>
								<div class="col-md-8"><?= $user->password; ?> </div>
							</div>
						</div>
						
						
						
						
						

						<div class="col-md-6" style="background-color:#d5e7f8;padding:20px">
						<form method="POST" id="user_form" action="<?php echo base_url(); ?>/admin/users/update_access"  enctype="multipart/form-data">
						<input type="hidden" name="users_id" value="<?= $user->users_id; ?>">
							 <h5><b>Access</b></h5> 
							 <?php if($user->approval_status == 1){?>
							<div class="row row_div">
								<div class="col-md-4"> <b>Status: </b></div>
								<div class="col-md-8"> 
									<select type="text" class="form-control" name="status">
										<option value="Active" <?php if($user->status =='Active'){ echo 'selected'; }  ?>>Paid</option>
										<option value="Inactive" <?php if($user->status =='Inactive'){ echo 'selected'; }  ?>>Not Paid</option>
									</select>
								</div>
							</div>
							<?php }?>
							<div class="row row_div">
								<div class="col-md-4"> <b>key Person : </b></div>
								<div class="col-md-8"> 
									<select type="text" class="form-control" name="key_person">
										<option value="No" <?php if($user->key_person =='No'){ echo 'selected'; }  ?>>No</option>
										<option value="Yes" <?php if($user->key_person =='Yes'){ echo 'selected'; }  ?>>Yes</option>
									</select>
								</div>
							</div>
							<!--
							<div class="row row_div">
								<div class="col-md-4"> <b>Membership: </b></div>
								<div class="col-md-8"> 
									<select type="text" class="form-control" name="membership">
										<option value="">Select Membership</option>
										<option value="Demo" <?php if($user->membership =='Demo'){ echo 'selected'; }  ?>>Demo</option>
										<option value="Extended"  <?php if($user->membership =='Extended'){ echo 'selected'; }  ?>>Extended</option>
										<option value="Extra" <?php if($user->membership =='Extra'){ echo 'selected'; }  ?>>Extra Edition</option>
										<option value="Paused" <?php if($user->membership =='Paused'){ echo 'selected'; }  ?>>Paused</option>
									</select>
								</div>
							</div>
							-->
							<div class="row row_div">
								<div class="col-md-4"> <b>Extra Eddition: </b></div>
								<div class="col-md-8"> 
									<select name="shows_id[]" data-placeholder="Select the shows" multiple class="form- chosen-select">
										<?php foreach($shows as $show){ ?>
										<option value="<?php echo $show['shows_id']; ?>" <?php if (in_array($show['shows_id'], $temp_array)) { echo 'selected'; } ?>> <?php echo $show['name']; ?></option>
										<?php } ?>
									</select>
								</div>
							</div>
							<br/>
							<br/>
							
							<div class="row row_div">
								<div class="col-md-4"> <b>Paid Blog: </b></div>
								<div class="col-md-8"> 
									<select name="blogs_id[]" data-placeholder="Select the shows" multiple class="form- chosen-select">
									    <?php 
									    $blog_ids = [];
									    if($user->blog_id && $user->blog_id != 'null'){ ?>
									    <?php $blog_ids = json_decode($user->blog_id);?>
									    <?php }?>
										<?php foreach($blogs as $blog){ 
										
										?>
										<option value="<?php echo $blog['blogs_id']; ?>" <?php if (in_array($blog['blogs_id'], $blog_ids)) { echo 'selected'; } ?>> <?php echo $blog['title']; ?></option>
										<?php } ?>
									</select>
								</div>
							</div>
							<br/>
							<br/>
							
							<div class="row">
								<div class="col-md-4"> <b>Last Visit: </b></div>
								<div class="col-md-8"> 01.01.24 </div>
							</div>
							<div class="row">
								<div class="col-md-4"> <b>Number of Visits: </b></div>
								<div class="col-md-8"> 12 </div>
							</div>
							<?php if($user->membership_start_data){?>
							<div class="row">
								<div class="col-md-4">  <b>Membership Start: </b></div>
								<div class="col-md-8"> <?= date('d-m-Y', strtotime($user->membership_start_data)); ?> </div>
							</div>
							<div class="row">
								<div class="col-md-4"> <b>Membership End: </b></div>
								<div class="col-md-8"><?= date('d-m-Y', strtotime('+1 year', strtotime($user->membership_start_data)) ); ?> </div> 
							</div>
							<?php }?>
							<div class="row row_div">
								<div class="col-md-12"> <textarea name="notes"class="form-control" placeholder="Notes..."><?php echo $user->notes; ?></textarea></div> 
							</div>
							
							
							
							
						</form>
						</div>
						
						
						
					</div>
				</div>
			</div>
		</div>
	</div>      
</div>


<script>
$(".chosen-select").chosen({
  no_results_text: "Oops, nothing found!"
});
</script>