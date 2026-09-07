<style>
.chk_box{ 
	margin-right: 15px;
    margin-top: 5px;
    margin-left: 5px;
}
body{
    color: black !important;
}
@font-face {
        font-family: 'Agency';
        src: url('<?php echo base_url();?>assets/font/Agency.ttf') format('truetype');
    }
    @font-face {
    font-family: 'Archivo Black';
    src: url('<?php echo base_url();?>assets/fonts/ArchivoBlack.ttf') format('truetype');
}

@font-face {
    font-family: 'Arial Black';
    src: url('<?php echo base_url();?>assets/fonts/ArialBlack.ttf') format('truetype');
}
    .ck-editor__editable {
        font-family: 'Agency', sans-serif;
    }
</style>
<link rel="stylesheet" href="<?php echo base_url();?>assets/font/Agency.ttf">
<link rel="stylesheet" href="<?php echo base_url();?>assets/font/ArchivoBlack.ttf">
<link rel="stylesheet" href="https://www.tiny.cloud/css/codepen.min.css">
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />

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
								<h5>Upload New Email</h5>
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
								<form method="POST" action="<?php echo base_url().strtolower($this->session->userdata('role_name')); ?>/admin_emails/update"  enctype="multipart/form-data">
									<div class="row">
										<div class="col-md-12">
										    
											<div class="row">
												<label class="col-sm-4 col-lg-2 col-form-label">Email</label>
												<div class="col-sm-8 col-lg-10">
													<div class="input-group">
														<span class="input-group-addon"><i class="icofont icofont-ui-volume"></i></span>
														<input type="text" name="email" class="form-control" placeholder="Enter Email" value="<?php echo $admin_email[0]['email']?>" required>
													</div>
												</div>
											</div>
											
											
											<div class="row">
												<label class="col-sm-4 col-lg-2 col-form-label"> Status</label>
												<div class="col-sm-8 col-lg-10">
													<div class="input-group">
														<span class="input-group-addon"><i class="icofont icofont-ui-check"></i></span>
														<select  name="status" id="status" class="form-control" required>
															<option value="1" <?php if($admin_email[0]['status'] == 1){ echo 'selected';}?>> Active </option>
															<option value="0" <?php if($admin_email[0]['status'] == 0){ echo 'selected';}?>> Inactive </option>
														</select>
													</div>
												</div>
											</div>
											
											
											
											<div class="row">
												<label class="col-sm-4 col-lg-2 col-form-label"> </label>
												<div class="col-sm-8 col-lg-10">
													<div class="input-group">
													    <input type="hidden" name="admin_emails_id" value="<?php echo $admin_email[0]['admin_emails_id']?>"/>
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
