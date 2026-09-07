
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
										<a href="<?php echo base_url(); ?>admin"> <i class="fa fa-cogs"></i> </a>
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
								<h5>Add New Category</h5>
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
								<form method="POST" action="<?php echo base_url().strtolower($this->session->userdata('role_name')); ?>/sub_categories/create"  enctype="multipart/form-data">
									<div class="row">
										<div class="col-md-3">
											<div class="input-group">
												<span class="input-group-addon"><i class="fa fa-list"></i></span>
												<select  name="main_categories_id" id="main_categories_id" class="form-control" required  onchange="get_categories();">
													<option disabled selected> Select Main Category</option>
													<?php foreach($main_categories as $m_cat){ ?>
														<option value="<?php echo $m_cat['main_categories_id']; ?>"> <?= $m_cat['name']; ?> </option>
													
													<?php } ?>
												</select>
											</div>
										</div>
										<div class="col-md-3">
											<div class="input-group">
												<span class="input-group-addon"><i class="fa fa-list"></i></span>
												<select  name="categories_id" id="categories_id" class="form-control" required>
													<option disabled selected> Select Category</option>
													<?php foreach($categories as $cat){ ?>
														<option value="<?php echo $cat['categories_id']; ?>"> <?= $cat['name']; ?> </option>
													
													<?php } ?>
												</select>
											</div>
										</div>
										<div class="col-md-3">
											<div class="input-group">
												<span class="input-group-addon"><i class="icofont icofont-ui-volume"></i></span>
												<input type="text" name="name" class="form-control  " placeholder="Enter Category Tittle"  required>
											</div>
										</div>
										
										<div class="col-md-3">
											<div class="input-group">
												<span class="input-group-addon"><i class="fa fa-image"></i></span>
												<input type="file" name="f_image" class="form-control" >
											</div>
										</div>
										<div class="col-md-3">
											<div class="input-group">
												<button type="sunmit" class="btn btn-success">Save</button>
											</div>												
										</div>
									</div>
								</form>
							</div>
						</div>
					</div>
				</div>
				
				
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
											<th> Sub Category Name</th>
											<th> Category Name</th>
 											<th> Date</th>
											<th class="print_hide">Action</th>
										</tr>
										</thead>
										<tbody>
											<?php $i=0; foreach($sub_categories as $data): $i++; ?>
											<tr>
												<td><?php echo $i; ?></td>
												<td> <input type="number" id="order_<?php echo $data['sub_categories_id']; ?>" value="<?php echo $data['s_order']; ?>" style="width: 35px; border-radius: 5px;border: 1px solid gray;" onkeyup="update_order(<?php echo $data['sub_categories_id']; ?>);"  onchange="update_order(<?php echo $data['sub_categories_id']; ?>);"> <?php echo $data['name']; ?></td>
												<td><?php echo $this->Admin_model->get_tc('categories',$data['categories_id'],'name'); ?></td>
 												<td><?php echo date('d-M-Y', strtotime($data['date'])); ?></td>
												
												<td class="print_hide">
													<button type="button" class="btn btn-warning btn-sm" data-toggle="modal" data-target="#edit_<?php echo $data['sub_categories_id']; ?>">
														<i class="fa fa-pencil"></i>
													</button>
													
													<a href="javascript:;" onclick="confirm_modal_action('<?php echo base_url(); ?>admin/sub_categories/delete/<?php echo $data['sub_categories_id']; ?>');" class="btn btn-danger btn-sm">
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




<?php foreach($sub_categories as $data): ?>
	<div id="edit_<?php echo $data['sub_categories_id']; ?>" class="modal fade" role="dialog">
		<div class="modal-dialog">
			<div class="login-card card-block login-card-modal">
				<form class="md-float-material" method="POST" action="<?php echo base_url(); ?>admin/sub_categories/update"  enctype="multipart/form-data">
					<div class="text-center">
						<!--<img src="<?php echo base_url(); ?>uploads/admin/system_image.jpg" alt="logo.png">-->
					</div>
					<div class="card m-t-15">
						<div class="auth-box card-block">
						<div class="row m-b-20">
							<div class="col-md-12">
								<h3 class="text-center txt-primary">Edit Category</h3>
							</div>
						</div>
						<hr/>
						<div class="row">
							<div class="col-md-12"> 
								<div class="row">
								
									<div class="col-md-12">
										<div class="input-group">
											<span class="input-group-addon"> Categories </span>
											<select  name="categories_id" class="form-control" required>
												<option disabled > Select Category</option>
												<?php foreach($categories as $cat){ ?>
												<option value="<?php echo $cat['categories_id']; ?>" <?php if($cat['categories_id'] ==$data['categories_id'] ){ echo 'selected'; }?>> <?= $cat['name']; ?> </option>
												
												<?php } ?>
											</select>
										</div>
									</div>
									
									
									<div class="col-md-12">
										<div class="input-group">
											<span class="input-group-addon"> Sub Category Name </span>
											<input type="text" name="name" value="<?php echo $data['name']; ?>" class="form-control" placeholder="Enter Category Name" required>
										</div>
									</div>
									
    									<div class="col-md-12">
    										<div class="input-group">
    											<span class="input-group-addon"> Feature Image </span>
    											<input type="file" name="f_image" class="form-control" ><br>
    											<input type="hidden" name="old_image" value="<?php echo $data['image']; ?>" >
    											<?php if($data['image']){ ?>
    											<img src="<?php echo base_url().'uploads/admin/shows/image/'.$data['image']; ?>" style="height: 42px;">
    											<?php }
    											?>
    										</div>
    									</div>
									
								</div>
								
							</div>
						</div>
						
						
						<div class="row m-t-15">
							<div class="col-md-12">
								<input type="hidden" name="sub_categories_id" value="<?php echo $data['sub_categories_id']; ?>">
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
    function update_order(id){
        var order = $("#order_"+id).val();
        if(order >= 0 && order !=''){
        	$.ajax({
    			type:'post',
    			url: '<?php echo base_url()."ajax/update_sorder/"; ?>',
    			data: {id: id, order: order},
    			success:function (result) {
    				notify('fa fa-comments', 'success', 'Title ', ' Status Updates');
    			}
    		});	
        }
    }
 
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
</script>