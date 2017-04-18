<?php
require_once("includes/config.php");
require_once("includes/inc.language.php");
require_once("models/maplayers.php");

class SingleMapLayer {
    var $id = 0;
    
    public function SingleMapLayer($id) {
        $this->id = $id;
    }
    
    private function GetBlankLayer() {
      $gislayer = array('displayname' => '', 
        'geoserver_name' => '', 
        'dateadded' => '', 
        'layer_order' => '',
        'allow_display_albertine' => '',
        'allow_display_mountains' => '',
        'allow_display_lakes' => '',
        'allow_identify' => '',
        'disabled' => '',
        'allow_download' => '',
        'projection' => '');
      $attribs['preview_img'] = "";
      $attribs['download_link'] = "";
      $attribs['legend_img'] = "";
      $attribs['gislayer'] = $gislayer;
      return $attribs;
    }
    
    public function GetAttributes() {
        global $siteconfig;
        
        $thumbnail_maxx = 200;
        
        $attribs = $this->GetBlankLayer();
        
        $res = pg_query_params("SELECT *, whenadded::date as dateadded FROM gislayer WHERE id = $1", array($this->id));
        if (!$res) return $attribs; //error
        $row = pg_fetch_array($res, null, PGSQL_ASSOC);
        if (!$row) return $attribs; //no layer with that id
        if (count($row)) $attribs['gislayer'] = $row;
        
        $map_layers = & new MapLayers();
        $l = $map_layers->GetLayerFromGeoserver($row['geoserver_name']);
        if (!count($l)) return $attribs; //layer does not exist in geoserver
        //$attribs[] = $l;
        $current_minx = 0;
        $current_miny = 0;
        $current_maxx = 1;
        $current_maxy = 1;
        $srs_native = (array_keys($l['BoundingBox']));
        if (isset($l['BoundingBox'][$srs_native[0]]['minx'])) 
        {
            $current_minx_t = $l['BoundingBox'][$srs_native[0]]['minx'];  
            $current_minx = floatval($current_minx_t);
        }

        if (isset($l['BoundingBox'][$srs_native[0]]['miny'])) 
        {
            $current_miny_t = $l['BoundingBox'][$srs_native[0]]['miny']; 
            $current_miny = floatval($current_miny_t);
        }

        if (isset($l['BoundingBox'][$srs_native[0]]['maxx'])) 
        {
            $current_maxx_t = $l['BoundingBox'][$srs_native[0]]['maxx'];
            $current_maxx = floatval($current_maxx_t);  
        }

        if (isset($l['BoundingBox'][$srs_native[0]]['maxy'])) 
        {
            $current_maxy_t = $l['BoundingBox'][$srs_native[0]]['maxy']; 
            $current_maxy = floatval($current_maxy_t);
        }    

        $boundingbox_native = $current_minx.",".$current_miny.",".$current_maxx.",".$current_maxy;

        $distance_xmin_xmax = $current_maxx - $current_minx;
        $distance_ymin_ymax = $current_maxy - $current_miny;

        $thumbnail_ratio = ($distance_ymin_ymax/$distance_xmin_xmax);

        $thumbnail_maxy = intval($thumbnail_maxx*$thumbnail_ratio);

        
        $attribs['preview_img'] = "<img src=\"" . $siteconfig['path_geoserver'] . "/cite/wms?service=WMS&version=1.1.1&request=GetMap&layers=" . $l['Name'] . "&styles=&bbox=" . $boundingbox_native . "&width=" . $thumbnail_maxx . "&height=" . $thumbnail_maxy . "&srs=" . $srs_native[0]. "&format=image/png\">";
        $attribs['download_link'] = "<a href=\"" .  $siteconfig['path_geoserver'] . "/ows?service=WFS&version=1.0.0&request=GetFeature&typeName=" . $l['Name'] . "&outputFormat=SHAPE-ZIP\" target=\"_blank\">"; //note: does not include closing </a> tag
        $attribs['legend_img'] = "<img src = \"" . $siteconfig['path_geoserver'] . "/ows?service=wms&REQUEST=GetLegendGraphic&VERSION=1.0.0&FORMAT=image/png&WIDTH=20&HEIGHT=20&LAYER=" . $l['Name']. "\">";
        
        
        return $attribs;
    }
    
    //javascript to validate form data before saving
    public function GetPresaveJavascriptCheck() {
        $js = "";
        //nothing can really be checked: booleans should be valid, and displayname must be checked server-side
        return $js;
    }
    
    //assumes all allow_display values will be submitted
    public function SetAttributes($data, &$save_msg) {        
        $res = pg_query_params("SELECT * FROM gislayer WHERE id = $1", array($data['id']));
        if (!$res) { $save_msg = getMLtext('sql_error'); return 0; }
        $row = pg_fetch_array($res, null, PGSQL_ASSOC);
        if (!$row) { $save_msg = getMLtext('save_layer_unknown', array('id' => $data['id'])); return 0; } //invalid id
        if ($data['allow_display_albertine'] != 't' && $data['allow_display_albertine'] != 'f') { $save_msg = getMLtext('save_layer_invalid_value'); return 0; } 
        if ($data['allow_display_mountains'] != 't' && $data['allow_display_mountains'] != 'f') { $save_msg = getMLtext('save_layer_invalid_value'); return 0; } 
        if ($data['allow_display_lakes'] != 't' && $data['allow_display_lakes'] != 'f') { $save_msg = getMLtext('save_layer_invalid_value'); return 0; } 
        if ($data['disabled'] != 't' && $data['disabled'] != 'f') { $save_msg = getMLtext('save_layer_invalid_value'); return 0; } 
        if ($data['allow_identify'] != 't' && $data['allow_identify'] != 'f') { $save_msg = getMLtext('save_layer_invalid_value'); return 0; } 
        if ($data['allow_download'] != 't' && $data['allow_download'] != 'f') { $save_msg = getMLtext('save_layer_invalid_value'); return 0; } 
        if (getMLtext($data['displayname'], null, "***") == "***") { $save_msg = getMLtext('save_layer_invalid_name', array("displayname" => $data['displayname'])); return 0; } //displayname not found in dictionary
        
        $save_msg = getMLtext('sql_error');
        $res = pg_query_params("UPDATE gislayer SET (displayname, allow_display_albertine, allow_display_mountains, allow_display_lakes, allow_identify, allow_download, disabled, layer_order) = ($1, $2::bool, $3::bool, $4::bool, $5::bool, $6::bool, $7::bool, $8) WHERE id = $9", array($data['displayname'], $data['allow_display_albertine'], $data['allow_display_mountains'], $data['allow_display_lakes'], $data['allow_identify'], $data['allow_download'], $data['disabled'], $data['layer_order'], $data['id']));
        if (!$res) return 0;
        $save_msg = getMLtext('save_layer_saved');
        return -1;
    }
    
     public function DeleteLayer($id, & $save_msg) {
        //check: Id is of a valid user
        $save_msg = getMLtext('layer_unknown');
        $res = pg_query_params("SELECT * FROM \"gislayer\" WHERE id = $1", array($id));
        if (!$res) return 0;
        $row = pg_fetch_array($res);
        if (!$row) return 0;
        
        $save_msg = getMLtext('invalid_form_data');
        $res = pg_query_params("DELETE FROM \"gislayer\" WHERE id = $1", array($id));
        if (!$res) return 0;
        $save_msg = getMLtext('layer_deleted');
        return -1;
    }
}
?>