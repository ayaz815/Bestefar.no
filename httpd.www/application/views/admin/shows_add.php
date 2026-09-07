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
												<label class="col-sm-4 col-lg-2 col-form-label"> Select Category</label>
												<div class="col-sm-8 col-lg-10">
													<div class="input-group">
														<span class="input-group-addon"><i class="icofont icofont-list"></i></span>
														<select  name="access" class="form-control" required>
															<option disabled selected> Select Category</option>
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
											
											<div class="row">
												<label class="col-sm-4 col-lg-2 col-form-label">Description</label>
												<div class="col-sm-8 col-lg-10">
													<div class="input-group">
														<textarea name="description" required class="form-control" rows="5" placeholder="Enter text here..."></textarea></td>
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