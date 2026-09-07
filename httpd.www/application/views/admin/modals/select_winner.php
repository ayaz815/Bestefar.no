<?php 
	$conditions['competitions_id'] = $param2; 
	$conditions['paid_status'] = 'Paid';
	$tickets = $this->Admin_model->get_data_conditions('competitions_sold',$conditions)->result_array(); 
	$competition = $this->Admin_model->get_row_id('competitions',$param2); 
	$ques = $this->Admin_model->get_row_id('questions',$competition->questions_id);
	if($ques->answer=='a'){
		$correct_ans = $ques->option_a;
	}elseif($ques->answer=='b'){
		$correct_ans = $ques->option_b;
	}elseif($ques->answer=='c'){
		$correct_ans = $ques->option_c;
	}
?>

<style> .row_div{ margin-top:40px; border-bottom: 1px solid lightgrey; } </style>
<div class="contentpanel">
	<div class="row">
		<div class="col-md-12">
			<div class="panel panel-default">
				<div class="panel-heading">
					<center><h3 class="panel-title">Choose Prize Winner</h3></center><hr/>
				</div>
				<div class="panel-body">
					<center style="color:grey"> 
						<b>Question => </b><?php echo $ques->question; ?>
						<br/>
						<b>A)</b> <?php echo $ques->option_a; ?>
						<b>B)</b> <?php echo $ques->option_b; ?>
						<b>C)</b> <?php echo $ques->option_c; ?>
						<br/>
						<font color="green"><b>Correct Answer => </b> <?= $correct_ans; ?></font>
					</center>
					<div class="dt-responsive table-responsive">
						<a href="<?php echo base_url(); ?>admin/export_data/<?php echo $param2; ?>" class="btn btn-sm btn-success">Export Buyers List</a>
						<table id="simpletable"
							   class="table table-striped table-bordered nowrap">
							<thead>
							<tr>
								<th>#</th>
								<th>Buyer Info</th>
								<th>Answer</th>
								<th>Price * Quantity</th>
								<!--<th>Buying Date</th>-->
								<th class="print_hide">Select Winner</th>
							</tr>
							</thead>
							<tbody>
								<?php 
									$i=0; foreach($tickets as $ticket):  $i++;
										$buyer = $this->Admin_model->get_row_id('users',$ticket['users_id']);
										$color = 'red';
										$color_q = 'red';
										$ans_mark = '<i class="fa fa-close" ></i>';
										if($ticket['paid_status']=='Paid'){
											$color = 'green';
										}
										if($ticket['answer']==$ques->answer){
											$color_q = 'green';
											$ans_mark = '<i class="fa fa-check"></i>';
										}
										if($ticket['answer']=='a'){
											$ans_option = $ques->option_a;
										}elseif($ticket['answer']=='b'){
											$ans_option = $ques->option_b;
										}elseif($ticket['answer']=='c'){
											$ans_option = $ques->option_c;
										}
									
								?>
								<tr>
									<td><?php echo $i; ?></td>
									<td>
										<?php echo $buyer->firstname; ?> <?php echo $buyer->lastname; ?>
										<br/>
										<?php echo $buyer->email; ?>
										<br/>
										<?php echo $buyer->telephone; ?>
									</td>
									<td>
										<?php echo '<font style="color:'.$color_q.'"> '.$ans_mark.' '.$ans_option.'</font> '; ?>
									</td>
									<td>
										<b>&pound;</b><?php echo $ticket['ticket_price']; ?>
										<i class="fa fa-close"></i>
										<?php echo $ticket['quantity']; ?>
										<b>&equals; &pound;</b><?php echo $ticket['total_price']; ?>
										<br/>
										<?php echo '<b style="color:'.$color.'">'.$ticket['paid_status'].'</b>'; ?>
									</td>
									<!--<td><?php echo date('d-M-Y', strtotime($ticket['date'])); ?></td>-->
									<td>
										<button type="button" class="btn btn-warning btn-sm" onclick="showAjaxModalInherit('<?php echo base_url() ?>modal/popup/winner_form/<?php echo $ticket['competitions_sold_id']; ?>');">
											<i class="fa fa-gift"></i> 
										</button>
									</td>
									
								</tr>
								<?php endforeach; ?>
							</tbody>
							
							</tfoot>
						</table>
					</div>
				</div>
			</div>
		</div>
	</div>      
</div>


<?php 
	foreach($tickets as $ticket):
	$buyer = $this->Admin_model->get_row_id('users',$ticket['users_id']);
?>
	<div id="winner_<?php echo $ticket['competitions_sold_id']; ?>" class="modal fade" role="dialog">
		<div class="modal-dialog">
			<div class="login-card card-block login-card-modal">
				<form class="md-float-material" method="POST" action="<?php echo base_url(); ?>admin/competitions/update"  enctype="multipart/form-data">
					<div class="text-center">
						<!--<img src="<?php echo base_url(); ?>uploads/admin/system_image.jpg" alt="logo.png">-->
					</div>
					<div class="card m-t-15">
						<div class="auth-box card-block">
						<div class="row m-b-20">
							<div class="col-md-12">
								<h3 class="text-center txt-primary">PRIZE WINNER</h3>
							</div>
						</div>
						<hr/>
						<div class="row">
							<div class="col-md-12">
								<div class="row">
									<div class="col-md-12">
										<div class="input-group">
											<span class="input-group-addon">Prize</span>
											<input type="text" name="question" value="<?php echo $competition->prize_tittle; ?>" class="form-control" placeholder="Enter Question" required>
										</div>
									</div>
									<div class="col-md-12">
										<div class="input-group">
											<span class="input-group-addon">Name</span>
											<input type="text" name="question" value="<?php echo $buyer->firstname; ?>" class="form-control" placeholder="Enter Question" required>
										</div>
									</div>
								</div>
								
								
						
						<div class="row m-t-15">
							<div class="col-md-12">
								<input type="hidden" name="competitions_id" value="<?php echo $ticket['competitions_id']; ?>">
								<input type="hidden" name="users_id" value="<?php echo $ticket['users_id']; ?>">
								<input type="hidden" name="competitions_sold_id" value="<?php echo $ticket['competitions_sold_id']; ?>">
								<button type="submit" class="btn btn-primary btn-md btn-block waves-effect text-center">Update</button>
							</div>
						</div>
						<hr/>
						
					</div>
					</div>
				</form>
			</div>
		</div>
	</div>
	<!-- Register modal end-->
<?php endforeach; ?>