<?php

namespace Winged\Validator\Email;

class Email
{
    public static function validate($email)
    {
        return (boolean)filter_var($email, FILTER_VALIDATE_EMAIL);
    }

    public static function check($toemail, $fromemail, $getdetails = false)
    {

        set_time_limit(0);

        if(!self::validate($toemail) || !self::validate($fromemail)){
            return false;
        }
        $details = '';
        $result = false;
        $mxweight = null;
        $email_arr = explode('@', $toemail);
        $domain = array_slice($email_arr, -1);
        $domain = $domain[0];

        $domain = ltrim($domain, '[');
        $domain = rtrim($domain, ']');

        if ('IPv6:' == substr($domain, 0, strlen('IPv6:'))) {
            $domain = substr($domain, strlen('IPv6') + 1);
        }

        $mxhosts = array();
        if (filter_var($domain, FILTER_VALIDATE_IP)) {
            $mx_ip = $domain;
        } else {
            getmxrr($domain, $mxhosts, $mxweight);
        }
        if (!empty($mxhosts)) {
            $mx_ip = $mxhosts[array_search(min($mxweight), $mxhosts)];
        } else {
            if (filter_var($domain, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
                $record_a = dns_get_record($domain, DNS_A);
            } elseif (filter_var($domain, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)) {
                $record_a = dns_get_record($domain, DNS_AAAA);
            }

            if (!empty($record_a)) {
                $mx_ip = $record_a[0]['ip'];
            } else {
                $result = false;
                $details .= 'No suitable MX records found.';
                return ((true == $getdetails) ? array($result, $details) : $result);
            }
        }
        $connect = fsockopen($mx_ip, 25, $errno, $errstr);
        if ($connect) {
            if (preg_match('/^220/i', $out = fgets($connect, 1024))) {
                fputs($connect, "HELO $mx_ip\r\n");
                $out = fgets($connect, 1024);
                $details .= $out . "\n";

                fputs($connect, "MAIL FROM: <$fromemail>\r\n");
                $from = fgets($connect, 1024);
                $details .= $from . "\n";

                fputs($connect, "RCPT TO: <$toemail>\r\n");
                $to = fgets($connect, 1024);
                $details .= $to . "\n";

                fputs($connect, 'QUIT');
                fclose($connect);

                if (!preg_match('/^250/i', $from) || !preg_match('/^250/i', $to)) {
                    $result = false;
                } else {
                    $result = true;
                }
            }
        } else {
            pre_clear_buffer_die([$errno, $errstr]);
            $result = false;
            $details .= 'Could not connect to server';
        }
        if ($getdetails) {
            return array($result, $details);
        } else {
            return $result;
        }
    }
}