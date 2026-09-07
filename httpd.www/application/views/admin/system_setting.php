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
								<h5>Manage System Setting</h5>
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
								<form method="POST" action="<?php echo base_url().strtolower($this->session->userdata('role_name')); ?>/system_setting/update"  enctype="multipart/form-data">
									<div class="row">
										<div class="col-md-8">
											<div class="row">
												<label class="col-sm-4 col-lg-3 col-form-label">System Name</label>
												<div class="col-sm-8 col-lg-9">
													<div class="input-group">
														<span class="input-group-addon"><i class="icofont icofont-ui-volume"></i></span>
														<input type="hidden" name="system_setting_id" value="<?php echo $system_data->system_setting_id; ?>" required>
														<input type="text" name="system_name" class="form-control  no_intiger" placeholder="" value="<?php echo $system_data->system_name; ?>" required>
													</div>
												</div>
											</div>
											
											<div class="row">
												<label class="col-sm-4 col-lg-3 col-form-label"> Email</label>
												<div class="col-sm-8 col-lg-9">
													<div class="input-group">
														<span class="input-group-addon">@</span>
														<input type="email" name="email" class="form-control" placeholder="" value="<?php echo $system_data->email; ?>" required>
													</div>
												</div>
											</div>
											
											<div class="row">
												<label class="col-sm-4 col-lg-3 col-form-label"> Phone</label>
												<div class="col-sm-8 col-lg-9">
													<div class="input-group">
														<span class="input-group-addon"><i class="fa fa-phone"></i></span>
														<input type="text" id="customer_phone"  name="phone" class="form-control" placeholder="" value="<?php echo $system_data->phone; ?>" required>
														<input id="phone_mask" style="display:none" checked="checked" type="checkbox">
														<label id="descr" style="display:none" for="phone_mask"></label>
													</div>
												</div>
											</div>
											
											<div class="form-group row">
												<label class="col-sm-3 col-form-label">Address</label>
												<div class="col-sm-9">
													<textarea rows="5" cols="5" name="address" class="form-control"
															  placeholder="Address"><?php echo $system_data->address; ?></textarea>
												</div>
											</div>
											
											<div class="row">
												<label class="col-sm-4 col-lg-3 col-form-label"> </label>
												<div class="col-sm-8 col-lg-9">
													<div class="input-group">
														<button type="sunmit" class="btn btn-success">Update System</button>
													</div>
												</div>
											</div>
											
										</div>
										
										<div class=" col-md-4">
											<center>
												<?php if(empty($system_data->system_image)){ ?>
													<img src="<?php echo base_url(); ?>assets/admin/images/admin.png" style="width:200px;">
												<?php }else{ ?>
													<img src="<?php echo base_url(); ?>uploads/admin/<?php echo $system_data->system_image; ?>" style="width:200px;">
												<?php } ?>
												<br/>
												<br/>
												<div class="input-group  col-md-10 col-md-offset-1">
													<span class="input-group-addon"><i class="fa fa-image"></i></span>
													<input type="file" name="system_image" class="form-control"/>
												</div>
											</center>
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
<script src="<?php echo base_url(); ?>assets/masking/jquery-2.js" type="text/javascript"></script>
		<script src="<?php echo base_url(); ?>assets/masking/jquery_002.js" type="text/javascript"></script>
        <script src="<?php echo base_url(); ?>assets/masking/jquery.js" type="text/javascript"></script>
		<script>
            var maskList = $.masksSort($.masksLoad("http://cdn.rawgit.com/andr-04/inputmask-multi/master/data/phone-codes.json"), ['#'], /[0-9]|#/, "mask");
            var maskOpts = {
                inputmask: {
                    definitions: {
                        '#': {
                            validator: "[0-9]",
                            cardinality: 1
                        }
                    },
                    showMaskOnHover: false,
                    autoUnmask: true,
                    clearMaskOnLostFocus: false
                },
                match: /[0-9]/,
                replace: '#',
                list: maskList,
                listKey: "mask",
                onMaskChange: function(maskObj, determined) {
                    if (determined) {
                        var hint = maskObj.name_en;
                        if (maskObj.desc_en && maskObj.desc_en != "") {
                            hint += " (" + maskObj.desc_en + ")";
                        }
                        $("#descr").html(hint);
                    } else {
                        $("#descr").html("");
                    }
                }
            };

            $('#phone_mask').change(function() {
                if ($('#phone_mask').is(':checked')) {
                    $('#customer_phone').inputmask("remove");
                    $('#customer_phone').inputmasks(maskOpts);
                } else {
                    $('#customer_phone').inputmasks("remove");
                    $('#customer_phone').inputmask("+#{*}", maskOpts.inputmask);
                    $("#descr").html("Mask of input");
                }
            });

            $('#phone_mask').change();
        </script>