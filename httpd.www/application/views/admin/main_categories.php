
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
								<h5>Categories Details</h5>
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
											<th> Main Category Title</th>
											<th class="print_hide">Action</th>
										</tr>
										</thead>
										<tbody>
											<?php $i=0; foreach($main_categories as $data): $i++; ?>
											<tr>
												<td><?php echo $i; ?></td>
												<td> <?php echo $data['name']; ?> </td>

												<td class="print_hide">
													<button type="button" class="btn btn-warning btn-sm" data-toggle="modal" data-target="#edit_<?php echo $data['main_categories_id']; ?>">
														<i class="fa fa-pencil"></i>
													</button>
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




<?php foreach($main_categories as $data): ?>
	<div id="edit_<?php echo $data['main_categories_id']; ?>" class="modal fade" role="dialog">
		<div class="modal-dialog">
			<div class="login-card card-block login-card-modal">
				<form class="md-float-material" method="POST" action="<?php echo base_url(); ?>admin/main_categories/update"  enctype="multipart/form-data">
					<div class="text-center">
						<!--<img src="<?php echo base_url(); ?>uploads/admin/system_image.jpg" alt="logo.png">-->
					</div>
					<div class="card m-t-15">
						<div class="auth-box card-block">
						<div class="row m-b-20">
							<div class="col-md-12">
								<h3 class="text-center txt-primary">Edit Main Category</h3>
							</div>
						</div>
						<hr/>
						<div class="row">
							<div class="col-md-12">
								
								<div class="row">
									<div class="col-md-12">
										<div class="input-group">
											<span class="input-group-addon"> Main Category </span>
											<input type="text" name="name" value="<?php echo $data['name']; ?>" class="form-control" placeholder="Enter Main Category Name" required>
										</div>
									</div>
									 
								</div>
								
							</div>
						</div>
						
						
						<div class="row m-t-15">
							<div class="col-md-12">
								<input type="hidden" name="main_categories_id" value="<?php echo $data['main_categories_id']; ?>">
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

