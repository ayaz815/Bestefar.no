<?php $data = $this->Admin_model->get_row_id('shows',$param2); ?>
<?php $categories = $this->Admin_model->get_data_conditions('categories', 'main_categories_id=2')->result_array(); ?>
<style>
 .row_div{ margin-top:20px; border-bottom: 1px solid #d3d3d352; } 
 .chk_box{ 
	margin-right: 15px;
    margin-top: 5px;
    margin-left: 5px;
}
</style>
<div class="contentpanel">
	<div class="row">
		<div class="col-md-12">
			<div class="panel panel-default">
				<div class="panel-heading">
					<center><h3 class="panel-title">Edit Show</h3></center><hr/>
				</div>
				<div class="panel-body">
					
					<div class="row ">
						
						<div class="col-md-12">
							<form method="POST" action="<?php echo base_url().strtolower($this->session->userdata('role_name')); ?>/shows/update"  enctype="multipart/form-data">
									<div class="row">
										<div class="col-md-12">
											<div class="row">
												<label class="col-sm-4 col-lg-2 col-form-label">Show Name</label>
												<div class="col-sm-8 col-lg-10">
													<div class="input-group">
														<span class="input-group-addon"><i class="icofont icofont-ui-volume"></i></span>
														<input type="hidden" name="shows_id" value="<?php echo $data->shows_id; ?>">
														<input type="text" name="name" class="form-control" placeholder="Enter Show Name" required  value="<?php echo $data->name; ?>">
													</div>
												</div>
											</div>
											
											
										<div class="row">
											<label class="col-sm-4 col-lg-2 col-form-label"> Category</label>
											<div class="col-sm-8 col-lg-10">
												<div class="input-group">
													<span class="input-group-addon"><i class="fa fa-list"></i></span>
													<select  name="categories_id" id="categories_id" class="form-control" required  onchange="get_sub_categories();">
														<option disabled selected> Select Category</option>
														<?php foreach($categories as $cat){ ?>
															<option value="<?php echo $cat['categories_id']; ?>" <?php if($cat['categories_id'] ==$data->categories_id ){ echo 'selected'; }?>> <?= $cat['name']; ?> </option>
														
														<?php } ?>
													</select>
												</div>
											</div>
										</div>
										
										<div class="row">
											<label class="col-sm-4 col-lg-2 col-form-label"> Sub Category</label>
											<div class="col-sm-8 col-lg-10">
												<div class="input-group">
													<span class="input-group-addon"><i class="fa fa-list"></i></span>
													<select  name="sub_categories_id" id="sub_categories_id" class="form-control">
														<option disabled selected> Select Sub-Category</option>
														<?php $sub_categories = $this->Admin_model->get_data_conditions('sub_categories', 'categories_id='.$data->categories_id)->result_array(); ?>
														<?php foreach($sub_categories as $cat){ ?>
															<option value="<?php echo $cat['sub_categories_id']; ?>" <?php if($cat['sub_categories_id'] ==$data->sub_categories_id ){ echo 'selected'; }?>> <?= $cat['name']; ?> </option>
														
														<?php } ?>
													</select>
												</div>
											</div>
										</div>
										
											
											<div class="row">
												<label class="col-sm-4 col-lg-2 col-form-label"> Allow Category</label>
												<div class="col-sm-8 col-lg-10">
													<div class="input-group">
														<span class="input-group-addon"><i class="icofont icofont-list"></i></span>
														<select  name="access" class="form-control" required>
															<option disabled> Select Permission</option>
															<option value="Demo" <?php if($data->access=='Demo'){ echo 'selected'; }?> > Demo Show</option>
															<option value="Extended" <?php if($data->access=='Extended'){ echo 'selected'; }?> > Extended Demo</option>
															<option value="Extra" <?php if($data->access=='Extra'){ echo 'selected'; }?> > Extra Edition</option>
															<option value="Paused" <?php if($data->access=='Paused'){ echo 'selected'; }?> > Paused</option>
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
												<label class="col-sm-4 col-lg-2 col-form-label"> Poster(File)</label>
												<div class="col-sm-8 col-lg-10">		
													<div class="input-group">
														<span class="input-group-addo"><img src="<?php echo base_url().'uploads/admin/shows/image/'.$data->image; ?>" style="height: 42px;"></span>
														<input type="file" name="image"  value="<?php echo $data->image; ?>"  accept="image/*" class="form-control"  />
													
													</div>
												</div>
											</div>
											
											<div class="row">
												<label class="col-sm-4 col-lg-2 col-form-label"> PDF(File)</label>
												<div class="col-sm-8 col-lg-10">
													<div class="input-group">
														<a href="<?php echo base_url().'uploads/admin/shows/pdf/'.$data->pdf; ?>" target="_blank">
														<span class="input-group-addon"><i class="fa fa-file"></i></span>
														</a>
														<input type="file" value="<?php echo $data->pdf; ?>"  accept="application/pdf" name="pdf" class="form-control"  />
													</div>
												</div>
											</div>
											<!--
											<div class="row">
												<label class="col-sm-4 col-lg-2 col-form-label"> HTML(File)</label>
												<div class="col-sm-8 col-lg-10">
													<div class="input-group">
														<a href="<?php echo base_url().'uploads/admin/shows/folder/'.$data->folder.'/'; ?>" target="_blank">
														<span class="input-group-addon"><i class="fa fa-globe"></i></span>
														</a>
														<input type="file"  accept=".html" name="html" class="form-control"/>
													</div>
												</div>
											</div>
											
											<div class="row">
												<label class="col-sm-4 col-lg-2 col-form-label"> Folder(Files)</label>
												<div class="col-sm-8 col-lg-10">
													<div class="input-group">
														<a href="<?php echo base_url().'uploads/admin/shows/folder/'.$data->folder.'/'; ?>" target="_blank">
														<span class="input-group-addon"><i class="fa fa-folder"></i></span>
														</a>
														<input type="file" name="folder[]" id="files" multiple directory="" webkitdirectory="" class="form-control"/ >
													</div>
												</div>
											</div> 
											-->
											<!-- Ispring
											<div class="row">
												<label class="col-sm-4 col-lg-2 col-form-label">Ispring Link</label>
												<div class="col-sm-8 col-lg-10">
													<div class="input-group">
														<span class="input-group-addon"><i class="icofont icofont-ui-volume"></i></span>
														<input type="link" name="link" value="<?php echo $data->link; ?>"  class="form-control" placeholder="Enter Ispring Show URL" required>
													</div>
												</div>
											</div>
											-->
											<div class="row">
												<label class="col-sm-4 col-lg-2 col-form-label">Screen</label>
												<div class="col-sm-8 col-lg-10">
													<div class="input-group">
														<input name="pc" type="checkbox" id="d1" value="pc" <?php if($data->is_pc=='Yes'){ echo 'checked'; }?>> 
														<label for="d1" class="chk_box"> PC</label> 
														
														<input  name="mobile" type="checkbox" id="d2" value="mobile" <?php if($data->is_mobile=='Yes'){ echo 'checked'; }?>>
														<label for="d2" class="chk_box" > Mobile</label> 
														
														<input  name="live" type="checkbox" id="d3" value="live" <?php if($data->is_live=='Yes'){ echo 'checked'; }?>>
														<label for="d3" class="chk_box"> Live</label>
													</div>
												</div>
											</div>
											
											<div class="row">
												<label class="col-sm-4 col-lg-2 col-form-label">Description</label>
												<div class="col-sm-8 col-lg-10">
													<div class="input-group">
														<textarea name="description" required  class="form-control textarea" id="editor1" rows="5" placeholder="Enter text here..."><?php echo $data->description; ?></textarea></td>
													</div>
												</div>
											</div>
											
											<div class="row">
												<label class="col-sm-4 col-lg-2 col-form-label"> </label>
												<div class="col-sm-8 col-lg-10">
													<div class="input-group">
														<button type="sunmit" class="btn btn-success">Save</button>
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

<script>
   
	function get_categories(){
		var mid = $('#main_categories_id').val(); 
		$.ajax({
			type:'post',
			url: '<?php echo base_url()."ajax/get_categories/"; ?>',
			data: {mid: mid},
			success:function (result) {
				var res = "'"+result+"'";
				$('#categories_id').html(result);  
				$('#sub_categories_id').html('<option value="">No Record Found</option>');  
			}
		});	
	}
	function get_sub_categories(){
		var cid = $('#categories_id').val(); 
		$.ajax({
			type:'post',
			url: '<?php echo base_url()."ajax/get_sub_categories/"; ?>',
			data: {cid: cid},
			success:function (result) {
				var res = "'"+result+"'";
				$('#sub_categories_id').html(result);  
			}
		});	
	}
</script>

<script src="<?php echo base_url();?>assets/admin/ckeditor/ckeditor.js"></script>
<script>
  
    
    CKEDITOR.replace( 'editor1', {
        toolbar: [
        { name: 'clipboard', groups: [ 'clipboard', 'undo' ], items: [ 'Cut', 'Copy', 'Paste', 'PasteText', 'PasteFromWord', '-', 'Undo', 'Redo' ] },
        '/',
        { name: 'basicstyles', groups: [ 'basicstyles', 'cleanup' ], items: [ 'Bold', 'Italic', 'Underline', 'Strike', 'Subscript', 'Superscript', '-', 'RemoveFormat' ] },
        { name: 'paragraph', groups: [ 'list', 'indent', 'blocks', 'align', 'bidi' ], items: [ 'NumberedList', 'BulletedList', '-', 'Outdent', 'Indent', '-', 'Blockquote', 'CreateDiv', '-', 'JustifyLeft', 'JustifyCenter', 'JustifyRight', 'JustifyBlock', '-', 'BidiLtr', 'BidiRtl', 'Language' ] },
        { name: 'links', items: [ 'Link', 'Unlink', 'Anchor' ] },
        '/',
        { name: 'styles', items: [ 'Styles', 'Format', 'Font', 'FontSize' ] },
        { name: 'colors', items: [ 'TextColor', 'BGColor' ] },
        { name: 'tools', items: [ 'Maximize', 'ShowBlocks' ] },
        { name: 'others', items: [ '-' ] },
        { name: 'about', items: [ 'About' ] }
    ]
    });
    </script>