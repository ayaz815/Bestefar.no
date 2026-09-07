<style>
	.form-control{
		border-width: 1px !important;
		border-style: solid !important;
		border-color: #ffc000fc  !important;
		border-radius: 2px !important;
		background: #f9fafb !important;
		box-shadow: none !important;
		box-sizing: border-box !important;
		transition: all .2s linear !important;
	}
	.form_c{
		text-transform: capitalize;
	}
	.mg_btm{
		margin-bottom:15px;
	}
	.mg_btm_p{
		margin-bottom:15px;
	}
	@media only screen and (max-width: 900px) {
		.mg_btm{
			margin-bottom:5px;
		}
	}
	
	@media only screen and (max-width: 900px) {
		.mg_btm_p{
		    margin-top: 15px;  
			margin-bottom:2px;
		}
	}
	
	.btn-primary{
		    background-color: #337ab7 !important;
		    padding: 5px 10px !important;
	}
	.register_btn{
		float: left !important;
		background: url(<?php echo base_url(); ?>assets/frontend/images/send-btn.png) no-repeat !important;
		cursor: pointer  !important;
		border: none !important;
		background-size: 100%  !important;
		width: 170px  !important;
		margin-left: -13px !important;
	}
	.register_btn2{
		float: right !important;
		border-radius: 100px !important;
		padding: 0px 40px !important;
		border: 3px solid #d2e5f5 !important;
		background-color: #95bfe3 !important;
		color: #1212ff !important;
		font-weight: 700 !important;
		font-size: 18px !important;
		font-family: sans-serif !important;
		outline: 3px solid #95bfe3 !important;
	}
	@media(max-width: 1025px){
	.elementor-16{
		background-color:#FFC000;
	}
	}
	@media(min-width: 991px){
	.lh{
		padding-top: 5px;;
	}.width_50{
		width: 37.5%;;
	}
	}
</style>
<div id="content" class="site-content">
		<div id="content" class="elementor elementor-16 elementor-14">
			<div id="content" class="elementor-section elementor-top-section elementor-element elementor-element-60622e5 elementor-section-boxed elementor-section-height-default elementor-section-height-default">
				<div class="elementor-container elementor-column-gap-default">
				 <!------------>


	<div class="elementor-column elementor-col-100 elementor-top-column elementor-element elementor-element-c9a3d65" data-id="c9a3d65" data-element_type="column">
		<div class="elementor-widget-wrap elementor-element-populated">
								
			<section class="elementor-section elementor-inner-section elementor-element elementor-element-a473ad5 elementor-section-full_width elementor-section-height-default elementor-section-height-default" data-id="a473ad5" data-element_type="section" style="background-color:#FFC000;margin-left: -1px;">
				<div class="elementor-container--- elementor-column-gap-default">
					<div class="elementor-column--- elementor-col-100 elementor-inner-column elementor-element elementor-element-bcd4cfc" data-id="bcd4cfc" data-element_type="column">
						<div class="elementor-widget-wrap---">
						<!------------>
							<div class="container-"  style="padding:0px 10px;">
								<div class="row">
									<div class="col-md-12">
										
<div class="wrapper" style="">
	<form method="POST" id="register_form" action="<?php echo base_url(); ?>/login/register/create"  enctype="multipart/form-data">
	  <div class="row">
		<div class="col-md-12">
		  <h3  style="font-family: "><b>Registrering</b></h3>
		</div>
		
	  </div>
	  <div class="row mg_btm">
		<div class="col-md-3 lh">
		  Institusjon/forening:
		</div>
		<div class="col-md-9">
		 <input type="text" class="form-control form_c" name="institute" required>
		</div>
	  </div>
	  
	  <div class="row mg_btm">
		<div class="col-md-3 lh">
		  Gateadresse:
		</div>
		<div class="col-md-9">
		 <input type="text" class="form-control form_c" name="address" required> 
		</div>
	  </div>
	  
	  <div class="row mg_btm">
		<div class="col-md-3 lh">
		  Poststed:
		</div>
		<div class="col-lg-3 col-md-3 col-sm-4 col-xs-4" style="padding-right: 10px !important;">
		 <input type="text" placeholder="Nr" class="form-control form_c" name="postal1" required>
		</div>
		<div class="col-lg-6 col-md-6 col-sm-8  col-xs-8" style="padding-left: 0px !important;">
		 <input type="text" placeholder="Sted" class="form-control form_c" name="postal2" required>
		</div>
	  </div>
	  
	  <div class="row mg_btm">
		<div class="col-md-3 lh">
		  Web eller Facebook:
		</div>
		<div class="col-md-9">
		 <input type="text" class="form-control form_c" name="web" placeholder="Velg enten eller. Bruk URL-adresse." required> 
		</div>
	  </div>
	  
	  <div class="row mg_btm_p">
		<div class="col-md-3 col-sm-4 col-xs-4 col-lg-3">
		  Vi har projektor: 
		</div>
		<div class="col-md-9 col-sm-8 col-xs-8 col-lg-9">
		<label for="c1"> Ja</label> 
		  <input name="projector" value="Yes" type="radio" id="c1" style="-ms-transform: scale(1.5); transform: scale(1.3);margin: 0px 10px 0px 3px;"> 
		
		<label for="c2"> Nei</label> 
		<input  name="projector" value="No" type="radio" id="c2" style="-ms-transform: scale(1.5); transform: scale(1.3);margin: 0px 10px 0px 3px;">
		</div>
	  </div>
	  <!--
	  <div class="row">
		<div class="col-md-12">
		  <b>Kontaktperson:</b>
		</div>
	  </div>
	  <br/>
	  -->
	  <div class="row mg_btm">
		<div class="col-md-3 lh">
		  Kontaktperson:
		</div>
		<div class="col-lg-4 col-md-4 col-sm-4 col-xs-4" style="padding-right: 10px !important;">
		 <input type="text" placeholder="Fornavn" class="form-control form_c" name="fname" required>
		</div>
		<div class="col-lg-5 col-md-5 col-sm-8  col-xs-8" style="padding-left: 0px !important;">
		 <input type="text" placeholder="Etternavn" class="form-control form_c" name="lname" required>
		</div>
	  </div>
	  
	  <div class="row mg_btm">
		<div class="col-md-3 lh">
		  Telefon(er):
		</div>
		<div class="col-lg-4 col-md-4 col-sm-6 col-xs-6 width_50" style="padding-right: 10px !important;">
		 <input type="text" placeholder="Institusjon/forening" class="form-control form_c" name="phone" required>
		</div>
		<div class="col-lg-5 col-md-5 col-sm-6  col-xs-6 width_50" style="padding-left: 0px !important;">
		 <input type="text" placeholder="Privat" class="form-control form_c" name="mobile" required>
		</div>
	  </div>
	  
	  
	  <!--
	  <div class="row">
		<div class="col-md-3 lh">
		  Stilling/funksjon:
		</div>
		<div class="col-md-9">
		 <input type="text" class="form-control" name="position" required> 
		</div>
	  </div>
	  <br/>
	  -->
	  
	  <div class="row mg_btm">
		<div class="col-md-3 lh">
		 Epostadresse:
		</div>
		<div class="col-md-9">
		 <input type="email" class="form-control" name="email" required> 
		</div>
	  </div>
	  
	  <div class="row mg_btm">
		<div class="col-md-3 lh">
		Velg et passord
		</div>
		<div class="col-md-9">
		 <input type="password" class="form-control" name="password"  minlength="8" placeholder="Bruk minst 8 karakterer" required> 
		</div>
	  </div>
	  
	  <div class="row mg_btm">
		<div class="col-md-3 lh">
		
		</div>
		<div class="col-md-9">
		 <input type="submit" class="register_btn" name="register" value=" " style="padding: 15px 30px;" > 
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
		</section>
				
		</div>
	</div>
	
	
	<!------------>
				</div>
			</div>
		</div>
	</div><!-- #content -->
	<script>
	function register_user(){
		$('#register_form').submit();
	}
	</script>