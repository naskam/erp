<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Accounting extends CI_Controller {

	function __construct()
	{
		parent::__construct();
		session_start();
		$this->load->model('user_model');
		if (isset($_SESSION['username'])) {
			$res=$this
			->user_model
			->verify_user(
				array(
					'user_id'=>$_SESSION['user_id'],
					'username'=>$_SESSION['username'],
					'password'=>$_SESSION['password']
					)
				);
			if ($res == false) { redirect('logout'); exit(); }
		}
		else
		{
			redirect('logout');
		}
	}
	public function index()
	{
		$this->load->view('nav');
	}
	function new_record(){
		$this->load->model('accounting_record_model');
		$this->load->model('accounting_record_items_model');
		$this->load->model('group_accounts_model');
		$this->load->model('ledger_accounts_model');
		$this->load->model('sub_accounts_model');
		$this->load->view('accounting/new_record_view');
	}
	function submit_record_items(){
		if ($this->input->post('detail_id')) {
			$new_item = array(array('id'=>sha1(rand()) ,'group_id'=>$_POST['group_id'], 'ledger_id'=>$_POST['ledger_id'], 'sub_id'=>$_POST['sub_id'], 'detail_id'=>$_POST['detail_id'], 'debit'=>$_POST['debit'],'credit'=>$_POST['credit'],'title'=>$_POST['title'],'item_date'=>$_POST['item_date']));
		}
		else
		{
			$new_item = array(array('id'=>sha1(rand()) ,'group_id'=>$_POST['group_id'], 'ledger_id'=>$_POST['ledger_id'], 'sub_id'=>$_POST['sub_id'], 'detail_id'=>'0', 'debit'=>$_POST['debit'],'credit'=>$_POST['credit'],'title'=>$_POST['title'],'item_date'=>$_POST['item_date']));
		}
		if (isset($_SESSION["record_items"])) {
			foreach ($_SESSION["record_items"] as $itm) //loop through session array
				{
					$items[] = array('id'=>$itm['id'] , 'group_id'=>$itm['group_id'], 'ledger_id'=>$itm['ledger_id'], 'sub_id'=>$itm['sub_id'], 'detail_id'=>$itm['detail_id'], 'debit'=>$itm['debit'],'credit'=>$itm['credit'],'title'=>$itm['title'],'item_date'=>$itm['item_date']);
				}

				$_SESSION["record_items"] = array_merge($items, $new_item);
			}
			 else
			{
				$_SESSION["record_items"] = $new_item;
			}
		$this->load->model('group_accounts_model');
		$this->load->model('ledger_accounts_model');
		$this->load->model('sub_accounts_model');
		$this->load->model('accounting_record_items_model');
		$this->load->view('accounting/record_items_table');
	}
	function submit_record(){
		$this->load->model('accounting_record_items_model');
		$this->load->model('accounting_record_model');
		if (isset($_SESSION['record_items'])) {
			if ($this->input->post('title') & $this->input->post('record_date')) {
				$debit=$this->accounting_record_items_model->get_total_session_debit();
				$credit=$this->accounting_record_items_model->get_total_session_credit();
				if ($debit > 0 & $credit > 0) {
					$record_data=array(
						'title'=>$this->input->post('title'),
						'record_date'=>$this->input->post('record_date'),
						'description'=>$this->input->post('description'),
						'confirm'=>'0',
						'reg_date'=>date("Y/m/d"),
						'user_id'=>$_SESSION['user_id']
						);
					$res=$this->accounting_record_model->insert($record_data);
					if ($res==1) {
						$record_id=$this->db->insert_id();
						$success=0;
						$failure=0;
						foreach ($_SESSION['record_items'] as $key) {
							$item_data=array(
								'record_id'=>$record_id,
								'group_id'=>$key['group_id'],
								'ledger_id'=>$key['ledger_id'],
								'sub_id'=>$key['sub_id'],
								'detail_id'=>$key['detail_id'],
								'debit'=>$key['debit'],
								'credit'=>$key['credit'],
								'title'=>$key['title'],
								'item_date'=>$key['item_date'],
								'user_id'=>$_SESSION['user_id']
								);
							$res=$this->accounting_record_items_model->insert($item_data);
							if ($res==1) {
								$success++;
							} else
							{
								$failure++;
							}
						}
						unset($_SESSION['record_items']);
						echo "<div class='alert alert-info'>بەڵگەی ژمێریاری پاشکەوت کرا ژمارە #".$record_id." و سەرکەوتو:".$success." و ناسەرکەوتو:".$failure." .</div>";
					} else{ echo "<div class='alert alert-danger'>هەلە لە پاشکەوت کردن دا روی داوە.</div>"; }
				} else { echo "<div class='alert alert-danger'>بری پارەی قەرزداری ق قەرزدەری بەرامبەر نێ .</div>"; }
			} else { echo "<div class='alert alert-danger'>سەردێر و رێکەورت نەناردراوە.</div>"; }
		} else { echo "<div class='alert alert-danger'>هیچ ئایتێمەک بو ئەم بەڵگەیە دابین نەکراوە </div>"; }
	}
	function load_items_table(){
		$this->load->model('group_accounts_model');
		$this->load->model('ledger_accounts_model');
		$this->load->model('sub_accounts_model');
		$this->load->model('accounting_record_items_model');
		$this->load->view('accounting/record_items_table');
	}
	function records_list(){
		$this->load->model('group_accounts_model');
		$this->load->model('ledger_accounts_model');
		$this->load->model('accounting_record_model');
		$this->load->model('accounting_record_items_model');
		$this->load->model('user_model');
		$this->load->view('accounting/records_list_view');
	}
	function accounts_manager(){
		$this->load->model('group_accounts_model');
		$this->load->view('accounting/accounts_manager');
	}
	function delete_record_items(){
		unset($_SESSION["record_items"]);
	}
	function delete_record_item(){
		if (isset($_SESSION["record_items"])) {
			foreach ($_SESSION["record_items"] as $itm) //loop through session array
				{
					if($itm['id'] != $this->uri->segment(3)){
					$items[] = array('id'=>$itm['id'] , 'group_id'=>$itm['group_id'], 'ledger_id'=>$itm['ledger_id'], 'sub_id'=>$itm['sub_id'], 'detail_id'=>$itm['detail_id'], 'debit'=>$itm['debit'],'credit'=>$itm['credit'],'title'=>$itm['title'],'item_date'=>$itm['item_date']);
				} }

				$_SESSION["record_items"] = $items;
			}
		redirect('accounting/load_items_table');
	}
	function define_accounts(){
		$this->load->model('group_accounts_model');
		$this->load->model('ledger_accounts_model');
		$this->load->model('sub_accounts_model');
		$this->load->view('accounting/define_accounts');
	}
	function new_ledger_account(){
		$this->load->model('ledger_accounts_model');
		if( $this->uri->segment(3) > 0 && !empty($this->input->post('title'))){
		$data=array(
			'group_id'=>$this->uri->segment(3),
			'title'=>$this->input->post('title'),
			'nature'=>$this->input->post('nature'),
			'status'=>'1',
			'user_id'=>$_SESSION['user_id']
			);
		$res=$this->ledger_accounts_model->insert($data);
		if ($res==1) {
			echo "<div class='alert alert-success'>هەژماری سەررەکی زیاد کڕا.</div>";
		}
	} else
	{
		echo "<div class='alert alert-danger'>هەلەیەک رۆی داوە ، تکایە دوبارە حەول بدە.</div>";
	}
	}
	function new_sub_account(){
		$this->load->model('ledger_accounts_model');
		$this->load->model('sub_accounts_model');
		if($this->uri->segment(3))
		{
			$data=array(
				'ledger_id'=>$this->uri->segment(3),
				'title'=>$this->input->post('title'),
				'detail_kind'=>$this->input->post('detail_kind'),
				'user_id'=>$_SESSION['user_id']
				);
			$res=$this->sub_accounts_model->insert($data);
			if ($res==1) {
				echo "<div class='alert alert-success'>ژێر هەژمار زیاد کڕا.</div>";
			} else {
				echo "<div class='alert alert-danger'>هەلەیەک رۆی داوە ، تکایە دوبارە حەول بدە.</div>";
			}
		} else
		{
			echo "<div class='alert alert-danger'>هەلەیەک رۆی داوە ، تکایە دوبارە حەول بدە.</div>";
		}
	}
	function ajax_ledger_load(){
		$this->load->model('ledger_accounts_model');
		$this->load->view('accounting/ajax/load_ledger_accounts');
	}
	function ajax_sub_load(){
		$this->load->model('sub_accounts_model');
		$this->load->view('accounting/ajax/load_sub_accounts');
	}
	function ajax_detail_load(){
		$this->load->model('sub_accounts_model');
		$this->load->model('independent_model');
		$this->load->model('customer_model');
		$this->load->model('revolving_model');
		$this->load->model('personnel_model');
		$this->load->model('owners_model');
		$this->load->model('fix_assets_model');
		$this->load->model('banks_model');
		$this->load->model('general_model');
		$this->load->view('accounting/ajax/load_detail_accounts');
	}
	function ajax_ledger_load_manager(){
		$this->load->model('ledger_accounts_model');
		$this->load->view('accounting/ajax/load_ledger_accounts_manager');
	}
	function ajax_sub_load_manager(){
		$this->load->model('sub_accounts_model');
		$this->load->view('accounting/ajax/load_sub_accounts_manager');
	}
	function ajax_detail_load_manager(){
		$this->load->model('sub_accounts_model');
		$this->load->model('independent_model');
		$this->load->model('customer_model');
		$this->load->model('revolving_model');
		$this->load->model('personnel_model');
		$this->load->model('owners_model');
		$this->load->model('fix_assets_model');
		$this->load->model('banks_model');
		$this->load->model('general_model');
		$this->load->view('accounting/ajax/load_detail_accounts_manager');
	}
	function delete_ledger_account(){
		$this->load->model('accounting_record_items_model');
		$this->load->model('ledger_accounts_model');
		$this->load->model('sub_accounts_model');
		if ($this->uri->segment(3)) {
			$count=$this->accounting_record_items_model->count(array('ledger_id'=>$this->uri->segment(3)));
			if($count == 0){
				$this->sub_accounts_model->delete(array('ledger_id'=>$this->uri->segment(3)));
				$this->ledger_accounts_model->delete(array('id'=>$this->uri->segment(3)));
				echo "<div class='alert alert-success'>Your request was done successfuly.</div>";
			} else{
				echo "<div class='alert alert-danger'>You Cannot delete this account because used in accounting records.</div>";
			}
		} else {
			echo 'Recived Data Not Valid !';
		}
	}
	function delete_sub_account(){
		$this->load->model('accounting_record_items_model');
		$this->load->model('sub_accounts_model');
		if ($this->uri->segment(3)) {
			$count=$this->accounting_record_items_model->count(array('sub_id'=>$this->uri->segment(3)));
			if($count == 0){
				$sub=$this->sub_accounts_model->findbyid($this->uri->segment(3));
				if ($sub->detail_kind == '1') {
					$this->load->model('independent_model');
					$this->independent_model->delete(array('sub_id'=>$this->uri->segment(3)));
				}
				$this->sub_accounts_model->delete(array('id'=>$this->uri->segment(3)));
				echo "<div class='alert alert-success'>Your request was done successfuly.</div>";
			} else{
				echo "<div class='alert alert-danger'>You Cannot delete this Subsidiary account because used in accounting records.</div>";
			}
		} else {
			echo 'Recived Data Not Valid !';
		}
	}
	function edit_ledger_manager(){
		$this->load->model('ledger_accounts_model');
		$this->load->view('accounting/ajax/load_edit_ledger_manager');
	}
	function update_ledger_manager(){
		$this->load->model('ledger_accounts_model');
		$new_data=array(
			'title'=>$this->input->post('title'),
			'nature'=>$this->input->post('nature')
			);
		if ($this->uri->segment(3) !== false) {
			$this->ledger_accounts_model->update($new_data,array('id'=>$this->uri->segment(3)));
			echo "<div class='alert alert-success'>گورانکاری سەرکەوتو بۆ.</div>";
		} else {
			echo "<div class='alert alert-danger'>ئەنجامەکە سەرکەوتۆ نەبو.</div>";
		}
	}
	function edit_sub_manager(){
		$this->load->model('sub_accounts_model');
		$this->load->view('accounting/ajax/load_edit_sub_manager');
	}
	function update_sub_manager(){
		$this->load->model('sub_accounts_model');
		$this->load->model('accounting_record_items_model');
		if ($this->uri->segment(3)) {
			$count=$this->accounting_record_items_model->count(array('sub_id'=>$this->uri->segment(3)));
			$data1=array(
				'title'=>$this->input->post('title'),
				'detail_kind'=>$this->input->post('detail_kind')
				);
			$data2=array(
				'title'=>$this->input->post('title'),
				);
			if ($count==0) {
				$this->sub_accounts_model->update($data1,array('id'=>$this->uri->segment(3)));
				echo "<div class='alert alert-success'>گورانکاری سەرکەوتو بۆ.</div>";
			} else {
				$this->sub_accounts_model->update($data2,array('id'=>$this->uri->segment(3)));
				echo "<div class='alert alert-success'>گورانکاری سەرکەوتو بۆ.</div>";
			}
		} else {
			echo "<div class='alert alert-alert'>ئەنجامەکە سەرکەوتۆ نەبو.</div>";
		}
	}
	function record_edit(){
		$this->load->model('accounting_record_model');
		$this->load->model('accounting_record_items_model');
		$this->load->model('group_accounts_model');
		$this->load->model('ledger_accounts_model');
		$this->load->model('sub_accounts_model');
		$this->load->view('accounting/record_edit');
	}
	function update_record_details(){
		if($this->uri->segment(3)){
			$data=array(
				'title'=>$this->input->post('title'),
				'record_date'=>$this->input->post('record_date'),
				'description'=>$this->input->post('description')
				);
			$this->load->model('accounting_record_model');
			$res=$this->accounting_record_model->update($data,array('id'=>$this->uri->segment(3)));
			if ($res) {
				echo '<div class="alert alert-success">successfuly</div>';
			}
		}
	}
	function edit_record_details(){
		$this->load->model('accounting_record_model');
		$this->load->model('accounting_record_items_model');
		$this->load->model('group_accounts_model');
		$this->load->model('ledger_accounts_model');
		$this->load->model('sub_accounts_model');
		$this->load->model('sub_accounts_model');
		$this->load->model('independent_model');
		$this->load->model('customer_model');
		$this->load->model('revolving_model');
		$this->load->model('personnel_model');
		$this->load->model('owners_model');
		$this->load->model('fix_assets_model');
		$this->load->model('banks_model');
		$this->load->model('general_model');
		$this->load->view('accounting/record_item_edit');
	}
	function update_record_item(){
		if($this->uri->segment(3)){
			$item_data=array(
								'group_id'=>$this->input->post('group_id'),
								'ledger_id'=>$this->input->post('ledger_id'),
								'sub_id'=>$this->input->post('sub_id'),
								'detail_id'=>$this->input->post('detail_id'),
								'debit'=>$this->input->post('debit'),
								'credit'=>$this->input->post('credit'),
								'title'=>$this->input->post('title'),
								'item_date'=>$this->input->post('item_date'),
								'user_id'=>$_SESSION['user_id']
								);
			$this->load->model('accounting_record_items_model');
			$res=$this->accounting_record_items_model->update($item_data,array('id'=>$this->uri->segment(3)));
			if($res){
				echo "<div class='alert alert-success'>Success</div>";
			} else {
				echo "<div class='alert alert-warning'>Failure</div>";
			}
		}
	}
	function new_record_item(){
		$this->load->model('group_accounts_model');
		$this->load->view('accounting/new_record_item');
	}
	function insert_record_new_item(){
		if($this->uri->segment(3)){
			$item_data=array(
								'group_id'=>$this->input->post('group_id'),
								'ledger_id'=>$this->input->post('ledger_id'),
								'sub_id'=>$this->input->post('sub_id'),
								'detail_id'=>$this->input->post('detail_id'),
								'debit'=>$this->input->post('debit'),
								'credit'=>$this->input->post('credit'),
								'title'=>$this->input->post('title'),
								'item_date'=>$this->input->post('item_date'),
								'user_id'=>$_SESSION['user_id']
								);
			$this->load->model('accounting_record_items_model');
			$res=$this->accounting_record_items_model->insert($item_data);
			if($res){
				echo "<div class='alert alert-success'>Success</div>";
			} else {
				echo "<div class='alert alert-warning'>Failure</div>";
			}
		}
	}
}

