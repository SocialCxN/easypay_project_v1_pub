<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

class MY_Exceptions extends CI_Exceptions {

    function __construct() {
        parent::__construct();
    }

    function log_exception($severity, $message, $filepath, $line) {
//        if (ENVIRONMENT === 'development') {
            $ci = & get_instance();

//            ================ slack notification =========================
//            $ci->load->library('Slack');
//            log_message('debug', print_r($this->session, TRUE));
//
////            // Send the notification
//            if ($ci->slack->send('Severity: ' . $severity . '  --> ' . $message . ' ' . $filepath . ' ' . $line)) {
////             print_r($ci->slack->output); // Print the response from Slack if you want.
//            } else {
////               print_r($ci->slack->error); // This will output the error.
//            }

            $message1 = "\r\n".' ######  Severity: ' . $severity . '  --> ' . $message . ' ' . $filepath . ' ' . $line;
            $fp = fopen('error_log.log', 'a');
            fwrite($fp, $message1);
            fclose($fp);
//        }


        parent::log_exception($severity, $message, $filepath, $line);
    }

}
