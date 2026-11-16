<?php
defined('BASEPATH') OR exit('No direct script access allowed');

// Order form "Bestilling" (ACCESS_CONTROL_PLAN.md, Module 7).
// Pure functions: no database, no session, so they are unit-tested directly.
// The plan keys are the paid plans the access rule already knows (access_helper.php).

if (!function_exists('order_plans')) {
	// plan key => label shown to the customer, price in kr, number of quizzes to pick.
	// Wording and prices from the client (e-mail 16 Nov 2026).
	function order_plans() {
		return array(
			'per_show'     => array('label' => '2 quizer i 3 dager',                 'price' => 150,  'shows' => 2),
			'audio_simple' => array('label' => 'Audio Quiz og Tekst Quiz i ett år', 'price' => 1800, 'shows' => 0),
			'all_shows'    => array('label' => 'Alle variantene i ett år',          'price' => 2900, 'shows' => 0),
		);
	}
}

if (!function_exists('order_plan_text')) {
	// "2 quizer i 3 dager (kr 150)". $price: the price saved with an order, so an
	// old order keeps the price the customer saw; NULL = today's price.
	function order_plan_text($plan, $price = null) {
		$plans = order_plans();
		if (!isset($plans[$plan])) {
			return (string) $plan;
		}
		$kr = $price === null || $price === '' ? $plans[$plan]['price'] : (int) $price;
		return $plans[$plan]['label'] . ' (kr ' . number_format($kr, 0, ',', '.') . ')';
	}
}

if (!function_exists('order_input')) {
	// Checks the posted form against the plans and the catalog.
	// $show_names: 'type:id' => name, the shows a customer may pick.
	// Returns array('order' => row for the orders table, or null; 'errors' => list of messages).
	function order_input($post, array $show_names, $today) {
		$plans = order_plans();
		$text = function ($key, $max) use ($post) {
			return isset($post[$key]) && is_string($post[$key]) ? mb_substr(trim($post[$key]), 0, $max) : '';
		};
		$errors = array();

		$plan = $text('plan', 20);
		if (!isset($plans[$plan])) {
			$errors[] = 'Velg hva du vil bestille.';
		}

		$shows = array();
		if (isset($plans[$plan]) && $plans[$plan]['shows'] > 0) {
			$picked = isset($post['shows']) && is_array($post['shows']) ? array_values(array_unique(array_filter($post['shows'], 'is_string'))) : array();
			foreach ($picked as $value) {
				if (isset($show_names[$value])) {
					$shows[] = array('value' => $value, 'name' => $show_names[$value]);
				}
			}
			if (count($shows) !== $plans[$plan]['shows'] || count($picked) !== count($shows)) {
				$errors[] = 'Velg ' . $plans[$plan]['shows'] . ' forskjellige quizer.';
			}
		}

		$start = $text('start_date', 10);
		if ($start !== '') {
			$date = DateTime::createFromFormat('!Y-m-d', $start);
			if (!$date || $date->format('Y-m-d') !== $start) {
				$errors[] = 'Ugyldig startdato.';
			} elseif ($start < $today) {
				$errors[] = 'Startdatoen kan ikke være før i dag.';
			}
		}

		if ($errors) {
			return array('order' => null, 'errors' => $errors);
		}
		return array('order' => array(
			'plan'       => $plan,
			'price'      => $plans[$plan]['price'],
			'shows'      => $shows ? json_encode($shows, JSON_UNESCAPED_UNICODE) : null,
			'start_date' => $start !== '' ? $start : null,
			'comment'    => $text('comment', 1000),
			'status'     => 'new',
		), 'errors' => array());
	}
}

if (!function_exists('order_summary_html')) {
	// The order as e-mail HTML, for the admin and the customer's copy. $user is a users row.
	function order_summary_html(array $user, array $order) {
		$e = function ($value) { return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8'); };
		$rows = array(
			'Institusjon/forening' => $user['institute'],
			'Kontaktperson'        => trim($user['fname'] . ' ' . $user['lname']),
			'Telefon'              => trim($user['phone'] . ' / ' . $user['mobile'], ' /'),
			'Epostadresse'         => $user['email'],
			'Bestilling'           => order_plan_text($order['plan'], isset($order['price']) ? $order['price'] : null),
		);
		if (!empty($order['shows'])) {
			$rows['Quizer'] = implode(', ', array_column(json_decode($order['shows'], true), 'name'));
		}
		if (!empty($order['start_date'])) {
			$rows['Ønsket startdato'] = date('d.m.Y', strtotime($order['start_date']));
		}
		if ($order['comment'] !== '') {
			$rows['Kommentar'] = $order['comment'];
		}
		$html = '';
		foreach ($rows as $label => $value) {
			$html .= '<strong>' . $e($label) . ':</strong> ' . nl2br($e($value)) . '<br>';
		}
		return $html;
	}
}
