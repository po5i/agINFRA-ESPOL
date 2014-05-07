<?php
/**
- fsb directory has to be chmod 777
- image will be resized to 980x160
*/
function recurse_copy($src,$dst) {
    $dir = opendir($src); 
    @mkdir($dst); 
    while(false !== ( $file = readdir($dir)) ) { 
        if (( $file != '.' ) && ( $file != '..' )) { 
            if ( is_dir($src . '/' . $file) ) { 
                recurse_copy($src . '/' . $file,$dst . '/' . $file); 
            } 
            else { 
                copy($src . '/' . $file,$dst . '/' . $file); 
            } 
        } 
    } 
    closedir($dir); 
}

define('THUMBNAIL_IMAGE_MAX_WIDTH', 980);
define('THUMBNAIL_IMAGE_MAX_HEIGHT', 160);

function generate_image_thumbnail($source_image_path, $thumbnail_image_path)
{
    list($source_image_width, $source_image_height, $source_image_type) = getimagesize($source_image_path);
    switch ($source_image_type) {
        case IMAGETYPE_GIF:
            $source_gd_image = imagecreatefromgif($source_image_path);
            break;
        case IMAGETYPE_JPEG:
            $source_gd_image = imagecreatefromjpeg($source_image_path);
            break;
        case IMAGETYPE_PNG:
            $source_gd_image = imagecreatefrompng($source_image_path);
            break;
    }
    if ($source_gd_image === false) {
        return false;
    }
    /*$source_aspect_ratio = $source_image_width / $source_image_height;
    $thumbnail_aspect_ratio = THUMBNAIL_IMAGE_MAX_WIDTH / THUMBNAIL_IMAGE_MAX_HEIGHT;
    if ($source_image_width <= THUMBNAIL_IMAGE_MAX_WIDTH && $source_image_height <= THUMBNAIL_IMAGE_MAX_HEIGHT) {
        $thumbnail_image_width = $source_image_width;
        $thumbnail_image_height = $source_image_height;
    } elseif ($thumbnail_aspect_ratio > $source_aspect_ratio) {
        $thumbnail_image_width = (int) (THUMBNAIL_IMAGE_MAX_HEIGHT * $source_aspect_ratio);
        $thumbnail_image_height = THUMBNAIL_IMAGE_MAX_HEIGHT;
    } else {
        $thumbnail_image_width = THUMBNAIL_IMAGE_MAX_WIDTH;
        $thumbnail_image_height = (int) (THUMBNAIL_IMAGE_MAX_WIDTH / $source_aspect_ratio);
    }*/    
    $thumbnail_image_width = THUMBNAIL_IMAGE_MAX_WIDTH;
    $thumbnail_image_height = THUMBNAIL_IMAGE_MAX_HEIGHT;    

    $thumbnail_gd_image = imagecreatetruecolor($thumbnail_image_width, $thumbnail_image_height);
    
    //imagecopyresampled($thumbnail_gd_image, $source_gd_image, 0, 0, 0, 0, $thumbnail_image_width, $thumbnail_image_height, $source_image_width, $source_image_height);        
    imagecopyresized($thumbnail_gd_image, $source_gd_image,     0, 0, 0, 0, $thumbnail_image_width, $thumbnail_image_height, $source_image_width, $source_image_height);

    imagepng($thumbnail_gd_image, $thumbnail_image_path);    
    imagedestroy($source_gd_image);
    imagedestroy($thumbnail_gd_image);
    return true;
}


function process_image_upload($field, $path, $thumb_name)
{
    $temp_image_path = $_FILES[$field]['tmp_name'];
    $temp_image_name = $_FILES[$field]['name'];
    list(, , $temp_image_type) = getimagesize($temp_image_path);
    if ($temp_image_type === NULL) {
        return false;
    }
    switch ($temp_image_type) {
        case IMAGETYPE_GIF:
            break;
        case IMAGETYPE_JPEG:
            break;
        case IMAGETYPE_PNG:
            break;
        default:
            return false;
    }
    $uploaded_image_path = $path . $temp_image_name ;
    move_uploaded_file($temp_image_path, $uploaded_image_path);
    $thumbnail_image_path = $path . $thumb_name;
    $result = generate_image_thumbnail($uploaded_image_path, $thumbnail_image_path);
    return $result ? array($uploaded_image_path, $thumbnail_image_path) : false;
}





$new_directory = "ag_".htmlentities($_REQUEST["name"]);	//TODO: validate more
$new_path = "/var/www/aginfra/fsb/".$new_directory."/";
if(!file_exists($new_path))
    mkdir($new_path);
else{
    $out = array(
                "output" => "component already exists",
                "component_name" => $new_directory,
                "url_of_component" => 'http://'.$_SERVER['HTTP_HOST'].'/aginfra/fsb/'.$new_directory
            );
    header('Content-Type: application/json');
    echo json_encode($out);
    return;
}




//copy template
recurse_copy("/var/www/aginfra/fsb/template/",$new_path);


//copy the logo parameter
if(isset($_FILES["logo"]))
    $result = process_image_upload('logo',$new_path."img/", "aginfra_logo.png");

//replace parameters
$color = $_REQUEST["color"];
$str=file_get_contents($new_path.'simple.css');
$str=str_replace("[color]", $color, $str);
file_put_contents($new_path.'simple.css', $str);

$facets = array();
$facet_select_view = "";
$facet_accordion = "";

$facet_language = $_REQUEST["facet_language"];
if($facet_language == "true"){
    $facets[] = '
        "aginfra_eu.lom_general_language_type.value" : {
            terms : {
                field : "aginfra_eu.lom_general_language_type.value",
                size : 20
            },
            facet_filter : {
                match_all: { }
            }
        }';
    $facet_select_view .= <<<VIEW
new esbbSearchFacetSelectView( { 
            facetName: 'aginfra_eu.lom_general_language_type.value',
            headerName: 'Language',
            el: '#' + this.options.id_prefix + '-language-selector',
            searchQueryModel: this.query,
            model: this.model,
            template: '\
                {{#items}}\
                    <li><a class="esbb-facet-item" href="{{name}}"><em>{{count}}</em><img src="../../search/img/flags/{{name}}.png"> {{name}}<span>{{perc}}%</span></a></li>\
                {{/items}}\
                {{^items}}\
                    <li><a href="#"><em>01</em>None<span>0%</span></a></li>\
                {{/items}}\
                ',
            templateNoResults: '\
                    <li><a href="#"><em>0</em>No Results<span>0%</span></a></li>\
                ',
        } );
VIEW;
    $facet_accordion .= <<<LI
                 <li class="mail">\
                    <a href="#">Language<span>100%</span></a>\
                    <ul class="sub-menu" id="{{prefix}}-language-selector"></ul>\
                </li>\

LI;
}

$facet_format = $_REQUEST["facet_format"];
if($facet_format == "true"){
    $facets[] = '
        "aginfra_eu.lom_technical_format_type.value" : {
            terms : {
                field    : "aginfra_eu.lom_technical_format_type.value",
                size : 20
            },
            facet_filter : {
                match_all: { }
            }
        }
        ';
    $facet_select_view .= <<<VIEW
new esbbSearchFacetSelectView( { 
            facetName: 'aginfra_eu.lom_technical_format_type.value',
            headerName: 'Format',
            el: '#' + this.options.id_prefix + '-format-selector',
            searchQueryModel: this.query,
            model: this.model,
            template: '\
                {{#items}}\
                    <li><a class="esbb-facet-item" href="{{name}}"><em>{{count}}</em>{{name}}<span>{{perc}}%</span></a></li>\
                {{/items}}\
                {{^items}}\
                    <li><a href="#"><em>01</em>None<span>0%</span></a></li>\
                {{/items}}\
                ',
            templateNoResults: '\
                    <li><a href="#"><em>0</em>No Results<span>0%</span></a></li>\
                ',
        } );
VIEW;
    $facet_accordion .= <<<LI
                <li class="sign">\
                    <a href="#">Format<span>100%</span></a>\
                    <ul class="sub-menu" id="{{prefix}}-format-selector"></ul>\
                </li>\

LI;
}

$facet_context = $_REQUEST["facet_context"];
if($facet_context == "true"){
    $facets[] = '
        "aginfra_eu.lom_educational_context_value_type.value" : {
            terms : {
                field : "aginfra_eu.lom_educational_context_value_type.value",
                size : 20
            },
            facet_filter : {
                match_all: { }
            }
        }
        ';
    $facet_select_view .= <<<VIEW
new esbbSearchFacetSelectView( { 
            facetName: 'aginfra_eu.lom_educational_context_value_type.value',
            headerName: 'Context',
            el: '#' + this.options.id_prefix + '-context-selector',
            searchQueryModel: this.query,
            model: this.model,
            template: '\
                {{#items}}\
                    <li><a class="esbb-facet-item" href="{{name}}"><em>{{count}}</em>{{name}}<span>{{perc}}%</span></a></li>\
                {{/items}}\
                {{^items}}\
                    <li><a href="#"><em>01</em>None<span>0%</span></a></li>\
                {{/items}}\
                ',
            templateNoResults: '\
                    <li><a href="#"><em>0</em>No Results<span>0%</span></a></li>\
                ',
        } );
VIEW;
    $facet_accordion .= <<<LI
                <li class="files">\
                    <a href="#">Context<span>100%</span></a>\
                    <ul class="sub-menu" id="{{prefix}}-context-selector"></ul>\
                </li>\

LI;
}

$facet_dataset = $_REQUEST["facet_dataset"];
if($facet_dataset == "true"){
    $facets[] = '
        "aginfra_eu.dataset.value" : {
            terms : {
                field    : "aginfra_eu.dataset.value",
                size : 20
            },
            facet_filter : {
                match_all: { }
            }
        }
        ';
    $facet_select_view .= <<<VIEW
new esbbSearchFacetSelectView( { 
            facetName: 'aginfra_eu.dataset.value',
            headerName: 'Dataset',
            el: '#' + this.options.id_prefix + '-dataset-selector',
            searchQueryModel: this.query,
            model: this.model,
            template: '\
                {{#items}}\
                    <li><a class="esbb-facet-item" href="{{name}}"><em>{{count}}</em>{{name}}<span>{{perc}}%</span></a></li>\
                {{/items}}\
                {{^items}}\
                    <li><a href="#"><em>01</em>None<span>0%</span></a></li>\
                {{/items}}\
                ',
            templateNoResults: '\
                    <li><a href="#"><em>0</em>No Results<span>0%</span></a></li>\
                ',
        } );
VIEW;
    $facet_accordion .= <<<LI
                <li class="cloud">\
                    <a href="#">Dataset<span>100%</span></a>\
                    <ul class="sub-menu" id="{{prefix}}-dataset-selector"></ul>\
                </li>\

LI;
}

$str=file_get_contents($new_path.'index.php');
$str=str_replace("[facets]", implode(",", $facets), $str);
file_put_contents($new_path.'index.php', $str);

$str=file_get_contents($new_path.'simple-view.js');
$str=str_replace("[facet_select_view]", $facet_select_view, $str);
file_put_contents($new_path.'simple-view.js', $str);

$str=file_get_contents($new_path.'simple-view.js');
$str=str_replace("[facet_accordion]", $facet_accordion, $str);
file_put_contents($new_path.'simple-view.js', $str);

//output of the method
$out = array(
				"output" => "component created",
				"component_name" => $new_directory,
				"url_of_component" => 'http://'.$_SERVER['HTTP_HOST'].'/aginfra/fsb/'.$new_directory
			);
header('Content-Type: application/json');
echo json_encode($out);
?>