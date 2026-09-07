	<div id="content" class="site-content">
		<div id="content" class="elementor elementor-16">
			<div id="content" class="elementor-section elementor-top-section elementor-element elementor-element-60622e5 elementor-section-boxed elementor-section-height-default elementor-section-height-default">
				<div class="elementor-container elementor-column-gap-default">
				 
					<div class="row">
						<?php foreach($blogs as $blog){ ?>
						<div class="col-md-6">
							<img src="<?php echo base_url(); ?>uploads/admin/blogs/<?php echo $blog['f_image']; ?>" class="img-thumbnail img-responsive">
							<h4> <?php echo $blog['title']; ?> </h4>
						</div>
						<?php } ?>
					</div>
				</div>
			</div>
		</div>
	</div><!-- #content -->