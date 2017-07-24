<?php
function retrieve_message($auth_user, $accountid, $messageid, $fullheaders) {
    $message = array();

    if(!($auth_user && $messageid && $accountid)) {
        return false;
    }
    $imap = open_mailbox($auth_user, $accountid);
    if(!$imap) {
        return false;
    }
    $header = imap_header($imap, $messageid);

    if(!$header) {
        return false;
    }
    $message['body'] = imap_body($imap, $messageid);
    if(!$message['body']) {
        $message['body'] = "[This message has no body]\n\n\n\n\n\n";
    }
    if($fullheaders) {
        $message['fullheaders'] = imap_fetchheader($imap, $messageid);
    } else {
        $message['fullheaders'] = '';
    }

    $message['subject'] = $header->subject;
    $message['fromaddress'] =   $header->fromaddress;
    $message['toaddress'] =   $header->toaddress;
    $message['ccaddress'] =   $header->ccaddress;
    $message['date'] =   $header->date;

    // note we can get more detailed information by using from and to
    // rather than fromaddress and toaddress, but these are easier

    imap_close($imap);
    return $message;
}

function account_exists($auth_user, $accountid) {
    // does this user have an account with this id?

    $query = "select count(*) from accounts where username = '".$auth_user."' and accountid = '".$accountid."'";

    if($conn=db_connect())  {
        $result = $conn->query($query);
        $row = $result->fetch_array();
        return $row[0];
    }
    return false;
}

function get_account_list($auth_user) {
    // get an array of the account ids that belong to this user

    $query = "select accountid from accounts where username = '".$auth_user."'";
    $list = array();

    if($conn=db_connect())   {
        $result = $conn->query($query);
        $num = $result->num_rows;
        for($i = 0; $i<$num; $i++) {
            $row = $result->fetch_array();
            array_push($list, $row[0]);
        }
    }
    return $list;
}

function number_of_accounts($auth_user) {
    // get the number of accounts that belong to this user

    $query = "select count(*) from accounts where username = '".$auth_user."'";

    if($conn=db_connect()) {
        $result = $conn->query($query);
        if($result) {
            $row = $result->fetch_array();
            return $row[0];
        }
    }
    return 0;
}

function delete_account($auth_user, $accountid) {
    //delete one of this user's accounts from the DB

    $query = "delete from accounts where accountid = '".$accountid."' and username = '".$auth_user."'";
    if($conn=db_connect()) {
        $result = $conn->query($query);
    }
    return $result;
}

function get_account_settings($auth_user, $accountid=0) {
    //get an array containing the settings for this account

    $settings = array();
    if($conn=db_connect())   {
        if(($accountid > 0) && ($accountid != '')) {
            $query = "select * from accounts where accountid = '".$accountid."' and username = '".$auth_user."'";
        } else if (number_of_accounts($auth_user)==1) {
            $query = "select * from accounts where username = '".$auth_user."'";
        } else  {
            return false;
        }

        $result = $conn->query($query);
        if($result) {
            $settings = $result->fetch_assoc();
            return $settings;
        } else {
            return false;
        }
    }
    return false;
}

function get_accounts($auth_user) {
    $list = array();
    if($conn=db_connect()) {
        $query = "select * from accounts where username = '".$auth_user."'";
        $result = $conn->query($query);
        if($result) {
            while($settings = $result->fetch_assoc()) {
                array_push($list, $settings);
            }
        } else {
            return false;
        }
    }
    return $list;
}


function store_account_settings($auth_user, $settings) {
    if(!filled_out($settings))  {
        echo "<p>All fields must be filled in.  Try again.</p>";
        return false;
    } else {
        if($settings['account']>0) {
            $query = "update accounts  set server = '".$settings[server]."',
                  port = ".$settings[port].", type = '".$settings[type]."',
                  remoteuser = '".$settings[remoteuser]."',
                  remotepassword = '".$settings[remotepassword]."'
                where accountid = '".$settings[account]."'
                  and username = '".$auth_user."'";
        } else {
            $query = "insert into accounts values ('".$auth_user."',
                     '".$settings[server]."', '".$settings[port]."',
                     '".$settings[type]."', '".$settings[remoteuser]."',
                     '".$settings[remotepassword]."', NULL)";
        }

        if($conn=db_connect()) {
            $result=$conn->query($query);
            if ($result) {
                return true;
            } else {
                return false;
            }
        } else {
            echo "<p>Could not store changes.</p>";
            return false;
        }
    }
}

function delete_message($auth_user, $accountid, $message_id) {
    // delete a single message from the server

    $imap = open_mailbox($auth_user, $accountid);
    if($imap) {
        imap_delete($imap, $message_id);
        imap_expunge($imap);
        imap_close($imap);
        return true;
    }
    return false;
}

function open_mailbox($auth_user, $accountid) {

    // select mailbox if there is only one
    if(number_of_accounts($auth_user)==1) {
        $accounts = get_account_list($auth_user);
        $_SESSION['selected_account'] = $accounts[0];
        $accountid = $accounts[0];
    }
    /*
    else{
        $accountid = $_SESSION['selected_account'];
    }
    */
    // connect to the POP3 or IMAP server the user has selected
    $settings = get_account_settings($auth_user, $accountid);
    if(!sizeof($settings)) {
        return 0;
    }
    $mailbox = '{'.$settings[server];

    $mailbox .= ':'.$settings[port];

    if($settings[type]=='POP3') {
        //avoid bug
        $mailbox .= '/pop3';
    }

    $mailbox .= '/ssl}INBOX';
    // suppress warning, remember to check return value
    @$imap = imap_open($mailbox, $settings['remoteuser'], $settings['remotepassword']);
    return  $imap;
}


function get_list($imap) {
    // get the list of messages in this mailbox
    $headers = imap_headers($imap);
    $messages = sizeof($headers);
    for($i = 0; $i<$messages; $i++) {
        echo $headers[$i];
    }
    imap_close($imap);
}

function send_message($from, $to, $cc, $subject, $message) {
    // send one email via PHP

    if (!$conn=db_connect()) {
        return false;
    }
    $query = "SELECT remotepassword FROM mail.accounts where username = '".$_SESSION['auth_user']."' and remoteuser = '".$from."'";

    $result = $conn->query($query);
    if (!$result) {
        return false;
    } else if ($result->num_rows==0) {
        return false;
    } else {
        $password = $result->fetch_object()->remotepassword;
    }
    //modify inn setting
    $newfp='';
    $myfile = fopen("C:\\sendmail\\sendmail.ini", "r+") or die("Unable to open file!");
    $delline1=16;
    $delline2=17;
    $delline3=18;
    $farray=file("C:\\sendmail\\sendmail.ini");
    for($tmpa=0;$tmpa<count($farray);$tmpa++){
        if(strcmp($tmpa+1,$delline1)==0){
            $newfp.="auth_username=".$from."\n";
            continue;
        }
        if(strcmp($tmpa+1,$delline2)==0){
            $newfp.="auth_password=".$password."\n";
            continue;
        }
        if(strcmp($tmpa+1,$delline3)==0){
            $newfp.="force_sender=".$from."\n";
            continue;
        }

        $newfp.=$farray[$tmpa];
    }
    fwrite($myfile, $newfp);
    fclose($myfile);

	$headers = "From: $from"; 

	$fileName = "C:\\test.csv";

	// boundary  
	$semi_rand = md5(time());  
	$mime_boundary = "==Multipart_Boundary_x{$semi_rand}x";  

	// headers for attachment  
	$headers .= "\nMIME-Version: 1.0\n" . "Content-Type: multipart/mixed;\n" . " boundary=\"{$mime_boundary}\"";  

	// multipart boundary  
	$message = "This is a multi-part message in MIME format.\n\n" . "--{$mime_boundary}\n" . "Content-Type: text/plain; charset=\"iso-8859-1\"\n" . "Content-Transfer-Encoding: 7bit\n\n" . $message . "\n\n";  
	$message .= "--{$mime_boundary}\n"; 

	// preparing attachments 
	$file = fopen($fileName,"rb"); 
	$data = fread($file,filesize($fileName)); 
	fclose($file); 
	$data = chunk_split(base64_encode($data)); 
	$message .= "Content-Type: {\"application/octet-stream\"};\n" . " name=\"abc.csv\"\n" .  
	"Content-Disposition: attachment;\n" . " filename=\"test.csv\"\n" .  
	"Content-Transfer-Encoding: base64\n\n" . $data . "\n\n"; 
	$message .= "--{$mime_boundary}--\n"; 

    if (!empty($cc)) {
        $headers.="\r\nCc: $cc";
    }

    if (mail($to, $subject, $message, $headers)) {
        return true;
    } else {
        return false;
    }
}



function add_quoting($string, $pattern = '> ') {
    // add a quoting pattern to mark text quoted in your reply
    return $pattern.str_replace("\n", "\n$pattern", $string);
}