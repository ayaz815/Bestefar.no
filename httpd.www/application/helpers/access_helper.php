<?php
defined('BASEPATH') OR define('BASEPATH', 'standalone'); // lets tests load this file without CodeIgniter

// The one rule that decides whether a customer may open a show.
// Pure functions: no database, no session. The caller loads the customer row and
// that customer's grants and passes them in.
//
//   $user   customer row (array) or null when not logged in. Uses: status,
//           approval_status, client_type (test|full|paid|null), plan
//           (per_show|audio_simple|all_shows), payment_registered, access_start,
//           access_end, allow_audio, allow_image, allow_imagemusic, allow_demo
//   $quiz   array('type' => audio|image|imagemusic, 'id' => string,
//           'tier' => restricted|registered|demo|null)   null tier = restricted
//   $grants rows of user_show_grants for this customer: quiz_type, quiz_id,
//           days_valid, first_opened_at, expires_at
//   $now    DateTimeInterface
//
// Returns array('allowed' => bool, 'reason' => string, 'grant' => row|null,
//               'starts_grant' => bool)
// 'starts_grant' = this open is the grant's first one, so the caller must now store
// first_opened_at and expires_at = access_grant_expiry(days_valid, now).

if (!function_exists('access_decision')) {
	function access_decision($allowed, $reason, $grant = null, $starts = false) {
		return array('allowed' => (bool) $allowed, 'reason' => $reason, 'grant' => $grant, 'starts_grant' => (bool) $starts);
	}
}

if (!function_exists('access_grant_expiry')) {
	function access_grant_expiry($days_valid, $now) {
		$end = DateTimeImmutable::createFromInterface($now)->modify('+' . (int) $days_valid . ' days');
		return $end->format('Y-m-d H:i:s');
	}
}

if (!function_exists('access_can_open')) {
	function access_can_open($user, array $quiz, array $grants, $now, $enabled = true) {
		if (!$enabled) {
			return access_decision(true, 'switch_off'); // old behaviour: nothing is restricted
		}
		if (empty($user)) {
			return access_decision(false, 'login_required');
		}
		$type = isset($quiz['type']) ? $quiz['type'] : '';
		if (!in_array($type, array('audio', 'image', 'imagemusic'), true)) {
			return access_decision(false, 'unknown_type'); // e.g. crossword: postponed
		}
		$tier = !empty($quiz['tier']) ? $quiz['tier'] : 'restricted';

		// Shows open to every registered customer, approved or not.
		if ($tier === 'registered') {
			return access_decision(true, 'open_to_registered');
		}

		if (isset($user['status']) && $user['status'] !== 'Active') {
			return access_decision(false, 'inactive');
		}
		if ((int) (isset($user['approval_status']) ? $user['approval_status'] : 0) !== 1) {
			return access_decision(false, 'not_approved');
		}

		$client = isset($user['client_type']) ? $user['client_type'] : null;
		if ($client === 'full') {
			return access_decision(true, 'full_access');
		}
		if ($tier === 'demo') {
			return !empty($user['allow_demo'])
				? access_decision(true, 'demo_allowed')
				: access_decision(false, 'demo_not_allowed');
		}

		// Restricted shows from here on.
		$grant = null;
		foreach ($grants as $g) {
			if ($g['quiz_type'] === $type && (string) $g['quiz_id'] === (string) (isset($quiz['id']) ? $quiz['id'] : '')) {
				$grant = $g;
				break;
			}
		}

		if ($client === 'paid') {
			if (empty($user['payment_registered'])) {
				return access_decision(false, 'payment_missing');
			}
			$plan = isset($user['plan']) ? $user['plan'] : null;
			if ($plan === 'per_show') {
				return access_grant_decision($grant, $now);
			}
			if ($plan !== 'all_shows' && $plan !== 'audio_simple') {
				return access_decision(false, 'no_plan');
			}
			if (!empty($user['access_start']) && new DateTime($user['access_start']) > $now) {
				return access_decision(false, 'not_started');
			}
			if (!empty($user['access_end']) && new DateTime($user['access_end']) < $now) {
				return access_decision(false, 'expired');
			}
			if ($plan === 'all_shows' || $type === 'audio') {
				return access_decision(true, 'paid_plan');
			}
			return access_decision(false, 'plan_excludes_type');
		}

		// Test customers, and old customers not yet moved (client_type NULL):
		// the ticks for the show type, or a show picked for them.
		if (!empty($user['allow_' . $type])) {
			return access_decision(true, 'type_allowed');
		}
		if ($client === 'test' || $client === null) {
			if ($grant) {
				return access_grant_decision($grant, $now);
			}
		}
		return access_decision(false, 'not_allowed');
	}
}

if (!function_exists('access_grant_decision')) {
	function access_grant_decision($grant, $now) {
		if (!$grant) {
			return access_decision(false, 'not_allowed');
		}
		if (!empty($grant['expires_at'])) {
			return new DateTime($grant['expires_at']) < $now
				? access_decision(false, 'expired', $grant)
				: access_decision(true, 'grant_active', $grant);
		}
		if (empty($grant['first_opened_at']) && !empty($grant['days_valid'])) {
			return access_decision(true, 'grant_first_open', $grant, true); // the clock starts now
		}
		return access_decision(true, 'grant_active', $grant); // no time limit
	}
}
