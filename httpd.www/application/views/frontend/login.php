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
	
	@media only screen and (max-width: 900px) {
		.mg_btm{
			margin-bottom:5px;
		}
	}
	
	.btn-primary{
		    background-color: #337ab7 !important;
		    padding: 5px 10px !important;
	}
	.login_btn{
		background: url(<?php echo base_url(); ?>assets/frontend/images/apne.png) no-repeat !important;
		cursor: pointer  !important;
		border: none !important;
		background-size: 100%  !important;
		width: 130px  !important;
	}
	@media(max-width: 1025px){
	.elementor-16{
		background-color:#FFC000;
	}
	}
	
	
</style>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
<div id="content" class="site-content">
		<div id="content" class="elementor elementor-16 elementor-14">
			<div id="content" class="elementor-section elementor-top-section elementor-element elementor-element-60622e5 elementor-section-boxed elementor-section-height-default elementor-section-height-default">
				<div class="elementor-container elementor-column-gap-default">
				 <!------------>


	<div class="elementor-column elementor-col-100 elementor-top-column elementor-element elementor-element-c9a3d65" data-id="c9a3d65" data-element_type="column">
		<div class="elementor-widget-wrap elementor-element-populated">
							
			<section class="elementor-section elementor-inner-section elementor-element elementor-element-a473ad5 elementor-section-full_width elementor-section-height-default elementor-section-height-default login_div" data-id="a473ad5" data-element_type="section" style="background-color:#FFC000;margin-left: -1px;">
				<div class="elementor-container--- elementor-column-gap-default">
					<div class="elementor-column--- elementor-col-100 elementor-inner-column elementor-element elementor-element-bcd4cfc" data-id="bcd4cfc" data-element_type="column">
						<div class="elementor-widget-wrap---">
						<!------------>
							<div class="container-"  style="padding:20px 25px 25px 25px;">
								<div class="row">
									<div class="col-md-12">
										
<div class="wrapper" style="">
<form method="POST" id="register_form" action="<?php echo base_url(); ?>/login/user_login_varify" enctype="multipart/form-data">
    <div class="row">
        <div class="col-md-12">
            <h3 style="font-family:arial;margin-top:0px"><b>Logg Inn</b></h3>
            <?php if(isset($login_failed) && $login_failed=='failed'){ ?>
            <span style="color:red;font-weight:600;">Ugyldig brukernavn og passord.</span>
            <?php } ?>
        </div>
    </div>

    <div class="row mg_btm">
        <div class="col-md-12">
            <input type="text" class="form-control" placeholder="Epost" name="email" required>
        </div>
    </div>

    <div class="row mg_btm">
        <div class="col-md-12">
            <div class="password-container">
                <input type="password" class="form-control" id="password-field" name="password" placeholder="Password" required>
                <i class="far fa-eye" id="toggle-password" style="cursor: pointer; position: absolute; right: 29px; top: 50%; transform: translateY(-50%);"></i>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-12" align="center">
            <input type="submit" class="login_btn" name="login" value=" " style="">
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
    const passwordField = document.getElementById('password-field');
    const togglePassword = document.getElementById('toggle-password');

    togglePassword.addEventListener('click', function (e) {
        // Toggle the type attribute of the password field
        const type = passwordField.getAttribute('type') === 'password' ? 'text' : 'password';
        passwordField.setAttribute('type', type);
        
        // Toggle the eye / eye-slash icon
        this.classList.toggle('fa-eye-slash');
    });
	</script>