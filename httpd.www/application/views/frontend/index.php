<!DOCTYPE html>
<html lang="en">

<head>
 	  <?php $this->load->view('frontend/theme/head'); ?>
<style>
  video::-webkit-media-controls {
    display: none !important;
  }

  video::-webkit-media-controls-enclosure {
    display: none !important;
  }

  video::-webkit-media-controls-panel {
    display: none !important;
  }

  video {
    width: 100%; /* Adjust as necessary */
    height: auto; /* Adjust as necessary */
    outline: none; /* Optional, for better visuals */
  }
</style>

</head> 

<body itemtype='https://schema.org/WebPage' itemscope='itemscope' class="page-template-default page page-id-392 ehf-header ehf-footer ehf-template-astra ehf-stylesheet-astra ast-desktop ast-page-builder-template ast-no-sidebar astra-4.5.2 ast-single-post ast-inherit-site-logo-transparent ast-hfb-header elementor-default elementor-kit-10 elementor-page elementor-page-392">
<a class="skip-link screen-reader-text" href="#content" role="link" title="Skip to content"> Skip to content</a>
  <!-- Navigation -->
	<div class="hfeed site full-height" id="page">
  	   <?php $this->load->view('frontend/theme/navigation'); ?>
		<!-- Page Content -->
		<?php $this->load->view('frontend/'.$page_name); ?>	
		 

		<?php $this->load->view('frontend/theme/footer'); ?>
	</div>
	<?php $this->load->view('frontend/theme/script'); ?>

</body>

</html>
