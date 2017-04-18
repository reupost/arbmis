<?php

class OccurrenceController {
    private $region;
    
    public function OccurrenceController($reg) {
        $this->region = $reg;
    }
    //returns the page HTML
    public function GetOccurrenceList() {
        global $siteconfig;
        global $USER_SESSION;
        global $_CLEAN;

        /* page options */
        $params = array();

        $params['filterlistby'] = (isset($_CLEAN['filterlistby']) ? $_CLEAN['filterlistby'] : '');
        $params['taxon'] = (isset($_CLEAN['taxon']) ? $_CLEAN['taxon'] : '');
        $params['rank'] = (isset($_CLEAN['rank']) ? $_CLEAN['rank'] : '');
        $params['taxonparent'] = (isset($_CLEAN['taxonparent']) ? $_CLEAN['taxonparent'] : '');
        $params['datasetid'] = (isset($_CLEAN['datasetid']) ? $_CLEAN['datasetid'] : '');
        $params['occlist'] = (isset($_CLEAN['occlist']) ? $_CLEAN['occlist'] : 0);
        if ($params['occlist'] != 1) $params['occlist'] = 0; //boolean
        
        foreach (array('x1','x2','y1','y2') as $numeric_param) {
            $params[$numeric_param] = (isset($_CLEAN[$numeric_param])? $_CLEAN[$numeric_param] : '');
        }
        foreach (array('x1','x2','y1','y2') as $numeric_param) {
            if (!is_numeric($params[$numeric_param]) && $params[$numeric_param]>'') $params[$numeric_param] = $params[$numeric_param] . ' [' . htmlspecialchars(getMLtext('invalid')) . ']';
        }

        $params['sortlistby'] = (isset($_CLEAN['sortlistby']) ? $_CLEAN['sortlistby'] : 'dataset_title');
        $params['page'] = GetCleanInteger(isset($_CLEAN['page']) ? $_CLEAN['page'] : '1');
        $params['scrollto'] = (isset($_CLEAN['scrollto']) ? $_CLEAN['scrollto'] : '');

        //download button posts back to the same page (using the previously set criteria, so if they have entered a new filter 
        //and then click 'download this list' the new filter won't be magically applied to the download)
        $params['download'] = GetCleanInteger(isset($_CLEAN['download']) ? $_CLEAN['download'] : 0);

        $session = & new SessionMsgHandler();
        $session_msg = $session->GetSessionMsgMerged($USER_SESSION['id'], "message", true);

        /* get model for page content */
        $tbloccurrence = & new TableOccurrence();
        $tbldataset = & new TableDatasets();

        //advanced criteria
        //put in $setcriteria so will be saved
        $setcriteria = array();
        $adv_criteria = "";
        $searchfields = $tbloccurrence->GetSearchFields();
        foreach ($searchfields as $field) {
            if (isset($_CLEAN[$field])) {
                if ($_CLEAN[$field] != '') {
                    $raw_data = $_GET[$field]; //use GET since AddWhere protects against SQLinject
                    if ($raw_data == '(blank)') $raw_data = '';
                    $tbloccurrence->AddWhere($field, "=", $raw_data); 
                    $setcriteria[$field] = $raw_data; //html_entity_decode($_CLEAN[$field]);
                    $adv_criteria .= ($adv_criteria > ''? "<br/>" : "");
                    $adv_criteria .= getMLtext('occ_' . $field) . " = " . ($_CLEAN[$field] == '(blank)'? "(" . getMLtext('blank') . ")" : $_CLEAN[$field]);
                }
            }
        }
        
        $tbloccurrence->AddWhere('region:' . $this->region, '=', true); 

        if ($params['filterlistby'] > '')
            $tbloccurrence->AddWhere('filtercontent', '***', $params['filterlistby']);
        if ($params['datasetid'] > '') {
            $tbloccurrence->AddWhere('datasetid', '=', $params['datasetid']);
            $params['dataset_title'] = getMLtext('dataset') . ': ' . $tbldataset->GetDatasetTitle($params['datasetid']);
        }
        if ($params['taxon'] > '' && $params['rank'] > '' && $params['taxonparent'] > '') { //all must be present to filter like this
            $tbloccurrence->AddWhere('taxon', '=', array($params['taxon'], $params['rank'], $params['taxonparent']));
            if ($params['rank'] == 'species') {
                $params['taxon_title'] = getMLtext('taxon_species') . ': <i>' . $params['taxonparent'] . ' ' . $params['taxon'] . '</i>';
            } else {
                $rankpos = array_search($params['rank'], $siteconfig['taxonranks']);
                $params['taxon_title'] = getMLtext('taxon_' . $params['rank']) . ': ' . $params['taxon'];
                if ($rankpos>1) $params['taxon_title'] .= ' (' .  getMLtext('taxon_' . $siteconfig['taxonranks'][$rankpos-1]) . ' ' . $params['taxonparent'] . ')';
            }
        }
        $params['bounding_box'] = '';
        if ($params['x1'] > '' && $params['x2'] > '') {
            //normal case.  Where only one limit is specified it is handled in the individual cases below
            $params['bounding_box'] .= ', ' . getMLtext('longitude') . ': ' . $params['x1'] . ' - ' . $params['x2'];
        }
        if ($params['x1'] > '') {
            if (is_numeric($params['x1'])) $tbloccurrence->AddWhere('longitude', '>=', $params['x1']);
            if ($params['x2'] == '') $params['bounding_box'] .= ', ' . getMLtext('longitude') . ' > ' . $params['x1'];
        }
        if ($params['x2'] > '') {
            if (is_numeric($params['x2'])) $tbloccurrence->AddWhere('longitude', '<=', $params['x2']);
            if ($params['x1'] == '') $params['bounding_box'] .= ', ' . getMLtext('longitude') . ' < ' . $params['x2'];
        }
        if ($params['y1'] > '' && $params['y2'] > '') {
            //normal case.  Where only one limit is specified it is handled in the individual cases below
            $params['bounding_box'] .= ', ' . getMLtext('latitude') . ': ' . $params['y1'] . ' - ' . $params['y2'];
        }
        if ($params['y1'] > '') {
            if (is_numeric($params['y1'])) $tbloccurrence->AddWhere('latitude', '>=', $params['y1']);
            if ($params['y2'] == '') $params['bounding_box'] .= ', ' . getMLtext('latitude') . ' > ' . $params['y1'];
        }
        if ($params['y2'] > '') {
            if (is_numeric($params['y2'])) $tbloccurrence->AddWhere('latitude', '<=', $params['y2']);
            if ($params['y2'] == '') $params['bounding_box'] .= ', ' . getMLtext('latitude') . ' < ' . $params['y2'];
        }
        if ($params['bounding_box'] > '') $params['bounding_box'] = substr($params['bounding_box'], strlen(', '));
        
        //must be specified, do not rely on DB since there might be residual session data from another query        
        //if ($tbloccurrence->IsDbOccList(session_id())) {
        $occlist_criteria = "";
        if ($params['occlist']) { 
            $tbloccurrence->AddWhere('occlist', '=', session_id());
            $oldescr = $tbloccurrence->GetOccListDescr(session_id());
            if (is_array($oldescr)) {
                if ($oldescr['layer'] > "") {
                    $occlist_criteria = getMLtext("area_of_interest") . ": " . getMLtext($oldescr['layer']) . " - " . substr($oldescr['feature'],0,50); //arbitrary cut-off length
                }
            }
        }
        
        if ($params['download']) {
            if (!$USER_SESSION['id']) {
                //not logged in
                $session_msg["msg"] .= getMLtext('logged_in_users_only');
                $session_msg["state"] = "error";
            } else {
                $download_ok = $tbloccurrence->Download();
                if (!$download_ok) {
                    $session_msg["msg"] .= getMLtext('download_error');
                    $session_msg["state"] = "error";
                } else {
                    exit();
                }
            }
        }
        


        $norecords = $tbloccurrence->GetRecordsCount();
        $startrecord = GetPageRecordOffset($siteconfig['display_occurrence_per_page'], $norecords, $params['page']);
        $result = $tbloccurrence->GetRecords($params['sortlistby'], $startrecord, $siteconfig['display_occurrence_per_page']);

        /* put results into pager control */
        $arrSorts = array();
        $arrSorts['dataset'] = getMLtext('dataset');
        $arrSorts['scientificname'] = getMLText('scientific_name');
        $arrSorts['fulltaxonomy'] = getMLText('full_taxonomy');
        $arrSorts['institution'] = getMLText('institution');
        $arrSorts['date'] = getMLText('date');
        $arrSorts['place'] = getMLText('place');
        $arrSorts['basisofrecord'] = getMLText('basis_of_record');

        $arrListCols = array();
        $arrListCols['dataset_title'] = array();
        $arrListCols['dataset_title']['heading'] = getMLText('dataset');
        $arrListCols['dataset_title']['link'] = 'out.dataset.php'; 
        $arrListCols['dataset_title']['linkparams'] = array('datasetid' => '_datasetid', 'region' => "'" . $this->region . "'");
        $arrListCols['institutioncode'] = array();
        $arrListCols['institutioncode']['heading'] = getMLText('institution');
        $arrListCols['collectioncode'] = array();
        $arrListCols['collectioncode']['heading'] = getMLText('collection');
        $arrListCols['catalognumber'] = array();
        $arrListCols['catalognumber']['heading'] = getMLText('catalog_number');
        $arrListCols['recordedby'] = array();
        $arrListCols['recordedby']['heading'] = getMLText('recorded_by');
        $arrListCols['basisofrecord'] = array();
        $arrListCols['basisofrecord']['heading'] = getMLText('basis_of_record');
        $arrListCols['colldate'] = array();
        $arrListCols['colldate']['heading'] = getMLText('collection_date');
        $arrListCols['display_taxon'] = array();
        $arrListCols['display_taxon']['heading'] = getMLText('taxon');
        $arrListCols['country'] = array();
        $arrListCols['country']['heading'] = getMLText('country');
        $arrListCols['stateprovince'] = array();
        $arrListCols['stateprovince']['heading'] = getMLText('state_province');
        $arrListCols['localitystart'] = array();
        $arrListCols['localitystart']['heading'] = getMLText('locality');
        $arrListCols['_id'] = array();
        $arrListCols['_id']['heading'] = getMLText('details');
        $arrListCols['_id']['link'] = 'out.occurrence.php';
        $arrListCols['_id']['linkparams'] = array('id' => '_id', 'region' => "'" . $this->region . "'");

        $pager = & new Pager($norecords, $siteconfig['display_occurrence_per_page'], $params['page']);
        $pageform = '';
        $pageopts = '';

        $pager->Setentries($result);
        $pager->SetEntriesDisplay($arrListCols);
        $pager->SetEntryTransform('highertaxonomy',' : ','<br>'); //reformat higher taxonomy data
        if ($params['taxon'] > '' && $params['rank'] > '' && $params['taxonparent'] > '') {
            $setcriteria['taxon'] = $params['taxon'];
            $setcriteria['rank'] = $params['rank'];
            $setcriteria['taxonparent'] = $params['taxonparent'];
        }
        if ($params['datasetid'] > '') $setcriteria['datasetid'] = $params['datasetid'];
        if ($params['occlist']) $setcriteria['occlist'] = 1;
        foreach (array('x1','x2','y1','y2') as $numeric_param) {
            if ($params[$numeric_param] > '' && is_numeric($params[$numeric_param])) $setcriteria[$numeric_param] = $params[$numeric_param];
        }
        $setcriteria['download'] = 0; //once download has happened, reset the download switch

        $pageform = $pager->ShowControlForm(url_for('out.listoccurrence.' . $this->region . '.php'), '', $params['page'], '', 'listanchor', $setcriteria, $params['filterlistby']);
        $pageopts = $pager->ShowPageOptions($params['filterlistby'], $arrSorts, $params['sortlistby']);


        /* page template main */
        $tpl = & new MasterTemplate();
        $tpl->set('site_head_title', getMLText('occurrence_list')); 
        $tpl->set('page_specific_head_content', "<link rel='stylesheet' type='text/css' media='screen' href='css/listoccurrence.css?version=1.0' />
            <script type='text/javascript' src='js/listoccurrence.js?version=1.0'></script>");
        $tpl->set('site_user', $USER_SESSION);
        $tpl->set('session_msg', $session_msg);
        $tpl->set('region', $this->region);

        /* page template body - pass page options to this as well */
        $bdy = & new MasterTemplate('templates/listoccurrence.tpl.php');
        $bdy->set('params', $params);
        $bdy->set('region', $this->region);
        $bdy->set('pager', $pager);
        $bdy->set('pageform', $pageform);
        $bdy->set('pageopts', $pageopts);
        $bdy->set('norecords', $norecords);
        $bdy->set('maxrecords_map', $siteconfig['max_occ_to_map']);
        $bdy->set('maxrecords_download', $siteconfig['max_occ_to_download']);
        $bdy->set('adv_criteria', $adv_criteria);
        $bdy->set('occlist_criteria', $occlist_criteria);
        $bdy->set('user', $USER_SESSION);
        $bdy->set('session_msg', $session_msg);

        $tpl->set('sf_content', $bdy);
        
        return $tpl->fetch('templates/layoutnew.tpl.php');
    }
}
?>