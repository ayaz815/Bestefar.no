<style>
@media (min-width:768px){
	.blogs_img{
		height:171px !important;
	}
	.elementor-post__title{
		margin-top: -20px !important; 
	}
	p{
	    text-align: justify !important;
	}
}
.up-arrow {
    width: 0;
    height: 0;
    border-left: 10px solid transparent;
    border-right: 10px solid transparent;
    border-bottom: 18px solid gray;
    /* Change this color as needed */
    display: inline-block;
    margin-top: -5px;
    transform: rotate(90deg);
    margin-right:6px;
    /* Adjust this to move the arrow closer to or further from the text */
}
a:hover {
    text-decoration: none;
}

.margin-class{
    margin-top: -33px !important;
}
@media only screen and (max-width: 767px) {
    .margin-class{
    margin-top: 0px !important;
}
}
</style>
	<div id="content" class="site-content">
		<div id="content" class="elementor elementor-16 elementor-14">
			<div id="content" class="elementor-section elementor-top-section elementor-element elementor-element-60622e5 elementor-section-boxed elementor-section-height-default elementor-section-height-default">
				<div class="elementor-container elementor-column-gap-default">
				 


<div class="elementor-column elementor-col-100 elementor-top-column elementor-element elementor-element-c9a3d65" data-id="c9a3d65" data-element_type="column">
			<div class="elementor-widget-wrap elementor-element-populated">
								<section class="elementor-section elementor-inner-section elementor-element elementor-element-76fa028 elementor-section-full_width elementor-section-height-default elementor-section-height-default" data-id="76fa028" data-element_type="section">
						<div class="elementor-container elementor-column-gap-default">
					<div class="elementor-column elementor-col-100 elementor-inner-column elementor-element elementor-element-1ad88bc" data-id="1ad88bc" data-element_type="column">
			<div class="elementor-widget-wrap elementor-element-populated">
								<div class="elementor-element elementor-element-0ecedeb elementor-widget elementor-widget-heading" data-id="0ecedeb" data-element_type="widget" data-widget_type="heading.default">
				<div class="elementor-widget-container">
			<style>/*! elementor - v3.18.0 - 08-12-2023 */
.elementor-heading-title{padding:0;margin:0;line-height:1}.elementor-widget-heading .elementor-heading-title[class*=elementor-size-]>a{color:inherit;font-size:inherit;line-height:inherit}.elementor-widget-heading .elementor-heading-title.elementor-size-small{font-size:15px}.elementor-widget-heading .elementor-heading-title.elementor-size-medium{font-size:19px}.elementor-widget-heading .elementor-heading-title.elementor-size-large{font-size:29px}.elementor-widget-heading .elementor-heading-title.elementor-size-xl{font-size:39px}.elementor-widget-heading .elementor-heading-title.elementor-size-xxl{font-size:59px}</style>
<?php if(!empty($blogs)){ ?>
<h2 class="elementor-heading-title elementor-size-default" style="margin-left:1px;font-family: agency_fbregular;">Artikler  <?php if($category){ echo '<div class="up-arrow"></div><a href="'.base_url().'home/blogs/'.$category[0]['categories_id'].'">'.$category[0]['name'].'</a>'; } if($sub_category){ echo '<div class="up-arrow" style="margin-left:6px"></div><a href="'.base_url().'home/blogs/'.$category[0]['categories_id'].'/'.$sub_category[0]['sub_categories_id'].'">'.$sub_category[0]['name']."</a>";}?></h2>
<?php } ?>
		</div>
				</div>
					</div>
		</div>
							</div>
		</section>
				<section class="elementor-section elementor-inner-section elementor-element elementor-element-a473ad5 elementor-section-full_width elementor-section-height-default elementor-section-height-default" data-id="a473ad5" data-element_type="section">
						<div class="elementor-container elementor-column-gap-default">
					<div class="elementor-column elementor-col-100 elementor-inner-column elementor-element elementor-element-bcd4cfc" data-id="bcd4cfc" data-element_type="column">
			<div class="elementor-widget-wrap">
									</div>
		</div>
							</div>
		</section>
				<div class="elementor-element elementor-element-1ff95c4 elementor-grid-2 elementor-grid-tablet-2 elementor-grid-mobile-1 elementor-posts--thumbnail-top elementor-widget elementor-widget-posts" data-id="1ff95c4" data-element_type="widget" data-settings="" data-widget_type="" style="margin-left:-1px;">
				<div class="elementor-widget-container">
			<link rel="stylesheet" href="<?php echo base_url(); ?>assets/frontend/css/widget-posts.min.css">		<div class="elementor-posts-container elementor-posts elementor-posts--skin-classic elementor-grid elementor-has-item-ratio" >
				
			<?php foreach($blogs as $k=> $blog){ ?>
			<article class="elementor-post elementor-grid-item post-1458 post type-post status-publish format-standard has-post-thumbnail hentry category-uncategorized <?php if($k > 1){?> margin-class <?php }?>">
				<a class="elementor-post__thumbnail__link" href="<?php echo base_url().'home/blog_details/'.$blog['blogs_id']; ?>" tabindex="">
			<div class="elementor-post__thumbnail"><img fetchpriority="high" decoding="async"  src="<?php echo base_url(); ?>uploads/admin/blogs/<?php echo $blog['f_image']; ?>" class="blogs_img attachment-medium size-medium wp-image-1355" alt="" srcset="<?php echo base_url(); ?>uploads/admin/blogs/<?php echo $blog['f_image']; ?>  300w, <?php echo base_url(); ?>uploads/admin/blogs/<?php echo $blog['f_image']; ?> 1024w, <?php echo base_url(); ?>uploads/admin/blogs/<?php echo $blog['f_image']; ?> 768w, <?php echo base_url(); ?>uploads/admin/blogs/<?php echo $blog['f_image']; ?> 1200w" sizes="(max-width: 300px) 100vw, 300px"></div>
		</a>
				<div class="elementor-post__text">
				<h3 class="elementor-post__title" >
			<a href="<?php echo base_url().'home/blog_details/'.$blog['blogs_id']; ?>" style="font-weight:bold">
				<?php echo $blog['title']; ?>			</a>
		</h3>
				</div>
				</article>
				
				
				<?php } ?>
				<?php if(empty($blogs)){ ?>
				<h4>Ingen Data Tilgjengelig</h4>
				<br/><br/><br/><br/><br/><br/><br/><br/><br/><br/><br/><br/><br/><br/><br/><br/><br/><br/><br/>
				<?php } ?>

				</div>
		
				</div>
				</div>
					</div>
		</div>

















				</div>
			</div>
		</div>
	</div><!-- #content -->