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
					<div class="row"  id="print_div">
						<div class="card col-md-12">
							<div class="card-header">
								<h5>Blogs Details</h5>
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
													<th> Title</th>
													<th> Feature Image</th>
													<th> Cover Image</th>
													<th class="print_hide">Action</th>
												</tr>
										</thead>
										 
										<tbody>
											<?php $i=0; foreach($blogs as $blog): $i++; ?>
											<tr>
												<td><?php echo $i; ?></td>
												
												<td>
												<input type="number" id="order_<?php echo $blog['blogs_id']; ?>" value="<?php echo $blog['b_order']; ?>" style="width: 35px; border-radius: 5px;border: 1px solid gray;" onkeyup="update_order(<?php echo $blog['blogs_id']; ?>);"  onchange="update_order(<?php echo $blog['blogs_id']; ?>);">
												<b><?php echo $blog['title']; ?></b><br/>
													<span style="color:grey"> 
														<?php echo $blog['writer']; ?> <br/>
														<?php echo $this->Admin_model->get_tc('categories',$blog['categories_id'],'name'); ?> 
															<?php if($blog['sub_categories_id'] > 0 ){ ?> 
														<i class="fa fa-play" style="color: #808080a6;font-size: 10px;"></i>
														<?php echo $this->Admin_model->get_tc('sub_categories',$blog['sub_categories_id'],'name'); ?>
														<?php } ?> <br/>
														<?php echo $blog['date']; ?>
													</span>
													
<div>
  <a href="<?php echo base_url().'home/blog_details/'.$blog['blogs_id']; ?>" onclick="copyURI(event)" class="btn btn-info btn-sm">Copy blog URL</a>
</div>
												
												</td>
												<td><img src="<?php echo base_url().'uploads/admin/blogs/'.$blog['f_image']; ?>" style="height:60px;"></td>
												 <td><img src="<?php echo base_url().'uploads/admin/blogs/'.$blog['c_image']; ?>" style="height:60px;"></td>
												<td class=""> 
													
													<br>
													<!--<button type="button" class="btn btn-warning btn-sm" onclick="showAjaxModal('<?php echo base_url() ?>modal/popup_frontend/edit_blog/<?php echo $blog['blogs_id']; ?>');" >
														<i class="fa fa-edit"></i>
													</button>-->
													<a type="button" class="btn btn-warning btn-sm" href="<?php echo base_url() ?>admin/blogs_edit/<?php echo $blog['blogs_id']; ?>" >
														<i class="fa fa-edit"></i>
													</a>
													<a href="javascript:;" onclick="confirm_modal_action('<?php echo base_url().strtolower($this->session->userdata('role_name')); ?>/blogs/delete/<?php echo $blog['blogs_id']; ?>');" class="btn btn-danger btn-sm">
														<i class="fa fa-trash"></i>
													</a>
													<?php if($blog['status'] == 'Active'): ?>
													<a href="javascript:;" onclick="confirm_modal_action('<?php echo base_url().strtolower($this->session->userdata('role_name')); ?>/blogs/status/<?php echo $blog['blogs_id']; ?>');" class="btn btn-warning btn-sm">
														Unpublish
													</a>
													<?php elseif($blog['status'] == 'Draft'): ?>
													
													<a href="javascript:;" onclick="confirm_modal_action('<?php echo base_url().strtolower($this->session->userdata('role_name')); ?>/blogs/status/<?php echo $blog['blogs_id']; ?>');" class="btn btn-success btn-sm">
														Publish
													</a>
    												<a type="button" class="btn btn-primary btn-sm" href="<?php echo base_url() ?>admin/preview_blogs/<?php echo $blog['blogs_id']; ?>" target="_blank">
                                                        <i class="fa fa-eye"></i>
                                                    </a>

													<?php endif; ?>
													<?php if($blog['paid'] == 1){ ?>
													<a type="button" class="btn btn-primary btn-sm" style="color:white" onclick="confirm_modal_action('<?php echo base_url().strtolower($this->session->userdata('role_name')); ?>/blogs/paid_blog/<?php echo $blog['blogs_id']; ?>');">
                                                        Paid
                                                    </a>
                                                    <?php }else{?>
                                                    
                                                    <a type="button" class="btn btn-primary btn-sm" style="color:white" onclick="confirm_modal_action('<?php echo base_url().strtolower($this->session->userdata('role_name')); ?>/blogs/paid_blog/<?php echo $blog['blogs_id']; ?>');">
                                                        Unpaid
                                                    </a>
                                                    
                                                    <?php } ?>
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
</div><a href="www.g.com" onclick="copyURI(event)">Copy cover URL</a>

 <script>
    function update_order(id){
        var order = $("#order_"+id).val();
        //alert(order);
        if(order >= 0 && order !=''){
        	$.ajax({
    			type:'post',
    			url: '<?php echo base_url()."ajax/update_border/"; ?>',
    			data: {id: id, order: order},
    			success:function (result) {
    				notify('fa fa-comments', 'success', 'Title ', ' Status Updates');
    			}
    		});	
        }
    }
    
      function copyURI(evt) {
            evt.preventDefault();
            navigator.clipboard.writeText(evt.target.getAttribute('href')).then(() => {
              /* clipboard successfully set */
            }, () => {
              /* clipboard write failed */
            });
        }
        
 </script>
