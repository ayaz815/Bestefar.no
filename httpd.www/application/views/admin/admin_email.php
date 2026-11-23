
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
									<li class="breadcrumb-item"><a href="#"> Admin Emails</a>
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
								<h5>Listing emails</h5>
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
											
											<?php $i=0; foreach($admin_emails as $data): $i++; ?>
											<tr>
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








