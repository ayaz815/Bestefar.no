<?php
 
//push Notification 

	class Strip_model extends CI_Model{
		function __construct() {			
			parent::__construct();
			$this->load->database();
			$this->output->set_header('Pragma: no-cache');
			$this->load->helper('date');
		}
		
		function strip_validate(){
			//check whether stripe token is not empty
				$users_id  =  $this->session->userdata('users_id');
				$competitions = $this->db->get_where('competitions_sold', array('users_id'=>$users_id, 'paid_status'=>'Unpaid' ))->result_array();
				foreach($competitions as $comp){ $cids[] = $comp['competitions_sold_id']; }
				$cid = implode(", ",$cids); 
				
			if(!empty($_POST['stripeToken']))
			{
				//get token, card and user info from the form
				$token  = $_POST['stripeToken'];
				$card_num = $_POST['card_num'];
				$card_cvc = $_POST['cvc'];
				$card_exp_month = $_POST['exp_month'];
				$card_exp_year = $_POST['exp_year'];
				$email = $this->Admin_model->get_row_id('users', $users_id)->email;
				
				//include Stripe PHP library
				require_once APPPATH."third_party/stripe/init.php";
				
				//set api key
				$stripe = array(
			 "secret_key"      => $this->Admin_model->get_row_id('system_setting', 1)->strip_secret_key,
			"publishable_key" => $this->Admin_model->get_row_id('system_setting', 1)->strip_public_key
				);
				
				\Stripe\Stripe::setApiKey($stripe['secret_key']);
				
				//add customer to stripe
				$customer = \Stripe\Customer::create(array(
					'email' => $email,
					'source'  => $token
				));
				$customer->id;
				//item information
				$price_q = $this->db->query('SELECT SUM(total_price) as total_amount  FROM competitions_sold WHERE paid_status="Unpaid" AND users_id="'.$users_id.'"')->row()->total_amount;
				$price = number_format((float)$price_q, 2, '.', '');
				$currency = "gbp";
				$orderID = "SKA92712382139";
				
				//charge a credit or a debit card
				$charge = \Stripe\Charge::create(array(
					'customer' => $customer->id,
					'amount'   => $price*100,
					'currency' => $currency,
					'description' => $cid,
					'metadata' => array(
						'item_id' => $cid
					)
				));
				
				//retrieve charge details
				$chargeJson = $charge->jsonSerialize();

				//check whether the charge is successful
				if($chargeJson['amount_refunded'] == 0 && empty($chargeJson['failure_code']) && $chargeJson['paid'] == 1 && $chargeJson['captured'] == 1)
				{
					//order details 
					$amount = $chargeJson['amount'];
					$balance_transaction = $chargeJson['balance_transaction'];
					$currency = $chargeJson['currency'];
					$status = $chargeJson['status'];
					$date = date("Y-m-d H:i:s");
				
					
					//insert tansaction data into the database
					$dataDB = array(
						'competitions_sold_id' => $cid,
						'users_id' => $this->session->userdata('users_id'),
						'name' => $this->session->userdata('name'),
						'email' => $email, 
						'card_number' => $card_num, 
						'card_cvc' => $card_cvc, 
						'card_exp_month' => $card_exp_month, 
						'card_exp_year' => $card_exp_year, 
						'amount' => $amount/100, 
						'transaction_id' => $balance_transaction, 
						'status' => $status,
						'date' => $date,
					);

						if($status == 'succeeded'){
							$this->db->insert('payments', $dataDB);
							
							$upd_data['paid_status'] = 'Paid';
							$this->db->where('paid_status' , 'Unpaid');
							$this->db->where('users_id' , $users_id);
							$this->db->update('competitions_sold' , $upd_data); 
						}else{
							echo "Transaction has been failed";
						}
				}else{
					echo "Invalid Token"; exit;
					$statusMsg = "";
				}
				return $status;
			}
		}
	}
?>