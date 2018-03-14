<?php

class MakePayment extends CI_Controller {

    function __construct() {
        parent::__construct();
        // Load helpers
        $this->load->helper('url');
    }

    public function index() {

        //testing code
        $now = new DateTime();
        $now = $now->format('Y-m-d H:i:s');

        $message = "\n ### -- CURL is running fine -- : ### " . $now;
        $fp = fopen('testing.log', 'a');
        fwrite($fp, $message);
        fclose($fp);
    }

    public function queryOne($oderId) {
        print_r("test");
        $message = "\r\n in queryOne function \r\n";
//        $fp = fopen('easypay_test.log', 'a');
//        fwrite($fp, $message1);
//        fclose($fp);
//        print_r("oderId : " . $oderId);

        $type = "";
        $forOrderDetails = $this->db->query("SELECT * FROM `transaction_log` WHERE order_unique_id = '" . $oderId . "' ");

        if ($forOrderDetails->num_rows() > 0) {
            $message .= "\n queryOneData : Found\n";

            $orderDetails = $forOrderDetails->result_array()[0];
            $type = $orderDetails['event'];
//                    $orderDetails = $forPackageID->result_array()[0];

            $message .= "\n\n" . print_r($orderDetails, TRUE);
            $message .= "\nType : " . $type . "\n";
//                    $message .= "\nBrandId : " . $brandID . "\n";
//            $transactionId = $data->transaction_id;
            $transactionId = 1000012;

//                    $paykey = strtolower($data->order_id);
            $paykey = $oderId;

            if ($type == 'subscription') {
                $message .= "\nCategory : subscription";

                $planId = $orderDetails['plan_id'];
                $entityId = $orderDetails['sender_entity_id'];

//                        $forBrandUserID = $this->db->query("SELECT bu.branduserid FROM brand_users AS bu "
//                                . "LEFT JOIN brand_users_roles AS bur ON bur.user_id = bu.branduserid "
//                                . "WHERE bu.`brand-id` = " . $brandId . " AND bur.role_id = 1");
//                        $brandUserId = 0;
//                        if ($forBrandUserID->num_rows() > 0) {
//                            $brandUserId = $forBrandUserID->result_array()[0]['branduserid'];
//                        }

                $this->load->model('M_subscription');
                $qrySubPay = $this->db->query("select * from entities_subscription_payment where paykey = '" . $paykey . "'");
                if ($qrySubPay->num_rows() < 1) {

                    $fp = fopen('easypay_test.log', 'a');
                    fwrite($fp, $message);
                    fclose($fp);
//                    update pakage without session for easypay
//                            $upResult = $this->M_subscription->updatePackageWithoutSession($pId, $ppId, $type, $brandId, $brandUserId, $paykey);
//                            
//                    $upResult = $this->M_subscription->updateSubscription($entityId, $planId, "paid", $formFields, $transactionId, $payKey);
//                    
//                        $upResult = $this->M_brand_package->updatePackageWithoutSession($ppId, $type, $brandId, $paykey);
                    $upResult = 'true';
                    $message = "\nPackage-Update : QUERY - ";
                    if ($upResult == 'true') {
                        $message .= "TRUE\n";
                    }
//                    
                    else {
                        $message .= "FALSE\n";
                    }

                    $message .= "\n//===========END===============//\n";
                    $fp = fopen('easypay_test.log', 'a');
                    fwrite($fp, $message);
                    fclose($fp);
//                    $redirectUri = base_url() . 'brandPackageController/brand_package_payment/1/' . $brandId . '/' . $brandUserId . '/' . $pId . '/' . $ppId . '/' . $data->transaction_amount . '/0/' . $paykey;
//                    redirect($redirectUri);
                }
//                
                else {
                    $message .= "\nPackage-Paykey : Exsist";
                    $message .= "\n//===========END===============//\n";
                    $fp = fopen('easypay_test.log', 'a');
                    fwrite($fp, $message);
                    fclose($fp);
                }
            }
//            
            else if ($type == 'campaign') {

                $message .= "\nCategory : Campaign";

                $senderId = $orderDetails['sender_entity_id'];
                $infUserId = $orderDetails['inf_user_id'];
                $recieverId = $orderDetails['receiver_entity_id'];
                $behalfOfRecieverId = $orderDetails['behalf_of_receiver_entity_id'];
                $campId = $orderDetails['campaign_id'];

//                        $sx_invoice = uniqid("sx", TRUE);
//                        $inf_invoice = uniqid("in", TRUE);
//                        $conversion = $this->CurrencyConverter->convert('PKR', 'USD', $data->transaction_amount, 1, 1);
//                        $socialx_charges = $conversion * SWCOMM / 100;
//                        making data 
                $message .= "\nmaking data for entries...\n";

                $data1['sender_entity_id'] = $senderId;
                $data1['campaign_id'] = $campId;
//                        $recieverId
                $data1['receiver_entity_id'] = ($behalfOfRecieverId == NULL || $behalfOfRecieverId == "" ? $recieverId : $behalfOfRecieverId);
//                        $behalfOfRecieverId
                $data1['behalf_of_receiver_entity_id'] = ($behalfOfRecieverId == null || $behalfOfRecieverId == "" ? null : $recieverId);
//                        $data1['paykey'] = 0;
//                        $data1['receiver_invoice'] = 0;
                $date = date_create();
                $data1['transaction_date'] = date_timestamp_get($date);
                $data1['transaction_status'] = "success";
                $data1['transaction_type'] = "auto_paid";
//                        $data1['transaction_type'] = date("Y-m-d H:i:s");
                $data1['amount_paid'] = 100;
//                        $data1['setup_currency_id'] = NULL;
                $data1['transaction_id'] = $transactionId;
                $data1['row_status'] = 'active';
//                        
//                        
                $data2['campaign_payment_status'] = "ask_release_scxn";
//                        $data2['campaign_payment_status'] = "escrow_paid_scxn";

                $message .= "\ntransactions begins...\n";
                $this->db->trans_begin();

                $this->db->insert('campaigns_transactions', $data1);


                $array2 = array('campaign_id' => $campId, 'user_id' => $infUserId, 'row_status' => 'active');
                $this->db->where($array2);
                $this->db->update('campaigns_offered_influencers', $data2);

                if ($this->db->trans_status() === FALSE) {
                    $this->db->trans_rollback();
                    $message .= "\nstatus : transaction rollback...\n";
//                            echo 'false';
                } else {
                    $this->db->trans_commit();
                    $message .= "\nstatus : transaction commit...\n";
                }


//                        $message .= "\nState < 1 : ";
//                        if ($state < 1) {
//                            $message .= "TRUE";
//                            $query = $this->db->query("select * from camp where `campid` = " . $campId . " AND `brandid` = '" . $brandID . "'");
//                            $array = $query->result_array();
//                            $message .= "\nCampaign    : FETCH - ";
//                            $brandUserId = NULL;
//                            if (count($array) > 0) {
//                                $message .= "TRUE";
//                                $brandUserId = $array[0]['brand_user_id'];
//                            } else {
//                                $message .= "FALSE";
//                            }
//
//                            $message .= "\nNotification : ";
//                            if ($brandUserId != NULL) {
//                                $message .= "YES";
//                                $message .= "\nInfluencer : ";
//                                $InResult = $this->db->insert('influencer_notifications', [
//                                    'type' => '2',
//                                    'state' => '0',
//                                    'datetime' => date("Y-m-d H:i:s"),
//                                    'generated_by' => $brandUserId,
//                                    'receiver' => $infId,
//                                    'campid' => $campId
//                                ]);
//
//                                if ($InResult) {
//                                    $message .= "YES";
//                                } else {
//                                    $message .= "NO";
//                                }
//                                $message .= "\nBrand      : ";
//                                $BnResult = $this->db->insert('brand_notifications', [
//                                    'type' => '2',
//                                    'state' => '0',
//                                    'datetime' => date("Y-m-d H:i:s"),
//                                    'generated_by' => $infId,
//                                    'receiver' => $brandUserId,
//                                    'campid' => $campId
//                                ]);
//                                if ($BnResult) {
//                                    $message .= "YES";
//                                } else {
//                                    $message .= "NO";
//                                }
//                            } else {
//                                $message .= "NO";
//                            }
//
//                            //Used for reducing number of campaigns
//                            $this->load->model('M_campaign_management');
//                            $reduceCampaignResult = $this->M_campaign_management->reduceCampaignCount($brandID, $campId);
//                            $message .= "\nREDUCE CAMP  : QUERY  - ";
//                            if ($reduceCampaignResult == TRUE) {
//                                $message .= "TRUE";
//                            } else {
//                                $message .= "FALSE";
//                            }
//
//                            $message .= "\n\nPAYKEY: " . $paykey;
//
//                            $sx_invoice = uniqid("sx", TRUE);
//                            $inf_invoice = uniqid("in", TRUE);
//                            $conversion = $this->CurrencyConverter->convert('PKR', 'USD', $data->transaction_amount, 1, 1);
//
//                            $socialx_charges = $conversion * SWCOMM / 100;
//
//                            $message .= "\n\nConversion: " . $conversion;
//                            $message .= "\n\nSocialCXNCharges: " . $socialx_charges;
//
//                            $qryPay = $this->db->query("select * from transactions where influencerid = '" . $infId . "' and brandid = '" . $brandID . "' and campid = '" . $campId . "'");
//                            $message .= "\nTransaction : FETCH 1 - ";
//                            if ($qryPay->num_rows() > 0) {
//                                $message .= "TRUE";
//
//                                $result = $this->db->query("update transactions set date_time = '" . date("Y-m-d H:i:s") . "', amount = '" . $conversion . "', paykey = '" . $paykey . "', state = '1', sx_invoice = '" . $sx_invoice . "', inf_invoice = '" . $inf_invoice . "' "
//                                        . "where influencerid = '" . $infId . "' and brandid = '" . $brandID . "' and campid = '" . $campId . "'");
//                                $message .= "\nTransaction : UPDATE - ";
//                                if ($result > 0) {
//                                    $message .= "TRUE\n";
//                                } else {
//                                    $message .= "FALSE\n";
//                                }
//                            }
//                            $message .= "\nContributors : UPDATE - ";
//                            $cresult = $this->db->query("update contributors set paid = '1' where influencerid = '" . $infId . "' and campid='" . $campId . "'");
//
//                            if ($cresult > 0) {
//                                $message .= "TRUE\n";
//                            } else {
//                                $message .= "FALSE\n";
//                            }
//
//                            $sub_inf = 'Campaign (ID:{camp_id}) has been confirmed';
//                            $msg_inf = '<p>Dear {influencer_name},</p>
//				<p>Congratulations, your Campaign has been confirmed!</p>
//				<p>As the hired social media influencer, you may now begin working on the brands campaign. You will be paid once the campaign is completed and approved by the relevant brand.</p>
//				<p>' . SITE_TITLE . ' offers a wide range of brands for you to work with and monetize your social media influence!</p>
//				<p>Click below to begin working with your new Brand Partner instantly!</p>
//				<p><a href="' . base_url() . 'Login" style="font-size: 100%; line-height: 2; color: #ffffff; border-radius: 10px; display: inline-block; cursor: pointer; font-weight: bold; text-decoration: none; background: #348eda; margin: 0; padding: 8px 15px; border-color: #348eda; border-style: solid; border-radius: 25px; border-width: 2px;">Login</a></p>
//				<p>(' . base_url() . 'Login)</p>
//				<p style="font-size: 12px; line-height: 1.6em; font-weight: normal; color:#777; margin: 0 0 10px; padding: 0;">If you have problems, please paste the above URL into your web browser.</p>
//				<p>Best, </p><br>
//                                <p>' . SITE_TITLE . ' Team</p>
//                                    <p>Please feel free to email us at <a style="cursor: pointer;"> support@socialcxn.com </a>in case of any questions.</p>';
//
//
//                            $brandAdminEmails = checkRoleReturnEmails($brandUserId, $brandid);
////                    for admin
//                            if (isset($brandAdminEmails) && $brandAdminEmails != NULL && $brandAdminEmails == '1') {
//
//                                $sub_brand_admin = '';
//                                $msg_brand_admin = '';
////                        for admin msg                
//                                $sub_brand_user = 'Campaign (ID:{camp_id}) Payment Received';
//                                $msg_brand_user = '<p>Dear {brand_name},</p>
//				<p>Thank you for submitting your payment. Your campaign is now active.</p>
//                        <p>Click below to track, analyze and stay updated with your campaign.</p>
//                        <p><a href="' . base_url() . 'PageController/loginForm" style="font-size: 100%; line-height: 2; color: #ffffff; border-radius: 10px; display: inline-block; cursor: pointer; font-weight: bold; text-decoration: none; background: #348eda; margin: 0; padding: 8px 15px; border-color: #348eda; border-style: solid; border-radius: 25px; border-width: 2px;">Login</a></p>
//                        <p>(' . base_url() . 'PageController/loginForm)</p>
//                        <p style="font-size: 12px; line-height: 1.6em; font-weight: normal; color:#777; margin: 0 0 10px; padding: 0;">If you are facing any problem in accessing the above link, you may paste the above URL into your web browser.</p>
//                        <p>Best - ' . SITE_TITLE . ' Team</p>
//                            <p>Please feel free to email us at <a style="cursor: pointer;"> support@socialcxn.com </a>in case of any questions.</p>';
//                            }
////                    for user            
//                            else {
////                        for admin msg			
//                                $sub_brand_admin = 'Campaign (ID:{camp_id}) Payment Received';
//                                $msg_brand_admin = '<p>Dear {brand_name},</p>
//				<p>Thank you for submitting your payment. Your campaign is now active.</p>
//				<p>Click below to track, analyze and stay updated with your campaign.</p>
//				<p><a href="' . base_url() . 'PageController/loginForm" style="font-size: 100%; line-height: 2; color: #ffffff; border-radius: 10px; display: inline-block; cursor: pointer; font-weight: bold; text-decoration: none; background: #348eda; margin: 0; padding: 8px 15px; border-color: #348eda; border-style: solid; border-radius: 25px; border-width: 2px;">Login</a></p>
//				<p>(' . base_url() . 'PageController/loginForm)</p>
//				<p style="font-size: 12px; line-height: 1.6em; font-weight: normal; color:#777; margin: 0 0 10px; padding: 0;">If you have problems, please paste the above URL into your web browser.</p>
//				<p>Best - ' . SITE_TITLE . ' Team</p>
//                                    <p>Please feel free to email us at <a style="cursor: pointer;"> support@socialcxn.com </a>in case of any questions.</p>';
////                        for user msg                
//                                $sub_brand_user = 'Campaign (ID:{camp_id}) Payment Received';
//                                $msg_brand_user = '<p>Dear {brand_name},</p>
//				<p>Thank you for submitting your payment. Your campaign is now active.</p>
//				<p>Click below to track, analyze and stay updated with your campaign.</p>
//				<p><a href="' . base_url() . 'PageController/loginForm" styAle="font-size: 100%; line-height: 2; color: #ffffff; border-radius: 10px; display: inline-block; cursor: pointer; font-weight: bold; text-decoration: none; background: #348eda; margin: 0; padding: 8px 15px; border-color: #348eda; border-style: solid; border-radius: 25px; border-width: 2px;">Login</a></p>
//				<p>(' . base_url() . 'PageController/loginForm)</p>
//				<p style="font-size: 12px; line-height: 1.6em; font-weight: normal; color:#777; margin: 0 0 10px; padding: 0;">If you have problems, please paste the above URL into your web browser.</p>
//				<p>Best - ' . SITE_TITLE . ' Team</p>
//                                    <p>Please feel free to email us at <a style="cursor: pointer;"> support@socialcxn.com </a>in case of any questions.</p>';
//                            }
//
//                            notificationEmail($infId, $brandUserId, $campId, $msg_inf, $msg_brand_user, $msg_brand_admin, $sub_inf, $sub_brand_user, $sub_brand_admin);
//                            $this->session->set_flashdata('success', 'Your payment for campaign has been recieved');
//                        } else {
//                            $message .= "FALSE\n";
//
//                            $this->session->set_flashdata('error', 'Your payment for campaign could not be processed.');
//                        }
//                for package payment
//                } else if ($type == 'DP') {
            }
//            
            else {
                $message .= "\nCategory : Not Found\n";
            }
        }
//        
        else {
            $message .= "\queryOneData : Not Found\n";
        }

//        $message = "test Easypay IPN\n";
        $fp = fopen('easypay_test.log', 'a');
        fwrite($fp, $message);
        fclose($fp);
    }

    function thankyou_payment($campid) {
        "Thankyou";
//        redirect(base_url() . 'campaign?a=show&paypal_payment=success&id=' . $campid);
    }

    function easypay_callback() {
        $this->load->library('session');
        $data['ep_live'] = $this->config->item('ep_live');
        $message = "\n//==========START==============//\n";
        $message .= "\n==========Callback=============\n";

        $data['easypayConfirmPage'] = '';
        if ($data['ep_live'] == 'no') {
            $data['easypayConfirmPage'] = 'https://easypaystg.easypaisa.com.pk/easypay/Confirm.jsf';
            $message .= "\nMode : Sandbox\n";
        } else {
            $data['easypayConfirmPage'] = 'https://easypay.easypaisa.com.pk/easypay/Confirm.jsf';
            $message .= "\nMode : Live\n";
        }

        $fp = fopen('easypay_test.log', 'a');
        fwrite($fp, $message);
        fclose($fp);

        $data['merchantStatusPage'] = base_url() . 'makePayment/easypay';

        $this->load->view('easypay_callback', $data);
    }

    function easypay() {
        $this->load->library('session');
        $data = $this->input->get();

        $message = "\n==========Easypay==============\n";
        $message .="\n\nEasypay Data- " . print_r($data, TRUE);

        $message .= "\n\n//===========EasyPay END===============//\n";

        $fp = fopen('easypay_test.log', 'a');
        fwrite($fp, $message);
        fclose($fp);

//      eg: redirct url after transaction for staging http://skillorbit.co/socialcxn_revamp/staging/app/dist/#/home
//      eg: redirct url after transaction for live    https://socialcxn.com/#/home
        redirect($this->config->item('web_app_url') . $this->config->item('web_app_home'));
    }

    function easypayIPN() {
        $this->load->library('session');
        print_r("success");
        $data['ep_live'] = $this->config->item('ep_live');

        $message1 = "test Easypay IPN start\n";
        $fp = fopen('easypay_test.log', 'a');
        fwrite($fp, $message1);
        fclose($fp);

        $message = "\n========EasyPay IPN============\n";

        $curl = curl_init();
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_URL, $_GET["url"]);
        $output = curl_exec($curl);
        curl_close($curl);
        $message .= "\nURL : " . $_GET["url"] . "\n";

        $data = json_decode($output);

        $message .= "\nResponse Data_ : \n";
        $message .= "\n\n" . print_r($data, TRUE);

        $fp = fopen('easypay_test.log', 'a');
        fwrite($fp, $message);
        fclose($fp);

        if ($output != null) {
            $orderRefNumber = $_GET['url'];

            $message .= "\nOutput is Exist \n";
//            $fp = fopen('easypay_test.log', 'a');
//            fwrite($fp, $message);
//            fclose($fp);

            if ($data->response_code == 0000 && $data->transaction_status == 'PAID') {
                $message .= "\ntransaction status is Paid \n";
                $message .= "\nfetching from transaction log...\n";

                $forOrderDetails = $this->db->query("SELECT * FROM `transaction_log` WHERE order_unique_id = '" . $data->order_id . "' ");

                if ($forOrderDetails->num_rows() > 0) {
                    $message .= "\nqueryOneData : Found\n";

                    $orderDetails = $forOrderDetails->result_array()[0];
                    $tlId = $orderDetails['id'];
                    $type = $orderDetails['event'];
                    $tStatus = $orderDetails['status'];
//                    $orderDetails = $forPackageID->result_array()[0];

                    $message .= "\n\n" . print_r($orderDetails, TRUE);
                    $message .= "\nType : " . $type . "\n";
//                    $message .= "\nBrandId : " . $brandID . "\n";
                    $transactionId = $data->transaction_id;

//                    $paykey = strtolower($data->order_id);
                    $paykey = $data->order_id;

                    if ($type == 'subscription') {
                        $message .= "\nCategory : subscription";

                        $planId = $orderDetails['plan_id'];
                        $entityId = $orderDetails['seender_enity_id'];

//                        $forBrandUserID = $this->db->query("SELECT bu.branduserid FROM brand_users AS bu "
//                                . "LEFT JOIN brand_users_roles AS bur ON bur.user_id = bu.branduserid "
//                                . "WHERE bu.`brand-id` = " . $brandId . " AND bur.role_id = 1");
//                        $brandUserId = 0;
//                        if ($forBrandUserID->num_rows() > 0) {
//                            $brandUserId = $forBrandUserID->result_array()[0]['branduserid'];
//                        }

                        $this->load->model('M_subscription');
                        $qrySubPay = $this->db->query("select * from entities_subscription_payment where paykey = '" . $paykey . "'");
                        if ($qrySubPay->num_rows() < 1) {

                            $fp = fopen('easypay_test.log', 'a');
                            fwrite($fp, $message);
                            fclose($fp);
//                    update pakage without session for easypay
//                            $upResult = $this->M_subscription->updatePackageWithoutSession($pId, $ppId, $type, $brandId, $brandUserId, $paykey);
                            $upResult = $this->M_subscription->updateSubscription($entityId, $planId, "paid", $formFields, $transactionId, $payKey);
//                        $upResult = $this->M_brand_package->updatePackageWithoutSession($ppId, $type, $brandId, $paykey);
                            $message = "\nPackage-Update : QUERY - ";
                            if ($upResult == 'true') {
                                $message .= "TRUE\n";
                            } else {
                                $message .= "FALSE\n";
                            }

                            $message .= "\n//===========END===============//\n";
                            $fp = fopen('easypay_test.log', 'a');
                            fwrite($fp, $message);
                            fclose($fp);
                            $redirectUri = base_url() . 'brandPackageController/brand_package_payment/1/' . $brandId . '/' . $brandUserId . '/' . $pId . '/' . $ppId . '/' . $data->transaction_amount . '/0/' . $paykey;
                            redirect($redirectUri);
                        } else {
                            $message .= "\nPackage-Paykey : Exsist";
                            $message .= "\n//===========END===============//\n";
                            $fp = fopen('easypay_test.log', 'a');
                            fwrite($fp, $message);
                            fclose($fp);
                        }
                    }
//                    
                    else if ($type == 'campaign') {

                        $message .= "\nCategory : Campaign";

                        $senderId = $orderDetails['sender_entity_id'];
                        $infUserId = $orderDetails['inf_user_id'];
                        $recieverId = $orderDetails['receiver_entity_id'];
                        $behalfOfRecieverId = $orderDetails['behalf_of_receiver_entity_id'];
                        $campId = $orderDetails['campaign_id'];
                        $statusForUpdate = $orderDetails['status_for_update'];

//                        $sx_invoice = uniqid("sx", TRUE);
//                        $inf_invoice = uniqid("in", TRUE);
//                        $conversion = $this->CurrencyConverter->convert('PKR', 'USD', $data->transaction_amount, 1, 1);
//                        $socialx_charges = $conversion * SWCOMM / 100;

                        $message .= "\nTransaction Status in db : " . $tStatus . "\n";
                        if ($tStatus != 'success') {

//                        making data 
                            $message .= "\nmaking data for entries...\n";

                            $data1['sender_entity_id'] = $senderId;
                            $data1['campaign_id'] = $campId;
//                        $recieverId
                            $data1['receiver_entity_id'] = ($behalfOfRecieverId == NULL || $behalfOfRecieverId == "" ? $recieverId : $behalfOfRecieverId);
//                        $behalfOfRecieverId
                            $data1['behalf_of_receiver_entity_id'] = ($behalfOfRecieverId == null || $behalfOfRecieverId == "" ? null : $recieverId);
                            $data1['paykey'] = $paykey;
//                        $data1['receiver_invoice'] = 0;
                            $date = date_create();
                            $data1['transaction_date'] = date_timestamp_get($date);
                            $data1['transaction_status'] = "success";
                            $data1['transaction_type'] = "auto_paid";
//                        $data1['transaction_type'] = date("Y-m-d H:i:s");
                            $data1['amount_paid'] = $data->transaction_amount;
//                        $data1['setup_currency_id'] = NULL;
                            $data1['transaction_id'] = $transactionId;
                            $data1['row_status'] = 'active';
//                        
//                        
                            $data2['campaign_payment_status'] = $statusForUpdate;
//                        $data2['campaign_payment_status'] = "escrow_paid_scxn";

                            $data3['status'] = "success";

                            $message .= "\ntransactions begins...\n";
                            $this->db->trans_begin();

                            $this->db->insert('campaigns_transactions', $data1);


                            $array2 = array('campaign_id' => $campId, 'user_id' => $infUserId, 'row_status' => 'active');
                            $this->db->where($array2);
                            $this->db->update('campaigns_offered_influencers', $data2);

                            $array3 = array('id' => $tlId);
                            $this->db->where($array3);
                            $this->db->update('transaction_log', $data3);

                            if ($this->db->trans_status() === FALSE) {
                                $this->db->trans_rollback();
                                $message .= "\nstatus : transaction rollback...\n";
//                            echo 'false';
                            } else {
                                $this->db->trans_commit();
                                $message .= "\nstatus : transaction commit...\n";
                            }


//                        $message .= "\nState < 1 : ";
//                        if ($state < 1) {
//                            $message .= "TRUE";
//                            $query = $this->db->query("select * from camp where `campid` = " . $campId . " AND `brandid` = '" . $brandID . "'");
//                            $array = $query->result_array();
//                            $message .= "\nCampaign    : FETCH - ";
//                            $brandUserId = NULL;
//                            if (count($array) > 0) {
//                                $message .= "TRUE";
//                                $brandUserId = $array[0]['brand_user_id'];
//                            } else {
//                                $message .= "FALSE";
//                            }
//
//                            $message .= "\nNotification : ";
//                            if ($brandUserId != NULL) {
//                                $message .= "YES";
//                                $message .= "\nInfluencer : ";
//                                $InResult = $this->db->insert('influencer_notifications', [
//                                    'type' => '2',
//                                    'state' => '0',
//                                    'datetime' => date("Y-m-d H:i:s"),
//                                    'generated_by' => $brandUserId,
//                                    'receiver' => $infId,
//                                    'campid' => $campId
//                                ]);
//
//                                if ($InResult) {
//                                    $message .= "YES";
//                                } else {
//                                    $message .= "NO";
//                                }
//                                $message .= "\nBrand      : ";
//                                $BnResult = $this->db->insert('brand_notifications', [
//                                    'type' => '2',
//                                    'state' => '0',
//                                    'datetime' => date("Y-m-d H:i:s"),
//                                    'generated_by' => $infId,
//                                    'receiver' => $brandUserId,
//                                    'campid' => $campId
//                                ]);
//                                if ($BnResult) {
//                                    $message .= "YES";
//                                } else {
//                                    $message .= "NO";
//                                }
//                            } else {
//                                $message .= "NO";
//                            }
//
//                            //Used for reducing number of campaigns
//                            $this->load->model('M_campaign_management');
//                            $reduceCampaignResult = $this->M_campaign_management->reduceCampaignCount($brandID, $campId);
//                            $message .= "\nREDUCE CAMP  : QUERY  - ";
//                            if ($reduceCampaignResult == TRUE) {
//                                $message .= "TRUE";
//                            } else {
//                                $message .= "FALSE";
//                            }
//
//                            $message .= "\n\nPAYKEY: " . $paykey;
//
//                            $sx_invoice = uniqid("sx", TRUE);
//                            $inf_invoice = uniqid("in", TRUE);
//                            $conversion = $this->CurrencyConverter->convert('PKR', 'USD', $data->transaction_amount, 1, 1);
//
//                            $socialx_charges = $conversion * SWCOMM / 100;
//
//                            $message .= "\n\nConversion: " . $conversion;
//                            $message .= "\n\nSocialCXNCharges: " . $socialx_charges;
//
//                            $qryPay = $this->db->query("select * from transactions where influencerid = '" . $infId . "' and brandid = '" . $brandID . "' and campid = '" . $campId . "'");
//                            $message .= "\nTransaction : FETCH 1 - ";
//                            if ($qryPay->num_rows() > 0) {
//                                $message .= "TRUE";
//
//                                $result = $this->db->query("update transactions set date_time = '" . date("Y-m-d H:i:s") . "', amount = '" . $conversion . "', paykey = '" . $paykey . "', state = '1', sx_invoice = '" . $sx_invoice . "', inf_invoice = '" . $inf_invoice . "' "
//                                        . "where influencerid = '" . $infId . "' and brandid = '" . $brandID . "' and campid = '" . $campId . "'");
//                                $message .= "\nTransaction : UPDATE - ";
//                                if ($result > 0) {
//                                    $message .= "TRUE\n";
//                                } else {
//                                    $message .= "FALSE\n";
//                                }
//                            }
//                            $message .= "\nContributors : UPDATE - ";
//                            $cresult = $this->db->query("update contributors set paid = '1' where influencerid = '" . $infId . "' and campid='" . $campId . "'");
//
//                            if ($cresult > 0) {
//                                $message .= "TRUE\n";
//                            } else {
//                                $message .= "FALSE\n";
//                            }
//
//                            $sub_inf = 'Campaign (ID:{camp_id}) has been confirmed';
//                            $msg_inf = '<p>Dear {influencer_name},</p>
//				<p>Congratulations, your Campaign has been confirmed!</p>
//				<p>As the hired social media influencer, you may now begin working on the brands campaign. You will be paid once the campaign is completed and approved by the relevant brand.</p>
//				<p>' . SITE_TITLE . ' offers a wide range of brands for you to work with and monetize your social media influence!</p>
//				<p>Click below to begin working with your new Brand Partner instantly!</p>
//				<p><a href="' . base_url() . 'Login" style="font-size: 100%; line-height: 2; color: #ffffff; border-radius: 10px; display: inline-block; cursor: pointer; font-weight: bold; text-decoration: none; background: #348eda; margin: 0; padding: 8px 15px; border-color: #348eda; border-style: solid; border-radius: 25px; border-width: 2px;">Login</a></p>
//				<p>(' . base_url() . 'Login)</p>
//				<p style="font-size: 12px; line-height: 1.6em; font-weight: normal; color:#777; margin: 0 0 10px; padding: 0;">If you have problems, please paste the above URL into your web browser.</p>
//				<p>Best, </p><br>
//                                <p>' . SITE_TITLE . ' Team</p>
//                                    <p>Please feel free to email us at <a style="cursor: pointer;"> support@socialcxn.com </a>in case of any questions.</p>';
//
//
//                            $brandAdminEmails = checkRoleReturnEmails($brandUserId, $brandid);
////                    for admin
//                            if (isset($brandAdminEmails) && $brandAdminEmails != NULL && $brandAdminEmails == '1') {
//
//                                $sub_brand_admin = '';
//                                $msg_brand_admin = '';
////                        for admin msg                
//                                $sub_brand_user = 'Campaign (ID:{camp_id}) Payment Received';
//                                $msg_brand_user = '<p>Dear {brand_name},</p>
//				<p>Thank you for submitting your payment. Your campaign is now active.</p>
//                        <p>Click below to track, analyze and stay updated with your campaign.</p>
//                        <p><a href="' . base_url() . 'PageController/loginForm" style="font-size: 100%; line-height: 2; color: #ffffff; border-radius: 10px; display: inline-block; cursor: pointer; font-weight: bold; text-decoration: none; background: #348eda; margin: 0; padding: 8px 15px; border-color: #348eda; border-style: solid; border-radius: 25px; border-width: 2px;">Login</a></p>
//                        <p>(' . base_url() . 'PageController/loginForm)</p>
//                        <p style="font-size: 12px; line-height: 1.6em; font-weight: normal; color:#777; margin: 0 0 10px; padding: 0;">If you are facing any problem in accessing the above link, you may paste the above URL into your web browser.</p>
//                        <p>Best - ' . SITE_TITLE . ' Team</p>
//                            <p>Please feel free to email us at <a style="cursor: pointer;"> support@socialcxn.com </a>in case of any questions.</p>';
//                            }
////                    for user            
//                            else {
////                        for admin msg			
//                                $sub_brand_admin = 'Campaign (ID:{camp_id}) Payment Received';
//                                $msg_brand_admin = '<p>Dear {brand_name},</p>
//				<p>Thank you for submitting your payment. Your campaign is now active.</p>
//				<p>Click below to track, analyze and stay updated with your campaign.</p>
//				<p><a href="' . base_url() . 'PageController/loginForm" style="font-size: 100%; line-height: 2; color: #ffffff; border-radius: 10px; display: inline-block; cursor: pointer; font-weight: bold; text-decoration: none; background: #348eda; margin: 0; padding: 8px 15px; border-color: #348eda; border-style: solid; border-radius: 25px; border-width: 2px;">Login</a></p>
//				<p>(' . base_url() . 'PageController/loginForm)</p>
//				<p style="font-size: 12px; line-height: 1.6em; font-weight: normal; color:#777; margin: 0 0 10px; padding: 0;">If you have problems, please paste the above URL into your web browser.</p>
//				<p>Best - ' . SITE_TITLE . ' Team</p>
//                                    <p>Please feel free to email us at <a style="cursor: pointer;"> support@socialcxn.com </a>in case of any questions.</p>';
////                        for user msg                
//                                $sub_brand_user = 'Campaign (ID:{camp_id}) Payment Received';
//                                $msg_brand_user = '<p>Dear {brand_name},</p>
//				<p>Thank you for submitting your payment. Your campaign is now active.</p>
//				<p>Click below to track, analyze and stay updated with your campaign.</p>
//				<p><a href="' . base_url() . 'PageController/loginForm" styAle="font-size: 100%; line-height: 2; color: #ffffff; border-radius: 10px; display: inline-block; cursor: pointer; font-weight: bold; text-decoration: none; background: #348eda; margin: 0; padding: 8px 15px; border-color: #348eda; border-style: solid; border-radius: 25px; border-width: 2px;">Login</a></p>
//				<p>(' . base_url() . 'PageController/loginForm)</p>
//				<p style="font-size: 12px; line-height: 1.6em; font-weight: normal; color:#777; margin: 0 0 10px; padding: 0;">If you have problems, please paste the above URL into your web browser.</p>
//				<p>Best - ' . SITE_TITLE . ' Team</p>
//                                    <p>Please feel free to email us at <a style="cursor: pointer;"> support@socialcxn.com </a>in case of any questions.</p>';
//                            }
//
//                            notificationEmail($infId, $brandUserId, $campId, $msg_inf, $msg_brand_user, $msg_brand_admin, $sub_inf, $sub_brand_user, $sub_brand_admin);
//                            $this->session->set_flashdata('success', 'Your payment for campaign has been recieved');
//                        } else {
//                            $message .= "FALSE\n";
//
//                            $this->session->set_flashdata('error', 'Your payment for campaign could not be processed.');
//                        }   
                        }
//                        
                        else {
                            
                        }
                    }
//                    
                    else {
                        $message .= "\nCategory : Not Found\n";
                    }
                } else {
                    $message .= "\queryOneData : Not Found\n";
                }
            }
//            
            else if ($data->response_code > 0000 && $data->transaction_status != 'PAID') {
                $message .= "\n\n//===========EasyPay IPN Response is Not PAID ===============//\n";

                $forOrderDetails = $this->db->query("SELECT * FROM `transaction_log` WHERE order_unique_id = '" . $data->order_id . "' ");
                if ($forOrderDetails->num_rows() > 0) {
                    $message .= "\queryOneData : Found\n";

                    $orderDetails = $forOrderDetails->result_array()[0];
                    $type = $orderDetails['event'];

                    if ($type == 'cammpaign') {
                        $message .= "\nError Message : Your payment for campaign could not be processed.\n";
//                        $this->session->set_flashdata('error', 'Your payment for campaign could not be processed.');
                    } else if ($type == 'subscription') {
                        $message .= "\nError Message : Your payment for package could not be processed.\n";
//                        $this->session->set_flashdata('error', 'Your payment for package could not be processed.');
                    } else {
                        $message .= "\type : Not Found\n";
                    }
                } else {
                    $message .= "\queryOneData : Not Found\n";
                }
            }
//            
            else {
                $message .= "\nNo Response at All\n";
            }
        } else {
            $message .= "\n\nCurl Output is Null";
        }
        $message .= "\n\n//===========EasyPay IPN END===============//\n";
        $fp = fopen('easypay_test.log', 'a');
        fwrite($fp, $message);
        fclose($fp);

//        die("success");
        $this->load->view('welcome_message');
//        redirect(base_url() . 'dashboard');
    }

    function ipn2() {
        $req_dump = print_r($_POST, TRUE);
        $fp = fopen('request2.log', 'a');
        fwrite($fp, $req_dump);
        fclose($fp);
    }

    function ipn($brandid, $brandUserId, $infid, $campid) {
        $query = $this->db->query("select * from transactions where brandid='" . $brandid . "' AND influencerid='" . $infid . "' AND campid='" . $campid . "'");
        if ($query->num_rows() > 0) {
            $transaction1 = $query->result_array()[0];
            if ($transaction1['paykey'] != NULL || $transaction1['paykey'] != "") {

                $this->load->model('M_campaign_management');
                $reduceCampaignResult = $this->M_campaign_management->reduceCampaignCount($brandid, $campid);

                if ($transaction1['state'] <= 1) {
                    $this->db->query("update transactions set state = 1 where brandid='" . $brandid . "' AND influencerid='" . $infid . "' AND campid='" . $campid . "'");
                }
                $this->db->query("update contributors set paid = 1 where influencerid = $infid and campid=$campid");

//            Generate Notifications

                $brandUserQuery = $this->db->query("select * from brand_users where `brand-id` = '" . $brandid . "'");
                foreach ($brandUserQuery->result() as $row) {
                    if ($brandUserId == $row->branduserid) {
                        $brandUserId = $row->branduserid;
                        break;
                    }
                }
                if ($brandUserId != NULL) {
                    $this->db->insert('influencer_notifications', [
                        'type' => '2',
                        'state' => '0',
                        'datetime' => date("Y-m-d H:i:s"),
                        'generated_by' => $brandUserId,
                        'receiver' => $infid,
                        'campid' => $campid
                    ]);
                    $this->db->insert('brand_notifications', [
                        'type' => '2',
                        'state' => '0',
                        'datetime' => date("Y-m-d H:i:s"),
                        'generated_by' => $infid,
                        'receiver' => $brandUserId,
                        'campid' => $campid
                    ]);
                }

//            Generate Emails

                $sub_inf = '';
                $msg_inf = '';

                $brandAdminEmails = checkRoleReturnEmails($brandUserId, $brandid);
//            for admin
                if (isset($brandAdminEmails) && $brandAdminEmails != NULL && $brandAdminEmails == '1') {

                    $sub_brand_admin = '';
                    $msg_brand_admin = '';
//                for admin msg                
                    $sub_brand_user = 'Campaign (ID:{camp_id}) Payment Received';
                    $msg_brand_user = '<p>Dear {brand_name},</p>
                    <p>Thank you for submitting your payment. Your campaign is now active.</p>
                        <p>Click below to track, analyze and stay updated with your campaign.</p>
                        <p><a href="' . base_url() . 'PageController/loginForm" style="font-size: 100%; line-height: 2; color: #ffffff; border-radius: 10px; display: inline-block; cursor: pointer; font-weight: bold; text-decoration: none; background: #348eda; margin: 0; padding: 8px 15px; border-color: #348eda; border-style: solid; border-radius: 25px; border-width: 2px;">Login</a></p>
                        <p>(' . base_url() . 'PageController/loginForm)</p>
                        <p style="font-size: 12px; line-height: 1.6em; font-weight: normal; color:#777; margin: 0 0 10px; padding: 0;">If you are facing any problem in accessing the above link, you may paste the above URL into your web browser.</p>
                        <p>Best - ' . SITE_TITLE . ' Team</p>
                            <p>Please feel free to email us at <a style="cursor: pointer;"> support@socialcxn.com </a>in case of any questions.</p>';
                }
//            for user            
                else {
//                for admin msg			
                    $sub_brand_admin = 'Campaign (ID:{camp_id}) Payment Received';
                    $msg_brand_admin = '<p>Dear {brand_name},</p>
                    <p>Thank you for submitting your payment. Your campaign is now active.</p>
                        <p>Click below to track, analyze and stay updated with your campaign.</p>
                        <p><a href="' . base_url() . 'PageController/loginForm" style="font-size: 100%; line-height: 2; color: #ffffff; border-radius: 10px; display: inline-block; cursor: pointer; font-weight: bold; text-decoration: none; background: #348eda; margin: 0; padding: 8px 15px; border-color: #348eda; border-style: solid; border-radius: 25px; border-width: 2px;">Login</a></p>
                        <p>(' . base_url() . 'PageController/loginForm)</p>
                        <p style="font-size: 12px; line-height: 1.6em; font-weight: normal; color:#777; margin: 0 0 10px; padding: 0;">If you are facing any problem in accessing the above link, you may paste the above URL into your web browser.</p>
                        <p>Best - ' . SITE_TITLE . ' Team</p>
                            <p>Please feel free to email us at <a style="cursor: pointer;"> support@socialcxn.com </a>in case of any questions.</p>';
//                for user msg                
                    $sub_brand_user = 'Campaign (ID:{camp_id}) Payment Received';
                    $msg_brand_user = '<p>Dear {brand_name},</p>
                    <p>Thank you for submitting your payment. Your campaign is now active.</p>
                        <p>Click below to track, analyze and stay updated with your campaign.</p>
                        <p><a href="' . base_url() . 'PageController/loginForm" style="font-size: 100%; line-height: 2; color: #ffffff; border-radius: 10px; display: inline-block; cursor: pointer; font-weight: bold; text-decoration: none; background: #348eda; margin: 0; padding: 8px 15px; border-color: #348eda; border-style: solid; border-radius: 25px; border-width: 2px;">Login</a></p>
                        <p>(' . base_url() . 'PageController/loginForm)</p>
                        <p style="font-size: 12px; line-height: 1.6em; font-weight: normal; color:#777; margin: 0 0 10px; padding: 0;">If you are facing any problem in accessing the above link, you may paste the above URL into your web browser.</p>
                        <p>Best - ' . SITE_TITLE . ' Team</p>
                            <p>Please feel free to email us at <a style="cursor: pointer;"> support@socialcxn.com </a>in case of any questions.</p>';
                }

                notificationEmail($infid, $brandUserId, $campid, $msg_inf, $msg_brand_user, $msg_brand_admin, $sub_inf, $sub_brand_user, $sub_brand_admin);
                redirect(base_url() . 'campaign?a=show&paypal_payment=success&id=' . $campid);
            } else {
                redirect(base_url() . 'campaign?a=show&paypal_payment=failed&id=' . $campid);
            }
        } else {
            redirect(base_url() . 'campaign?a=show&paypal_payment=failed&id=' . $campid);
        }

//        OLD IPN Function Code - Start
//        $raw_post_array_str = '';
//        $raw_post_data = file_get_contents('php://input');
//        $raw_post_array = explode('&', $raw_post_data);
//        $raw_post_array_str = implode(',', $raw_post_array);
//        $ipn_values = array();
//
//        if (!empty($raw_post_array)) {
//            foreach ($raw_post_array as $arr_key => $arr) {
//
//                if (!empty($arr)) {
//                    $arr_val = explode("=", $arr);
//                    $ipn_values[$arr_val[0]] = $arr_val[1];
//                }
//            }
//        }
//        $ipn_values_str = implode(',', $ipn_values);
//        $ipnData = $ipn_values;
//        $query = $this->db->query("select * from transactions where paykey='" . $ipnData['pay_key'] . "'");
//
//        if ($query->num_rows() > 0) {
//            $infid = $query->result_array()[0]['influencerid'];
//            $brandid = $query->result_array()[0]['brandid'];
//            $campid = $query->result_array()[0]['campid'];
//            $transaction1 = $query->result_array()[0];
//            $brandUserQuery = $this->db->query("select * from brand_users where `brand-id` = '" . $brandid . "'");
//            $brandUserId = NULL;
//            foreach ($brandUserQuery->result() as $row) {
//                if ($this->session->brandUserId == $row->branduserid) {
//                    $brandUserId = $row->branduserid;
//                    break;
//                }
//            }
//            if ($brandUserId != NULL) {
//                $this->db->insert('influencer_notifications', [
//                    'type' => '2',
//                    'state' => '0',
//                    'datetime' => date("Y-m-d H:i:s"),
//                    'generated_by' => $brandUserId,
//                    'receiver' => $infid,
//                    'campid' => $campid
//                ]);
//                $this->db->insert('brand_notifications', [
//                    'type' => '2',
//                    'state' => '0',
//                    'datetime' => date("Y-m-d H:i:s"),
//                    'generated_by' => $infid,
//                    'receiver' => $brandUserId,
//                    'campid' => $campid
//                ]);
//            }
//
//            $this->load->model('M_campaign_management');
//            $reduceCampaignResult = $this->M_campaign_management->reduceCampaignCount($this->session->brandId, $campid);
//
//            if ($transaction1['state'] <= 1) {
//                $this->db->query("update transactions set state = 1 where paykey='" . $ipnData['pay_key'] . "'");
//            }
//            $this->db->query("update contributors set paid = 1 where influencerid = $infid and campid=$campid");
//            $sub_inf = '';
//            $msg_inf = '';
//
//            $brandAdminEmails = checkRoleReturnEmails($this->session->brandUserId, $this->session->brandId);
////            for admin
//            if (isset($brandAdminEmails) && $brandAdminEmails != NULL && $brandAdminEmails == '1') {
//
//                $sub_brand_admin = '';
//                $msg_brand_admin = '';
////                for admin msg                
//                $sub_brand_user = 'Campaign (ID:{camp_id}) Payment Received';
//                $msg_brand_user = '<p>Dear {brand_name},</p>
//                    <p>Thank you for submitting your payment. Your campaign is now active.</p>
//                    <p>Click below to track, analyze and stay updated with your campaign.</p>
//                    <p><a href="' . base_url() . 'PageController/loginForm" style="font-size: 100%; line-height: 2; color: #ffffff; border-radius: 10px; display: inline-block; cursor: pointer; font-weight: bold; text-decoration: none; background: #348eda; margin: 0; padding: 8px 15px; border-color: #348eda; border-style: solid; border-radius: 25px; border-width: 2px;">Login</a></p>
//                    <p>(' . base_url() . 'PageController/loginForm)</p>
//                    <p style="font-size: 12px; line-height: 1.6em; font-weight: normal; color:#777; margin: 0 0 10px; padding: 0;">If you have problems, please paste the above URL into your web browser.</p>
//                    <p>Best - ' . SITE_TITLE . ' Team</p>
//                    <p>Please feel free to email us at <a style="cursor: pointer;"> support@socialcxn.com </a>in case of any questions.</p>';
//            }
////            for user            
//            else {
////                for admin msg			
//                $sub_brand_admin = 'Campaign (ID:{camp_id}) Payment Received';
//                $msg_brand_admin = '<p>Dear {brand_name},</p>
//                    <p>Thank you for submitting your payment. Your campaign is now active.</p>
//                    <p>Click below to track, analyze and stay updated with your campaign.</p>
//                    <p><a href="' . base_url() . 'PageController/loginForm" style="font-size: 100%; line-height: 2; color: #ffffff; border-radius: 10px; display: inline-block; cursor: pointer; font-weight: bold; text-decoration: none; background: #348eda; margin: 0; padding: 8px 15px; border-color: #348eda; border-style: solid; border-radius: 25px; border-width: 2px;">Login</a></p>
//                    <p>(' . base_url() . 'PageController/loginForm)</p>
//                    <p style="font-size: 12px; line-height: 1.6em; font-weight: normal; color:#777; margin: 0 0 10px; padding: 0;">If you have problems, please paste the above URL into your web browser.</p>
//                    <p>Best - ' . SITE_TITLE . ' Team</p>
//                    <p>Please feel free to email us at <a style="cursor: pointer;"> support@socialcxn.com </a>in case of any questions.</p>';
////                for user msg                
//                $sub_brand_user = 'Campaign (ID:{camp_id}) Payment Received';
//                $msg_brand_user = '<p>Dear {brand_name},</p>
//                    <p>Thank you for submitting your payment. Your campaign is now active.</p>
//                    <p>Click below to track, analyze and stay updated with your campaign.</p>
//                    <p><a href="' . base_url() . 'PageController/loginForm" style="font-size: 100%; line-height: 2; color: #ffffff; border-radius: 10px; display: inline-block; cursor: pointer; font-weight: bold; text-decoration: none; background: #348eda; margin: 0; padding: 8px 15px; border-color: #348eda; border-style: solid; border-radius: 25px; border-width: 2px;">Login</a></p>
//                    <p>(' . base_url() . 'PageController/loginForm)</p>
//                    <p style="font-size: 12px; line-height: 1.6em; font-weight: normal; color:#777; margin: 0 0 10px; padding: 0;">If you have problems, please paste the above URL into your web browser.</p>
//                    <p>Best - ' . SITE_TITLE . ' Team</p>
//                    <p>Please feel free to email us at <a style="cursor: pointer;"> support@socialcxn.com </a>in case of any questions.</p>';
//            }
//
//            notificationEmail($infid, $this->session->brandUserId, $campid, $msg_inf, $msg_brand_user, $msg_brand_admin, $sub_inf, $sub_brand_user, $sub_brand_admin);
//        } else {
//            return NULL;
//        }
        //        OLD IPN Function Code - End
    }

    public function successfulPaymentEntry() {

        $this->load->library('session');
        $data = $this->session->userdata('buying');

        if (isset($data['signup'])) {
            $redirectUri = base_url() . 'activate/brand_payment/1/' . $data['brandHash']
                    . '/' . $data['brandId'] . '/' . $data['packageId']
                    . '/' . $data['packagePlanId'] . '/' . $data['amount'] . '/0/0/0';
            $this->session->unset_userdata('buying');
            redirect($redirectUri);
        } else if (isset($data['dashboard'])) {
            $brandId = $this->session->userdata('brandId');
            if (isset($brandId) == false) {
                redirect(base_url() . '/pageController/loginForm');
            }
            $redirectUri = base_url() . 'brandPackageController/brand_package_payment/1/' . $data['brandId'] . '/' . $data['brandUserId'] . '/' . $data['packageId'] . '/' . $data['packagePlanId'] . '/' . $data['amount'] . '/0/0';
            $this->session->unset_userdata('buying');
            redirect($redirectUri);
        } else if (isset($data['dashboardPackage'])) {
            $brandId = $this->session->userdata('brandId');
            if (isset($brandId) == false) {
                redirect(base_url() . '/pageController/loginForm');
            }
            $this->load->model('M_brand_package');
            $this->M_brand_package->updatePackage($data['packageId'], $data['packagePlanId'], $data['packageType']);
            $redirectUri = base_url() . 'brandPackageController/brand_package_payment/1/' . $data['brandId'] . '/' . $data['brandUserId'] . '/' . $data['packageId'] . '/' . $data['packagePlanId'] . '/' . $data['amount'] . '/0/0';
            $this->session->unset_userdata('buying');
            redirect($redirectUri);
        } else if (isset($data['campaign'])) {
            $brandId = $this->session->userdata('brandId');
            if (isset($brandId) == false) {
                redirect(base_url() . '/pageController/loginForm');
            }
            $sx_invoice = uniqid("sx_");
            $inf_invoice = uniqid("inf_");
            $this->paidForCampaign($data, $sx_invoice, $inf_invoice);
            $this->notificationsForCampaignPayment($data, $sx_invoice, $inf_invoice);

            $redirectUri = base_url() . 'make_payment/thankyou_payment/' . $data['campId'];
            redirect($redirectUri);
        } else {
            $brandId = $this->session->userdata('brandId');
            if (isset($brandId) == false) {
                redirect(base_url() . '/pageController/loginForm');
            } else {
                redirect(base_url() . '/dashboard?transactionError=failed');
            }
//            print_r("We didn't underestand you");
        }
        die();
    }

    private function unsetAndStoreSessionData($store) {
        $this->load->library('session');
        $session = $this->session->userdata('buying');

        if (isset($session)) {
            $this->session->unset_userdata('buying');
        }
        $this->session->set_userdata($store);
        return;
    }

    function inquireTransaction() {
//        echo 'Test soap api';

        $messageLog = "\n### Initiate ###\n";

        $fp = fopen('easypay_inquire.log', 'a');
        fwrite($fp, $messageLog);
        fclose($fp);


        $messageLog = "\n####### Inquire Api Start #######\n";
        echo "\nTime : " . date("Y-m-d h:i:sa") . "\n";
        $messageLog .= "\nTime : " . date("Y-m-d h:i:sa") . "\n";
        $data12['ep_live'] = $this->config->item('ep_live');

        // Create the context as you did in your test
        $context = stream_context_create(
                array(
                    'ssl' => array(
                        'verify_peer' => false,
                        'verify_peer_name' => false,
                        'allow_self_signed' => true
                    )
        ));

        if ($data12['ep_live'] == 'no') {

            $client = new SoapClient("https://easypaystg.easypaisa.com.pk/easypay-service/PartnerBusinessService/META-INF/wsdl/partner/transaction/PartnerBusinessService.wsdl", array("classmap" => $this->classmap,
                "location" => "https://easypaystg.easypaisa.com.pk/easypay-service/PartnerBusinessService",
                "trace" => true,
                "exceptions" => true,
                "stream_context" => $context
            ));

            $client->__setLocation("https://easypaystg.easypaisa.com.pk/easypay-service/PartnerBusinessService");
        } else {
            $client = new SoapClient("https://easypay.easypaisa.com.pk/easypay-service/PartnerBusinessService/META-INF/wsdl/partner/transaction/PartnerBusinessService.wsdl", array("classmap" => $this->classmap,
                "location" => "https://easypay.easypaisa.com.pk/easypay-service/PartnerBusinessService",
                "trace" => true,
                "exceptions" => true,
                "stream_context" => $context
            ));
            $client->__setLocation("https://easypay.easypaisa.com.pk/easypay-service/PartnerBusinessService");
        }


        echo '<br> Fetching transaction log data ...';
        $messageLog .="\nFetching transaction log data of pending status ...";
        $transactionLog = $this->db->query("SELECT * FROM `transaction_log` WHERE status = 'pending' AND row_status = 'active' ");

        if ($transactionLog->num_rows() > 0) {
            echo "<br> data found";
            $messageLog .= "\n data found";
            $transactionLogData = $transactionLog->result_array();

            foreach ($transactionLogData as $key => $value) {
//                echo '<pre>';
//                echo '<br> Key : ' . $key . '';
//                echo '<br> Unique Id : ' . $value['order_unique_id'] . '<br>';
//                print_r($value);

                if ($data12['ep_live'] == 'no') {
                    $inquireResponse = $client->__soapCall("inquireTransaction", array("inquireTransactionRequestType" => array(
                            "username" => "skillorbit",
                            "password" => "472ef47c0d9a99d14ec5ed4a7f0a0053",
                            "orderId" => $value['order_unique_id'],
                            "accountNum" => "20074"
                    )));
                } else {
                    $inquireResponse = $client->__soapCall("inquireTransaction", array("inquireTransactionRequestType" => array(
                            "username" => "kueball",
                            "password" => "dab72e2062b7d094123288f6de4f844f",
                            "orderId" => $value['order_unique_id'],
                            "accountNum" => "54818908"
                    )));
                }


                echo '<br>checking response ';
                $messageLog .= "\n checking soap api response of order id : " . $value['order_unique_id'];
                echo '<br>inquireResponse->responseCode : ', $inquireResponse->responseCode;

//                echo '<pre>';
//                print_r($inquireResponse);

                if ($inquireResponse->responseCode == 0000) {
                    echo '<br>success ';
                    $messageLog .= "\n success ";

                    $messageLog .= "\n checking transaction status is " . $inquireResponse->transactionStatus;
                    if ($inquireResponse->transactionStatus == 'PAID') {


                        $messageLog .= "\nfetching from transaction log...\n";

                        $forOrderDetails = $this->db->query("SELECT * FROM `transaction_log` WHERE order_unique_id = '" . $inquireResponse->orderId . "' ");

                        if ($forOrderDetails->num_rows() > 0) {
                            $messageLog .= "\nqueryOneData : Found\n";

                            $orderDetails = $forOrderDetails->result_array()[0];
                            $tlId = $orderDetails['id'];
                            $type = $orderDetails['event'];
                            $tStatus = $orderDetails['status'];
//                    $orderDetails = $forPackageID->result_array()[0];

                            $messageLog .= "\n\n" . print_r($orderDetails, TRUE);
                            $messageLog .= "\nType : " . $type . "\n";
//                    $message .= "\nBrandId : " . $brandID . "\n";
                            $transactionId = $inquireResponse->transactionId;

//                    $paykey = strtolower($data->order_id);
                            $paykey = $inquireResponse->orderId;

//                    
                            if ($type == 'campaign') {

                                $messageLog .= "\n Category : Campaign";

                                $senderId = $orderDetails['sender_entity_id'];
                                $infUserId = $orderDetails['inf_user_id'];
                                $recieverId = $orderDetails['receiver_entity_id'];
                                $behalfOfRecieverId = $orderDetails['behalf_of_receiver_entity_id'];
                                $campId = $orderDetails['campaign_id'];
                                $statusForUpdate = $orderDetails['status_for_update'];

                                $messageLog .= "\n Transaction Status in db : " . $tStatus . "\n";
                                if ($tStatus != 'success') {

//                                  making data 
                                    $messageLog .= "\n making data for entries...\n";

                                    $data1['sender_entity_id'] = $senderId;
                                    $data1['campaign_id'] = $campId;
//                                  $recieverId
                                    $data1['receiver_entity_id'] = ($behalfOfRecieverId == NULL || $behalfOfRecieverId == "" ? $recieverId : $behalfOfRecieverId);
//                                  $behalfOfRecieverId
                                    $data1['behalf_of_receiver_entity_id'] = ($behalfOfRecieverId == null || $behalfOfRecieverId == "" ? null : $recieverId);
                                    $data1['paykey'] = $paykey;
//                                  $data1['receiver_invoice'] = 0;
                                    $date = date_create();
                                    $data1['transaction_date'] = date_timestamp_get($date);
                                    $data1['transaction_status'] = "success";
                                    $data1['transaction_type'] = "auto_paid";
//                                  $data1['transaction_type'] = date("Y-m-d H:i:s");
                                    $data1['amount_paid'] = $inquireResponse->transactionAmount;
//                                  $data1['setup_currency_id'] = NULL;
                                    $data1['transaction_id'] = $transactionId;
                                    $data1['row_status'] = 'active';
//                        
//                        
                                    $data2['campaign_payment_status'] = $statusForUpdate;
//                                  $data2['campaign_payment_status'] = "escrow_paid_scxn";

                                    $data3['status'] = "success";

                                    $messageLog .= "\n transactions begins...\n";
                                    $this->db->trans_begin();

                                    $this->db->insert('campaigns_transactions', $data1);


                                    $array2 = array('campaign_id' => $campId, 'user_id' => $infUserId, 'row_status' => 'active');
                                    $this->db->where($array2);
                                    $this->db->update('campaigns_offered_influencers', $data2);

                                    $array3 = array('id' => $tlId);
                                    $this->db->where($array3);
                                    $this->db->update('transaction_log', $data3);

                                    if ($this->db->trans_status() === FALSE) {
                                        $this->db->trans_rollback();
                                        $messageLog .= "\n status : transaction rollback...\n";
//                                      echo 'false';
                                    } else {
                                        $this->db->trans_commit();
                                        $messageLog .= "\n status : transaction commit...\n";
                                    }
                                }
                            }
//                    
                            else {
                                $messageLog .= "\n Category : Not Found\n";
                            }
                        } else {
                            $messageLog .= "\n queryOneData : Not Found\n";
                        }
                    } elseif ($inquireResponse->transactionStatus == 'PENDING' || $inquireResponse->transactionStatus == 'INITIATED') {

                        $messageLog .= "\n nothing to do \n";
                    } elseif ($inquireResponse->transactionStatus == 'EXPIRED' || $inquireResponse->transactionStatus == 'CANCELLED' || $inquireResponse->transactionStatus == 'REVERSED' || $inquireResponse->transactionStatus == 'FAILED' || $inquireResponse->transactionStatus == 'DROPPED' || $inquireResponse->transactionStatus == 'BLOCKED') {

                        echo "<br> http request";
//                        $messageLog .= "\n http request for token";
//                        $url = 'http://localhost:3000/api/v1.1.0/transactionToken';
                        $url = $this->config->item('api_url') . 'api/v1.1.0/transactionToken';
                        $curl = curl_init();
                        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
                        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
                        curl_setopt($curl, CURLOPT_URL, $url);
                        $output = curl_exec($curl);
                        curl_close($curl);

                        $data = json_decode($output);

//                        $messageLog .= "\n\n" . print_r($data, TRUE);
//                        echo "<pre>";
//                        print_r($data);
//                        print_r($value);
                        $token = $data->genericResponse->genericBody->data->token;
//                        echo '<br> token : ' . $token;

                        $data1['prefix'] = $value['prefix'];
                        $data1['sender_entity_id'] = $value['sender_entity_id'];
                        $data1['behalf_of_sender_entity_id'] = $value['behalf_of_sender_entity_id'];
                        $data1['campaign_id'] = $value['campaign_id'];
                        $data1['inf_user_id'] = $value['inf_user_id'];
                        $data1['receiver_entity_id'] = $value['receiver_entity_id'];
                        $data1['behalf_of_receiver_entity_id'] = $value['behalf_of_receiver_entity_id'];
                        $data1['status_for_update'] = $value['status_for_update'];
                        $data1['event'] = $value['event'];
                        $data1['order_unique_id'] = $token;
                        $data1['status'] = 'pending';
                        $data1['created_by'] = $value['created_by'];
                        $data1['row_status'] = 'active';
//                        
                        $data2['status'] = strtolower($inquireResponse->transactionStatus);
                        $data2['row_status'] = "inactive";

                        $messageLog .= "\n transactions begins...\n";
                        $this->db->trans_begin();

                        $this->db->insert('transaction_log', $data1);


                        $array2 = array('id' => $value['id']);
                        $this->db->where($array2);
                        $this->db->update('transaction_log', $data2);

                        if ($this->db->trans_status() === FALSE) {
                            $this->db->trans_rollback();
                            $messageLog .= "\n status : transaction rollback...\n";
//                                      echo 'false';
                        } else {
                            $this->db->trans_commit();
                            $messageLog .= "\n status : transaction commit...\n";
                        }
                    } else {
                        
                    }
                } elseif ($inquireResponse->responseCode == 0001) {

                    echo '<br> system error ';
                    $messageLog .= "\n system error ";
                } elseif ($inquireResponse->responseCode == 0002) {

                    echo '<br>required field is missing ';
                    $messageLog .= "\n required field is missing ";
                } else {
//                    $inquireResponse->responseCode == 0003
                    echo '<br>invalid order ID or may not initiated ';
                    $messageLog .= "\n invalid order ID or may not initiated ";
                }
            }
        } else {
            echo 'no data found';
            $messageLog .= "\n no data found";
        }

        $messageLog .= "\n####### Inquire Api End #######\n";

        $fp = fopen('easypay_inquire.log', 'a');
        fwrite($fp, $messageLog);
        fclose($fp);
    }

    function testInquireTransaction() {

        $url = $this->config->item('api_url') . 'api/v1.1.0/transactionToken';

        echo 'url : ' . $url;

        $client = new SoapClient("https://easypaystg.easypaisa.com.pk/easypay-service/PartnerBusinessService/META-INF/wsdl/partner/transaction/PartnerBusinessService.wsdl");
        $client->__setLocation("https://easypaystg.easypaisa.com.pk/easypay-service/PartnerBusinessService");


//        live
//        $inquireResponse = $client->__soapCall("inquireTransaction", array("inquireTransactionRequestType" => array(
//                "username" => "kueball",
//                "password" => "dab72e2062b7d094123288f6de4f844f",
//                "orderId" => "OwsLa0Hw8rxloaNTWFHG",
//                "accountNum" => "54818908"
//        )));
//        staging
        $inquireResponse = $client->__soapCall("inquireTransaction", array("inquireTransactionRequestType" => array(
                "username" => "skillorbit",
                "password" => "472ef47c0d9a99d14ec5ed4a7f0a0053",
                "orderId" => "LbVI9zCtFjS4KOn58oyK",
                "accountNum" => "20074"
        )));

//        var_dump($inquireResponse);
        echo '<pre>';
        print_r($inquireResponse);
    }

}
