 <script data-cfasync="false" src="../../../cdn-cgi/scripts/5c5dd728/cloudflare-static/email-decode.min.js"></script><script type="text/javascript" src="<?php echo base_url(); ?>assets/admin/bower_components/jquery/js/jquery.min.js"></script>
    <script type="text/javascript" src="<?php echo base_url(); ?>assets/admin/bower_components/jquery-ui/js/jquery-ui.min.js"></script>
    <script type="text/javascript" src="<?php echo base_url(); ?>assets/admin/bower_components/popper.js/js/popper.min.js"></script>
    <script type="text/javascript" src="<?php echo base_url(); ?>assets/admin/bower_components/bootstrap/js/bootstrap.min.js"></script>
    <!-- jquery slimscroll js -->
    <script type="text/javascript" src="<?php echo base_url(); ?>assets/admin/bower_components/jquery-slimscroll/js/jquery.slimscroll.js"></script>
    <!-- modernizr js -->
    <script type="text/javascript" src="<?php echo base_url(); ?>assets/admin/bower_components/modernizr/js/modernizr.js"></script>
	<!-- data-table js -->
	<script src="<?php echo base_url(); ?>assets/admin/bower_components/datatables.net/js/jquery.dataTables.min.js"></script>
	<script src="<?php echo base_url(); ?>assets/admin/bower_components/datatables.net-buttons/js/dataTables.buttons.min.js"></script>
	<script src="<?php echo base_url(); ?>assets/admin/assets/pages/data-table/js/jszip.min.js"></script>
	<script src="<?php echo base_url(); ?>assets/admin/assets/pages/data-table/js/pdfmake.min.js"></script>
	<script src="<?php echo base_url(); ?>assets/admin/assets/pages/data-table/js/vfs_fonts.js"></script>
	<script src="<?php echo base_url(); ?>assets/admin/bower_components/datatables.net-buttons/js/buttons.print.min.js"></script>
	<script src="<?php echo base_url(); ?>assets/admin/bower_components/datatables.net-buttons/js/buttons.html5.min.js"></script>
	<script src="<?php echo base_url(); ?>assets/admin/bower_components/datatables.net-bs4/js/dataTables.bootstrap4.min.js"></script>
	<script src="<?php echo base_url(); ?>assets/admin/bower_components/datatables.net-responsive/js/dataTables.responsive.min.js"></script>
	<script src="<?php echo base_url(); ?>assets/admin/bower_components/datatables.net-responsive-bs4/js/responsive.bootstrap4.min.js"></script>
	<script src="<?php echo base_url(); ?>assets/admin/assets/pages/data-table/js/data-table-custom.js"></script>
	
	 <!-- notification js -->
    <script type="text/javascript" src="<?php echo base_url(); ?>assets/admin/assets/js/bootstrap-growl.min.js"></script>
    <script type="text/javascript" src="<?php echo base_url(); ?>assets/admin/assets/pages/notification/notification.js"></script>
	
    <!-- Chart js -->
    <script type="text/javascript" src="<?php echo base_url(); ?>assets/admin/bower_components/chart.js/js/Chart.js"></script>
    <!-- amchart js -->
    <script src="<?php echo base_url(); ?>assets/admin/assets/pages/widget/amchart/amcharts.js"></script>
    <script src="<?php echo base_url(); ?>assets/admin/assets/pages/widget/amchart/serial.js"></script>
    <script src="<?php echo base_url(); ?>assets/admin/assets/pages/widget/amchart/light.js"></script>
    <script src="<?php echo base_url(); ?>assets/admin/assets/js/jquery.mCustomScrollbar.concat.min.js"></script>
    <script type="text/javascript" src="<?php echo base_url(); ?>assets/admin/assets/js/SmoothScroll.js"></script>
    <script src="<?php echo base_url(); ?>assets/admin/assets/js/pcoded.min.js"></script>
    <!-- custom js -->
    <script src="<?php echo base_url(); ?>assets/admin/assets/js/vartical-layout.min.js"></script>
    <script type="text/javascript" src="<?php echo base_url(); ?>assets/admin/assets/pages/dashboard/custom-dashboard.js"></script>
    <script type="text/javascript" src="<?php echo base_url(); ?>assets/admin/assets/js/script.min.js"></script>
<!-- Global site tag (gtag.js) - Google Analytics -->
<script async src="https://www.googletagmanager.com/gtag/js?id=UA-23581568-13"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
  window.dataLayer = window.dataLayer || [];
  function gtag(){dataLayer.push(arguments);}
  gtag('js', new Date());

  gtag('config', 'UA-23581568-13');
</script>


<!--- Toaster ----->
<script>
	<?php if($this->session->flashdata('msg_success')){ ?>
		notify('fa fa-comments', 'success', 'Title ', '<?php echo $this->session->flashdata("msg_success")?>');
	<?php } else if($this->session->flashdata('msg_error')){ ?>
		notify('fa fa-comments', 'danger', 'Title ', '<?php echo $this->session->flashdata("msg_error")?>');
	<?php } else if($this->session->flashdata('msg_warning')){ ?>
		notify('fa fa-comments', 'warning', 'Title ', '<?php echo $this->session->flashdata("msg_warning")?>');
	<?php } else if($this->session->flashdata('msg_info')){ ?>
		notify('fa fa-comments', 'info', 'Title ', '<?php echo $this->session->flashdata("msg_info")?>');
	<?php } ?>
    
 </script>
<script language="javascript" type="text/javascript">
	function printFun(divID) {
		$('.print_show').show();
		$('.print_hide').hide();
		//Get the HTML of div
		var divElements = document.getElementById(divID).innerHTML;
		//Get the HTML of whole page
		var oldPage = document.body.innerHTML;

		//Reset the page's HTML with div's HTML only
		document.body.innerHTML = 
		  "<html><head><title></title></head><body>" + 
		  divElements + "</body>";

		//Print Page
		window.print();

		//Restore orignal HTML
		document.body.innerHTML = oldPage;
		$('.print_hide').show();
		$('.print_show').hide();
		location.reload(); 
	}
	
	function print_table_Fun(divID) {
		$('.print_show').show();
		$('.print_hide').hide();
		$('.dataTables_filter').hide();
		$('.dataTables_length').hide();
		$('.pagination').hide();
		$('.dataTables_info').hide();
		//Get the HTML of div
		var divElements = document.getElementById(divID).innerHTML;
		//Get the HTML of whole page
		var oldPage = document.body.innerHTML;
		//Reset the page's HTML with div's HTML only
		document.body.innerHTML = 
		  "<html><head><title></title></head><body>" + 
		  divElements + "</body>";
		//Print Page
		window.print();
		document.body.innerHTML = oldPage;
		$('.print_show').hide();
		$('.print_hide').show();
		$('.dataTables_length').show();
		$('.dataTables_filter').show();
		$('.pagination').show();
		$('.dataTables_info').show();
		location.reload(); 
	}
	
	//no input integer
	function testInput(event) {
	   var value = String.fromCharCode(event.which);
	   var pattern = new RegExp(/[a-zåäö ]/i);
	   return pattern.test(value);
	}
	$('.no_intiger').bind('keypress', testInput);
	
		$(document).ready(function() {
    $('.js-example-basic-single').select2();
});
</script>


