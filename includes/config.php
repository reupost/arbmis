<?php
$siteconfig = array();
 global $siteconfig;
 global $USER_SESSION;

///////////////////////////////////////////////////
// SITE INFORMATION
$siteconfig['site_version'] = '0.1';
$siteconfig['site_year'] = '2014';
$siteconfig['site_title'] = 'ARBMIS';
$siteconfig['site_descr'] = 'ARCOS Biodiversity Management Information System';
$siteconfig['site_keywords'] = "environmental, impact, assessment, gbif, checklist, rwanda, national, biodiversity, arcos";     
 
$siteconfig['copyright_org'] = 'Reuben Roberts';
$siteconfig['copyright_web'] = 'http://reubenroberts.co.za';
$siteconfig['copyright_link'] = "(c) <a href='" . $siteconfig['copyright_web'] . "' target='_top'>" . $siteconfig['copyright_org'] . "</a> " . $siteconfig['site_year'];  
  
$siteconfig['admin_name'] = 'Reuben Roberts';
$siteconfig['admin_email'] = 'reupost@gmail.com'; 

// EMAIL INFORMATION
$siteconfig['email_from'] = "From: ".$siteconfig['admin_name']." " . $siteconfig['admin_email'] ."\r\n";
$siteconfig['email_from_team'] = "ARBMIS Team";
$siteconfig['email_reply_to'] = "Reply-To: arbmis@arcos.org.rw\r\n";
$siteconfig['email_html_enc'] = "Content-type: text/html; charset=iso-8859-1\r\n";	
$siteconfig['email_return_to'] = "Return-Path: arbmis@arcos.org.rw\r\n";

// DISPLAY INFORMATION
$siteconfig['display_date_format'] = "d-m-Y";     //dates are formatted as day-month-4 digit year
$siteconfig['display_users_per_page'] = 25;       //no. of user records to display per page of results
$siteconfig['display_datasets_per_page'] = 25;    //no. of dataset records to display per page of results
$siteconfig['display_occurrence_per_page'] = 100; //no. of occurrence records to display per page of results
$siteconfig['display_species_per_page'] = 50;     //no. of species records to display per page of results
$siteconfig['display_gislayers_per_page'] = 25;   //no. of GIS layer records to display per page of results

				
// FILE PATH INFORMATION
$siteconfig['path_basefolder'] = 'c:/ARBMIS/web';
$siteconfig['path_baseurl'] = 'http://localhost';
$siteconfig['path_templates'] = $siteconfig['path_baseurl'] . '/templates';
$siteconfig['path_lib'] = $siteconfig['path_baseurl'] . '/lib';
$siteconfig['path_ipt'] = $siteconfig['path_baseurl'] . '/ipt';
$siteconfig['path_geoserver'] = $siteconfig['path_baseurl'] . '/geoserver'; //cite
$siteconfig['path_controllers'] = $siteconfig['path_baseurl'] . '/controllers';
	
$siteconfig['path_java_exe'] = 'C:\Program Files\Java\jdk1.8.0_20\jre\bin\java.exe';
$siteconfig['path_psql_exe'] = 'C:\Program Files (x86)\PostgreSQL\9.3\bin\psql.exe'; //since Apache seems not to always have access to the PATH env. variable

$siteconfig['path_datasets_files'] = 'uploads/datasets/files';
$siteconfig['path_datasets'] = 'uploads/datasets';
$siteconfig['path_datasets_spp'] = 'uploads/datasets/spp';
$siteconfig['path_user_images'] = 'uploads/users';
$siteconfig['path_tmp'] = $siteconfig['path_basefolder'] . '/tmp';
$siteconfig['url_tmp'] = $siteconfig['path_baseurl'] . '/tmp';
$siteconfig['url_img'] = $siteconfig['path_baseurl'] . '/images';

$siteconfig['mail_log'] = 'C:/ARBMIS/ARBMIS_logs/bulletin.log';
	
// TAXONOMY LEVEL INFORMATION
$siteconfig['taxonranks'] = array('*root*', 'kingdom', 'phylum', 'class', 'order', 'family', 'genus', 'species');

// SIZE CONSTRAINTS
$siteconfig['max_imgfile_x'] = 300; //images maximum pixels width
$siteconfig['max_imgfile_y'] = 300; //images maximum pixels height

$siteconfig['max_occ_to_map'] = 1000;         //maximum no. of (filtered) occurrence records to put on map
$siteconfig['max_occ_to_download'] = 10000;   //max occurrence records that can be downloaded
$siteconfig['max_spp_to_download'] = 10000;   //max species records that can be downloaded
	
// SITE CAPTCHA
$siteconfig['recaptcha_private_key'] = '6Le5m_ISAAAAAK_nGi2_AMJSu9ODwIPn34tiR5WJ';
$siteconfig['recaptcha_public_key'] = '6Le5m_ISAAAAAIRTE-_X1iVnB0E51hyJ55pF4iAw '; //for *.arcosnetwork.org

// MEDIA LIBRARY DATABASE SETTINGS
$siteconfig['media_server'] = 'localhost';
$siteconfig['media_user'] = 'root';
$siteconfig['media_password'] = 'root';
$siteconfig['media_dbs'] = array("land" => "library_land", "lake" => "library_lake", "mnt" => "library_mnt", "eia" => "library_eia");

// PORTAL DATABASE SETTINGS
$siteconfig['dwc_db'] = 'arbmis'; //for main database: postgreSQL
$siteconfig['dwc_server'] = 'localhost';
$siteconfig['dwc_user'] = 'postgres';
$siteconfig['limbo_user'] = 'limbouser'; //limited user with rights to limbo schema in db
$siteconfig['dwc_password'] = '';
$siteconfig['dwc_port'] = '5432'; 
$siteconfig['schema_dwc'] = 'public'; 
$siteconfig['schema_limbo'] = 'limbo'; //used for importing raw SQL from DwCA's
$siteconfig['special_layers'] = array('cite:occurrence','cite:occurrence_overview',
                    'cite:occurrence_albertine','cite:occurrence_overview_albertine',
                    'cite:occurrence_mountains','cite:occurrence_overview_mountains',
                    'cite:occurrence_lakes','cite:occurrence_overview_lakes'); //special GIS layers, not user-configurable

$siteconfig['dwc_db_conn'] = pg_connect("host=" . $siteconfig['dwc_server'] . " port=" . $siteconfig['dwc_port'] . " dbname=" . $siteconfig['dwc_db'] . " user=" . $siteconfig['dwc_user']);

// USER SESSION 
session_start();
if (!isset($_SESSION['USER_SESSION'])) {
    $USER_SESSION = array();    
    $USER_SESSION['username'] = ''; //not logged in
    $USER_SESSION['id'] = 0;
    $USER_SESSION['siterole'] = 'guest';
    $USER_SESSION['email'] = '';
    //session_register("USER_SESSION"); deprecated
    $_SESSION["USER_SESSION"] = $USER_SESSION;
} else {   
   $USER_SESSION = $_SESSION['USER_SESSION'];   
}
$USER_SESSION['language'] = (isset($_COOKIE['arbmis_lang'])? $_COOKIE['arbmis_lang'] : 'en_GB');
if ($USER_SESSION['language'] != 'en_GB' && $USER_SESSION['language'] != 'fr_FR') $USER_SESSION['language'] = 'en_GB'; //in case of bad cookie

require_once($siteconfig['path_basefolder'] . "/includes/tools.php");

// 'clean' user URL parameters to avoid SQL injection hacks
$_CLEAN = Sanitize($_GET);
?>