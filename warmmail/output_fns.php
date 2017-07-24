<?php
// set for 600x800 screen
$table_width = '100%';

function do_html_header($auth_user, $title = '', $selected_account) {
  // print an HTML header including cute logo :)

  global $table_width;

  //draw title bar
?>
  <html>
  <head>
    <title><?php echo $title; ?></title>
    <style>
	  body, li, td { font-family: Arial, Helvetica, sans-serif;
                     font-size: 12; margin = 0px}
      h1 { font-family: 'Calibri',  sans-serif; font-size: 32;
           font-weight: bold; color:  white; margin-bottom: 0}
      b { font-family: 'Arial', sans-serif; font-size: 13;
          font-weight: bold; color: black }
      th { font-family: 'Calibri',  sans-serif; font-size: 18
           font-weight: bold; color: white; }
      a { color: #000000 }
	  #windowsize{
		  width:1280px; min-height:720px; background-color:#ffffff
	  }
	  #header{
		   width:1280px; height:60px; margin:0px; padding:0px; background-color:#ffffff
	  }
	  #container{
		  min-width: 1280px ; height: auto; padding:0px; background-color:#ffffff
	  }
	  #functions{
		   width:150px; height:auto; float:left; padding:0px; background-color:#ffffff
	  }
	  #contents{
		   width:1124px ; height:608px; float:left; padding:2px 2px 2px 2px; background-color:#ffffff; overflow-y: scroll
	  }
	  #footer{
		  min-width:1280px; height:45px; position:fixed ; margin:615px 0px 0px 0px; background-color:#ffffff
	  }
    </style>


  </head>
  <body >
  <div id="windowsize">
  <div id="header">
  <table width="1280px" height="56px" bgcolor="#9f181b" border="0">
  <tr bgcolor="#9f181b">
  <td bgcolor="#9f181b" width="103"><img src="images/mail.png"
      width="auto" height="56px" alt="center" valign="middle" border="0"/></td>
  <td bgcolor="#9f181b" width="<?php echo ($table_width-110);?>"><h1><?php echo $title;?></h1></td>
  <?php
  // include the account select box only if the user has more than one account
  if(number_of_accounts($auth_user)>1) {
    echo "<form action=\"index.php?action=open-mailbox\" method=\"post\">
          <td bgcolor=\"#9f181b\" align=\"right\" valign=\"middle\">";
          display_account_select($auth_user, $selected_account);
    echo "</td>
          </form>";
  }
  ?>
  </tr>
  </table>
  </div>
<?php
}

function do_html_footer() {
  // print an HTML footer
  global $table_width;
?>
  <div id="footer">
  <table width="<?php echo $table_width;?>" cellspacing="0" cellpadding="3" bgcolor="#9f181b" border="0">
  <tr>
  <td bgcolor="#9f181b" width="103" align="right"><img src="images/mail.png"
             width="auto" height="45px" alt="" valign="middle" />
  </td>
  </tr>
  </table>
  </div>
  </div>
  </body>
  </html>
<?php
}

function display_list($auth_user, $accountid) {
  // show the list of messages in this mailbox

  global $table_width;

  if(!$accountid) {
    echo "<p style=\"padding-bottom: 100px\">No mailbox selected.</p>";
  } else {

    $imap = open_mailbox($auth_user, $accountid);
	//echo "<p >".echo $auth_user."</p >";
    if($imap) {
      echo "<table width=\"".$table_width."\" cellspacing=\"0\"
                   cellpadding=\"6\" border=\"0\">";

        $headers = imap_headers($imap);
        $totalsize = sizeof($headers);
       // if ($totalsize >= $currentpage){
            for ($i = $totalsize; $i>=1; $i--) {
                $header = imap_headerinfo($imap, $i);
                echo "<tr><td bgcolor=\"";
                if($i%2) {
                    echo "#ffffff";
                } else {
                    echo "#fce2d1";
                }
                echo "\"><a href=\"index.php?action=view-message&messageid=".($i)."\">";
                //  echo fix_text($header[fromaddress]);
                echo "(".$i.")";
                echo fix_text($header->Date);
                echo "<strong>".fix_text($header->Subject)."</strong>>";
                echo fix_text($header->fromaddress);
                echo "</a></td></tr>\n";
            }
         echo "</table>";
    } else {
      $account = get_account_settings($auth_user, $accountid);
      echo "<p style=\"padding-bottom: 100px\">Could not open mail box ".$account['server'].".</p>";
    }

      /*  echo "<tr>
        </form>
                <form action=\"index.php?action=page-up\" method=\"post\">
                <input type=\"hidden\" name=\"currentpage\" value=\"".($currentpage)."\">
                <td align=\"left\">";
        display_form_button('page-up');
        echo "</td>
                </form>
                <form action=\"index.php?action=page-down\" method=\"post\">
                <input type=\"hidden\" name=\"currentpage\" value=\"".($currentpage)."\">
                <td align=\"right\">";
        display_form_button('page-down');
        echo "</td>
                </form>
                </tr>";*/
  }
}

function fix_text($str)
{	
				   
    $subject = '';
    $subject_array = imap_mime_header_decode($str);
	for ($i=0; $i<count($subject_array); $i++) {
		$subject .= "{$subject_array[$i]->text}\n\n";
	}
	/*
    foreach ($subject_array AS $obj){
		echo "<p>".$obj->charset."</p><br/>";
		if ($obj->charset == "UTF-8" ){
			$subject .= iconv_mime_decode($obj->text,0,"UTF-8"); 
		}else{
			$subject .= rtrim($obj->text, "\t");
		//}
	}
	*/
    return $subject;
}

function display_account_select($auth_user, $selected_account) {
  // show the dropdown box for the user to select from their accounts

  $list = get_account_list($auth_user);
  $accounts = sizeof($list);

  if($accounts>1)  {
    //echo "<select onchange=\"window.location=this.options[selectedIndex].value name=account\">";
    echo "<select onchange=\"window.location=this.options[selectedIndex].value\" name=account>";
	if($selected_account=='') {
      echo "<option value=\"0\" selected>Choose Account</a>";
    }

    for($i = 0; $i<$accounts; $i++) {
      $account = get_account_settings($auth_user, $list[$i]);
      echo "<option value=\"index.php?action=select-account&account=".$list[$i]."\"";
      if($list[$i]==$selected_account) {
        echo " selected";
      }
      echo ">".$account['server']."</option>";
    }
    echo "</select>";
  }
}

function display_account_setup($auth_user) {
  //display empty 'new account' form

  display_account_form($auth_user);
  $list = get_accounts($auth_user);
  $accounts = sizeof($list);

  // display each stored account
  foreach($list as $key => $account)  {
    // display form for each accounts details.
    // note that we are going to send the password for all accounts in the HTML
    // this is not really a very good idea
    display_account_form($auth_user, $account['accountid'], $account['server'], $account['remoteuser'],
                         $account['remotepassword'], $account['type'],
                         $account['port']);
  }
}

function display_account_form($auth_user, $accountid=0, $server='',
                              $remoteuser='', $remotepassword='',
                              $type='IMAP', $port=143) {
  //the default POP3 port is 110, the default IMAP port is 143

  //display one form for account settings

  if($server) {
    $title = $server;
  } else {
    $title = 'New Account';
  }
?>
  <div style="float:left; margin:5px">
  <form method="post" action="index.php?action=store-settings">
  <table bgcolor="#eeeeee" cellpadding="6" cellspacing="0" border="0">
   <tr>
     <th colspan="2" bgcolor="#9f181b">
        <?php echo $title;?>
     </th>
   </tr>
   <tr>
     <td>Server Name:</td>
     <td><input type="text" name="server" maxlength="100" value="<?php echo $server;?>"></td>
   </tr>
   <tr>
     <td>Port Number:</td>
     <td><input type="text" name="port" maxlength="5" value="<?php echo $port; ?>"></td>
   </tr>
   <tr>
     <td>Server Type:</td>
     <?php
       echo "<td><select name=\"type\"><option value=\"IMAP\"";
       if ($type == "IMAP") {
          echo " selected";
       }
       echo ">IMAP</option><option value=\"POP3\"";
       if ($type == "POP3") {
          echo " selected";
       }
       echo ">POP3</option></select></td>";
     ?>
   </tr>
   <tr>
     <td>User Name:</td>
     <td><input type="text" name="remoteuser" value="<?php echo $remoteuser; ?>"></td>
   </tr>
   <tr>
     <td>Password:</td>
     <td><input type="password" name="remotepassword" value="<?php echo $remotepassword; ?>"></td>
   </tr>
   <input type="hidden" name="account" value="<?php echo $accountid; ?>">
   <tr>
   <?php
      if($accountid>0){
        echo "<td align=\"center\">";
        display_form_button('save-changes');
        echo "</td>
              </form>
              <form action=\"index.php?action=delete-account\" method=\"post\">
              <input type=\"hidden\" name=\"account\" value=\"".$accountid."\">
              <td align=\"center\">";
        display_form_button('delete-account');
        echo "</td>
              </form>
              </tr>";
      } else {
        echo "<td colspan=\"2\" align=\"center\">";
        display_form_button('save-changes');
        echo "</td></form>";
      }
   ?>
   </tr>
 </table>
 </div>
 <br />
<?php
}

function display_login_form($action) {
  // display form asking for name and password
?>
  <div align="center">
  <form method="post" action="index.php?action=<?php echo $action; ?>">
  <table bgcolor="#eeeeee" border="0" cellpadding="6" cellspacing="0">
   <tr>
     <th colspan="2" bgcolor="#9f181b"><p>Please Log In</p></th>
   </tr>
   <tr>
     <td>Username:</td>
     <td><input type="text" name="username"/></td></tr>
   <tr>
     <td>Password:</td>
     <td><input type="password" name="passwd"/></td></tr>
   <tr>
     <td colspan="2" align="center">
     <?php display_form_button('log-in'); ?>
     </td></tr>
   <tr>
 </table></form>
 </div>
<?php
}

function display_AddressBook($auth_user) {
  $query = "select addressbook from addressbook where username = '".$auth_user."'";
  $addresslist = array();

  if($conn=db_connect())   {
    $result = $conn->query($query);
    $num = $result->num_rows;
    for($i = 0; $i<$num; $i++) {
      $row = $result->fetch_array();
      array_push($addresslist, $row[0]);
    }
  }
  foreach($addresslist as $addressbook)  {
	  echo "<option value=$addressbook>";
  }
}
function display_Address($auth_user)
{
    $query = "SELECT smtp_port,smtp_server,remotepassword,remoteuser FROM accounts where username = '" . $auth_user . "'";
    $list = array();

    if ($conn = db_connect()) {
        $result = $conn->query($query);
        $num = $result->num_rows;
        for ($i = 0; $i < $num; $i++) {
            $row = $result->fetch_array();
            array_push($list, $row);
        }
    }
    foreach ($list as  $key => $address) {
        echo "<option value= $address[remoteuser]>";
    }
}

function display_form_button($button) {
  //display one of our standard buttons in a form
  echo "<input type=\"image\" src=\"images/".$button.".png\"
        border=\"0\" width=\"149\" height=\"43\"
        alt=\"".format_action($button)."\"></a>";
}

function display_button($button, $extra_parameters = '') {
  //display one of our standard buttons as a href
  $url = "index.php?action=$button";
  if($extra_parameters) {
    $url .= $extra_parameters;
  }
    echo "<tr>
        <td bgcolor=\"#ccccc\" align=\"center\">
		<a href=\"$url\"><img src=\"images/".$button.".png\"
         border=\"0\" width=\"149\" height=\"43\"
         alt=\"".format_action($button)."\" /></a>
		 </td></tr>";
}

function display_mailbutton($button, $extra_parameters = '') {
  //original display one of our standard buttons as a href
  $url = "index.php?action=$button";
  if($extra_parameters) {
    $url .= $extra_parameters;
  }
  echo "<a href=\"$url\"><img src=\"images/".$button.".png\"
         border=\"0\" width=\"149\" height=\"35\"
         alt=\"".format_action($button)."\" /></a>";
}

function display_spacer() {
  //display blank spacer the size of our buttons
  echo "
  <tr>
  <td bgcolor=\"#eeeeee\" align=\"center\">
  <img src=\"images/spacer.gif\" border=\"0\"width=\"149\" height=\"43\" alt=\"\" />
  </td>
  </tr>";
}

function format_action($string) {
  // convert our actions into a displayable string
  // eg "account-setup" becomes "Account Setup"
  $string = str_replace('-', ' ', $string);
  $string = ucwords($string);
  return $string;
}

function display_toolbar($button, $extra_parameters = '') {
  // new function for buttons column draw on of our toolbars

  echo " <div id=\"container\"><div id=\"functions\">
  <table>";

  for($i = 0; $i < 5; $i++) {
    if ($button[$i]) {
      display_button($button[$i], $extra_parameters);
    } 
  }
  echo "</table>
		</div>";
}

function display_mailtoolbar($button, $extra_parameters = '') {
  // origianl function draw on of our toolbars

  $table_width=1118;

  echo "<table width=\"".$table_width."\" cellpadding=\"0\" cellspacing=\"0\" border=\"0\">
        <tr>
        <td bgcolor=\"#eeeeee\" align=\"left\">";

  for($i = 0; $i < 5; $i++) {
    if ($button[$i]) {
      display_mailbutton($button[$i], $extra_parameters);
    } else {
      display_spacer();
    }
  }
  echo "</td>
        </tr>
        </table>";
}

function pretty($string) {
  //prepare a text message for tidy display as HTML

  $string = trim($string);
  $string = htmlspecialchars($string);
  $string = nl2br($string);
  $string = stripslashes($string);

  return $string;
}

function pretty_all($array) {
  //prepare an array of text messages for tidy display as HTML
  foreach ($array as $key => $val) {
    $array[$key] = pretty($val);
  }
  return $array;
}


/**
 * @param $auth_user
 * @param $accountid
 * @param $messageid
 * @param $fullheaders
 */
function display_message($auth_user, $accountid, $messageid, $fullheaders) {
  //show an email message

  $table_width='1118px';

  $buttons = array();
  $buttons[0] = 'reply';
  $buttons[1] = 'reply-all';
  $buttons[2] = 'forward';
  $buttons[3] = 'delete';

  if($fullheaders) {
    $buttons[4] = 'hide-headers';
  } else {
    $buttons[4] = 'show-headers';
  }

  $message = retrieve_message($auth_user, $accountid, $messageid, $fullheaders);
    $imap = open_mailbox($auth_user, $accountid);
  $data = imap_fetchbody($imap, $messageid, 1.1);
  if ($data==""){
      $data = imap_fetchbody($imap, $messageid, 1);
  }

    if (iconv( "GBK","UTF-8", base64_decode($data))){
      $data=iconv( "GBK","UTF-8", base64_decode($data));
  }
    if (iconv( "UTF-8","UTF-8", base64_decode($data))){
        $data=iconv( "UTF-8","UTF-8", base64_decode($data));
    }

  if(sizeof($message)==0)   {
    echo "<p style=\"padding-bottom: 100px\">Cannot retrieve message number ".$messageid.".</p>";
  } else {
    $message = pretty_all($message);
  }
?>
  <table width="<?php echo $table_width; ?>" cellpadding="4" cellspacing="0" border="0">
  <tr>
    <td bgcolor="#eeeeee"><strong>Subject:</strong></td>
    <td bgcolor="#eeeeee"><strong><?php echo fix_text($message[subject]);?></strong></td>
  </tr>
  <tr>
    <td bgcolor="#eeeeee"><strong>From:</strong></td>
    <td bgcolor="#eeeeee"><strong><?php echo fix_text($message[fromaddress]);?></strong></td>
  </tr>
  <tr>
    <td bgcolor="#eeeeee"><strong>To:</strong></td>
    <td bgcolor="#eeeeee"><strong><?php echo $message[toaddress];?></strong></td>
  </tr>
  <tr>
    <td bgcolor="#eeeeee"><strong>CC:</strong></td>
    <td bgcolor="#eeeeee"><strong><?php echo $message[ccaddress];?></strong></td>
  </tr>
  <tr>
    <td bgcolor="#eeeeee"><strong>Received:</strong></td>
    <td bgcolor="#eeeeee"><strong><?php echo $message[date]; ?></strong></td>
  </tr>
  </table>

  <?php display_mailtoolbar($buttons, "&messageid=$messageid");?>
  <table style="table-layout:fixed; width:1124px" cellpadding="4" cellspacing="0" border="0">
  <tr>
    <td bgcolor="#cccccc" style="word-wrap:break-word">
    <?php echo $message[fullheaders]; ?>
    </td>
  </tr>
  </table>

  <table width="1118px" cellpadding="4" cellspacing="0" border="0">
  <tr>
    <td bgcolor="#ffffff">
    <?php
    echo ($data);
    ?>
    </td>
  </tr>
  </table>
<?php
}

/*

  <div width="<?php echo $table_width; ?>" cellpadding="4" cellspacing="0" border="0">
  <textarea>
    <?php echo $message[body]; ?>
  </textarea>
  </div>
*/


function display_new_message_form($auth_user,$from='', $to='',$cc='',$subject='', $message='') {
  // display html form either for a brand new message, or to allow user to
  // edit replies or forwards

  global $table_width;

?>
  <table cellpadding="4" cellspacing="0" border="0" width="1109px" bgcolor="#ffffff">
  <form action="index.php?action=send-message" method="post">
      <tr>
          <td bgcolor="#ffffff">From Address:</td>
          <td bgcolor="#ffffff">
              <input type="text" name="from" value="<?php echo $from; ?>" size="100" list="Address" />
              <datalist id="Address">
                  <?php display_Address($auth_user); ?>
              </datalist>
          </td>
      </tr>

      <tr>
    <td bgcolor="#ffffff">To Address:</td>
    <td bgcolor="#ffffff" >
      <input type="text" name="to" value="<?php echo $to; ?>" size="100" list="AddressBook" />
		<datalist id="AddressBook">
			<?php display_AddressBook($auth_user); ?>
		</datalist>
    </td>
  </tr>
  <tr>
    <td bgcolor="#ffffff">CC Address:</td>
    <td bgcolor="#ffffff">
      <input type="text" name="cc" value="<?php echo $cc; ?>" size="100" />
    </td>
  </tr>
  <tr>
    <td bgcolor="#ffffff">Subject:</td>
    <td bgcolor="#ffffff">
      <input type="text" name="subject" value="<?php echo $subject; ?>" size="100" />
  </tr>
  <tr>
    <td colspan="2" bgcolor="#ffffff">
      <textarea name="message" rows="26" cols="150"><?php echo $message; ?></textarea>
    </td>
  </tr>
    
  <tr>
  	<td  bgcolor="#ffffff">
       <input type="file" STYLE="border:none; font-size:15;" value="upload" name="attachment" onchange=""/>
	</td>
	<td  align="right" bgcolor="#ffffff">
      <?php display_form_button('send-message'); ?>
    </td>
  </tr>
  </form>
  </table>
<?php
}
?>

