<div class="pcoded-content">
	<div class="pcoded-inner-content">
		<div class="main-body">
			<div class="page-wrapper">
				<div class="page-header">
					<div class="row align-items-end">
						<div class="col-lg-8">
							<div class="page-header-title">
								<div class="d-inline">
									<h4><?php echo $page_title; ?></h4>
									<span><?php echo $page_sub_title; ?></span>
								</div>
							</div>
						</div>
					</div>
				</div>

				<div class="page-body">
					<div class="row">
						<div class="card col-md-12">
							<div class="card-header">
								<h5>Quizzes from api.bestefar.no</h5>
								<hr>
							</div>
							<div class="card-block">
								<?php $i=0; foreach($quizzes as $quiz){ $i++; $form_id = 'quizinfo-'.$i; ?>
									<form id="<?php echo $form_id; ?>" method="POST" action="<?php echo base_url(); ?>admin/quiz_info">
										<input type="hidden" name="quiz_type" value="<?php echo htmlspecialchars($quiz['quiz_type']); ?>">
										<input type="hidden" name="external_id" value="<?php echo htmlspecialchars($quiz['external_id']); ?>">
									</form>
								<?php } ?>
								<div class="dt-responsive table-responsive">
									<table class="table table-striped table-bordered nowrap">
										<thead>
											<tr>
												<th>Dato</th>
												<th>Kategorier</th>
												<th>Navn</th>
												<th>Ant spørsmål</th>
												<th style="min-width:320px;">Info (shown on the (i) icon)</th>
												<th></th>
											</tr>
										</thead>
										<tbody>
											<?php if(!empty($quizzes)){ ?>
												<?php $i=0; foreach($quizzes as $quiz){ $i++; $form_id = 'quizinfo-'.$i; ?>
													<tr>
														<td><?php echo $quiz['date'] ? date('d.m.y', strtotime($quiz['date'])) : '-'; ?></td>
														<td><?php echo htmlspecialchars($quiz['quiz_type']); ?></td>
														<td><?php echo htmlspecialchars($quiz['name']); ?></td>
														<td><?php echo (int) $quiz['question_count']; ?></td>
														<td>
															<textarea name="info" form="<?php echo $form_id; ?>" rows="2" class="form-control" placeholder="Kort info om quizen ..."><?php echo htmlspecialchars($quiz['info']); ?></textarea>
														</td>
														<td>
															<button type="submit" form="<?php echo $form_id; ?>" name="save" value="1" class="btn btn-success btn-sm">Lagre</button>
														</td>
													</tr>
												<?php } ?>
											<?php }else{ ?>
												<tr><td colspan="6">Ingen spørreleker funnet.</td></tr>
											<?php } ?>
										</tbody>
									</table>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
