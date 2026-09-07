<?php $blog = $this->Admin_model->get_row_id('blogs',$param2); ?>
<?php $categories = $this->Admin_model->get_data_asc('categories'); ?>
<?php $sub_categories = $this->Admin_model->get_data_asc('sub_categories'); ?>
<style>
.chk_box{ 
	margin-right: 15px;
    margin-top: 5px;
    margin-left: 5px;
}
#cke_1_contents{
	height: 600px !important;
}
</style>
<style> .row_div{ margin-top:20px; border-bottom: 1px solid #d3d3d352; } </style>
<div class="contentpanel">
	<div class="row">
		<div class="col-md-12">
			<div class="panel panel-default">
				<div class="panel-heading">
					<center><h3 class="panel-title">Edit Blog</h3></center><hr/>
				</div>
				<div class="panel-body">
					
					<div class="row ">
						
						<div class="col-md-12">
							<form method="POST" action="<?php echo base_url().strtolower($this->session->userdata('role_name')); ?>/blogs/update"  enctype="multipart/form-data">
								<div class="row">
									<div class="col-md-12">
										<div class="row">
											<label class="col-sm-4 col-lg-2 col-form-label">Blog Title</label>
											<div class="col-sm-8 col-lg-10">
												<div class="input-group">
													<span class="input-group-addon"><i class="icofont icofont-ui-volume"></i></span>
													<input type="hidden" name="blogs_id" value="<?php echo $blog->blogs_id; ?>">
													<input type="text" name="title" class="form-control" placeholder="Enter Blog Title" required value="<?php echo $blog->title; ?>">
												</div>
											</div>
										</div>
										
										<div class="row">
											<label class="col-sm-4 col-lg-2 col-form-label">Author Name</label>
											<div class="col-sm-8 col-lg-10">
												<div class="input-group">
													<span class="input-group-addon"><i class="fa fa-user"></i></span>
													<input type="text" name="writer" class="form-control" placeholder="Writer Name" required  value="<?php echo $blog->writer; ?>">
												</div>
											</div>
										</div>
										
										
										<div class="row">
											<label class="col-sm-4 col-lg-2 col-form-label"> Category</label>
											<div class="col-sm-8 col-lg-10">
												<div class="input-group">
													<span class="input-group-addon"><i class="icofont icofont-list"></i></span>
													<select  name="categories_id" class="form-control" required>
														<option disabled selected> Select Category</option>
														<?php foreach($categories as $cat){ ?>
															<option value="<?php echo $cat['categories_id']; ?>" <?php if($cat['categories_id'] ==$blog->categories_id ){ echo 'selected'; }?>> <?= $cat['name']; ?> </option>
														
														<?php } ?>
													</select>
												</div>
											</div>
										</div>
										
										<div class="row">
											<label class="col-sm-4 col-lg-2 col-form-label"> Sub Category</label>
											<div class="col-sm-8 col-lg-10">
												<div class="input-group">
													<span class="input-group-addon"><i class="icofont icofont-list"></i></span>
													<select  name="sub_categories_id" class="form-control" required>
														<option disabled selected> Select Sub-Category</option>
														<?php foreach($sub_categories as $cat){ ?>
															<option value="<?php echo $cat['sub_categories_id']; ?>" <?php if($cat['sub_categories_id'] ==$blog->categories_id ){ echo 'selected'; }?>> <?= $cat['name']; ?> </option>
														
														<?php } ?>
													</select>
												</div>
											</div>
										</div>
										
										<!--
										<div class="row">
											<label class="col-sm-4 col-lg-2 col-form-label"> Class Session</label>
											<div class="col-sm-8 col-lg-10">
												<div class="input-group">
													<span class="input-group-addon">!</span>
													<select  name="zee_class_sessions_id" class="form-control" required>
														<option disabled selected> Select </option>
														<option value="Male"> Male </option>
														<option value="Female"> Female </option>
													</select>
												</div>
											</div>
										</div>
										-->
										
										<div class="row">
											<label class="col-sm-4 col-lg-2 col-form-label"> Feature Image</label>
											<div class="col-sm-8 col-lg-10">		
												<div class="input-group">
													<span class="input-group-addo"><img src="<?php echo base_url().'uploads/admin/blogs/'.$blog->f_image; ?>" style="height: 42px;"></span>
													<input type="file" name="f_image"  value="<?php echo $blog->f_image; ?>"  accept="image/*" class="form-control"  />
												
												</div>
											</div>
										</div>
										
										<div class="row">
											<label class="col-sm-4 col-lg-2 col-form-label"> Cover Image</label>
											<div class="col-sm-8 col-lg-10">
												<div class="input-group">
													<span class="input-group-addo"><img src="<?php echo base_url().'uploads/admin/blogs/'.$blog->c_image; ?>" style="height: 42px;"></span>
													<input type="file"  accept="image/*" name="c_image"  value="<?php echo $blog->c_image; ?>" class="form-control"/>
												</div>
											</div>
										</div>
										<div class="row">
											<label class="col-sm-4 col-lg-2 col-form-label"> </label>
											<div class="col-sm-8 col-lg-10">
												<div class="input-group">
													<input name="home_page" type="checkbox" id="d1" value="home_page" <?php if($blog->home_page == 1){ echo 'checked'; } ?>> 
													<label for="d1" class="chk_box"> Show on "Home Page"</label> 
													
													<input name="category_list" type="checkbox" id="d2" value="category_list" <?php if($blog->category_list == 1){ echo 'checked'; } ?>>
													<label for="d2" class="chk_box" > Keep the page also in category list.</label> 
												</div>
											</div>
										</div>
										<div class="row">
											<label class="col-sm-4 col-lg-2 col-form-label">Description</label>
											<div class="col-sm-8 col-lg-10">
												<div class="input-group">
													<textarea name="description" rows="5" class="form-control textarea" id="editor1" placeholder="Enter Blog Description" required><?php echo $blog->description; ?></textarea>
												</div>
											</div>
										</div>
										
										<div class="row">
											<label class="col-sm-4 col-lg-2 col-form-label"> </label>
											<div class="col-sm-8 col-lg-10">
												<div class="input-group">
													<button type="sunmit" class="btn btn-success">Update Blog</button>
												</div>
											</div>
										</div>
										
									</div>
								</div>
							</form>
						</div>
						
						
						
					</div>
				</div>
			</div>
		</div>
	</div>      
</div>


<script src="<?php echo base_url();?>assets/admin/ckeditor/ckeditor.js"></script>
<script>
    CKEDITOR.replace('editor1', {
        filebrowserUploadUrl: '<?php echo base_url();?>assets/admin/ckeditor/ck_upload1.php',
        filebrowserUploadMethod: 'form'
    });
</script>