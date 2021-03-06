<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Admin extends CI_Controller
{
	public function __construct()
	{
		parent::__construct();		
	}
	/**
	 * Index Page for this controller.
	 *
	 * Maps to the following URL
	 * 		http://example.com/index.php/welcome
	 *	- or -
	 * 		http://example.com/index.php/welcome/index
	 *	- or -
	 * Since this controller is set as the default controller in
	 * config/routes.php, it's displayed at http://example.com/
	 *
	 * So any other public methods not prefixed with an underscore will
	 * map to /index.php/welcome/<method_name>
	 * @see https://codeigniter.com/user_guide/general/urls.html
	 */
	public function index()
	{
		// $this->load->model('Type');
		// $data = $this->Type->getAll();
		//print_r($data);
		$this->Login();
		$this->load->model("Order");
		$total = $this->Order->getTotal();
		$don_hang = $this->Order->getCount();
		$da_hoang_tat = $this->Order->getDHHoanTat();
		//phần load view
		$header = "Dashboard";
		$arrayLoad = array(
			'header'=>$header,
			"total"=>$total,
			"don_hang"=>$don_hang,
			"da_hoan_tat"=>$da_hoang_tat
		);
		$context = $this->load->view('admin/index', $arrayLoad, true);
		$this->load->view('admin/share_layout', array('context' => $context));
	}
	public function product()
	{
		$this->Login();
		$this->load->model('Products');	
		$this->load->model('Type');	
		$data = $this->Products->getAll();
		$datatype = $this->Type->getAll();
		//print_r($datatype);
		$header = "Products list";
		$context = $this->load->view('admin/products_list', array('header'=>$header,'list' => $data,'type'=>$datatype), true);
		$this->load->view('admin/share_layout', array('context' => $context));
	}
	public function addProduct()
	{
		$this->Login();
		$this->load->model('Type');
		$data = $this->Type->getAll();
		//print_r($data);			
		$header = "Thêm Sản Phẩm";
		$context = $this->load->view('admin/addProduct', array('header'=>$header,"type" => $data), true);
		$this->load->view('admin/share_layout', array('context' => $context));
	}

	public function insertProduct()
	{
		$this->Login();
		$config['upload_path'] = 'upload/';
		$config['allowed_types'] = 'gif|jpg|png';
		$config['max_size']     = '10000';
		$config['max_width'] = '10000';
		$config['max_height'] = '10000';

		$this->load->library('upload', $config);

		$this->upload->do_upload("image");
		$data = $this->upload->data();

		$CodeProduct = $this->input->post('codeProduct');
		$NameProduct = $this->input->post('nameProduct');
		$AmountProduct = $this->input->post('amount');
		$Descrip = $this->input->post('descript');
		$PriceProduct = $this->input->post('price');
		$Image = $data['file_name'];
		$manufacturer = $this->input->post('manufacturer');
		$type = $this->input->post('type');
		$this->load->model('Products');
		$this->Products->InsertProduct($CodeProduct, $NameProduct, $AmountProduct, $Descrip, $PriceProduct, $Image, $manufacturer, $type);

		//$nav = $this->load->view('admin/LoadNav', '', true);
		//$this->product();
		//$this->load->view("admin/products_list",array('nav'=>$nav,"list"=>$data));
		redirect("admin/product");
	}


	function deleteProduct($id)
	{
		$this->Login();
		$this->load->model('Products');
		$data = $this->Products->getByID($id);
		$this->Products->Delete($id);
		if($data->Image != ""){
			unlink("upload/" . $data->Image);
		}		
		redirect("admin/product");
	}

	function EditProduct($id)
	{
		$this->Login();
		$this->load->model('Type');
		$this->load->model('Products');
		$data = $this->Type->getAll();		
		$dataP = $this->Products->getByID($id);
		//print_r($dataP);
		$header = "Chỉnh Sửa Sản Phẩm";
		$context = $this->load->view('admin/editProduct', array('header'=>$header,"type" => $data,"product" => $dataP), true);
		$this->load->view('admin/share_layout', array('context' => $context));		
	}
	//update dữ liệu
	function UpdateProduct($id)
	{
		$this->Login();
		$config['upload_path'] = 'upload/';
		$config['allowed_types'] = 'gif|jpg|png';
		$config['max_size']     = '10000';
		$config['max_width'] = '10000';
		$config['max_height'] = '10000';
		$this->load->helper("file");

		$this->load->library('upload', $config);
		$this->upload->do_upload("image");
		$data = $this->upload->data();

		$CodeProduct = $this->input->post('codeProduct');
		$NameProduct = $this->input->post('nameProduct');
		$AmountProduct = $this->input->post('amount');
		$Descrip = $this->input->post('descript');
		$PriceProduct = $this->input->post('price');
		$Image = $data['file_name'];
		$manufacturer = $this->input->post('manufacturer');
		$type = $this->input->post('type');

		$product = $this->Products->getByID($id);
		if ($Image == null) {
			$Image = $product->Image;
		} else {			
			if(file_exists("upload/" . $product->Image)){
				if($product->Image != null){									
					unlink("upload/" . $product->Image);
				}				
			}
		}
		$this->Products->Update($id, $CodeProduct, $NameProduct, $AmountProduct, $Descrip, $PriceProduct, $Image, $manufacturer, $type);
		redirect('admin/product');
	}
	public function Order()
	{		
		$this->Login();
		$this->load->model("Order");	
		$header = "Danh Sách Đặt Hàng";
		$data = $this->Order->getAll();
		$context = $this->load->view('admin/order_page', array('header'=>$header,'data'=>$data), true);
		$this->load->view('admin/share_layout', array('context' => $context));
	}

	public function accout()
	{
		$this->Login();
		$header = "Tài khoản Quản Trị Viên";
		$this->load->model('User');	
		$data = $this->User->getAll();
		$context = $this->load->view('admin/accout', array('header'=>$header,'data' => $data), true);
		$this->load->view('admin/share_layout', array('context' => $context));
	}
	private function Login(){
		//session_destroy();
		if(!isset($_SESSION['id_admin'])){
			redirect("admin_login/login");
		}
		
	}
	public function ViewCart(){
		$this->Login();
		$header = "Khu Vực";
		$context = $this->load->view('admin/area', array('header'=>$header), true);
		$this->load->view('admin/share_layout', array('context' => $context));
	}
	public function CustomerAccout(){
		$this->Login();
		$header = "Danh Sách Khách Hàng";
		$this->load->model("Customer");
		$data = $this->Customer->getAll();
		$context = $this->load->view('admin/accoutCustomer', array('header'=>$header,'data'=>$data), true);
		$this->load->view('admin/share_layout', array('context' => $context));
	}
	public function Finish($id){
		$this->Login();
		$this->load->model("Order");
		$this->Order->Xong($id);
		redirect("admin/Order");
	}
}
