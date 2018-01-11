<?php     
/**
* Конвертирует hex и opacity в rgba
* @static
* @param array $data - передает цвет и прозрачность      
* @since 1.0.0
* @return string - rgba свойство
*/
function onp_to_rgba( $data, $returnProperty = false ) {
    if( !sizeof($data) ) return false;

    $get_rgb = OnpStrl_Color::hex2rgb($data['color']);             
    $get_rgb['a'] = $data['opacity'] / 100;
    $rbga = "rgba(".$get_rgb['r'].",".$get_rgb['g'].",".$get_rgb['b'].",".$get_rgb['a'].")"; 
    $get_rgb['str_prop'] = $rbga;

    if( $returnProperty ) return $get_rgb;
    return $rbga;
}
        
/**
* Конвертирует данные в свойства css градиента
* @static 
* @param json object $data - параметры градиента
* @since 1.0.0           
* @return string
*/
function onp_to_gradient( $data, $returnProperty = false ) {          
    $data = json_decode(stripcslashes($data)); 

    $strProperty = 'linear-gradient(' . $data->filldirection . ', '; 
    foreach( $data->color_points as $val ) {
        $val = explode(" ", $val);               
        $rgb = OnpStrl_Color::hex2rgb($val[0]);
        $strProperty .= 'rgba('.$rgb['r'].','.$rgb['g'].','.$rgb['b'].','.$val[2].') '. $val[1] .', ';
    }           
    $strProperty = rtrim($strProperty, ', ') . ')';
    $data->str_prop = $strProperty;

    if( $returnProperty ) return $data;
    return $strProperty;
} 

function onp_smart_whiteout_color_5( $data ) {
    return onp_smart_blackout_color( $data, -5 );
}

function onp_smart_whiteout_color_10( $data ) {
    return onp_smart_blackout_color( $data, -10 );
}

function onp_smart_whiteout_color_15( $data ) {
    return onp_smart_blackout_color( $data, -15 );
}

function onp_smart_whiteout_color_20( $data ) {
    return onp_smart_blackout_color( $data, -20 );
}

function onp_smart_whiteout_color_25( $data ) {
    return onp_smart_blackout_color( $data, -25 );
}

function onp_smart_whiteout_color_30( $data ) {
    return onp_smart_blackout_color( $data, -30 );
}

function onp_smart_whiteout_color_35( $data ) {
    return onp_smart_blackout_color( $data, -35 );
}

function onp_smart_blackout_color_5( $data ) {
    return onp_smart_blackout_color( $data, 5 );
}

function onp_smart_blackout_color_10( $data ) {
    return onp_smart_blackout_color( $data, 10 );
}

function onp_smart_blackout_color_15( $data ) {
    return onp_smart_blackout_color( $data, 15 );
}

function onp_smart_blackout_color_20( $data ) {
    return onp_smart_blackout_color( $data, 20 );
}

function onp_smart_blackout_color_25( $data ) {
    return onp_smart_blackout_color( $data, 25 );
}

function onp_smart_blackout_color_30( $data ) {
    return onp_smart_blackout_color( $data, 30 );
}

function onp_smart_blackout_color_35( $data ) {
    return onp_smart_blackout_color( $data, 35 );
}

/**
* Затемняет цвет
 * 
* @param array $data - передает цвет и прозрачность      
* @since 1.0.0
* @return string - возвращает затемненный цвет в том формате, в котором был принят
*/
function onp_smart_blackout_color( $data, $shift = 20 ) {
    $result = '';
    if( !is_array($data) ) {                
        $convertColor = '';                
        if( preg_match( "/\{.*\}/i", $data ) ) {

            $gradientProp = onp_to_gradient($data, true);
            $maxBlackOutColor = array();
            $colorType = '';  

            foreach( $gradientProp->color_points as $key => $color ) {                       
                $color = explode(' ', $color);   

                if( OnpStrl_Color::is_hex($color[0]) ) {                             
                    $color2hsv = OnpStrl_Color::hex2hsv($color[0]);                          
                    $colorType = 'hex'; 
                } elseif( OnpStrl_Color::is_rgb($color[0]) ) {                            
                    preg_match( "/rbg\(([0-9]{1,3}),(?:\s)?([0-9]{1,3}),(?:\s)?([0-9]{1,3})\)/i", $data, $rgb );

                    $color2hsv = OnpStrl_Color::rgb2hsv($rgb[1],$rgb[2],$rgb[3]);                          
                    $colorType = 'rgb';                             
                } elseif( OnpStrl_Color::is_rgba($color[0]) ) {    

                    preg_match( "/rbg\(([0-9]{1,3}),(?:\s)?([0-9]{1,3}),(?:\s)?([0-9]{1,3}),(?:\s)?([0-9.]+)\)/i", $color[0], $rgba ); 

                    $color2hsv = OnpStrl_Color::rgb2hsv('rgb('.$rgba[1].','.$rgba[2].','.$rgba[3].')');                            
                    $colorType = 'rgba';

                } 

                if( isset($color2hsv) )
                    $maxBlackOutColor[$key] = $color2hsv;                            
            }
            
            if ( !function_exists('opanda_sr_style_max_blackour_color') ) {
                function opanda_sr_style_max_blackour_color( $maxBlackOutColor ) {
                    return $maxBlackOutColor['s'];
                }
            }

            $maxs = max(array_map( 'opanda_sr_style_max_blackour_color', $maxBlackOutColor ));

            foreach( $maxBlackOutColor as $key => $val ) {
                if( $maxs === $val['s'] ) $maxs = $key;
            }

            $hsv = $maxBlackOutColor[$maxs];
            $hsv = onp_color_blackout_plus($hsv, $shift); 

            switch( $colorType ){

                case 'hex':  
                  $convert2hex = OnpStrl_Color::hsv2hex($hsv['h'], $hsv['s'], $hsv['v']);
                  $result = "#".$convert2hex;   

                break;

                case 'rgb':                             
                   $convert2rgb = OnpStrl_Color::hsv2rgb($hsv['h'], $hsv['s'], $hsv['v']);                        
                   $result = "rgb(".$convert2rgb['r'].",".$convert2rgb['g'].",".$convert2rgb['b'].")";  

                break;

                case 'rgba':                            
                   preg_match( "/rbg\(([0-9]{1,3}),(?:\s)?([0-9]{1,3}),(?:\s)?([0-9]{1,3}),(?:\s)?([0-9.]+)\)/i", $color[0], $rgba );
                   $convert2rgba = OnpStrl_Color::hsv2rgb($hsv['h'], $hsv['s'], $hsv['v']);                        
                   $result = "rgba(".$convert2rgba['r'].",".$convert2rgba['g'].",".$convert2rgba['b'].", ".$rgba[4].")"; 

                break;

            } 

        } else{
            if( OnpStrl_Color::is_hex( $data ) ) {

                $hsv = onp_color_blackout_plus(OnpStrl_Color::hex2hsv($data), $shift);
                $convert2hex = OnpStrl_Color::hsv2hex($hsv['h'], $hsv['s'], $hsv['v']);
                $result = "#".$convert2hex;

            } elseif( OnpStrl_Color::is_rgb( $data ) ) {
                preg_match( "/rbg\(([0-9]{1,3}),(?:\s)?([0-9]{1,3}),(?:\s)?([0-9]{1,3})\)/i", $data, $rgb );

                $hsv = onp_color_blackout_plus(OnpStrl_Color::rgb2hsv($rgb[1],$rgb[2],$rgb[3]), $shift);
                $convert2rgb = OnpStrl_Color::hsv2rgb($hsv['h'], $hsv['s'], $hsv['v']);                        
                $result = "rgb(".$convert2rgb['r'].",".$convert2rgb['g'].",".$convert2rgb['b'].")";                      

            } elseif( OnpStrl_Color::is_rgba( $data ) ) {
                preg_match( "/rbg\(([0-9]{1,3}),(?:\s)?([0-9]{1,3}),(?:\s)?([0-9]{1,3}),(?:\s)?([0-9.]+)\)/i", $color[0], $rgba ); 

                $hsv = onp_color_blackout_plus(OnpStrl_Color::rgb2hsv($rgba[1],$rgba[2],$rgba[3]), $shift);
                $convert2rgba = OnpStrl_Color::hsv2rgb($hsv['h'], $hsv['s'], $hsv['v']);
                $result = "rgba(".$convert2rgba['r'].",".$convert2rgba['g'].",".$convert2rgba['b'].", ".$rgba[4].")"; 

            }                    
        }                

    } else {
       $rgba = onp_to_rgba( $data, true );
       $hsv = onp_color_blackout_plus(OnpStrl_Color::rgb2hsv($rgba['r'],$rgba['g'],$rgba['b']), $shift);
       $convert2rgba = OnpStrl_Color::hsv2rgb($hsv['h'], $hsv['s'], $hsv['v']);
       $result = "rgba(".$convert2rgba['r'].",".$convert2rgba['g'].",".$convert2rgba['b'].", ".$rgba['a'].")";
    }
    return $result;
}

/**
 * Затемняет цвет
 * @param type $hsv
 * @since 1.0.0   
 * @return array - возвращает hsv массив с затемнеными свойствами
*/
function onp_color_blackout_plus($hsv, $shift = 20) {
    
    $hsv['s'] = $hsv['s'] + $shift;
    if ( $hsv['s'] > 100 ) $hsv['s'] = 100;
    if ( $hsv['s'] < 0 ) $hsv['s'] = 0;

    $hsv['v'] = $hsv['v'] + $shift;
    if ( $hsv['v'] > 100 ) $hsv['v'] = 100;
    if ( $hsv['v'] < 0 ) $hsv['v'] = 0;

    return $hsv;        
}

function opanda_sr_recolor( $fileUrl, $color, $options ) {
    
    $siteUrl = get_site_url();

    // if it's not a local file, don't process it
    if ( strpos( $fileUrl, $siteUrl ) === false ) return $fileUrl;
    $filePath = ABSPATH . trim( str_replace($siteUrl, '', $fileUrl), '/\\' );

    $rgb = OnpStrl_Color::hex2rgb( $color );
    $targetR = $rgb['r'];
    $targetG = $rgb['g'];
    $targetB = $rgb['b'];
    
    $targetName = $options['output'];
    $targetUrl = $options['url'];
    if ( !is_dir( $targetName ) ) mkdir( $targetName, 0777, true );

    $ext = '.' . pathinfo($filePath, PATHINFO_EXTENSION);
    $targetName = $targetName . basename( $filePath, $ext ) . '_' . trim( $color, '#' ) . $ext;
    $targetUrl = $targetUrl . basename( $filePath, $ext ) . '_' . trim( $color, '#' ) . $ext;
    
    // ToDo: make unlink instead of the return
    if ( is_file( $targetName ) ) return $targetUrl;
    
    $im_src = imagecreatefrompng( $filePath );

    $width = imagesx($im_src);
    $height = imagesy($im_src);

    $im_dst = imagecreatefrompng( $filePath );
    
   // $im_dst = imagecreatetruecolor($width, $height);
    imagealphablending($im_dst, false);
    imagesavealpha($im_dst,true);

    // Note this:
    // Let's reduce the number of colors in the image to ONE
    imagefilledrectangle( $im_dst, 0, 0, $width, $height, 0xFFFFFF );

    for( $x=0; $x<$width; $x++ ) {
        for( $y=0; $y<$height; $y++ ) {

            $alpha = ( imagecolorat( $im_src, $x, $y ) >> 24 & 0xFF );

            $col = imagecolorallocatealpha( $im_dst, $targetR, $targetG, $targetB, $alpha );

            if ( false === $col ) {
                die( 'sorry, out of colors...' );
            }

            imagesetpixel( $im_dst, $x, $y, $col );

        }

    }

    imagepng( $im_dst, $targetName);
    imagedestroy($im_dst);
 
    return $targetUrl;
}

/**
 * Rotates the hue of a given image to the hue of the specified color.
 * 
 * @since 1.0.0
 * @param string $fileUrl An URL of an image to change.
 * @param string $color A color from which we takes HSL values.
 * @param mixed[] $options A set of extra options.
 * @return string An URL to the result image.
 */
function opanda_sr_rehue( $fileUrl, $color, $options ) {
    if ( empty( $fileUrl ) ) return $fileUrl;
    if ( empty( $color ) ) return $fileUrl;
    
    // if it's not a local file, don't process it
    $siteUrl = get_site_url();
    if ( strpos( $fileUrl, $siteUrl ) === false ) return $fileUrl;
    
    $filePath = ABSPATH . trim( str_replace($siteUrl, '', $fileUrl), '/\\' );
    $targetHSL = OnpStrl_Color::hex2hsl( $color );
    
    // defines tagert URL and path
    $targetPath = $options['output'];
    $targetUrl = $options['url'];
    if ( !is_dir( $targetPath ) ) mkdir( $targetPath, 0777, true );
    
    $ext = '.' . pathinfo($filePath, PATHINFO_EXTENSION);
    $targetPath = $targetPath . basename( dirname($filePath) ) . '_' . basename( $filePath, $ext ) . '_' . trim( $color, '#' ) . $ext;
    $targetUrl = $targetUrl . basename( dirname($filePath) ) . '_' . basename( $filePath, $ext ) . '_' . trim( $color, '#' ) . $ext;
    
    // ToDo: make unlink instead of the return
    if ( is_file( $targetPath ) ) return $targetUrl;
    
    // reading the image source
    $im_src = false;
    if ( $ext === '.jpeg' || $ext === '.jpg' ) {
        $im_src = @imagecreatefromjpeg( $filePath );
    }
    
    if ( $im_src === false ) {
        $im_src = imagecreatefrompng( $filePath );
    }
    
    $width = imagesx($im_src);
    $height = imagesy($im_src);
    
    // getting some metrics
    $maxSL = OnpStrl_Color::getMaxSL( $im_src );

    $factorS = 0;
    $factorL = 0;
    if ( $maxSL['s'] > 0 ) $factorS = $targetHSL['s'] / $maxSL['s'];
    if ( $maxSL['l'] > 0 ) $factorL = $targetHSL['l'] / $maxSL['l'];

    $im_dst = imagecreatetruecolor  ( $width, $height );
    
    $colorCache = array();
    
    for( $x = 0; $x < $width; $x++ ) {
        for( $y = 0; $y < $height; $y++ ) {

            $rgb = imagecolorat($im_src, $x, $y);
            
            if ( isset( $colorCache[$rgb] ) ) {
                $col = $colorCache[$rgb];
            } else {
                $cols = imagecolorsforindex($im_src, $rgb);

                $pixelHSL = OnpStrl_Color::rgb2hsl($cols['red'], $cols['green'], $cols['blue']);

                $s = ( $maxSL['s'] > 0 ) ? $pixelHSL['s'] * $factorS : $targetHSL['s'];
                $l = ( $maxSL['l'] > 0 ) ? $pixelHSL['l'] * $factorL : $targetHSL['l'];   

                $newRGB = OnpStrl_Color::hsl2rgb($targetHSL['h'], $s, $l);
                $col = imagecolorallocatealpha( $im_dst, $newRGB['r'], $newRGB['g'], $newRGB['b'], $cols['alpha'] );
                $colorCache[$rgb] = $col;
            }

            if ( false === $col ) {
                die( 'sorry, out of colors...' );
            }

            imagesetpixel( $im_dst, $x, $y, $col );

        }

    }
   
    imagepng( $im_dst, $targetPath);
    imagedestroy($im_dst);
    imagedestroy($im_src);
 
    return $targetUrl;
}