<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Main extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('Cart');
        $this->load->model('Customer');
    }
    public function index()
    {
        $data = $this->Type->getAll();
        $products = $this->Products->getAll();
        $tittel = "Trang Chủ";
        $count = 0;
        $newProducts = array();
        for ($i = $this->Products->getNumRow() - 1; $i >= 0; $i--) {
            if ($count < 5 && $products[$i]->hide ==0) {
                $newProducts[$count] = $products[$i];
                $count++;
            } else {
                break;
            }
        }
        $context = $this->load->view('trangchu', array('newProducts' => $newProducts), true);
        $this->load->view("layout_share", array('type' => $data, 'context' => $context, 'tittel' => $tittel));
    }

    public function product_list($type, $page)
    {
        $data = $this->Type->getAll();
        $dataProduct = $this->Products->getByIDType($type);
        $numRow = $dataProduct['num_rows'];
        $products = $dataProduct['data'];
        $numpage = 0;
        for ($i = 0; $i < ($numRow / 32); $i++) {
            $numpage++;
        }
        $count = (32 * $page) - 32;
        $arrayProduct = array();
        if ($count >= 0 && $numRow > 0) {
            for ($count; $count < 32 * $page; $count++) {
                try {
                    if ($count < $numRow) {
                        $arrayProduct[] = $products[$count];
                    } else {
                        throw new Exception("Error Processing Request", 1);
                    }
                } catch (Exception  $th) {
                    break;
                }
            }
        }
        $tittel = "Sản phẩm";
        $context = $this->load->view('listProducts', array('numPage' => $numpage,'pageType'=>$type, 'products' => $arrayProduct), true);
        $this->load->view("layout_share", array('type' => $data, 'context' => $context, 'tittel' => $tittel));
    }

    public function ChiTiet()
    {
        $data = $this->Type->getAll();
        if (isset($_GET["id"])) {
            $id = $_GET["id"];
            if ($this->Products->checkByID($id)) {
                $dataP = $this->Products->getByID($id);
                $context = $this->load->view('product', array('product' => $dataP), true);
            } else {
                $context = "<h1>Sản phẩm không tồn tại</h1>";
            }
        } else {
            $context = "<h1>Sản phẩm không tồn tại</h1>";
        }
        $tittel = "Chi Tiết Sản phẩm";
        $this->load->view("layout_share", array('type' => $data, 'context' => $context, 'tittel' => $tittel));
    }
    public function Register()
    {
        $this->load->model('Customer');
        $user = $this->input->post('username');
        $password = $this->input->post('password');
        $email = $this->input->post('email');
        $this->Customer->Register($user, $password, $email);

        //gửi mail xác nhận
        $data = array(
            "name" => $user,
            "key" => $this->Customer->getID($user, $password)->ID_User
        );
        $context = $this->load->view("form_mail/sendConfirm", $data, true);
        $config = array();
        $config['protocol'] = 'smtp';
        $config['smtp_host'] = 'ssl://smtp.googlemail.com';
        //$config['smtp_host'] = 'tls://smtp.googlemail.com';
        $config['smtp_user'] = 'khoado29k11@viendong.edu.vn';
        $config['smtp_pass'] = 'khoa958632147';
        $config['smtp_port'] = 465;
        //$config['smtp_port'] = 579;
        $config['mailtype']  = 'html';
        $config['starttls']  = true;
        $config['newline']   = "\r\n";
        $this->load->library('email', $config);
        $this->email->initialize($config);
        $this->email->from("khoado29k11@viendong.edu.vn", 'Chúa tể hội đồng quản trị');
        $this->email->to($email);
        // $this->email->to("violent12340@gmail.com");
        $this->email->subject("Xác nhận tài khoản");
        $this->email->message($context);
        $this->email->send();

        //redirect("main/index");
        $this->onLogin($user, $password);
    }
    public function AddOrder()
    {
        $this->load->model('Order');
        $OnSellDate = $this->input->post('OnSellDate');
        $ID_User = $this->input->post('ID_User');
        $this->Order->Insert($OnSellDate, $ID_User);
    }
    public function Signout()
    {
        $this->session->unset_userdata('id');
        $this->session->unset_userdata('name');
        // redirect("main/index");
        redirect($_SERVER['HTTP_REFERER']);
    }
    function onLogin($username, $password)
    {
        $this->load->model('Customer');
        if ($this->Customer->Login($username, $password)) {
            $data = $this->Customer->getID($username, $password);
            $arrayName = array('id' => $data->ID_User, 'name' => $data->USER);
            $this->session->set_userdata($arrayName);
            // redirect("main/index");
            redirect($_SERVER['HTTP_REFERER']);
        } else {
            // redirect("main/index");
            redirect($_SERVER['HTTP_REFERER']);
        }
    }
    public function Login()
    {
        $this->load->model('Customer');
        $username = $this->input->post('username');
        // $password_1 = $this->input->post('password');
        $password_1 = $_POST['password'];
        if ($this->Customer->Login($username, $password_1)) {
            $data = $this->Customer->getID($username, $password_1);
            $arrayName = array('id' => $data->ID_User, 'name' => $data->USER);
            $this->session->set_userdata($arrayName);
            //redirect("main/index");
            redirect($_SERVER['HTTP_REFERER']);
        } else {
            // redirect("admin/index");
            redirect($_SERVER['HTTP_REFERER']);
        }
    }
    public function CheckOut()
    {
        $this->load->model("Customer");

        $this->load->model('Country');
        $tittel = "Thanh Toán";
        $data = $this->Type->getAll();
        if (isset($_SESSION['id'])) {
            $data_customer = $this->Customer->getDataByID($_SESSION['id']);
            $cart = $this->Cart->getAllByIDUser($_SESSION['id']);
            $city = $this->Country->Province();
            if ($cart != null) {
                $context = $this->load->view('product-checkout', array("data" => $data_customer, 'cart' => $cart, 'city' => $city), true);
            } else {
                $context = $this->load->view('notification/notif_nullCart', "", true);
            }
        } else {
            $context = $this->load->view('notification/notif_chuaLogin', "", true);
        }
        $this->load->view("layout_share", array('type' => $data, 'context' => $context, 'tittel' => $tittel, 'cmd' => 'hide_banner'));
    }
    public function about()
    {
        $data = $this->Type->getAll();
        $context = $this->load->view('about', '', true);
        $tittel = "Thông Tin";
        $this->load->view("layout_share", array('type' => $data, 'context' => $context, 'tittel' => $tittel));
    }
    public function resetPassword()
    {
        $tittel = "Quên mật khẩu";
        $data = $this->Type->getAll();
        $context = $this->load->view("notification/notif_resetPassword", '', true);
        $this->load->view("layout_share", array('type' => $data, 'context' => $context, 'tittel' => $tittel, 'cmd' => 'hide_banner'));
    }
    public function ViewCart()
    {
        if (isset($_SESSION['id'])) {
            $data = $this->Type->getAll();
            $cart = $this->Cart->getAllByIDUser($_SESSION['id']);
            if ($cart != null) {
                $context = $this->load->view('cart', array("data" => $cart), true);
            } else {
                $context = $this->load->view('notification/notif_nullCart', array("data" => $cart), true);
            }

            $tittel = "Chi tiết giỏ hàng";
            $this->load->view("layout_share", array('type' => $data, 'context' => $context, 'tittel' => $tittel, 'cmd' => 'hide_banner'));
        } else {
            redirect("main/index");
        }
    }
    //đăng nhập bên thứ 3 is google
    public function gg_Login()
    {
        if (isset($_POST['gg_id']) && isset($_POST['email']) && isset($_POST['name'])) {
            $gg_id = $_POST['gg_id'];
            $email =  $_POST['email'];
            $name = $_POST['name'];
            $password = "google";
            $this->Customer->gg_register($gg_id, $password, $email, $name);
            //$this->onLogin($gg_id, md5($password));
            $data = $this->Customer->gg_checkLogin( $email);
            if($data['response']== "true"){
                $arrayName = array('id' => $data['data']->ID_User, 'name' => $data['data']->NAME);
                $this->session->set_userdata($arrayName);
                echo "ok";
            }else{
                echo "404";
            }
        }else{
            echo "404";
        }
    }
    public function Search(){
        if(isset($_POST['tim_kiem'])){
            $products = $this->Products->Search($_POST['tim_kiem']);
        }else{
            $products = $this->Products->Search("");
        }
        $data = $this->Type->getAll();
        $context = $this->load->view('Search', array('isSearch'=>"true", "products"=>$products), true);
        $tittel = "Tìm Kiếm";
        $this->load->view("layout_share", array('type' => $data, 'context' => $context, 'tittel' => $tittel));
    }
}
