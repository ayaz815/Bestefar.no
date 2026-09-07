<style>
.chk_box{ 
	margin-right: 15px;
    margin-top: 5px;
    margin-left: 5px;
}
</style>

<div class="pcoded-content">
	<div class="pcoded-inner-content">
		<div class="main-body">
			<div class="page-wrapper">
				<!-- Page-header start -->
				<div class="page-header">
					<div class="row align-items-end">
						<div class="col-lg-8">
							<div class="page-header-title">
								<div class="d-inline">
									<h4><?php echo $page_title; ?></h4>
									<span><?php echo $page_sub_title; ?></span>
								</div>
							</div>
						</div>
						<div class="col-lg-4">
							<div class="page-header-breadcrumb">
								<ul class="breadcrumb-title">
									<li class="breadcrumb-item">
										<a href="#"> <i class="fa fa-cogs"></i> </a>
									</li>
									<li class="breadcrumb-item"><a href="#!">Admin</a>
									</li>
									<li class="breadcrumb-item"><a href="#"><?php echo $page_title; ?></a>
									</li>
								</ul>
							</div>
						</div>
					</div>
				</div>
				<!-- Page-header end -->
				<div class="page-body">
					<div class="row">
						<div class="card col-md-12">
							<div class="card-header">
								<h5>Upload New Show Information</h5>
								<div class="card-header-right">
									<ul class="list-unstyled card-option">
										<li><i class="feather icon-maximize full-card"></i></li>
										<li><i class="feather icon-minus minimize-card"></i></li>
										<li><i class="feather icon-trash-2 close-card"></i></li>
									</ul>
								</div>
								
								<hr>

							</div>
							<div class="card-block">
								<form method="POST" action="<?php echo base_url().strtolower($this->session->userdata('role_name')); ?>/shows_add/create"  enctype="multipart/form-data">
									<div class="row">
										<div class="col-md-12">
											<div class="row">
												<label class="col-sm-4 col-lg-2 col-form-label">Show Name</label>
												<div class="col-sm-8 col-lg-10">
													<div class="input-group">
														<span class="input-group-addon"><i class="icofont icofont-ui-volume"></i></span>
														<input type="text" name="name" class="form-control" placeholder="Enter Show Name" required>
													</div>
												</div>
											</div>
											
											<div class="row">
												<label class="col-sm-4 col-lg-2 col-form-label"> Category</label>
												<div class="col-sm-8 col-lg-10">
													<div class="input-group">
														<span class="input-group-addon"><i class="icofont icofont-list"></i></span>
														<select  name="categories_id" id="categories_id" class="form-control" required  onchange="get_sub_categories();">
															<option disabled selected> Select Category</option>
															<?php foreach($categories as $cat){ ?>
																<option value="<?php echo $cat['categories_id']; ?>"> <?= $cat['name']; ?> </option>
															
															<?php } ?>
														</select>
													</div>
												</div>
											</div>
											
											<div class="row">
												<label class="col-sm-4 col-lg-2 col-form-label"> Sub-Category</label>
												<div class="col-sm-8 col-lg-10">
													<div class="input-group">
														<span class="input-group-addon"><i class="icofont icofont-list"></i></span>
														<select  name="sub_categories_id"  id="sub_categories_id"  class="form-control">
															<option disabled selected> Select Sub-Category</option>
															<?php foreach($sub_categories as $cat){ ?>
																<option value="<?php echo $cat['sub_categories_id']; ?>"> <?= $cat['name']; ?> </option>
															
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
															<option disabled selected> Select Permission</option>
															<option value="Demo"> Demo Show</option>
															<option value="Extended"> Extended Demo</option>
															<option value="Extra"> Extra Edition</option>
															<option value="Paused" > Paused</option>
															
															<!--<?php foreach($categories as $cat){ ?>
																<option value="<?php echo $cat['categories_id']; ?>"> <?= $cat['name']; ?> </option>
															
															<?php } ?>-->
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
														<span class="input-group-addon"><i class="fa fa-image"></i></span>
														<input type="file" name="image"  accept="image/*" class="form-control" required />
													
													</div>
												</div>
											</div>
											
											<div class="row">
												<label class="col-sm-4 col-lg-2 col-form-label"> PDF(File)</label>
												<div class="col-sm-8 col-lg-10">
													<div class="input-group">
														<span class="input-group-addon"><i class="fa fa-file"></i></span>
														<input type="file"  accept="application/pdf" name="pdf" class="form-control" required />
													</div>
												</div>
											</div>
											
											
											<div class="row">
												<label class="col-sm-4 col-lg-2 col-form-label"> HTML(File)</label>
												<div class="col-sm-8 col-lg-10">
													<div class="input-group">
														<span class="input-group-addon"><i class="fa fa-globe"></i></span>
														<input type="file" required accept=".html" name="html" class="form-control"/>
													</div>
												</div>
											</div>
											
											<div class="row">
												<label class="col-sm-4 col-lg-2 col-form-label"> Folder(Files)</label>
												<div class="col-sm-8 col-lg-10">
													<div class="input-group">
														<span class="input-group-addon"><i class="fa fa-folder"></i></span>
														<input type="file" name="folder[]" id="files" multiple directory="" webkitdirectory="" class="form-control"/ required>
													</div>
												</div>
											</div>
											
											
											<!--
											<div class="row">
												<label class="col-sm-4 col-lg-2 col-form-label">Ispring Link</label>
												<div class="col-sm-8 col-lg-10">
													<div class="input-group">
														<span class="input-group-addon"><i class="icofont icofont-ui-volume"></i></span>
														<input type="link" name="link" class="form-control" placeholder="Enter Ispring Show URL" required>
													</div>
												</div>
											</div>
											-->
											<div class="row">
												<label class="col-sm-4 col-lg-2 col-form-label">Playing On</label>
												<div class="col-sm-8 col-lg-10">
													<div class="input-group">
														<input name="pc" type="checkbox" id="d1" value="pc"> 
														<label for="d1" class="chk_box"> PC</label> 
														
														<input  name="mobile" type="checkbox" id="d2" value="mobile">
														<label for="d2" class="chk_box" > Mobile</label> 
														
														<input  name="live" type="checkbox" id="d3" value="live">
														<label for="d3" class="chk_box"> Live</label>
													</div>
												</div>
											</div>
											
											<div class="row">
												<label class="col-sm-4 col-lg-2 col-form-label">Description</label>
												<div class="col-sm-8 col-lg-10">
													<div class="input-group">
 														<textarea rows="4" name="description" class="form-control textarea" id="editor1" placeholder="Enter Show Description" required></textarea>
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
</div>

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