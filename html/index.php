<?php
ini_set('display_errors', 'On');

$default = array();
// Notes: Height (h) and Width (w) do not have defaults.
$default['bg'] = (isset($_ENV['WALLPAPER_BG']) ? ($_ENV['WALLPAPER_BG'] == 'NULL' ? NULL : $_ENV['WALLPAPER_BG']) : 'bg');
$default['bl'] = (isset($_ENV['WALLPAPER_BL']) ? ($_ENV['WALLPAPER_BL'] == 'NULL' ? NULL : $_ENV['WALLPAPER_BL']) : NULL);
$default['blp'] = floatval(isset($_ENV['WALLPAPER_BLP']) ? $_ENV['WALLPAPER_BLP'] : 0);
$default['blm'] = (isset($_ENV['WALLPAPER_BLM']) ? $_ENV['WALLPAPER_BLM'] : '0');
$default['br'] = (isset($_ENV['WALLPAPER_BR']) ? ($_ENV['WALLPAPER_BR'] == 'NULL' ? NULL : $_ENV['WALLPAPER_BR']) : NULL);
$default['brp'] = floatval(isset($_ENV['WALLPAPER_BRP']) ? $_ENV['WALLPAPER_BRP'] : 0);
$default['brm'] = (isset($_ENV['WALLPAPER_BRM']) ? $_ENV['WALLPAPER_BRM'] : '0');
$default['f'] = (isset($_ENV['WALLPAPER_F']) ? $_ENV['WALLPAPER_F'] : 'bmp');
$default['README'] = (isset($_ENV['WALLPAPER_README']) ? $_ENV['WALLPAPER_README'] : 'https://github.com/UNT-CAS-ITS/Wallpaper-Server');
$default['tl'] = (isset($_ENV['WALLPAPER_TL']) ? ($_ENV['WALLPAPER_TL'] == 'NULL'? NULL : $_ENV['WALLPAPER_TL']) : 'est-1890-UNT-University-of-North-Texas-white');
$default['tlp'] = floatval(isset($_ENV['WALLPAPER_TLP']) ? $_ENV['WALLPAPER_TLP'] : .3);
$default['tlm'] = (isset($_ENV['WALLPAPER_TLM']) ? $_ENV['WALLPAPER_TLM'] : '.08');
$default['tr'] = (isset($_ENV['WALLPAPER_TR']) ? ($_ENV['WALLPAPER_TR'] == 'NULL'? NULL : $_ENV['WALLPAPER_TR']) : NULL);
$default['trp'] = floatval(isset($_ENV['WALLPAPER_TRP']) ? $_ENV['WALLPAPER_TRP'] : 0);
$default['trm'] = (isset($_ENV['WALLPAPER_TRM']) ? $_ENV['WALLPAPER_TRM'] : '0');

error_log('Default: '. json_encode($default));

$image = array();
$image['Background'] = array();
$image['Background']['pathf'] = 'assets/%s.png';
$image['BottomLeft'] = array();
$image['BottomLeft']['pathf'] = 'assets/%s.png';
$image['BottomRight'] = array();
$image['BottomRight']['pathf'] = 'assets/%s.png';
$image['TopLeft'] = array();
$image['TopLeft']['pathf'] = 'assets/%s.png';
$image['TopRight'] = array();
$image['TopRight']['pathf'] = 'assets/%s.png';

error_log('Image: '. json_encode($image));



function add_image_part($corner, $abbr) {
    global $default, $image;
    error_log('add_image_part Params: '. $corner .', '. $abbr);

    if (!file_exists(sprintf($image[$corner]['pathf'], $image[$corner]['filename']))) {
        die($corner .' image ('. $abbr .') not found; check the README: <a href="'. $default['README'] .'#more-query-string-parameters">'. $default['README'] .'</a>');
    }

    $image[$corner]['width_percentage'] = floatval(isset($_GET[$abbr .'p']) ? $_GET[$abbr .'p'] : $default[$abbr .'p']);
    error_log('add_image_part Width Percentage: '. $image[$corner]['width_percentage']);

    if ($image[$corner]['width_percentage'] < 0 || $image[$corner]['width_percentage'] > 1) {
        die($corner .' width percentage ('. $abbr .'p) out of range; check the README: <a href="'. $default['README'] .'#more-query-string-parameters">'. $default['README'] .'</a>');
    }


    $image[$corner]['margin_percentage_orig'] = isset($_GET[$abbr .'m']) ? $_GET[$abbr .'m'] : $default[$abbr .'m'];
    error_log('add_image_part Margin: '. $image[$corner]['margin_percentage_orig']);

    $image[$corner]['margin_percentage'] = explode(',', $image[$corner]['margin_percentage_orig']);

    if (count($image[$corner]['margin_percentage']) > 2) {
        die($corner .' margin percentage ('. $abbr .'m) has too many values; check the README: <a href="'. $default['README'] .'#more-query-string-parameters">'. $default['README'] .'</a>');
    } else {
        foreach ($image[$corner]['margin_percentage'] as $mp) {
            if ($mp < 0 || $mp > 1) {
                die($corner .' margin percentage ('. $abbr .'m) out of range; check the README: <a href="'. $default['README'] .'#more-query-string-parameters">'. $default['README'] .'</a>');
            }
        }
    }
    error_log('add_image_part Margin (array): '. print_r($image[$corner]['margin_percentage'], True));
}



function add_image_to_wallpaper($corner, $abbr) {
    global $default, $height, $image, $wallpaper, $width;
    error_log('add_image_to_wallpaper Params: '. $corner .', '. $abbr);

    list($w, $h) = getimagesize(sprintf($image[$corner]['pathf'], $image[$corner]['filename']));
    error_log('add_image_to_wallpaper Width:  '. $w);
    error_log('add_image_to_wallpaper Height: '. $h);
    error_log('add_image_to_wallpaper Percentage: '. $image[$corner]['width_percentage']);

    $nw = $width * $image[$corner]['width_percentage'];
    error_log('add_image_to_wallpaper New Width:  '. $nw);
    $nh = ($h/$w) * $nw;
    error_log('add_image_to_wallpaper New Height: '. $nh);

    if (count($image[$corner]['margin_percentage']) == 1) {
        (int)$m1 = $image[$corner]['margin_percentage'][0];
        (int)$m2 = $image[$corner]['margin_percentage'][0];
    } else {
        (int)$m1 = $image[$corner]['margin_percentage'][0];
        (int)$m2 = $image[$corner]['margin_percentage'][1];
    }
    error_log('add_image_to_wallpaper Margin TB %: '. $m1);
    error_log('add_image_to_wallpaper Margin LR %: '. $m2);

    if ($corner == 'BottomLeft') {
        $x = $width * $m2;
        $y = ($height - $nh - ($height * $m1));
    } elseif ($corner == 'BottomRight') {
        $x = ($width - $nw - ($width * $m2));
        $y = ($height - $nh - ($height * $m1));
    } elseif ($corner == 'TopLeft') {
        $x = $width * $m2;
        $y = $height * $m1;
    } elseif ($corner == 'TopRight') {
        $x = ($width - $nw - ($width * $m2));
        $y = $height * $m1;
    } else {
        die('Internal Error: Unsupported Corner: [add_image_to_wallpaper] '. $corner);
    }

    error_log('add_image_to_wallpaper Dst X: '. $x);
    error_log('add_image_to_wallpaper Dst Y: '. $y);

    $img_part = imagecreatefrompng(sprintf($image[$corner]['pathf'], $image[$corner]['filename']));
    imagecopyresampled($wallpaper, $img_part, $x, $y, 0, 0, $nw, $nh, $w, $h);
}



function get_abbr($corner) {
    error_log('get_abbr corner: '. $corner);

    if ($corner == 'BottomLeft') {
        $return = 'bl';
    } elseif ($corner == 'BottomRight') {
        $return = 'br';
    } elseif ($corner == 'TopLeft') {
        $return = 'tl';
    } elseif ($corner == 'TopRight') {
        $return = 'tr';
    } else {
        die('Internal Error: Unsupported Corner: [get_abbr] '. $corner);
    }

    error_log('get_abbr return: '. $return);
    return $return;
}



error_log('# Validating ...');

if (!isset($_GET['h']) || !isset($_GET['w'])) {
    die('Usage incorrect; check the README: <a href="'. $default['README'] .'">'. $default['README'] .'</a>');
}

$width = intval($_GET['w']);
error_log('Width:  '. $width);

$height = intval($_GET['h']);
error_log('Height: '. $height);


$format = isset($_GET['f']) ? $_GET['f'] : $default['f'];
if ($format != ('png' || 'bmp')) {
    die('Unsupported format (f); check the README: <a href="'. $default['README'] .'#more-query-string-parameters">'. $default['README'] .'</a>');
}
error_log('Format: '. $format);


$image['Background']['filename'] = isset($_GET['bg']) ? ($_GET['bg'] == 'NULL' ? NULL : $_GET['bg']) : $default['bg'];
if (!empty($image['Background']['filename'])) {
    if (!file_exists(sprintf($image['Background']['pathf'], $image['Background']['filename']))) {
        die('Background image ('. $image['Background']['filename'] .') not found; check the README: <a href="'. $default['README'] .'#more-query-string-parameters">'. $default['README'] .'</a>');
    }
}


foreach (array('BottomLeft', 'BottomRight', 'TopLeft', 'TopRight') as $corner) {
    $abbr = get_abbr($corner);

    $image[$corner]['filename'] = isset($_GET[$abbr]) ? ($_GET[$abbr] == 'NULL' ? NULL : $_GET[$abbr]) : $default[$abbr];
    if (!empty($image[$corner]['filename'])) {
        add_image_part($corner, $abbr);
    }
}

error_log('Image: '. json_encode($image));



error_log('# Executing ...');

$filename = $width . '_' . $height .'_'. $format .'_'. $image['Background']['filename'] .'_'. $image['BottomLeft']['filename'] .'_'. $image['BottomLeft']['width_percentage'] .'_'. $image['BottomLeft']['margin_percentage_orig'] .'_'. $image['BottomRight']['filename'] .'_'. $image['BottomRight']['width_percentage'] .'_'. $image['BottomRight']['margin_percentage_orig'] .'_'. $image['TopLeft']['filename'] .'_'. $image['TopLeft']['width_percentage'] .'_'. $image['TopLeft']['margin_percentage_orig'] .'_'. $image['TopRight']['filename'] .'_'. $image['TopRight']['width_percentage'] .'_'. $image['TopRight']['margin_percentage_orig'] .'.'. $format;
$filename = str_replace(array('"', "'", ' ', ','), '_', $filename);
error_log('Filename: '. $filename);

$fullfilepath = 'cache/' . $filename ;
error_log('Full Filename: '. $fullfilename);

if (file_exists($fullfilepath)) {
    error_log('FullPath Exists!');

    touch($fullfilepath);
} else {
    error_log('FullPath DOES NOT Exist!');

    $wallpaper = imagecreatetruecolor($width, $height);


    if (!empty($image['Background']['filename'])) {
        error_log('## Adding Background ...');
        list($bgw, $bgh) = getimagesize(sprintf($image['Background']['pathf'], $image['Background']['filename']));
        $bg = imagecreatefrompng(sprintf($image['Background']['pathf'], $image['Background']['filename']));
        imagecopyresampled($wallpaper, $bg, 0, 0, 0, 0, $width, $height, $bgw, $bgh);
    }


    foreach (array('BottomLeft', 'BottomRight', 'TopLeft', 'TopRight') as $corner) {
        error_log('## Adding Corner: '. $corner .' ...');
        $abbr = get_abbr($corner);

        if (!empty($image[$corner]['filename'])) {
            add_image_to_wallpaper($corner, $abbr);
        }
    }


    error_log('## Saving as Format: '. $format .' ...');
    if ($format = 'png') {
        imagealphablending($wallpaper, false);
        imagesavealpha($wallpaper, true);
        imagepng($wallpaper, $fullfilepath);
    } else {
        imagewbmp($wallpaper, $fullfilepath);
    }

    error_log('## Done Creating Wallpaper in Cache!');
}

error_log('# Serving from Cache ...');

$filesize = filesize($fullfilepath);
error_log('File Size:'. $filesize);

header('Content-Description: File Transfer');
header('Content-type: image/'. $format);
header('Content-disposition: filename="'.$filename .'"');
header('Content-Length: '. $filesize);
readfile($fullfilepath);
?>