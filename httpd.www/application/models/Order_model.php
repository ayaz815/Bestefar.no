<?php
defined('BASEPATH') OR exit('No direct script access allowed');

// Order form "Bestilling" (ACCESS_CONTROL_PLAN.md, Module 7).
// Saves a logged-in customer's order and e-mails it. Ordering grants nothing:
// the admin registers payment and access by hand in "Customer access".
class Order_model extends CI_Model {

	public function __construct() {
		parent::__construct();
		$this->load->database();
		$this->load->helper('order');
	}

	// False until database/migrations/2026-11-14_orders_up.sql has been run.
	public function table_ready() {
		return $this->db->table_exists('orders');
	}

	// The logged-in customer's users row, or null.
	public function current_customer() {
		$id = (int) $this->session->userdata('users_id');
		return $id > 0 ? $this->db->get_where('users', array('users_id' => $id))->row_array() : null;
	}

	// 'type:id' => 'Category - name' for every show a customer can pick
	// (same values as the admin "Picked shows" list, see Access_model::QUIZ_TYPES).
	// Sorted by type (AudioQuiz, BildeQuiz, BilderMusikk), newest first inside each
	// type like the quiz list (client 26 Nov 2026: "sorted by type and by date").
	public function show_choices() {
		$this->load->model('Access_model');
		$rank = array_flip(array_keys(Access_model::QUIZ_TYPES));
		$quizzes = array();
		foreach ($this->Admin_model->get_quizzes_catalog('', 'date', 'desc') as $i => $quiz) {
			if (isset($rank[$quiz['quiz_type']])) {
				$quizzes[] = array('rank' => $rank[$quiz['quiz_type']], 'i' => $i, 'quiz' => $quiz);
			}
		}
		usort($quizzes, function ($a, $b) { return ($a['rank'] - $b['rank']) ?: ($a['i'] - $b['i']); });
		$choices = array();
		foreach ($quizzes as $row) {
			$quiz = $row['quiz'];
			$choices[Access_model::QUIZ_TYPES[$quiz['quiz_type']] . ':' . $quiz['external_id']] = $quiz['quiz_type'] . ' - ' . $quiz['name'];
		}
		return $choices;
	}

	// Checks and saves the posted order. Returns array('ok' => bool, 'errors' => list).
	public function place(array $customer, $post, array $show_choices) {
		$result = order_input($post, $show_choices, date('Y-m-d'));
		if (!$result['order']) {
			return array('ok' => false, 'errors' => $result['errors']);
		}
		$order = $result['order'];
		$order['users_id'] = (int) $customer['users_id'];
		$order['created_at'] = date('Y-m-d H:i:s');
		$this->db->insert('orders', $order);

		$summary = order_summary_html($customer, $order);
		$admins = $this->Admin_model->admin_email_addresses();
		if ($admins) {
			$this->Admin_model->send_site_email($admins, 'NY BESTILLING', 'Ny bestilling på Bestefar.no<br><br>' . $summary);
		}
		$this->Admin_model->send_site_email($customer['email'], 'Bekreftelse på bestilling',
			'Hei, og takk for bestillingen.<br>Vi tar kontakt med deg om betaling og tilgang.<br><br>' . $summary
			. '<br>Med vennlig hilsen<br><a href="https://bestefar.no/">www.bestefar.no</a>');
		return array('ok' => true, 'errors' => array());
	}

	// One customer's orders, newest first (admin "Customer access").
	public function for_customer($users_id) {
		if (!$this->table_ready()) {
			return array();
		}
		return $this->db->order_by('created_at', 'DESC')->get_where('orders', array('users_id' => (int) $users_id))->result_array();
	}
}
