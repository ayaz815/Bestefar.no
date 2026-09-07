<?php 
	$ticket = $this->Admin_model->get_row_id('competitions_sold',$param2); 
	$competition = $this->Admin_model->get_row_id('competitions',$ticket->competitions_id); 
	$buyer = $this->Admin_model->get_row_id('users',$ticket->users_id);
?>

<style> .row_div{ margin-top:40px; border-bottom: 1px solid lightgrey; } </style>
<div class="contentpanel">
	<div class="row">
		<div class="col-md-12">
			<div class="panel panel-default">
				<div class="panel-heading">
					<center><h3 class="panel-title">SAVE PRIZE WINNER</h3></center><hr/>
				</div>
				<div class="panel-body">
				<form class="md-float-material" method="POST" action="<?php echo base_url(); ?>admin/winners/save_winner"  enctype="multipart/form-data">
					<div class="row">
						<div class="col-md-12">
							<div class="input-group">
								<span class="input-group-addon">Prize</span>
								<input type="text" name="question" value="<?php echo $competition->prize_tittle; ?>" class="form-control" placeholder="Enter Question" required readonly>
							</div>
						</div>
						<div class="col-md-12">
							<div class="input-group">
								<span class="input-group-addon">Name</span>
								<input type="text" name="question" value="<?php echo $buyer->firstname; ?> <?php echo $buyer->lastname; ?>" class="form-control" placeholder="Enter Question" required readonly>
							</div>
						</div>
						<div class="col-md-12">
							<div class="input-group">
								<span class="input-group-addon">Email</span>
								<input type="text" name="question" value="<?php echo $buyer->email; ?>" class="form-control" placeholder="Enter Question" required readonly>
							</div>
						</div>
						<div class="col-md-12">
							<div class="input-group">
								<span class="input-group-addon">Date</span>
								<input type="date" name="date" value="<?php echo date('Y-m-d'); ?>" class="form-control" placeholder="Enter Question" required>
							</div>
						</div>
						<div class="col-md-12">
							<div class="input-group">
								<span class="input-group-addon">Image</span>
								<input type="file" accept="imaage/*" name="prize_image"  class="form-control" placeholder="Enter Question" required>
							</div>
						</div>
					</div>
					
					
			
			<div class="row m-t-15">
				<div class="col-md-12">
					<input type="hidden" name="competitions_id" value="<?php echo $ticket->competitions_id; ?>">
					<input type="hidden" name="users_id" value="<?php echo $ticket->users_id; ?>">
					<input type="hidden" name="competitions_sold_id" value="<?php echo $ticket->competitions_sold_id; ?>">
					<button type="submit" class="btn btn-primary btn-md btn-block waves-effect text-center">Save As Winner</button>
				</div>
			</div>
				</div>
			</div>
		</div>
	</div>      
</div>

