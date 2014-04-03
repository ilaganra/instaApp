<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
$emailRecipient[0]          ='railagan@philweb.com.ph';
$emailRecipient[1]          ='hbras@philweb.com.ph';
$emailRecipient[2]          ='mrgonzales@philweb.com.ph';
$emailRecipient[3]          ='jclim@philweb.com.ph';
$emailRecipient[4]          ='customerservice@philweb.com.ph';
$config['cronEmailRecipient'] = $emailRecipient;
$config['useragent']        = 'PHPMailer';              // Mail engine switcher: 'CodeIgniter' or 'PHPMailer'
$config['protocol']         = 'smtp';                   // 'mail', 'sendmail', or 'smtp'
$config['mailpath']         = '/usr/sbin/sendmail';
$config['smtp_host']        = 'smtp.gmail.com';
$config['smtp_user']        = 'itswebadmin@gmail.com';
$config['smtp_pass']        = '';
$config['smtp_port']        = 465;
$config['smtp_timeout']     = 5;                        // (in seconds)
$config['smtp_crypto']      = 'ssl';                    // '' or 'tls' or 'ssl'
$config['wordwrap']         = true;
$config['wrapchars']        = 76;
$config['mailtype']         = 'html';                   // 'text' or 'html'
$config['charset']          = 'utf-8';
$config['validate']         = true;
$config['priority']         = 3;                        // 1, 2, 3, 4, 5
$config['crlf']             = "\n";                     // "\r\n" or "\n" or "\r"
$config['newline']          = "\n";                     // "\r\n" or "\n" or "\r"
$config['bcc_batch_mode']   = false;
$config['bcc_batch_size']   = 200;


?>
