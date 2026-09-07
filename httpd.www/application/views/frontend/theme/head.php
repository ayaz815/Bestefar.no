<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
 <!-- This site is optimized with the Yoast SEO plugin v21.7 - https://yoast.com/wordpress/plugins/seo/ -->
	<title>Bestefar</title>
 	<meta property="og:locale" content="en_US" />
	<meta property="og:type" content="article" />
	<!-- gutter link
	<meta property="og:title" content="Superdatamaskiner skal beregne vaksineeffekt - Bestefar" />
	<meta property="og:description" content="Klovnene kommer snart One morning, when Gregor Samsa woke from troubled dreams, he found himself transf ormed in his bed into a horrible vermin. He lay on his armor-like back, and if he lifted his head a little, he could see his brown belly, slightly domed and divided by arches into stiff sections.  The bedding &hellip; Superdatamaskiner skal beregne vaksineeffekt Read More &raquo;" />
	 
	 -->
	<?php if($page_name == 'blog_details'){ ?>
        <meta property="og:title" content="<?php echo $blog->title; ?> - Bestefar" />
        <meta property="og:image" content="<?php echo base_url(); ?>uploads/admin/blogs/<?php echo $blog->f_image; ?>" width="1024" height="629">
        <meta property="og:image:width" content="1200" />
        <meta property="og:image:height" content="630" />
        <meta property="og:site_name" content="bestefar.no" />
        <meta property="article:author" content="bestefar.no" />

    <?php }elseif($page_name == 'show_details'){ ?>
        <meta property="og:title" content="<?php echo $show->name; ?> - Bestefar" />
        <meta property="og:image" content="<?php echo base_url() . 'uploads/admin/shows/image/' . $show->image; ?>" width="1024" height="629"/>
        <meta property="og:image:width" content="1200" />
        <meta property="og:image:height" content="630" />
        <!--<meta property="og:description" content="Klovnene kommer snart One morning, when Gregor Samsa woke from troubled dreams, he found himself transf ormed in his bed into a horrible vermin. He lay on his armor-like back, and if he lifted his head a little, he could see his brown belly, slightly domed and divided by arches into stiff sections.  The bedding &hellip; Superdatamaskiner skal beregne vaksineeffekt Read More &raquo;" />-->
        <meta property="og:site_name" content="bestefar.no" />
        <meta property="article:author" content="bestefar.no" />
    <?php }elseif($page_name == 'shows_f'){?>
        <meta property="og:title" content="Shows - Bestefar" />
        <meta property="og:description" content="<?php if(!empty($blog->title)){ echo $blog->title; } ?>" />
        <meta property="og:url" content="https://bestefar.com" />
        <meta property="og:image" content="<?php echo base_url() . 'uploads/admin/shows/image/' . $categories_data[0]['image']; ?>"/>
        <meta property="og:type" content="article" />
        <meta property="og:site_name" content="bestefar.com" />
        <meta property="article:author" content="bestefar.com" />
        <meta property="og:title" content="- Bestefar" />
       
    <?php }?>
<link rel='stylesheet' id='astra-theme-css-css' href='<?php echo base_url(); ?>assets/frontend/css/main.min.css?ver=4.5.2' media='all' />
 
<link rel='stylesheet' id='hfe-style-css' href='<?php echo base_url(); ?>assets/frontend/css/header-footer-elementor.css?ver=1.6.22' media='all' />
<link rel='stylesheet' id='elementor-frontend-css' href='<?php echo base_url(); ?>assets/frontend/css/frontend-lite.min_1.css' media='all' />
<link rel='stylesheet' id='elementor-frontend-css' href='<?php echo base_url(); ?>assets/frontend/css/frontend-lite.min.css?ver=3.18.2' media='all' />
<link rel='stylesheet' id='swiper-css' href='<?php echo base_url(); ?>assets/frontend/css/swiper.min.css?ver=8.4.5' media='all' />
<link rel='stylesheet' id='elementor-post-10-css' href='<?php echo base_url(); ?>assets/frontend/css/post-10.css?ver=1702483896' media='all' />
<link rel='stylesheet' id='elementor-pro-css' href='<?php echo base_url(); ?>assets/frontend/css/frontend-lite.min.css?ver=3.18.1' media='all' />
<link rel='stylesheet' id='elementor-global-css' href='<?php echo base_url(); ?>assets/frontend/css/global.css?ver=1702632613' media='all' />
<link rel='stylesheet' id='elementor-post-392-css' href='<?php echo base_url(); ?>assets/frontend/css/post-392.css?ver=1704043331' media='all' />
<link rel='stylesheet' id='hfe-widgets-style-css' href='<?php echo base_url(); ?>assets/frontend/css/frontend.css?ver=1.6.22' media='all' />
<link rel='stylesheet' id='elementor-post-241-css' href='<?php echo base_url(); ?>assets/frontend/css/post-241.css?ver=1702677552' media='all' />
<link rel='stylesheet' id='elementor-post-649-css' href='<?php echo base_url(); ?>assets/frontend/css/post-649.css?ver=1702632613' media='all' />
<link rel='stylesheet' id='elementor-icons-ekiticons-css' href='<?php echo base_url(); ?>assets/frontend/css/ekiticons.css?ver=3.0.4' media='all' />
<link rel='stylesheet' id='ekit-widget-styles-css' href='<?php echo base_url(); ?>assets/frontend/css/widget-styles.css?ver=3.0.4' media='all' />
   <link rel='stylesheet' id='elementor-post-16-css' href='<?php echo base_url(); ?>assets/frontend/css/post-16.css?ver=1702760016' media='all' />
<link rel="stylesheet" href="<?php echo base_url();?>assets/font/Agency.ttf">
<link rel="stylesheet" href="<?php echo base_url();?>assets/font/ArchivoBlack.ttf">
<link rel="stylesheet" href="<?php echo base_url();?>assets/font/ArialBlack.ttf">
   
<link rel='stylesheet' id='elementor-post-14-css' href='<?php echo base_url(); ?>assets/frontend/css/post-14.css?ver=1704135908' media='all' />


    <link rel="stylesheet" href="<?php echo base_url(); ?>assets/frontend/agencyfb_font/specimen_files/specimen_stylesheet.css" type="text/css" charset="utf-8"/>
    <link rel="stylesheet" href="<?php echo base_url(); ?>assets/frontend/agencyfb_font/stylesheet.css" type="text/css" charset="utf-8"/>

    <style type="text/css">
        		body {
			font-family: 'agency_fbbold';
        		}
        	
@font-face {
        font-family: 'Agency';
        src: url('<?php echo base_url();?>assets/font/Agency.ttf') format('truetype');
    }
   @font-face {
    font-family: 'Arial Black';
    src: local('Arial Black'), local('Arial Black'), url('<?php echo base_url();?>assets/font/ArialBlack.ttf') format('truetype');
}

    @font-face {
    font-family: 'Archivo Black';
    src: url('<?php echo base_url();?>assets/fonts/ArchivoBlack.ttf') format('truetype');
}

@media only screen and (min-width: 1366px) and (max-width: 1366px) and (min-height: 1024px) and (max-height: 1024px) {
    #ekit-megamenu-main-menu{
        visibility: display;
    }
   
}



            </style>




<link rel="icon" href="<?php echo base_url(); ?>assets/frontend/images/fevicon.png" sizes="32x32" />
<link rel="icon" href="<?php echo base_url(); ?>assets/frontend/images/fevicon.png" sizes="192x192" />
<link rel="apple-touch-icon" href="<?php echo base_url(); ?>assets/frontend/images/fevicon.png" />
    
<style id="responsive-fixes">
/* ── Global responsive fixes ── */
img { max-width: 100%; height: auto; }

.elementor-column.elementor-col-100 {
    width: 100% !important;
}

/* Desktop nav margin fix */
@media (min-width: 1025px) {
    .elementor-241 .elementor-element.elementor-element-8aa116c > .elementor-element-populated {
        margin-top: 0 !important;
    }
}

/* Mobile/Tablet nav: hide the dropdown by default, show only when toggled */
@media (max-width: 1024px) {
    .elementor-241 .elementor-element.elementor-element-54cc628 > .elementor-container {
        max-width: 100% !important;
    }

    /* Hide nav menu by default; JS adds .menu-open to show it */
    nav.hfe-nav-menu__layout-horizontal {
        display: none !important;
        position: absolute !important;
        right: 0 !important;
        top: 100% !important;
        width: 200px !important;
        flex-direction: column !important;
        background: #ffc000;
        z-index: 9999 !important;
        box-shadow: 0 4px 12px rgba(0,0,0,0.2);
    }
    nav.hfe-nav-menu__layout-horizontal.menu-open {
        display: flex !important;
    }
    nav.hfe-nav-menu__layout-horizontal ul.hfe-nav-menu {
        flex-direction: column;
        width: 100%;
    }
    nav.hfe-nav-menu__layout-horizontal ul.hfe-nav-menu li.menu-item {
        width: 100%;
    }
    nav.hfe-nav-menu__layout-horizontal ul.hfe-nav-menu li a {
        display: block;
        padding: 10px 14px;
        color: #000 !important;
        font-weight: 600;
        text-decoration: none !important;
        border-bottom: 1px solid rgba(0,0,0,0.1);
    }

    /* Keep the hamburger column positioned relatively so dropdown anchors to it */
    .elementor-241 .elementor-element.elementor-element-1f440fa {
        position: relative !important;
    }
}

/* Mobile (<768px): ensure logo + key + hamburger fit in one row */
@media (max-width: 767px) {
    .elementor-241 .elementor-element.elementor-element-54cc628 {
        padding: 6px 0 !important;
    }
    .elementor-241 .elementor-element.elementor-element-5c19654 { width: 50% !important; }
    .elementor-241 .elementor-element.elementor-element-23fec82 { width: 35% !important; }
    .elementor-241 .elementor-element.elementor-element-1f440fa { width: 15% !important; }
    .blog_desc { font-size: 14px !important; }
    .blog_desc p { font-size: 14px !important; }
    h1 { font-size: 24pt !important; }
}

/* Very small screens (<400px) */
@media (max-width: 400px) {
    .elementor-241 .elementor-element.elementor-element-5c19654 { width: 55% !important; }
    .elementor-241 .elementor-element.elementor-element-23fec82 { width: 30% !important; }
    .elementor-241 .elementor-element.elementor-element-1f440fa { width: 15% !important; }
    .dropdown-content a { font-size: 12px; padding: 10px 6px; }
}
</style>

<style id="wp-custom-css">
    @media only screen and (max-width: 767px) {
		.dropdown-content {
			right: -7px !important;
		}    
	}
	html, body {
		overflow-x: hidden;
	}


	.elementor-11 .elementor-element.elementor-element-150fae6 .elementskit-navbar-nav > li > a {
		min-width:270px;
		justify-content:center;
	}

	.elementor-241 .elementor-element.elementor-element-943ca85 .elementskit-navbar-nav > li > a {
		min-width:200px;
		justify-content:center
	}
	.hed a {
		text-decoration:none !important;
	}


	.elementor-241 .elementor-element.elementor-element-1b289fd nav.hfe-dropdown .menu-item a.hfe-sub-menu-item {
		background-color: #9dc3e6 !important;
	}
	.elementor-145 .elementor-element.elementor-element-80c8331 nav.hfe-dropdown .menu-item a.hfe-sub-menu-item {
		background-color: #9dc3e6 !important;
	}


	.elementor-241 .elementor-element.elementor-element-943ca85 .elementskit-navbar-nav > li > a .elementskit-submenu-indicator {
		color: #101010;
		fill: #101010;
		display:none;
	}
	.elementor-11 .elementor-element.elementor-element-150fae6 .elementskit-navbar-nav > li > a .elementskit-submenu-indicator {
		display:none;
	}



	.elementor-1291 .elementor-element.elementor-element-4ad02d3 .elementskit-navbar-nav > li > a {
		min-width:200px;
		justify-content:center;
	}
	img.wp-image-467.alignleft{
		margin-right: 30px;
	}

	.elementor-1468 .elementor-element.elementor-element-6047bb4 .elementor-post__title, .elementor-1468 .elementor-element.elementor-element-6047bb4 .elementor-post__title a {
		margin-top:-8px
	}		

	.dropdown {
	  position: relative;
	  display: inline-block;
	}
	.dropdown-content {
	  display: none;
	  position: absolute;
	  background-color: #ffc000;
	  min-width: 30px;
	  box-shadow: 0px 8px 16px 0px rgba(0,0,0,0.2);
	  z-index: 1;
	  //width:60%;
	  right: 0;
	}

	.dropdown-content a {
	  color: black;
	  padding: 12px 10px;
	  text-decoration: none !important;
	  display: block;
	  font-family: "Calibri", sans-serif;
	  font-weight: 600;
	}
	.dropdown:hover .dropdown-content {display: block;}
	.lh{ line-height: 30px; }
	
	html, body {
  height: 100%;
  margin: 0;
}

.full-height {
  height: 110%;
}
@media (max-width:383px){
    .dropdown-content a {
	  padding: 12px 5px;
	}
 .hide_mobile{
	 display:none;
 }
}
@media (max-width:333px){
    .dropdown-content a {
	  padding: 12px 2px;
	}
}
@media(max-width:1024px) and (min-width:768px){.elementor-241 .elementor-element.elementor-element-54cc628{padding:10px 0px 10px 0px;} }
 @font-face {
        font-family: 'Agency';
        font-style: normal;
        src: url('<?php echo base_url();?>assets/font/Agency.ttf') format('truetype');
}

 @font-face {
        font-family: 'Archivo Black';
        font-style: normal;
        src: url('<?php echo base_url();?>assets/font/ArchivoBlack.ttf') format('truetype');
}
</style>

<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/css/bootstrap.min.css">
  <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
  <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/js/bootstrap.min.js"></script>
<!--<script src="https://kit.fontawesome.com/dc9dc0fbbf.js" crossorigin="anonymous"></script>-->
