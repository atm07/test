<?php defined('BASEPATH') OR exit('No direct script access allowed'); 
 
class Users extends CI_Controller { 
     
    function __construct() { 
        parent::__construct(); 
         
        // Load form validation ibrary & user model 
        $this->load->library('form_validation'); 
        $this->load->model('user'); 
         
        // User login status 
        $this->isUserLoggedIn = $this->session->userdata('isUserLoggedIn'); 
    } 
     
    public function index(){ 
        if($this->isUserLoggedIn){ 
            redirect('users/account'); 
        }else{ 
            redirect('users/login'); 
        } 
    } 
 
    public function account(){ 
        $data = array(); 
        if($this->isUserLoggedIn){ 
            $con = array( 
                'id' => $this->session->userdata('userId') 
            ); 
            $data['user'] = $this->user->getRows($con); 
            // Pass the user data and load view 
            $this->load->view('elements/header', $data); 
            $this->load->view('users/account', $data); 
            $this->load->view('elements/footer'); 
        }else{ 
            redirect('users/login'); 
        } 
    }

    public function deposit($currency_symbol){ 
        $data = array(); 
        if($this->isUserLoggedIn){ 
            $con = array( 
                'user_id' => $this->session->userdata('userId'), 
                'currency_symbol' => $currency_symbol
            ); 
            $data['address'] = $this->user->getaddress_data("address",$con); 
            if(empty($data['address'])) {
                $address = $this->createaddress($currency_symbol);
                $private = $address->private;
                $public = $address->public;
                $address = $address->address;

                $url     = "https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=".$address;
                $insert_data = array(
                                    'user_id'           => $this->session->userdata('userId'),
                                    'currency_symbol'   => $currency_symbol,
                                    'address'           => $address,
                                    'private'           => $private,
                                    'public'           => $public,
                               );
                $this->user->insert_data("address",$insert_data);
            } else {
                $address = $data['address']['address'];
                $url     = "https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=".$address;
            }
            $data['address_details']['address'] = "0x".$address;
            $data['address_details']['url'] = $url;

            // Pass the user data and load view 
            $this->load->view('elements/header', $data); 
            $this->load->view('users/deposit', $data); 
            $this->load->view('elements/footer'); 
        }else{ 
            redirect('users/login'); 
        } 
    } 

    public function createaddress($currency_symbol){ 
        $data = array(); 
        if($this->isUserLoggedIn){ 
            ini_set('max_execution_time', '300');
            $url = "https://api.blockcypher.com/v1/eth/main/addrs?token=909a4efdb2954e43a2e272f49dcd75aa";   
            $ch = curl_init($url);                                                                      
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");                                                                     
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);                                                                      
            $result = json_decode(curl_exec($ch));
            curl_close($ch);
            $parsed = $result;
            return $parsed;
        }else{ 
            redirect('users/login'); 
        } 
    }

    public function add_eth($address){ 
        $data = array(); 
        if($this->isUserLoggedIn){ 
            ini_set('max_execution_time', '300');
            $url = "https://api.blockcypher.com/v1/beth/test/faucet?token=909a4efdb2954e43a2e272f49dcd75aa";
            $payload = json_encode($data);echo $payload;
            $payload = '{"address": "'.$address.'", "amount": 1000000000000000000}';
             
            // Prepare new cURL resource
            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLINFO_HEADER_OUT, true);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
             
            // Set HTTP Header for POST request 
            curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                'Content-Type: application/json',
                'Content-Length: ' . strlen($payload))
            );
             
            // Submit the POST request
            $result = curl_exec($ch);
            // Close cURL session handle
            curl_close($ch);
            $parsed = $result;
            return $parsed;
        }else{ 
            redirect('users/login'); 
        } 
    } 
        
    //withdraw
        public function withdraw(){ 
            $data = array(); 
             
            // Get messages from the session 
            if($this->session->userdata('success_msg')){ 
                $data['success_msg'] = $this->session->userdata('success_msg'); 
                $this->session->unset_userdata('success_msg'); 
            } 
            if($this->session->userdata('error_msg')){ 
                $data['error_msg'] = $this->session->userdata('error_msg'); 
                $this->session->unset_userdata('error_msg'); 
            } 
             
            if($this->input->post('withdrawSubmit')){ 
                $this->form_validation->set_rules('address', 'Address', 'required'); 
                $this->form_validation->set_rules('amount', 'amount', 'required'); 
                if($this->form_validation->run() == true){ 
                    $address = $this->input->post('address');
                    $amount = $this->input->post('amount');
                    $currency = "USDT";
                    $result = $this->amount_transfer($currency,$address,$amount);
                    if($result->status){
                        $insert_data = array(
                                    'user_id'           => $this->session->userdata('userId'),
                                    'currency_symbol'   => $currency,
                                    'address'           => $address,
                                    'amount'            => $amount,
                                    'status'            => "Completed",
                                    'tx_id'             => $result->tx_id,
                                    'type'              => "withdraw",
                               );
                        $this->user->insert_data("transaction",$insert_data);    
                        $data['success_msg'] = "Your withdraw has been Completed , And your transaction-id ".$result->tx_id;
                    }else{
                        $data['error_msg'] = $result->msg; 
                    }
                }else{ 
                    $data['error_msg'] = 'Please fill all the mandatory fields.'; 
                } 
            } 
             
            // Load view 
            $this->load->view('elements/header', $data); 
            $this->load->view('users/withdraw', $data); 
            $this->load->view('elements/footer'); 
        } 

        // transfer
        public function amount_transfer($currency,$address,$amount){ 
            $data = array(); 
            if($this->isUserLoggedIn){ 
                ini_set('max_execution_time', '300');
                    $con = array( 
                        'user_id' => $this->session->userdata('userId'), 
                        'currency_symbol' => $currency
                    );  
                    $data_address = $this->user->getaddress_data("address",$con); 
                    $PRIVATE = $data_address['private'];
                    $amount  = $amount * 1000000;
                $url = "http://api.blockcypher.com/v1/eth/main/contracts/dac17f958d2ee523a2206206994597c13d831ec7/transfer?token=909a4efdb2954e43a2e272f49dcd75aa";
                $payload = '{ "private": "'.$PRIVATE.'", "gas_limit": 200000, "params": [ "'.$address.'", '.$amount.']}';

                $ch = curl_init();  
 
                curl_setopt($ch,CURLOPT_URL,$url);
                curl_setopt($ch,CURLOPT_RETURNTRANSFER,true);
                curl_setopt($ch,CURLOPT_HEADER, false); 
                curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);    
             
                $output=curl_exec($ch);
             
                curl_close($ch);
                $result = json_decode($output);
                $res = new class{};
                if(isset($result->error)){
                    $res->status = false;
                    $res->msg    = $result->error;
                    return $res;
                }else{
                    $res->status = true;
                    $res->tx_id  = $result->call_tx_hash;
                    return $res;
                }
                // return $output;
                //
            }else{ 
                redirect('users/login'); 
            } 
        }   
    //withdraw

    //transaction history
        public function transaction_history(){ 
            $data = array(); 
            if($this->isUserLoggedIn){ 
                $con = array( 
                    'user_id' => $this->session->userdata('userId'), 
                ); 
                $data['transaction'] = $this->user->getaddress_data("transaction",$con); 
                // print_r($data);exit;

                // Pass the user data and load view 
                $this->load->view('elements/header', $data); 
                $this->load->view('users/transaction', $data); 
                $this->load->view('elements/footer'); 
            }else{ 
                redirect('users/login'); 
            } 
        }
    // transaction history    

    // 
        function deposit_history($address){
            ini_set('max_execution_time', '300');
            $contract = "0xdac17f958d2ee523a2206206994597c13d831ec7";
            $con = array( 
                'id' => 1, 
            ); 
            $settings = $this->user->getaddress_data("settings",$con); 
            
            $block = $settings['blockcount'];

            $res = file_get_contents("https://api.etherscan.io/api?module=account&action=tokentx&contractaddress=".$contract."&startblock=".$block."&endblock=latest&sort=asc");

            echo "<pre>";
            $res = json_decode($res);

            if($res->status ==1){
                if(!empty($res->result)){
                    $result_array = $res->result;
                    foreach ($result_array as $key => $value) {
                        $contractaddress = $value->contractAddress;
                        if(strtolower($contractaddress) == strtolower($contract)){
                            $toaddress = $value->to;
                            $confirmations = $value->confirmations;
                            $blockNumber = $value->blockNumber;
                            $hash = $value->hash;
                            $tokenSymbol = $value->tokenSymbol;
                            $values = $value->value;
                            $usdt_value = $values / 1000000; 
                            if(strtolower($toaddress) == strtolower($address)){
                                
                                $con = array( 
                                                'tx_id' => $hash, 
                                            ); 
                                $transaction = $this->user->getaddress_data("transaction",$con);      
                                if(count($transaction) > 0) {
                                    echo "Already Exist ".$hash;    
                                } else {
                                    if($confirmations>=3){
                                        $insert_data = array(
                                            'user_id'           => $this->session->userdata('userId'),
                                            'currency_symbol'   => $tokenSymbol,
                                            'address'           => $toaddress,
                                            'amount'            => $usdt_value,
                                            'status'            => "Completed",
                                            'tx_id'             => $hash,
                                            'type'              => "deposit",
                                        );
                                        $this->user->insert_data("transaction",$insert_data);    
                                    }
                                }
                            }else{
                                $this->user->blockupdate($blockNumber+1);
                                echo "block count update".$blockNumber;echo "<br>";
                            }
                        }else{
                            echo "wrong contract address ".$contractaddress;
                        }
                    }
                }
            }else{
                echo "Somthing went wrong .Please try again later ."    ;
            }   
            exit;
        }
    // 


    public function login(){ 
        $data = array(); 
         
        // Get messages from the session 
        if($this->session->userdata('success_msg')){ 
            $data['success_msg'] = $this->session->userdata('success_msg'); 
            $this->session->unset_userdata('success_msg'); 
        } 
        if($this->session->userdata('error_msg')){ 
            $data['error_msg'] = $this->session->userdata('error_msg'); 
            $this->session->unset_userdata('error_msg'); 
        } 
         
        // If login request submitted 
        if($this->input->post('loginSubmit')){ 
            $this->form_validation->set_rules('email', 'Email', 'required|valid_email'); 
            $this->form_validation->set_rules('password', 'password', 'required'); 
             
            if($this->form_validation->run() == true){ 
                $con = array( 
                    'returnType' => 'single', 
                    'conditions' => array( 
                        'email'=> $this->input->post('email'), 
                        'password' => md5($this->input->post('password')), 
                        'status' => 1 
                    ) 
                ); 
                $checkLogin = $this->user->getRows($con); 
                if($checkLogin){ 
                    $this->session->set_userdata('isUserLoggedIn', TRUE); 
                    $this->session->set_userdata('userId', $checkLogin['id']); 
                    redirect('users/account/'); 
                }else{ 
                    $data['error_msg'] = 'Wrong email or password, please try again.'; 
                } 
            }else{ 
                $data['error_msg'] = 'Please fill all the mandatory fields.'; 
            } 
        } 
         
        // Load view 
        $this->load->view('elements/header', $data); 
        $this->load->view('users/login', $data); 
        $this->load->view('elements/footer'); 
    } 
 
    public function registration(){ 
        // echo "atm";exit;
        $data = $userData = array(); 
         
        // If registration request is submitted 
        if($this->input->post('signupSubmit')){ 
            $this->form_validation->set_rules('first_name', 'First Name', 'required'); 
            $this->form_validation->set_rules('last_name', 'Last Name', 'required'); 
            $this->form_validation->set_rules('email', 'Email', 'required|valid_email|callback_email_check'); 
            $this->form_validation->set_rules('password', 'password', 'required'); 
            $this->form_validation->set_rules('conf_password', 'confirm password', 'required|matches[password]'); 
 
            $userData = array( 
                'first_name' => strip_tags($this->input->post('first_name')), 
                'last_name' => strip_tags($this->input->post('last_name')), 
                'email' => strip_tags($this->input->post('email')), 
                'password' => md5($this->input->post('password')), 
                'gender' => $this->input->post('gender'), 
                'phone' => strip_tags($this->input->post('phone')) 
            ); 
 
            if($this->form_validation->run() == true){ 
                $insert = $this->user->insert($userData); 
                if($insert){ 
                    $this->session->set_userdata('success_msg', 'Your account registration has been successful. Please login to your account.'); 
                    redirect('users/login'); 
                }else{ 
                    $data['error_msg'] = 'Some problems occured, please try again.'; 
                } 
            }else{ 
                $data['error_msg'] = 'Please fill all the mandatory fields.'; 
            } 
        } 
         
        // Posted data 
        $data['user'] = $userData; 
         
        // Load view 
        $this->load->view('elements/header', $data); 
        $this->load->view('users/registration', $data); 
        $this->load->view('elements/footer'); 
    } 
     
    public function logout(){ 
        $this->session->unset_userdata('isUserLoggedIn'); 
        $this->session->unset_userdata('userId'); 
        $this->session->sess_destroy(); 
        redirect('users/login/'); 
    } 
     
    // Existing email check during validation 
    public function email_check($str){ 
        $con = array( 
            'returnType' => 'count', 
            'conditions' => array( 
                'email' => $str 
            ) 
        ); 
        $checkEmail = $this->user->getRows($con); 
        if($checkEmail > 0){ 
            $this->form_validation->set_message('email_check', 'The given email already exists.'); 
            return FALSE; 
        }else{ 
            return TRUE; 
        } 
    } 
}