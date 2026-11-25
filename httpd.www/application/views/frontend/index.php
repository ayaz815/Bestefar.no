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
<?php if (isset($page_name) && $page_name == 'sporreleker') { ?>
<!-- Quiz list page only (client video 24 Nov 2026, "jumping" while loading):
     its stylesheet and the Agency font are fetched here, early, instead of half
     way down the page, and the list stays invisible (its space kept) until its
     rows and font are ready - see the qc-ready script in sporreleker.php.
     The stylesheet stays after theme/head.php: it overrides #page.full-height.
     ?v= is the file's upload time: one.com sends no cache headers, so browsers
     kept an old copy for a day or more after an upload; a new file = a new link. -->
<link rel="preload" href="<?php echo base_url('assets/frontend/agencyfb_font/agencyr-webfont.woff2'); ?>" as="font" type="font/woff2" crossorigin>
<link rel="stylesheet" href="<?php echo base_url('assets/frontend/css/sporreleker.css') . '?v=' . (int) @filemtime(FCPATH . 'assets/frontend/css/sporreleker.css'); ?>">
<script id="qc-wait">document.documentElement.classList.add('qc-wait'); setTimeout(function(){ document.documentElement.classList.remove('qc-wait'); }, 3000);</script>
<?php } ?>

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
