<header id="masthead" itemscope="itemscope" itemtype="https://schema.org/WPHeader">
			<p class="main-title bhf-hidden" itemprop="headline"><a href="<?php echo base_url(); ?>" title="Bestefar" rel="home">Bestefar</a></p>
					<div data-elementor-type="wp-post" data-elementor-id="241" class="elementor elementor-241" data-elementor-post-type="elementor-hf">
									<section class="elementor-section elementor-top-section elementor-element elementor-element-f43b260 elementor-section-content-middle elementor-hidden-tablet elementor-hidden-mobile elementor-section-boxed elementor-section-height-default elementor-section-height-default" data-id="f43b260" data-element_type="section" data-settings="{&quot;background_background&quot;:&quot;classic&quot;}">
						<div class="elementor-container elementor-column-gap-default">
					<div class="elementor-column elementor-col-50 elementor-top-column elementor-element elementor-element-c962d77" data-id="c962d77" data-element_type="column">
			<div class="elementor-widget-wrap elementor-element-populated">
								<div class="elementor-element elementor-element-73b917e elementor-widget elementor-widget-image" data-id="73b917e" data-element_type="widget" data-widget_type="image.default">
				<div class="elementor-widget-container">
			<style>/*! elementor - v3.18.0 - 08-12-2023 */
.elementor-widget-image{text-align:center}.elementor-widget-image a{display:inline-block}.elementor-widget-image a img[src$=".svg"]{width:48px}.elementor-widget-image img{vertical-align:middle;display:inline-block}</style>													<a href="<?php echo base_url();?>home">
							<img width="982" height="268" src="<?php echo base_url(); ?>assets/frontend/images/logo/Logo-1.jpg" class="attachment-large size-large wp-image-151" alt="" srcset="<?php echo base_url(); ?>assets/frontend/images/logo/Logo-1.jpg 982w, <?php echo base_url(); ?>assets/frontend/images/logo/Logo-1-300x82.jpg 300w, <?php echo base_url(); ?>assets/frontend/images/logo/Logo-1-768x210.jpg 768w" sizes="(max-width: 982px) 100vw, 982px" />								</a>
															</div>
				</div>
					</div>
		</div>
		<div class="elementor-column elementor-col-50 elementor-top-column elementor-element elementor-element-8e0e9b0" data-id="8e0e9b0" data-element_type="column">
			<div class="elementor-widget-wrap elementor-element-populated">
				<div class="dropdown elementor-element elementor-element-8e3e1fa elementor-widget elementor-widget-image" data-id="8e3e1fa" data-element_type="widget" data-widget_type="image.default">
					<div class="  elementor-widget-container">
						<a href="#">
							<img width="951" height="408" src="<?php echo base_url(); ?>assets/frontend/images/logo/key_3-1.png" class="attachment-large size-large wp-image-150" alt="" srcset="<?php echo base_url(); ?>assets/frontend/images/logo/key_3-1.png 951w, <?php echo base_url(); ?>assets/frontend/images/logo/key_3-1-300x129.png 300w, <?php echo base_url(); ?>assets/frontend/images/logo/key_3-1-768x329.png 768w" sizes="(max-width: 951px) 100vw, 951px" />
						</a>
					</div>
					<div class="dropdown-content">
						<?php if(isset($_SESSION['users_id'])){ ?>
						<a href="<?= base_url(); ?>login/logout_user">Logg ut</a>
						<?php }else{ ?>
						<a href="<?= base_url(); ?>login">Åpne</a>|<!--<span class="hide_mobile">|</span>--><a href="<?= base_url(); ?>login/signup">Registrere</a>
						<?php } ?>
					</div>
				</div>
			</div>
		</div>
							</div>
		</section>
				<section class="elementor-section elementor-top-section elementor-element elementor-element-891b9b8 elementor-section-boxed elementor-section-height-default elementor-section-height-default" data-id="891b9b8" id="nav_bar" data-element_type="section" style="background-color: #;">
						<div class="elementor-container elementor-column-gap-default">
					<div class="elementor-column elementor-col-100 elementor-top-column elementor-element elementor-element-8aa116c" data-id="8aa116c" data-element_type="column">
			<div class="elementor-widget-wrap elementor-element-populated">
								<div class="elementor-element elementor-element-943ca85 elementor-hidden-tablet elementor-hidden-mobile elementor-widget elementor-widget-ekit-nav-menu" data-id="943ca85" data-element_type="widget" data-widget_type="ekit-nav-menu.default">
				<div class="elementor-widget-container">
			<div class="ekit-wid-con ekit_menu_responsive_tablet" data-hamburger-icon="" data-hamburger-icon-type="icon" data-responsive-breakpoint="1024">            <button class="elementskit-menu-hamburger elementskit-menu-toggler"  type="button" aria-label="hamburger-icon">
                                    <span class="elementskit-menu-hamburger-icon"></span><span class="elementskit-menu-hamburger-icon"></span><span class="elementskit-menu-hamburger-icon"></span>
                            </button>
            <div id="ekit-megamenu-main-menu" class="elementskit-menu-container elementskit-menu-offcanvas-elements elementskit-navbar-nav-default ekit-nav-menu-one-page-no ekit-nav-dropdown-hover"><ul id="menu-main-menu" class="elementskit-navbar-nav elementskit-menu-po-center submenu-click-on-icon"><?php
	// All sub-category dropdowns removed site-wide per explicit client
	// direction (email, Oct 8): flat 3-item menu only - Litt om oss,
	// Spørreleker, Litt å lese på. Each top-level item links straight to
	// its first real category's blog listing, instead of showing a dropdown.
	if(!function_exists('nav_first_category_url')){
		function nav_first_category_url($ci, $main_categories_id){
			$cat = $ci->db->select('categories_id')
				->from('categories')
				->where('main_categories_id', $main_categories_id)
				->order_by('c_order', 'ASC')
				->order_by('categories_id', 'ASC')
				->limit(1)
				->get()->row();
			return $cat ? base_url().'home/blogs/'.$cat->categories_id : '#';
		}
	}
?>
<li id="menu-item-22" class="menu-item menu-item-type-custom menu-item-object-custom menu-item-22 nav-item elementskit-mobile-builder-content" data-vertical-menu=750px>
	<a href="<?php echo nav_first_category_url($this, 1); ?>" class="ekit-menu-nav-link menu-link">
		<?php echo $this->Admin_model->get_tc('main_categories','1','name'); ?>
	</a>
</li>
<li id="menu-item-27" class="menu-item menu-item-type-custom menu-item-object-custom menu-item-27 nav-item elementskit-mobile-builder-content" data-vertical-menu=750px>
	<a href="<?php echo base_url().'home/sporreleker'; ?>" class="ekit-menu-nav-link menu-link">Spørreleker</a>
</li>
<li id="menu-item-41" class="menu-item menu-item-type-custom menu-item-object-custom menu-item-41 nav-item elementskit-mobile-builder-content" data-vertical-menu=750px>
	<a href="<?php echo nav_first_category_url($this, 3); ?>" class="ekit-menu-nav-link menu-link">
		<?php echo $this->Admin_model->get_tc('main_categories','3','name'); ?>
	</a>
</li>

</ul><div class="elementskit-nav-identity-panel">
				<div class="elementskit-site-title">
					<a class="elementskit-nav-logo" href="<?php echo base_url(); ?>" target="_self" rel="">
						<img src="" title="" alt="" />
					</a> 
				</div><button class="elementskit-menu-close elementskit-menu-toggler" type="button">X</button></div></div>			
			<div class="elementskit-menu-overlay elementskit-menu-offcanvas-elements elementskit-menu-toggler ekit-nav-menu--overlay"></div></div>		</div>
				</div>
					</div>
		</div>
							</div>
		</section>
				<section class="elementor-section elementor-top-section elementor-element elementor-element-54cc628 elementor-section-content-middle elementor-hidden-desktop elementor-section-boxed elementor-section-height-default elementor-section-height-default" data-id="54cc628" data-element_type="section" data-settings="{&quot;background_background&quot;:&quot;classic&quot;}">
						<div class="elementor-container elementor-column-gap-default">
					<div class="elementor-column elementor-col-33 elementor-top-column elementor-element elementor-element-5c19654" data-id="5c19654" data-element_type="column">
			<div class="elementor-widget-wrap elementor-element-populated">
								<div class="elementor-element elementor-element-4c66180 elementor-widget elementor-widget-image" data-id="4c66180" data-element_type="widget" data-widget_type="image.default">
				<div class="elementor-widget-container">
																<a href="<?php echo base_url();?>home">
							<img style="margin-left:3px !important" width="982" height="268" src="<?php echo base_url(); ?>assets/frontend/images/logo/Logo-1.jpg" class="attachment-large size-large wp-image-151" alt="" srcset="<?php echo base_url(); ?>assets/frontend/images/logo/Logo-1.jpg 982w, <?php echo base_url(); ?>assets/frontend/images/logo/Logo-1-300x82.jpg 300w, <?php echo base_url(); ?>assets/frontend/images/logo/Logo-1-768x210.jpg 768w" sizes="(max-width: 982px) 100vw, 982px" />								</a>
															</div>
				</div>
					</div>
		</div>
				<div class="elementor-column elementor-col-33 elementor-top-column elementor-element elementor-element-23fec82" data-id="23fec82" data-element_type="column">
			<div class="elementor-widget-wrap elementor-element-populated">
			<div class="dropdown elementor-element elementor-element-eae3730 elementor-widget elementor-widget-image" data-id="eae3730" data-element_type="widget" data-widget_type="image.default" onclick="hide_dropdown();">
				<div class="elementor-widget-container">
					<a href="#">
						<img width="951" height="408" src="<?php echo base_url(); ?>assets/frontend/images/logo/key_3-1.png" class="attachment-large size-large wp-image-150" alt="" srcset="<?php echo base_url(); ?>assets/frontend/images/logo/key_3-1.png 951w, <?php echo base_url(); ?>assets/frontend/images/logo/key_3-1-300x129.png 300w, <?php echo base_url(); ?>assets/frontend/images/logo/key_3-1-768x329.png 768w" sizes="(max-width: 951px) 100vw, 951px" />
					</a>
					</div>
					<div class="dropdown-content">
						<?php if(isset($_SESSION['users_id'])){ ?>
						<a href="<?= base_url(); ?>login/logout_user">Logg ut</a>
						<?php }else{ ?>
						<a href="<?= base_url(); ?>login">Åpne</a>|<!--<span class="hide_mobile">|</span>--><a href="<?= base_url(); ?>login/signup">Registrere</a>
						<?php } ?>
					</div>
				</div>
					</div>
		</div>
				<div class="elementor-column elementor-col-33 elementor-top-column elementor-element elementor-element-1f440fa elementor-hidden-desktop" data-id="1f440fa" data-element_type="column">
			<div class="elementor-widget-wrap elementor-element-populated">
								<div class="elementor-element elementor-element-1b289fd elementor-widget-tablet__width-initial elementor-widget-mobile__width-inherit hfe-nav-menu__align-left hfe-submenu-icon-arrow hfe-submenu-animation-none hfe-link-redirect-child hfe-nav-menu__breakpoint-tablet elementor-widget elementor-widget-navigation-menu" data-id="1b289fd" data-element_type="widget" data-widget_type="navigation-menu.default">
				<div class="elementor-widget-container">
						<div class="hfe-nav-menu hfe-layout-horizontal hfe-nav-menu-layout horizontal hfe-pointer__none" data-layout="horizontal">
				<div role="button" class="hfe-nav-menu__toggle elementor-clickable">
					<span class="screen-reader-text">Menu</span>
					<div class="hfe-nav-menu-icon" onclick="give_margin()">
						<svg aria-hidden="true"  class="e-font-icon-svg e-fas-align-justify" viewBox="0 0 448 512" xmlns="http://www.w3.org/2000/svg"><path d="M432 416H16a16 16 0 0 0-16 16v32a16 16 0 0 0 16 16h416a16 16 0 0 0 16-16v-32a16 16 0 0 0-16-16zm0-128H16a16 16 0 0 0-16 16v32a16 16 0 0 0 16 16h416a16 16 0 0 0 16-16v-32a16 16 0 0 0-16-16zm0-128H16a16 16 0 0 0-16 16v32a16 16 0 0 0 16 16h416a16 16 0 0 0 16-16v-32a16 16 0 0 0-16-16zm0-128H16A16 16 0 0 0 0 48v32a16 16 0 0 0 16 16h416a16 16 0 0 0 16-16V48a16 16 0 0 0-16-16z"></path></svg>					</div>
				</div>
				<nav class="hfe-nav-menu__layout-horizontal hfe-nav-menu__submenu-arrow" id="nav_bar" data-full-width="yes" style="z-index: 9999 !important; right: 12px !important; width: 200px !important;">
				    <ul id="menu-1-1b289fd" class="hfe-nav-menu">
				
				
				<li id="menu-item-22" class="menu-item menu-item-type-custom menu-item-object-custom hfe-creative-menu">
					<a href="<?php echo nav_first_category_url($this, 1); ?>" class="hfe-menu-item">
						<?php echo $this->Admin_model->get_tc('main_categories','1','name'); ?>
					</a>
				</li>
<li id="menu-item-41" class="menu-item menu-item-type-custom menu-item-object-custom hfe-creative-menu">
	<a href="<?php echo nav_first_category_url($this, 3); ?>" class="hfe-menu-item">
		<?php echo $this->Admin_model->get_tc('main_categories','3','name'); ?>
	</a>
</li>
<!-- Mobile only shows AudioQuiz, not the full catalog - per client direction
     (email, Oct 8): "it will only be the AudioQuiz that should be available
     on that device", so this links straight to the catalog pre-filtered to
     AudioQuiz instead of the generic Spørreleker link used on desktop. -->
<li id="menu-item-27" class="menu-item menu-item-type-custom menu-item-object-custom hfe-creative-menu">
	<a href="<?php echo base_url().'home/sporreleker?type=AudioQuiz'; ?>" class="hfe-menu-item">AudioQuiz</a>
</li>
</ul></nav>
			</div>
					</div>
				</div>
					</div>
		</div>
							</div>
		</section>
							</div>
				</header>