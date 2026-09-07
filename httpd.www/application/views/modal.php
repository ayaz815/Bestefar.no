    <script type="text/javascript">
	function showAjaxModalUser(url)
	{
		// SHOWING AJAX PRELOADER IMAGE
		jQuery('#modal_ajaxUser .modal-body').html('<div style="text-align:center;margin-top:200px;"><img src="<?php echo base_url();?>assets/preloader.gif" /></div>');
		
		// LOADING THE AJAX MODAL
		jQuery('#modal_ajaxUser').modal('show', {backdrop: 'true'});
		
		// SHOW AJAX RESPONSE ON REQUEST SUCCESS
		$.ajax({
			url: url,
			success: function(response)
			{
				jQuery('#modal_ajaxUser .modal-body').html(response);
			}
		});
	}
	</script>
    
    <!-- (Ajax Modal)-->
    <div class="modal fade" id="modal_ajaxUser">
        <div class="modal-dialog" style="max-width: 90% ;">
            <div class="modal-content">
                
                <!--<div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                    <?php $system_name = $this->Admin_model->get_row_id('system_setting',1)->system_name;?>
					<h4 class="modal-title"><?php echo $system_name;?></h4>
                </div>-->
                
                <div class="modal-body" style="height:550px; overflow:auto;">
                
                    
                    
                </div>
                
                <div class="modal-footer">
					<button type="button" class="btn btn-success" onclick="submit_user_form();">Update</button>
					<button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
    
    <script type="text/javascript">
	function submit_user_form()
	{
	    
		$('#user_form').submit();
	}
	
	function showAjaxModal(url)
	{
	    alert(url);
		// SHOWING AJAX PRELOADER IMAGE
		jQuery('#modal_ajax .modal-body').html('<div style="text-align:center;margin-top:200px;"><img src="<?php echo base_url();?>assets/preloader.gif" /></div>');
		
		// LOADING THE AJAX MODAL
		jQuery('#modal_ajax').modal('show', {backdrop: 'true'});
		
		// SHOW AJAX RESPONSE ON REQUEST SUCCESS
		$.ajax({
			url: url,
			success: function(response)
			{
				jQuery('#modal_ajax .modal-body').html(response);
			}
		});
	}
	</script>
    
    <!-- (Ajax Modal)-->
    <div class="modal fade" id="modal_ajax">
        <div class="modal-dialog"  style="max-width: 90% ;">
            <div class="modal-content">
                
                <!--<div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                    <?php $system_name = $this->Admin_model->get_row_id('system_setting',1)->system_name;?>
					<h4 class="modal-title"><?php echo $system_name;?></h4>
                </div>-->
                
                <div class="modal-body" style=" overflow:auto;">
                
                    
                    
                </div>
                
                <div class="modal-footer">
					<button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
    
    
        <script type="text/javascript">
	function showAjaxModalInherit(url)
	{
	    alert(url);
		// SHOWING AJAX PRELOADER IMAGE
		jQuery('#modal_ajax_inherit .modal-body').html('<div style="text-align:center;margin-top:200px;"><img src="<?php echo base_url();?>assets/preloader.gif" /></div>');
		
		// LOADING THE AJAX MODAL
		jQuery('#modal_ajax_inherit').modal('show', {backdrop: 'true'});
		
		// SHOW AJAX RESPONSE ON REQUEST SUCCESS
		$.ajax({
			url: url,
			success: function(response)
			{
				jQuery('#modal_ajax_inherit .modal-body').html(response);
			}
		});
	}
	</script>
    
    <!-- (Ajax Modal)-->
    <div class="modal fade" id="modal_ajax_inherit">
        <div class="modal-dialog">
            <div class="modal-content">
                
                <!--<div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                    <?php $system_name = $this->Admin_model->get_row_id('system_setting',1)->system_name;?>
					<h4 class="modal-title"><?php echo $system_name;?></h4>
                </div>-->
                
                <div class="modal-body" style="height:500px; overflow:auto;">
                
                    
                    
                </div>
                
                <div class="modal-footer">
					<button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
    
    
    
    
    <script type="text/javascript">
	function confirm_modal(delete_url)
	{
		jQuery('#modal-4').modal('show', {backdrop: 'static'});
		document.getElementById('delete_link').setAttribute('href' , delete_url);
	}
	</script>
    
    <!-- (Normal Modal)-->
    <div class="modal fade" id="modal-4">
        <div class="modal-dialog">
            <div class="modal-content" style="margin-top:100px;">
                
                <div class="modal-header">
                    <h4 class="modal-title" style="text-align:center;">Are you sure to delete this information ?</h4>
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                </div>
                
                
                <div class="modal-footer" style="margin:0px; border-top:0px; text-align:center;">
                    <a href="#" class="btn btn-danger" id="delete_link"><?php echo 'Delete';?></a>
                    <button type="button" class="btn btn-info" data-dismiss="modal"><?php echo 'Cancel';?></button>
                </div>
            </div>
        </div>
    </div>
	
	  <script type="text/javascript">
	function confirm_modal_action(delete_url)
	{
		jQuery('#modal-5').modal('show', {backdrop: 'static'});
		document.getElementById('action_link').setAttribute('href' , delete_url);
	}
	</script>
    
    <!-- (Normal Modal)-->
    <div class="modal fade" id="modal-5">
        <div class="modal-dialog">
            <div class="modal-content" style="margin-top:100px;">
                
                <div class="modal-header">
                    <h4 class="modal-title" style="text-align:center;">Are you sure to take this action ?</h4>
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                </div>
                
                
                <div class="modal-footer" style="margin:0px; border-top:0px; text-align:center;">
                    <a href="#" class="btn btn-danger" id="action_link"><?php echo 'Yes';?></a>
                    <button type="button" class="btn btn-info" data-dismiss="modal"><?php echo 'No';?></button>
                </div>
            </div>
        </div>
    </div>
	
	