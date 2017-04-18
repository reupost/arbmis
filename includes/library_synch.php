<?php
require_once("includes/config.php");

global $siteconfig;

mysql_connect($siteconfig['media_server'], $siteconfig['media_user'], $siteconfig['media_password']) OR DIE("<p><b>DATABASE ERROR: </b>Unable to connect to database server</p>");
    
function Library_SynchUserToLibrary($username, $email, $password) {
    //from library inc.classDMS
    //there are defaults as follows: 
    //$isHidden=0, $isDisabled=1, $pwdexpiration=''  (set disabled = 0 when account activated)
    //$theme = 'bootstrap'
    //$role = 2 (guest)
    //Dec 2014: role = 0 (user) is now the default
    
    global $USER_SESSION;
    global $siteconfig;
    
    $allok = true;
    foreach ($siteconfig['media_dbs'] as $theme => $dbname) {
        @mysql_select_db($dbname) or die( "<p><b>DATABASE ERROR: </b>Unable to open database</p>");
        //first check if alreay there
        $sql = "SELECT * from tblUsers WHERE login = '" . mysql_real_escape_string($username) . "'";
        $res = mysql_query($sql);
        if (mysql_fetch_row($res)) continue; //aready in library user list
    
        $theme = 'bootstrap';
        $role = 0; //was 2;
        $disabled = 1;
        $comment = 'Added by ARBMIS';
        $language = $USER_SESSION['language'];
        $sql = "INSERT INTO tblusers (login, pwd, fullName, email, `language`, theme, comment, role, disabled, linkedaccount) ";
        $sql .= "VALUES (";
        $sql .= "'" . mysql_real_escape_string($username) . "','" . md5($password) . "','" . mysql_real_escape_string($username) . "',";
        $sql .= "'" . mysql_real_escape_string($email) . "','" . $language . "','" . $theme . "','" . $comment . "',";
        $sql .= $role . "," . $disabled . ", 1)";    
        $allok = mysql_query($sql) && $allok;
    }
    if (!$allok) return 0; //SQL error
    return -1;
}

function Library_DeleteUser($username) {   
    global $siteconfig;
    foreach ($siteconfig['media_dbs'] as $theme => $dbname) {
        @mysql_select_db($dbname) or die( "<p><b>DATABASE ERROR: </b>Unable to open database</p>");
        $sql = "DELETE FROM tblusers WHERE login = '" . mysql_real_escape_string($username) . "'";
        mysql_query($sql);
    }
    return -1;
}

function Library_ActivateUser($username) {
    global $siteconfig;
    foreach ($siteconfig['media_dbs'] as $theme => $dbname) {
        @mysql_select_db($dbname) or die( "<p><b>DATABASE ERROR: </b>Unable to open database</p>");
        $sql = "UPDATE tblusers SET disabled = 0 WHERE login = '" . mysql_real_escape_string($username) . "'";
        mysql_query($sql);
    }
    return -1;
}

function Library_UpdateUserPassword($username, $pwd) {
    global $siteconfig;
    foreach ($siteconfig['media_dbs'] as $theme => $dbname) {
        @mysql_select_db($dbname) or die( "<p><b>DATABASE ERROR: </b>Unable to open database</p>");
        $sql = "UPDATE tblusers SET pwd = '" . md5($pwd) . "' WHERE login = '" . mysql_real_escape_string($username) . "'";
        mysql_query($sql);
    }
    return -1;
}

function Library_AttemptLogin($user, $pwd) {
    global $siteconfig;
    foreach ($siteconfig['media_dbs'] as $theme => $dbname) {
        @mysql_select_db($dbname) or die( "<p><b>DATABASE ERROR: </b>Unable to open database</p>");
        // Try to find user with given login.
        $res = mysql_query("SELECT * FROM tblusers WHERE login = '" . mysql_real_escape_string($user) . "' AND pwd = '" . md5($pwd) . "'");
        if (!$res) continue; //SQL error
        $userdb = mysql_fetch_array($res);
        if (!$userdb) continue; //user not found or pwd incorrect
        if ($userdb['disabled']) return 0; //disabled account
    
        $queryStr = "DELETE FROM tblSessions WHERE " . time() . " - lastAccess > 86400";
        $res = mysql_query($queryStr);
        if (!$res) continue; //sql error
    
        $id = "" . rand() . time() . rand() . "";
        $id = md5($id);
        $lastaccess = time();
        $queryStr = "INSERT INTO tblSessions (id, userID, lastAccess, theme, language, su) ".
                "VALUES ('".$id."', ".$userdb['id'].", ".$lastaccess.", '".$userdb['theme']."', '".$userdb['language']."', 0)";
        $res = mysql_query($queryStr);
        if (!$res) continue; //sql error
            
        $lifetime = 0;
        setcookie("mydms_session", $id, $lifetime, '/library' . $theme . '/', null, null, !false);

        /* add_log_line(); */
    }
    return -1;
}

?>