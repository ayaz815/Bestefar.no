<?php
defined('BASEPATH') OR exit('No direct script access allowed');

// Order form "Bestilling" (ACCESS_CONTROL_PLAN.md, Module 7).
// Pure functions: no database, no session, so they are unit-tested directly.
// The plan keys are the paid plans the access rule already knows (access_helper.php).

if (!function_exists('order_plans')) {
	// plan key => array(label shown to the customer, number of shows to pick)
	function order_plans() {
		return array(
			'per_show'     => array('label' => '2 forestillinger, 3 dager (f.eks. bursdag)', 'shows' => 2),
			'audio_simple' => array('label' => 'AudioQuiz og enkle spørreark, 1 år', 'shows' => 0),
			'all_shows'    => array('label' => 'Alle forestillinger, 1 år', 'shows' => 0),
		);
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
				$errors[] = 'Velg ' . $plans[$plan]['shows'] . ' forskjellige forestillinger.';
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
		$plans = order_plans();
		$e = function ($value) { return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8'); };
		$rows = array(
			'Institusjon/forening' => $user['institute'],
			'Kontaktperson'        => trim($user['fname'] . ' ' . $user['lname']),
			'Telefon'              => trim($user['phone'] . ' / ' . $user['mobile'], ' /'),
			'Epostadresse'         => $user['email'],
			'Bestilling'           => isset($plans[$order['plan']]) ? $plans[$order['plan']]['label'] : $order['plan'],
		);
		if (!empty($order['shows'])) {
			$rows['Forestillinger'] = implode(', ', array_column(json_decode($order['shows'], true), 'name'));
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
