<style>
.tbl_img{width:40px;}
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
					<div class="row"  id="print_div">
						<div class="card col-md-12">
							<div class="card-header">
								<h5>Shows Details</h5>
								<div class="card-header-right">
									<ul class="list-unstyled card-option">
										<li><i class="feather icon-maximize full-card print_hide"></i></li>
										<li><i class="feather icon-minus minimize-card print_hide"></i></li>
										<li><i class="feather icon-trash-2 close-card print_hide"></i></li>
									</ul>
								</div>
								
								<hr>

							</div>
							<div class="card-block">
								<div class="dt-responsive table-responsive">
									<table id="simpletable"
										   class="table table-striped table-bordered nowrap">
										 <thead>
												<tr>
													<th>#</th>
													<th> Feature Image</th>
													<th> Title</th>
													<th> Operating System</th>
													<th> Play</th>
													<th> File</th>
													<th class="print_hide">Action</th>
												</tr>
										</thead>
										<tbody>
											<?php $i=0; foreach($shows as $show): $i++; ?>
											
											<tr>
												<td><?php echo $i; ?></td>
												<td><img src="<?php echo base_url().'uploads/admin/shows/image/'.$show['image']; ?>" style="width:100px;"></td>
												<td>
													<h4><?php echo $show['name']; ?></h4>
													<input onchange="update_access('<?php echo $show['shows_id']; ?>','Demo')" name="category_<?php echo $i; ?>" type="radio" id="c1_<?php echo $i; ?>" <?php if($show['access']=='Demo'){ echo 'checked'; } ?>> 
													<label for="c1_<?php echo $i; ?>"> Demo Show</label> <br/>
													
													<input onchange="update_access('<?php echo $show['shows_id']; ?>','Extended')" name="category_<?php echo $i; ?>" type="radio" id="c2_<?php echo $i; ?>" <?php if($show['access']=='Extended'){ echo 'checked'; } ?>>
													<label for="c2_<?php echo $i; ?>"> Extended Demo</label> <br/>
													
													<input onchange="update_access('<?php echo $show['shows_id']; ?>','Extra')"  name="category_<?php echo $i; ?>" type="radio" id="c3_<?php echo $i; ?>" <?php if($show['access']=='Extra'){ echo 'checked'; } ?>>
													<label for="c3_<?php echo $i; ?>"> Extra Edition</label> <br/>
													
													<input onchange="update_access('<?php echo $show['shows_id']; ?>','Paused')" name="category_<?php echo $i; ?>" type="radio" id="c4_<?php echo $i; ?>" <?php if($show['access']=='Paused'){ echo 'checked'; } ?>>
													<label for="c4_<?php echo $i; ?>"> Paused</label> <br/>
													
													<input onchange="update_access('<?php echo $show['shows_id']; ?>','Info')" name="category_<?php echo $i; ?>" type="radio" id="c4_<?php echo $i; ?>" <?php if($show['access']=='Info'){ echo 'checked'; } ?>>
													<label for="c4_<?php echo $i; ?>"> Åpne</label> <br/>
												</td>
												<td> <br/><br/>
													<input onclick="update_screen('<?php echo $show['shows_id']; ?>','PC','d1_<?php echo $i; ?>')" name="device" type="checkbox" id="d1_<?php echo $i; ?>" <?php if($show['is_pc']=='Yes'){ echo 'checked'; }?>> 
													<label for="d1_<?php echo $i; ?>"> PC</label> <br/>
													
													<input onclick="update_screen('<?php echo $show['shows_id']; ?>','Mobile', 'd2_<?php echo $i; ?>')" name="Mobile" type="checkbox" id="d2_<?php echo $i; ?>" <?php if($show['is_mobile']=='Yes'){ echo 'checked'; }?>>
													<label for="d2_<?php echo $i; ?>"> Mobile</label> <br/>
													
													<input onclick="update_screen('<?php echo $show['shows_id']; ?>','Live', 'd3_<?php echo $i; ?>')" name="device" type="checkbox" id="d3_<?php echo $i; ?>" <?php if($show['is_live']=='Yes'){ echo 'checked'; }?>>
													<label for="d3_<?php echo $i; ?>"> Live</label> <br/>
												</td> 
												<td>
													 <br/> <br/>
													  <a href="<?php echo base_url().'uploads/admin/shows/folder/'.$show['folder'].'/'; ?>" target="_blank">
													  <!--<a href="<?php echo $show['link']; ?>" target="_blank">-->
													<img src="<?php echo base_url().'assets/admin/images/power.webp' ?>" style="width:80px;">
													</a>
												</td> 
												 <td>
													 <br/> <br/>
													 <a href="<?php echo base_url().'uploads/admin/shows/pdf/'.$show['pdf']; ?>" target="_blank">
													<img src="<?php echo base_url().'assets/admin/images/doc.svg' ?>" style="width:80px;">
													</a>
												</td>
												 
												<td class=""> 
													
													<br>
													<button type="button" class="btn btn-info btn-sm" data-toggle="modal" data-target="#edit_<?php echo $show['shows_id']; ?>" style="margin-left: 10px;">
														<i class="fa fa-folder"></i> Update Folder
													</button> <br><br>
													<!--  Edit Button fo Modal
													<button type="button" class="btn btn-warning btn-sm" onclick="showAjaxModal('<?php echo base_url() ?>modal/popup_frontend/edit_show/<?php echo $show['shows_id']; ?>');" style="margin-left: 10px;">
														<i class="fa fa-edit"></i> Update Show
													</button>
													-->
													<a type="button" class="btn btn-warning btn-sm"  href="<?php echo base_url() ?>admin/shows_edit/<?php echo  $show['shows_id']; ?>" style="margin-left: 10px;">
														<i class="fa fa-edit"></i> Update Show
													</a>
													<br>
													<br>
													<a href="javascript:;" onclick="confirm_modal_action('<?php echo base_url().strtolower($this->session->userdata('role_name')); ?>/shows/delete/<?php echo $show['shows_id']; ?>');" class="btn btn-danger btn-sm" style="margin-left: 10px;">
														<i class="fa fa-trash"></i> Delete Show
													</a>
												</td> 
											</tr>
											<?php endforeach; ?>
										</tbody>
										
										</tfoot>
									</table>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
<script>
	function update_access(sid,access){
		$.ajax({
			type:'post',
			url: '<?php echo base_url()."ajax/update_access/"; ?>',
			data: {sid: sid, access: access},
			success:function (result) {
				notify('fa fa-comments', 'success', 'Title ', ' Status Updates');
			}
		});	
	}
	function update_screen(sid,access, id){
		if($('#'+id).prop('checked') == true){
			var status = 'Yes';
		}else{
			var status = 'No';
		}
		$.ajax({
			type:'post',
			url: '<?php echo base_url()."ajax/update_screen/"; ?>',
			data: {sid: sid, access: access, status:status},
			success:function (result) {
				notify('fa fa-comments', 'success', 'Title ', ' Status Updates');
			}
		});	
	}
</script>

<?php foreach($shows as $show): ?>
	<div id="edit_<?php echo $show['shows_id']; ?>" class="modal fade" role="dialog">
		<div class="modal-dialog">
			<div class="login-card card-block login-card-modal">
				<form class="md-float-material" method="POST" action="<?php echo base_url().strtolower($this->session->userdata('role_name')); ?>/shows/update_show_folder"  enctype="multipart/form-data">
					
					<div class="card m-t-15">
						<div class="auth-box card-block">
						<div class="row m-b-20">
							<div class="col-md-12">
							<button type="button" class="close" data-dismiss="modal">&times;</button>
								<h4 class="text-center txt-primary">Update the folder and HTML File</h3>
							</div>
						</div>
						<hr/>
						<div class="row">
							<input type="hidden" name="shows_id" value="<?php echo $show['shows_id']; ?>" required>
							<label class="col-sm-5 col-lg-4 col-form-label"> HTML(File)</label>
							<div class="col-sm-7 col-lg-8">
								<div class="input-group">
									<span class="input-group-addon"><i class="fa fa-globe"></i></span>
									<input type="file" required accept=".html" name="html" class="form-control"/>
								</div>
							</div>
							<span class="md-line"></span>
						</div>
						
						<div class="row">
							<label class="col-sm-5 col-lg-4 col-form-label"> Folder(Files)</label>
							<div class="col-sm-7 col-lg-8">
								<div class="input-group">
									<span class="input-group-addon"><i class="fa fa-folder"></i></span>
									<input type="file" name="folder[]" id="files" multiple class="form-control" accept=".zip" required />

								</div>
							</div>
						</div>
						
						
						<div class="row m-t-15">
							<div class="col-md-12">
								<button type="submit" class="btn btn-primary btn-md btn-block waves-effect text-center">Update</button>
							</div>
						</div>
						<hr/>
						
					</div>
					</div>
				</form>
			</div>
		</div>
	</div>
	<!-- Register modal end -->
<?php endforeach; ?>