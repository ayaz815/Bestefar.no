<?php
defined('BASEPATH') OR exit('No direct script access allowed');

// Connects the access rule (helpers/access_helper.php) to the logged-in customer,
// their picked shows (user_show_grants) and the quiz API. Everything here is a
// no-op while config/access.php has access_control_enabled = FALSE.
class Access_model extends CI_Model {

	// Catalog category -> quiz type used by the rule and the backend.
	// TekstQuiz (crosswords) is left out on purpose: postponed by the client.
	const QUIZ_TYPES = array(
		'AudioQuiz'    => 'audio',
		'BildeQuiz'    => 'image',
		'BilderMusikk' => 'imagemusic',
	);

	const PLAYER_URLS = array(
		'audio'      => 'https://bestefar.no/bestefar-audio/index.html?screen=1&show=%s&autostart=1',
		'image'      => 'https://bestefar.no/bestefar-image/index.html?show=%s',
		'imagemusic' => 'https://bestefar.no/bestefar-imagemusic/index.html?show=%s',
	);

	// Key-checked PDF route on the backend (see backend routes/playRoutes.js).
	const PDF_URL = 'https://api.bestefar.no/api/play/%s/%s/pdf';

	const PLAY_KEY_HOURS = 12;

	private $api_base = 'https://api.bestefar.no/api';
	private $user = false; // false = not loaded yet, null = not logged in

	public function __construct() {
		parent::__construct();
		$this->load->database();
		$this->load->helper('access');
		$this->config->load('access', TRUE);
		$this->config->load('access_secret', TRUE, TRUE); // optional file, kept out of git
	}

	public function enabled() {
		return (bool) $this->config->item('access_control_enabled', 'access');
	}

	public function is_logged_in() {
		return $this->current_user() !== null;
	}

	// Adds an 'access' decision to each catalog row the rule controls.
	public function annotate_catalog(array $quizzes) {
		if (!$this->enabled()) {
			return $quizzes;
		}
		$user = $this->current_user();
		$grants = $user ? $this->grants_for($user['users_id']) : array();
		foreach ($quizzes as &$quiz) {
			$type = $this->quiz_type($quiz['quiz_type']);
			if ($type) {
				$quiz['access'] = access_can_open($user, $this->rule_quiz($type, $quiz['external_id'], $quiz['tier']), $grants, new DateTime());
				$quiz['open_url'] = base_url('home/open_quiz/' . $type . '/' . rawurlencode($quiz['external_id']));
			}
		}
		unset($quiz);
		return $quizzes;
	}

	// Checks one show right before it opens and starts a grant's clock on first open.
	// $target is 'player' or 'pdf'. Returns the decision, plus 'target_url' when
	// allowed: the player or PDF link, carrying a 12-hour play key when the switch is on.
	public function open($type, $quiz_id, $target = 'player') {
		if (!isset(self::PLAYER_URLS[$type])) {
			return access_decision(false, 'unknown_type');
		}
		$user = $this->current_user();
		$grants = $user ? $this->grants_for($user['users_id']) : array();
		$decision = access_can_open($user, $this->rule_quiz($type, $quiz_id, $this->fetch_tier($type, $quiz_id)), $grants, new DateTime(), $this->enabled());
		if ($decision['starts_grant']) {
			$this->start_grant_clock($decision['grant']);
		}
		if ($decision['allowed']) {
			$decision['target_url'] = $this->target_url($type, $quiz_id, $target, $user);
		}
		return $decision;
	}

	private function target_url($type, $quiz_id, $target, $user) {
		$url = $target === 'pdf'
			? sprintf(self::PDF_URL, $type, rawurlencode($quiz_id))
			: sprintf(self::PLAYER_URLS[$type], rawurlencode($quiz_id));
		$secret = $this->config->item('access_token_secret', 'access_secret');
		if (!$this->enabled() || !$user) {
			return $url;
		}
		if (!$secret) {
			log_message('error', 'Access control is on but config/access_secret.php has no access_token_secret');
			return $url;
		}
		$key = access_play_key($user['users_id'], $type, $quiz_id, new DateTime(), $secret, self::PLAY_KEY_HOURS);
		return $url . (strpos($url, '?') === false ? '?' : '&') . 'key=' . rawurlencode($key);
	}

	// Text shown on a locked row / after a refused open.
	public static function message_for($reason) {
		switch ($reason) {
			case 'expired':        return 'Ditt abonnement har utgått. Vil du fornye det?';
			case 'login_required': return 'Logg inn for å åpne denne quizen.';
			default:               return 'Du har ikke tilgang til denne quizen.';
		}
	}

	// Banner text after open_quiz refused a show (?access=<reason>). Empty when the
	// switch is off or no reason is given, so the catalog looks as before.
	public function refused_message($reason) {
		return ($this->enabled() && $reason) ? self::message_for($reason) : '';
	}

	// Short label next to the lock icon in the catalog table.
	public static function short_label_for($reason) {
		return $reason === 'expired' ? 'Utgått' : 'Låst';
	}

	private function quiz_type($category) {
		return isset(self::QUIZ_TYPES[$category]) ? self::QUIZ_TYPES[$category] : null;
	}

	private function rule_quiz($type, $id, $tier) {
		return array('type' => $type, 'id' => (string) $id, 'tier' => $tier);
	}

	private function current_user() {
		if ($this->user === false) {
			$id = $this->session->userdata('users_id');
			$this->user = $id ? $this->db->get_where('users', array('users_id' => $id))->row_array() : null;
		}
		return $this->user;
	}

	private function grants_for($users_id) {
		return $this->db->get_where('user_show_grants', array('users_id' => $users_id))->result_array();
	}

	private function start_grant_clock(array $grant) {
		$now = new DateTime();
		$this->db->where('grant_id', $grant['grant_id'])
			->where('first_opened_at IS NULL', null, false) // never restart a running clock
			->update('user_show_grants', array(
				'first_opened_at' => $now->format('Y-m-d H:i:s'),
				'expires_at'      => access_grant_expiry($grant['days_valid'], $now),
			));
	}

	// Unknown or unreachable = restricted, the safe default.
	protected function fetch_tier($type, $quiz_id) {
		$ch = curl_init($this->api_base . '/access-tier/' . $type . '/' . rawurlencode($quiz_id));
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_TIMEOUT, 8);
		$response = curl_exec($ch);
		$ok = ($response !== false && curl_getinfo($ch, CURLINFO_HTTP_CODE) == 200);
		curl_close($ch);
		$data = $ok ? json_decode($response, true) : null;
		return isset($data['accessTier']) ? $data['accessTier'] : 'restricted';
	}
}
