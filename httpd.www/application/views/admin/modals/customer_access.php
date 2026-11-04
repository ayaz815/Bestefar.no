<?php
// "Customer access" section of the customer details form (access control, Module 5).
// Included inside #user_form in user_details.php and saved by Admin::users('update_access').
// Expects: $user (users row, object), $grants (user_show_grants rows),
//          $show_choices (array of array('value' => 'type:id', 'label' => ..., 'type' => ...)).
$val = function ($field) use ($user) { return isset($user->$field) ? $user->$field : null; };
$checked = function ($field) use ($val) { return $val($field) ? 'checked' : ''; };
$selected = function ($field, $value) use ($val) { return $val($field) === $value ? 'selected' : ''; };
$day = function ($field) use ($val) { return $val($field) ? date('Y-m-d', strtotime($val($field))) : ''; };
$show_names = array();
foreach ($show_choices as $choice) { $show_names[$choice['value']] = $choice['label']; }
?>
<div class="customer-access" style="margin-top:25px;padding-top:10px;border-top:2px solid #9cc2e6">
	<h5><b>Customer access</b></h5>

	<div class="row row_div">
		<div class="col-md-4"><b>Client type:</b></div>
		<div class="col-md-8">
			<select class="form-control" name="access[client_type]">
				<option value="">Not set (old customer)</option>
				<option value="test" <?= $selected('client_type', 'test') ?>>Test user (free, some shows)</option>
				<option value="full" <?= $selected('client_type', 'full') ?>>Full access (friends / developers)</option>
				<option value="paid" <?= $selected('client_type', 'paid') ?>>Paid</option>
			</select>
		</div>
	</div>

	<div class="row row_div">
		<div class="col-md-4"><b>Plan (paid):</b></div>
		<div class="col-md-8">
			<select class="form-control" name="access[plan]">
				<option value="">-</option>
				<option value="per_show" <?= $selected('plan', 'per_show') ?>>Pay per show (picked shows, 3 days)</option>
				<option value="audio_simple" <?= $selected('plan', 'audio_simple') ?>>AudioQuiz + simple quizzes (annual)</option>
				<option value="all_shows" <?= $selected('plan', 'all_shows') ?>>All shows (annual)</option>
			</select>
			<label style="margin-top:8px;font-weight:normal"><input type="checkbox" name="access[payment_registered]" value="1" <?= $checked('payment_registered') ?>> Payment registered</label>
		</div>
	</div>

	<div class="row row_div">
		<div class="col-md-4"><b>Access from / to:</b></div>
		<div class="col-md-8">
			<input type="date" class="form-control" name="access[access_start]" value="<?= $day('access_start') ?>" style="display:inline-block;width:48%">
			<input type="date" class="form-control" name="access[access_end]" value="<?= $day('access_end') ?>" style="display:inline-block;width:48%">
			<small>Annual plans. Empty end date = no end.</small>
		</div>
	</div>

	<div class="row row_div">
		<div class="col-md-4"><b>Allow to open:</b></div>
		<div class="col-md-8">
			<label style="font-weight:normal;margin-right:12px"><input type="checkbox" name="access[allow_audio]" value="1" <?= $checked('allow_audio') ?>> AudioQuiz</label>
			<label style="font-weight:normal;margin-right:12px"><input type="checkbox" name="access[allow_image]" value="1" <?= $checked('allow_image') ?>> BildeQuiz</label>
			<label style="font-weight:normal;margin-right:12px"><input type="checkbox" name="access[allow_imagemusic]" value="1" <?= $checked('allow_imagemusic') ?>> BilderMusikk</label>
			<label style="font-weight:normal"><input type="checkbox" name="access[allow_demo]" value="1" <?= $checked('allow_demo') ?>> Demo shows</label>
		</div>
	</div>

	<div class="row row_div">
		<div class="col-md-4"><b>Picked shows:</b></div>
		<div class="col-md-8">
			<?php if (empty($grants)) { ?>
				<p class="text-muted">None.</p>
			<?php } ?>
			<?php foreach ($grants as $g) {
				$label = isset($show_names[$g['quiz_type'] . ':' . $g['quiz_id']]) ? $show_names[$g['quiz_type'] . ':' . $g['quiz_id']] : $g['quiz_type'] . ' ' . $g['quiz_id'];
				if (!empty($g['expires_at'])) {
					$when = (strtotime($g['expires_at']) < time() ? 'ended ' : 'until ') . date('d.m.Y H:i', strtotime($g['expires_at']));
				} else {
					$when = $g['days_valid'] ? $g['days_valid'] . ' days from first open (not opened yet)' : 'no time limit';
				}
			?>
				<div>
					<label style="font-weight:normal"><input type="checkbox" name="grant_remove[]" value="<?= (int) $g['grant_id'] ?>"> remove</label>
					<b><?= htmlspecialchars($label) ?></b> - <?= htmlspecialchars($when) ?>
				</div>
			<?php } ?>
			<div style="margin-top:8px">
				<select class="form-control" name="grant_add_show">
					<option value="">Add a show...</option>
					<?php foreach ($show_choices as $choice) { ?>
						<option value="<?= htmlspecialchars($choice['value']) ?>"><?= htmlspecialchars($choice['label']) ?></option>
					<?php } ?>
				</select>
				<input type="number" min="1" max="366" class="form-control" name="grant_add_days" placeholder="Days from first open (3 for birthday, empty = no limit)" style="margin-top:6px">
			</div>
		</div>
	</div>
</div>
