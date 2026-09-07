 
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
<link rel='stylesheet' id='elementor-post-14-css' href='<?php echo base_url(); ?>assets/frontend/css/post-452.css' media='all' />

<style>
.close_btn{
    	background: url(<?php echo base_url(); ?>assets/frontend/images/Lukk.png) no-repeat !important;
    	cursor: pointer  !important;
    	border: none !important;
    	background-size: 100% !important;
        width: 120px !important;
        height: 50px; 
    }
    
    #laptopicon{
        margin-left:16px;
    }
/*@media (min-width:768px){*/
/*	.div_ml{*/
/*		margin-left:-1px;*/
/*	}*/
	
/*}*/
@media (max-width:768px){
	.div_ml{
		margin-left:0px;
	}
    .show_mb_1{
		margin-right:1px;
	}
	#laptopicon{
        margin-left:6px !important;
    }
}
@media only screen and (max-width: 1025px) {
	.category_title{
	    margin-top: 13px !important;
	}
}
@media only screen and (min-width: 1025px) {
    #myModal{
	    margin-left: 100px;
	}
}
@media screen and (max-width: 1024px) and (min-width: 768px) {
    #myModal{
	    margin-left: -3%;
	}
}
</style>

	<div id="content" class="site-content">
		<div id="content" class="elementor elementor-16 elementor-14 elementor-452">
			<div id="content" class="elementor-section elementor-top-section elementor-element elementor-element-60622e5 elementor-section-boxed elementor-section-height-default elementor-section-height-default">
				<div class="elementor-container elementor-column-gap-default">
				 <!------------>


	<div class="elementor-column elementor-col-100 elementor-top-column elementor-element elementor-element-c9a3d65 div_ml" data-id="c9a3d65" data-element_type="column"  style="margin-top:5px;">
		<div class="elementor-widget-wrap elementor-element-populated">
								
			 
								<section class="elementor-section elementor-inner-section elementor-element elementor-element-362013b elementor-section-boxed elementor-section-height-default elementor-section-height-default" data-id="362013b" data-element_type="section">
						<div class="elementor-container elementor-column-gap-default">
					<div class="elementor-column elementor-col-100 elementor-inner-column elementor-element elementor-element-b202f16" data-id="b202f16" data-element_type="column">
			<div class="elementor-widget-wrap elementor-element-populated">
								<div class="elementor-element elementor-element-64e3720 elementor-widget elementor-widget-heading" data-id="64e3720" data-element_type="widget" data-widget_type="heading.default">
				<div class="elementor-widget-container">
			<style>/*! elementor - v3.18.0 - 08-12-2023 */
.elementor-heading-title{padding:0;margin:0;line-height:1}.elementor-widget-heading .elementor-heading-title[class*=elementor-size-]>a{color:inherit;font-size:inherit;line-height:inherit}.elementor-widget-heading .elementor-heading-title.elementor-size-small{font-size:15px}.elementor-widget-heading .elementor-heading-title.elementor-size-medium{font-size:19px}.elementor-widget-heading .elementor-heading-title.elementor-size-large{font-size:29px}.elementor-widget-heading .elementor-heading-title.elementor-size-xl{font-size:39px}.elementor-widget-heading .elementor-heading-title.elementor-size-xxl{font-size:59px}</style>
	<h2 class="elementor-heading-title elementor-size-default category_title"  style="margin-left: 2px;font-family: agency_fbregular;font-size: 27px;">
	    <a href="<?php echo base_url(); ?>/home/shows/<?php echo $show->categories_id; ?>"  style="text-decoration:none;color:black;">
		    <?php echo $this->Admin_model->get_tc('categories',$show->categories_id, 'name'); ?> <i class="fa fa-play" style="color: #808080a6;font-size: 16px;"></i>
		</a>
		<?php if($show->sub_categories_id > 0){ ?>
		 <a href="<?php echo base_url().'/home/shows/'. $show->categories_id.'/'.$show->sub_categories_id; ?>" style="text-decoration:none;color:black;">
		    <?php echo $this->Admin_model->get_tc('sub_categories',$show->sub_categories_id, 'name'); ?>  <i class="fa fa-play" style="color: #808080a6;font-size: 16px;"></i>
		</a>
		<?php } ?>
		<?php echo $show->name; ?>
		
	</h2>		
</div>
				</div>
					</div>
		</div>
							</div>
		</section>
				<section class="elementor-section elementor-inner-section elementor-element elementor-element-6166a3d elementor-section-boxed elementor-section-height-default elementor-section-height-default show_mb_1" data-id="6166a3d" data-element_type="section" data-settings="{&quot;background_background&quot;:&quot;classic&quot;}">
						<div class="elementor-container elementor-column-gap-default">
					<div class="elementor-column elementor-col-50 elementor-inner-column elementor-element elementor-element-2269245" data-id="2269245" data-element_type="column">
			<div class="elementor-widget-wrap elementor-element-populated">
				<div class="elementor-element elementor-element-ce1a71f elementor-widget elementor-widget-image" data-id="ce1a71f" data-element_type="widget" data-widget_type="image.default">
					<div class="elementor-widget-container">
						<img fetchpriority="high" decoding="async" width="840" height="1024" src="<?php echo base_url(); ?>uploads/admin/shows/image/<?php echo $show->image; ?>" class="attachment-large size-large wp-image-458" alt="" srcset="<?php echo base_url(); ?>uploads/admin/shows/image/<?php echo $show->image; ?> 840w, <?php echo base_url(); ?>uploads/admin/shows/image/<?php echo $show->image; ?> 246w, <?php echo base_url(); ?>uploads/admin/shows/image/<?php echo $show->image; ?> 768w, <?php echo base_url(); ?>uploads/admin/shows/image/<?php echo $show->image; ?> 920w" sizes="(max-width: 840px) 100vw, 840px">
					</div>
				</div>
					</div>
		</div>
				<div class="elementor-column elementor-col-50 elementor-inner-column elementor-element elementor-element-e9dbff3" data-id="e9dbff3" data-element_type="column">
			<div class="elementor-widget-wrap elementor-element-populated">
			<div class="elementor-element elementor-element-e0cd736 elementor-widget elementor-widget-image" data-id="e0cd736" data-element_type="widget" data-widget_type="image.default">
				<div class="elementor-widget-container">
					<?php
						if(isset($_SESSION['users_id']) && $_SESSION['approval_status'] == 1 && ($show->access == 'Info' || $show->access == 'Demo')){
							$link_folder = 'href="'.base_url().'uploads/admin/shows/folder/'.$show->folder.'/"  target="_blank"';
							$link_pdf = 'href="'.base_url().'uploads/admin/shows/pdf/'.$show->pdf.'"  target="_blank"';
						}elseif(isset($_SESSION['users_id']) || $show->access == 'Info'){
						    
							$link_folder = 'href="'.base_url().'uploads/admin/shows/folder/'.$show->folder.'/"  target="_blank"';
							$link_pdf = 'href="'.base_url().'uploads/admin/shows/pdf/'.$show->pdf.'"  target="_blank"';
						}elseif(isset($_SESSION['users_id']) && $_SESSION['status'] == 'Active' && ($show->access == 'Info' || $show->access == 'Demo')){
							$link_folder = 'href="'.base_url().'uploads/admin/shows/folder/'.$show->folder.'/"  target="_blank"';
							$link_pdf = 'href="'.base_url().'uploads/admin/shows/pdf/'.$show->pdf.'"  target="_blank"';
						}else{
						    if(isset($_SESSION['status']) && $_SESSION['status'] == 'Active'){
						        
						        $link_folder = 'href="#"  data-toggle="modal" data-target="#myPaidModal"';
							    $link_pdf = 'href="#"  data-toggle="modal" data-target="#myPaidModal"';
						    }else{
						        
						        $link_folder = 'href="#"  data-toggle="modal" data-target="#myModal"';
							    $link_pdf = 'href="#"  data-toggle="modal" data-target="#myModal"';
						    }
						    
						}
					?>
					<a <?php echo $link_folder; ?> >
					<!--<a href="<?php echo base_url().'uploads/admin/shows/folder/'.$show->folder.'/'; ?>" target="_blank">-->
					<img decoding="async" width="133" height="133" src="<?php echo base_url(); ?>assets/frontend/images/power.png" class="attachment-large size-large wp-image-466" alt="" style="width:80%">
					</a>
				</div>
			</div>
			<div class="elementor-element elementor-element-cd3a072 elementor-widget elementor-widget-image" data-id="cd3a072" data-element_type="widget" data-widget_type="image.default">
				<div class="elementor-widget-container">
					<a <?php echo $link_pdf; ?>>
					<img decoding="async" width="103" height="134" src="<?php echo base_url(); ?>assets/frontend/images/doc.png" class="attachment-large size-large wp-image-467" alt="" style="width:80%">
					</a>
				</div>
			</div>
			
			<?php if($show->is_pc =='Yes'){ ?>
			<div class="elementor-element elementor-element-44e0680 elementor-widget__width-auto elementor-widget-mobile__width-auto elementor-view-default elementor-widget elementor-widget-icon" data-id="44e0680" data-element_type="widget" data-widget_type="icon.default">
				<div class="elementor-widget-container">
					<div class="elementor-icon-wrapper">
						<div class="elementor-icon" id="laptopicon">
							<svg aria-hidden="true" class="e-font-icon-svg e-fas-laptop" viewBox="0 0 640 512" xmlns="http://www.w3.org/2000/svg"><path d="M624 416H381.54c-.74 19.81-14.71 32-32.74 32H288c-18.69 0-33.02-17.47-32.77-32H16c-8.8 0-16 7.2-16 16v16c0 35.2 28.8 64 64 64h512c35.2 0 64-28.8 64-64v-16c0-8.8-7.2-16-16-16zM576 48c0-26.4-21.6-48-48-48H112C85.6 0 64 21.6 64 48v336h512V48zm-64 272H128V64h384v256z"></path>
							</svg>			
						</div>
					</div>
				</div>
			</div>
			<?php } if($show->is_mobile =='Yes'){ ?>
			<div class="elementor-element elementor-element-c7e4791 elementor-widget__width-auto elementor-widget-mobile__width-auto elementor-view-default elementor-widget elementor-widget-icon" data-id="c7e4791" data-element_type="widget" data-widget_type="icon.default">
				<div class="elementor-widget-container">
					<div class="elementor-icon-wrapper">
						<div class="elementor-icon">
							<svg aria-hidden="true" class="e-font-icon-svg e-fas-mobile-alt" viewBox="0 0 320 512" xmlns="http://www.w3.org/2000/svg"><path d="M272 0H48C21.5 0 0 21.5 0 48v416c0 26.5 21.5 48 48 48h224c26.5 0 48-21.5 48-48V48c0-26.5-21.5-48-48-48zM160 480c-17.7 0-32-14.3-32-32s14.3-32 32-32 32 14.3 32 32-14.3 32-32 32zm112-108c0 6.6-5.4 12-12 12H60c-6.6 0-12-5.4-12-12V60c0-6.6 5.4-12 12-12h200c6.6 0 12 5.4 12 12v312z"></path></svg>		
						</div>
					</div>
				</div>
			</div>
			<?php } if($show->is_live =='Yes'){ ?>
			<div class="elementor-element elementor-element-db69e6e elementor-widget__width-auto elementor-widget-mobile__width-auto elementor-view-default elementor-widget elementor-widget-icon" data-id="db69e6e" data-element_type="widget" data-widget_type="icon.default">
				<div class="elementor-widget-container">
					<div class="elementor-icon-wrapper">
						<div class="elementor-icon">
							<svg aria-hidden="true" class="e-font-icon-svg e-fas-microphone-alt" viewBox="0 0 352 512" xmlns="http://www.w3.org/2000/svg"><path d="M336 192h-16c-8.84 0-16 7.16-16 16v48c0 74.8-64.49 134.82-140.79 127.38C96.71 376.89 48 317.11 48 250.3V208c0-8.84-7.16-16-16-16H16c-8.84 0-16 7.16-16 16v40.16c0 89.64 63.97 169.55 152 181.69V464H96c-8.84 0-16 7.16-16 16v16c0 8.84 7.16 16 16 16h160c8.84 0 16-7.16 16-16v-16c0-8.84-7.16-16-16-16h-56v-33.77C285.71 418.47 352 344.9 352 256v-48c0-8.84-7.16-16-16-16zM176 352c53.02 0 96-42.98 96-96h-85.33c-5.89 0-10.67-3.58-10.67-8v-16c0-4.42 4.78-8 10.67-8H272v-32h-85.33c-5.89 0-10.67-3.58-10.67-8v-16c0-4.42 4.78-8 10.67-8H272v-32h-85.33c-5.89 0-10.67-3.58-10.67-8v-16c0-4.42 4.78-8 10.67-8H272c0-53.02-42.98-96-96-96S80 42.98 80 96v160c0 53.02 42.98 96 96 96z"></path></svg>
						</div>
					</div>
				</div>
			</div>
			<?php }  ?>
		</div>
		</div>
							</div>
		</section>
				<section class="elementor-section elementor-inner-section elementor-element elementor-element-f730fa8 elementor-section-boxed elementor-section-height-default elementor-section-height-default" data-id="f730fa8" data-element_type="section" data-settings="{&quot;background_background&quot;:&quot;classic&quot;}">
						<div class="elementor-container elementor-column-gap-default">
					<div class="elementor-column elementor-col-100 elementor-inner-column elementor-element elementor-element-1463b81" data-id="1463b81" data-element_type="column" data-settings="{&quot;background_background&quot;:&quot;classic&quot;}">
			<div class="elementor-widget-wrap elementor-element-populated">
								<div class="elementor-element elementor-element-c8b5726 elementor-widget elementor-widget-heading" data-id="c8b5726" data-element_type="widget" data-widget_type="heading.default">
				<div class="elementor-widget-container">
			<h2 class="elementor-heading-title elementor-size-default"  style="font-family: arial;"><b>Beskrivelse</b></h2>		</div>
				</div>
				<div class="elementor-element elementor-element-1685d9a elementor-widget elementor-widget-text-editor" data-id="1685d9a" data-element_type="widget" data-widget_type="text-editor.default ">
				<div class="elementor-widget-container " style="padding-bottom: 10px;">
			<style>/*! elementor - v3.18.0 - 08-12-2023 */
.elementor-widget-text-editor.elementor-drop-cap-view-stacked .elementor-drop-cap{background-color:#69727d;color:#fff}.elementor-widget-text-editor.elementor-drop-cap-view-framed .elementor-drop-cap{color:#69727d;border:3px solid;background-color:transparent}.elementor-widget-text-editor:not(.elementor-drop-cap-view-default) .elementor-drop-cap{margin-top:8px}.elementor-widget-text-editor:not(.elementor-drop-cap-view-default) .elementor-drop-cap-letter{width:1em;height:1em}.elementor-widget-text-editor .elementor-drop-cap{float:left;text-align:center;line-height:1;font-size:50px}.elementor-widget-text-editor .elementor-drop-cap-letter{display:inline-block}</style>				
					
				<?php echo $show->description; ?>

						</div>
				</div>
					</div>
		</div>
							</div>
		</section>
				 
				
		</div>
	</div>
	
	
	<!------------>
				</div>
			</div>
		</div>
	</div><!-- #content -->
	
	<div id="myModal" class="modal fade" role="dialog">
  <div class="modal-dialog">

    <!-- Modal content-->
    <div class="modal-content"style="background-color: #ffc000;">
      <div class="modalheader">
        <!--<button type="button" class="close" data-dismiss="modal">&times;</button>-->
        <center><h1 class="modal-title" style="font-family: agency_fbregular;margin-top:30px;"><b>Vi beklager</b></h1></center>
      </div>
      <div class="modalbody" align="center">
        <h3 style="font-family: agency_fbregular;margin-top: 6px;">Tilgangen gjelder kun for abonnenter, men.. <br/> dersom du registrerer deg, får du tilgang til demonstrasjonene</h3>
      </div>
      <div class="modalfooter" align="center">
        <button type="button"  class="btn btn-default close_btn" data-dismiss="modal" style="margin:0px 0px 24px 0px"></button>
      </div>
    </div>

  </div>
</div>