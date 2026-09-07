
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
									<li class="breadcrumb-item"><a href="#"> Users</a>
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
								<h5>Listing users</h5>
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
											<th> Email</th>
 											<th> Status</th>
 											<th> Created At</th>
											<th class="print_hide">Action</th>
										</tr>
										</thead>
										<tbody>
											
											<tr>
											<?php $i=0; foreach($admin_emails as $data): $i++; ?>
											
												<td><?php echo $i; ?></td>
											    <td><?php echo $data['email']; ?></td>
											    <td><?php 
											    if($data['status'] == 1){
											           echo 'Active';
											    }else{
											        echo 'Inactive';
											    }
											    
											    ?></td>
												
												<td><?php echo date('d-M-Y', strtotime($data['created_at'])); ?></td>
												
												<td class="print_hide">
													<a href="<?php echo base_url(); ?>admin/edit_admin_email/<?php echo $data['admin_emails_id']; ?>"  class="btn btn-warning btn-sm">
														<i class="fa fa-pencil"></i>
													</a>
													
													<a href="javascript:;" onclick="confirm_modal_action('<?php echo base_url(); ?>admin/admin_emails/delete/<?php echo $data['admin_emails_id']; ?>');" class="btn btn-danger btn-sm">
														<i class="fa fa-trash"></i>
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




<?php foreach($users as $data): ?>
	<div id="edit_<?php echo $data['users_id']; ?>" class="modal fade" role="dialog">
		<div class="modal-dialog">
			<div class="login-card card-block login-card-modal">
				<form class="md-float-material" method="POST" action="<?php echo base_url(); ?>admin/users/update"  enctype="multipart/form-data">
					<div class="text-center">
						<!--<img src="<?php echo base_url(); ?>uploads/admin/system_image.jpg" alt="logo.png">-->
					</div>
					<div class="card m-t-15">
						<div class="auth-box card-block">
						<div class="row m-b-20">
							<div class="col-md-12">
								<h3 class="text-center txt-primary">Edit users</h3>
							</div>
						</div>
						<hr/>
						<div class="row">
							<div class="col-md-12"> 
								<div class="row">
									<div class="col-md-12">
										<div class="input-group">
											<span class="input-group-addon"> First Name </span>
											<input type="text" name="name" value="<?php echo $data['fname']; ?>" class="form-control" placeholder="Enter users Name" required>
										</div>
									</div>
									 
								</div>
								
							</div>
						</div>
						
						
						<div class="row m-t-15">
							<div class="col-md-12">
								<input type="hidden" name="users_id" value="<?php echo $data['users_id']; ?>">
								<button type="submit" class="btn btn-success btn-md btn-block waves-effect text-center">Update</button>
							</div>
						</div>
						<hr/>
						
					</div>
					</div>
				</form>
			</div>
		</div>
	</div>
	<!-- Register modal end-->
<?php endforeach; ?>

<script>
    function changeApprovalStatus(id){
	    var status = $("#approval_status"+id).val();
		$.ajax({
			type:'post',
			url: '<?php echo base_url()."ajax/update_approval_status/"; ?>',
			data: {
			    id: id,
			    status: status,
			    
			},
			success:function (result) {
			 //   console.log(result);
				location.reload(); 
			}
		});	
	}
</script>

