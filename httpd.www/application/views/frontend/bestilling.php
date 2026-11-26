<?php
// Order form "Bestilling" (Module 7, client sketch 8 Nov 2026). Same look as register.php.
// Expects: $customer (users row), $show_choices ('type:id' => name), $errors, $posted, $sent.
$e = function ($value) { return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8'); };
$posted_plan = isset($posted['plan']) ? $posted['plan'] : '';
$posted_shows = isset($posted['shows']) && is_array($posted['shows']) ? array_values($posted['shows']) : array();
$show_slots = order_plans()['per_show']['shows'];
?>
<style>
	.order-form .form-control{
		border: 1px solid #ffc000fc !important;
		border-radius: 2px !important;
		background: #f9fafb !important;
		box-shadow: none !important;
		box-sizing: border-box !important;
	}
	.order-form .form-control[readonly]{ color: #000; cursor: default; }
	.order-form h3{ margin: 10px 0 15px; }
	.order-form .mg_btm{ margin-bottom: 15px; }
	.order-form .lh{ padding-top: 5px; }
	.order-form .order-plan{ display: block; font-weight: normal; margin-bottom: 8px; cursor: pointer; }
	.order-form .order-plan input{ transform: scale(1.3); margin: 0 10px 0 3px; }
	.order-form .order-shows select{ margin-bottom: 8px; }
	/* iPhone: a date field ignores width:100% and ran past the right edge (client 26 Nov 2026) */
	.order-form input[type="date"]{ -webkit-appearance: none; appearance: none; display: block; width: 100%; min-width: 0; max-width: 100%; }
	.order-form input[type="date"]::-webkit-date-and-time-value{ text-align: left; }
	.order-form .order-errors{ background: #fff; border-left: 4px solid #c00; padding: 8px 12px; margin-bottom: 15px; }
	.order-form .order-btn{
		border-radius: 100px !important;
		padding: 4px 40px !important;
		border: 3px solid #d2e5f5 !important;
		background-color: #95bfe3 !important;
		color: #000 !important;
		font-weight: 700 !important;
		font-size: 18px !important;
		outline: 3px solid #95bfe3 !important;
		cursor: pointer;
	}
	@media only screen and (max-width: 900px){
		.order-form .mg_btm{ margin-bottom: 5px; }
		.order-form .lh{ padding-top: 10px; }
		.order-form .half{ margin-bottom: 5px; }
	}
	@media(max-width: 1025px){
		.elementor-16{ background-color: #FFC000; }
	}
</style>
<div id="content" class="site-content">
	<div class="elementor elementor-16 elementor-14">
		<div class="elementor-section elementor-top-section elementor-element elementor-element-60622e5 elementor-section-boxed elementor-section-height-default">
			<div class="elementor-container elementor-column-gap-default">
				<div class="elementor-column elementor-col-100 elementor-top-column elementor-element elementor-element-c9a3d65">
					<div class="elementor-widget-wrap elementor-element-populated">
						<section class="elementor-section elementor-inner-section elementor-element elementor-element-a473ad5 elementor-section-full_width" style="background-color:#FFC000;margin-left:-1px;">
							<div class="order-form" style="padding:0 10px 20px;">
							<?php if ($sent) { ?>
								<div align="center" style="padding:20px 10px;">
									<h1 style="font-family:agency_fbregular">Takk for bestillingen din.</h1>
									<h2 style="font-family:agency_fbregular;margin-top:-6px;">Vi tar kontakt med deg om betaling og tilgang.</h2>
									<p>En kopi sendes til <?= $e($customer['email']) ?>.</p>
								</div>
							<?php } else { ?>
								<form method="POST" id="order_form" action="<?= base_url('home/bestilling') ?>">
									<h3><b>Bestilling</b></h3>
									<?php if ($errors) { ?>
										<div class="order-errors" role="alert"><?php foreach ($errors as $error) { ?><div><?= $e($error) ?></div><?php } ?></div>
									<?php } ?>

									<div class="row mg_btm">
										<div class="col-md-3 lh">Institusjon/forening:</div>
										<div class="col-md-9"><input type="text" class="form-control" value="<?= $e($customer['institute']) ?>" readonly></div>
									</div>
									<div class="row mg_btm">
										<div class="col-md-3 lh">Kontaktperson:</div>
										<div class="col-md-4 col-sm-6 col-xs-6 half" style="padding-right:5px;"><input type="text" class="form-control" value="<?= $e($customer['fname']) ?>" placeholder="Fornavn" readonly></div>
										<div class="col-md-5 col-sm-6 col-xs-6 half" style="padding-left:5px;"><input type="text" class="form-control" value="<?= $e($customer['lname']) ?>" placeholder="Etternavn" readonly></div>
									</div>
									<div class="row mg_btm">
										<div class="col-md-3 lh">Telefon(er):</div>
										<div class="col-md-4 col-sm-6 col-xs-6 half" style="padding-right:5px;"><input type="text" class="form-control" value="<?= $e($customer['phone']) ?>" placeholder="Institusjon/Forening" readonly></div>
										<div class="col-md-5 col-sm-6 col-xs-6 half" style="padding-left:5px;"><input type="text" class="form-control" value="<?= $e($customer['mobile']) ?>" placeholder="Privat" readonly></div>
									</div>
									<div class="row mg_btm">
										<div class="col-md-3 lh">Epostadresse:</div>
										<div class="col-md-9"><input type="email" class="form-control" value="<?= $e($customer['email']) ?>" readonly></div>
									</div>

									<h3><b>Detaljer</b></h3>
									<div class="row mg_btm">
										<div class="col-md-3 lh">Jeg vil bestille:</div>
										<div class="col-md-9">
											<?php foreach (order_plans() as $key => $plan) { ?>
												<label class="order-plan"><input type="radio" name="plan" value="<?= $e($key) ?>" <?= $posted_plan === $key ? 'checked' : '' ?> required> <?= $e(order_plan_text($key)) ?></label>
											<?php } ?>
										</div>
									</div>
									<div class="row mg_btm order-shows" id="order_shows">
										<div class="col-md-3 lh">Quizer:</div>
										<div class="col-md-9">
											<?php for ($i = 0; $i < $show_slots; $i++) { ?>
												<select class="form-control" name="shows[]" aria-label="Quiz <?= $i + 1 ?>">
													<option value="">Velg quiz <?= $i + 1 ?></option>
													<?php foreach (order_show_groups($show_choices) as $group => $names) { ?>
														<optgroup label="<?= $e($group) ?>">
														<?php foreach ($names as $value => $name) { ?>
															<option value="<?= $e($value) ?>" <?= isset($posted_shows[$i]) && $posted_shows[$i] === $value ? 'selected' : '' ?>><?= $e($name) ?></option>
														<?php } ?>
														</optgroup>
													<?php } ?>
												</select>
											<?php } ?>
										</div>
									</div>
									<div class="row mg_btm">
										<div class="col-md-3 lh">Ønsket startdato:</div>
										<div class="col-md-9"><input type="date" class="form-control" name="start_date" min="<?= date('Y-m-d') ?>" value="<?= $e(isset($posted['start_date']) ? $posted['start_date'] : '') ?>"></div>
									</div>
									<div class="row mg_btm">
										<div class="col-md-3 lh">Kommentar:</div>
										<div class="col-md-9"><textarea class="form-control" name="comment" rows="3" maxlength="1000" placeholder="Valgfritt"><?= $e(isset($posted['comment']) ? $posted['comment'] : '') ?></textarea></div>
									</div>
									<div class="row mg_btm">
										<div class="col-md-3"></div>
										<div class="col-md-9"><button type="submit" class="order-btn">Bestill</button></div>
									</div>
								</form>
								<script>
								// The quiz pickers only belong to the 2-quiz plan, and a quiz picked in one
								// can't be picked in the other (the server checks both again).
								(function () {
									var form = document.getElementById('order_form');
									var shows = document.getElementById('order_shows');
									var pickers = shows.querySelectorAll('select');
									function noTwice() {
										var taken = [];
										pickers.forEach(function (s) { // a later picker can't keep a quiz an earlier one has
											if (s.value && taken.indexOf(s.value) !== -1) s.value = '';
											if (s.value) taken.push(s.value);
										});
										pickers.forEach(function (s) { // the other pickers' quizzes are greyed out
											Array.prototype.forEach.call(s.options, function (o) {
												o.disabled = o.value !== '' && o.value !== s.value && taken.indexOf(o.value) !== -1;
											});
										});
									}
									function update() {
										var picked = form.querySelector('input[name="plan"]:checked');
										var on = !!picked && picked.value === 'per_show';
										shows.style.display = on ? '' : 'none';
										pickers.forEach(function (s) { s.disabled = !on; s.required = on; });
										noTwice();
									}
									form.addEventListener('change', update);
									update();
								})();
								</script>
							<?php } ?>
							</div>
						</section>
					</div>
				</div>
			</div>
		</div>
	</div>
</div><!-- #content -->
