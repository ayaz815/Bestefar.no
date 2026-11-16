<?php
// "Orders" section of the customer details window (order form, Module 7).
// Read-only: what the customer asked for, so the admin can set "Customer access".
// Expects: $orders (orders rows, newest first).
$e = function ($value) { return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8'); };
?>
<div class="customer-orders" style="margin-top:25px;padding-top:10px;border-top:2px solid #9cc2e6">
	<h5><b>Orders</b></h5>
	<?php if (empty($orders)) { ?>
		<p class="text-muted">None.</p>
	<?php } ?>
	<?php foreach ($orders as $order) {
		$shows = $order['shows'] ? array_column(json_decode($order['shows'], true), 'name') : array();
	?>
		<div class="row row_div">
			<div class="col-md-4"><b><?= $e(date('d.m.Y H:i', strtotime($order['created_at']))) ?></b></div>
			<div class="col-md-8">
				<?= $e(order_plan_text($order['plan'], isset($order['price']) ? $order['price'] : null)) ?>
				<?php if ($shows) { ?><br><?= $e(implode(', ', $shows)) ?><?php } ?>
				<?php if ($order['start_date']) { ?><br>Start: <?= $e(date('d.m.Y', strtotime($order['start_date']))) ?><?php } ?>
				<?php if ($order['comment'] !== null && $order['comment'] !== '') { ?><br><i><?= nl2br($e($order['comment'])) ?></i><?php } ?>
			</div>
		</div>
	<?php } ?>
</div>
