<?php
 
//push Notification 

	class Excel_model extends CI_Model{
		function __construct() {			
			parent::__construct();
			$this->load->database();
			$this->output->set_header('Pragma: no-cache');
			$this->load->helper('date');
		}
		
		function export_buyers($param){
			//$this->load->model("excel_export_model");
			$this->load->library("PHPExcel");
			$object = new PHPExcel();

			$object->setActiveSheetIndex(0);

			$table_columns = array("First Name", "Last Name", "Email", "country", "Town", "address", "Quantity", "Price", "Total Price");

			$column = 0;

			foreach($table_columns as $field)
			{
			$object->getActiveSheet()->setCellValueByColumnAndRow($column, 1, $field);
			$column++;
			}
			$conditions['competitions_id'] = $param; 
			$conditions['paid_status'] = 'Paid'; 
			$tickets = $this->Admin_model->get_data_conditions('competitions_sold',$conditions)->result_array(); 
			$excel_row = 2;

			foreach($tickets as $row)
			{
				$buyer = $this->Admin_model->get_row_id('users',$row['users_id']);
				$object->getActiveSheet()->setCellValueByColumnAndRow(0, $excel_row, $buyer->firstname);
				$object->getActiveSheet()->setCellValueByColumnAndRow(1, $excel_row, $buyer->lastname);
				$object->getActiveSheet()->setCellValueByColumnAndRow(2, $excel_row, $buyer->email);
				//$object->getActiveSheet()->setCellValueByColumnAndRow(3, $excel_row, $buyer->telephone);
				$object->getActiveSheet()->setCellValueByColumnAndRow(3, $excel_row, $buyer->country);
				$object->getActiveSheet()->setCellValueByColumnAndRow(4, $excel_row, $buyer->town);
				$object->getActiveSheet()->setCellValueByColumnAndRow(5, $excel_row, $buyer->address);
				$object->getActiveSheet()->setCellValueByColumnAndRow(6, $excel_row, $row['quantity']);
				$object->getActiveSheet()->setCellValueByColumnAndRow(7, $excel_row, $row['ticket_price']);
				$object->getActiveSheet()->setCellValueByColumnAndRow(8, $excel_row, $row['total_price']);
				$excel_row++;
			}
			
			$object_writer = PHPExcel_IOFactory::createWriter($object, 'Excel5');
			header('Content-Type: application/vnd.ms-excel');
			header('Content-Disposition: attachment;filename="Employee Data.xls"');
			$object_writer->save('php://output');
		}
	}
?>