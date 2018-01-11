<?php
/**
 * Color
 *
 * Small class for working with different types of color values.
 *
 * Color gives you simple methods for working with the different types of
 * color values; RGB, Hexadecimal, HSL and HSV values. Color allows you to
 * convert between these four different methods with ease.
 *
 * @package     Color
 * @author      Nathan Lucas <hide@address.com>
 * @link        http://www.gimpstraw.com/
 * @copyright   Copyright (c) 2008, Nathan Lucas
 * @version     0.8.0
 */
class OnpStrl_Color {
	
    /**
     * Convert hexadcimal value to RGB(r, g, b)
     *
     * @access  public
     * @param   string $hex  hexadecimal color code 
     * @return  array
     */
    public static function hex2rgb($hex) {            
            try {
                    if( preg_match("/\#([A-z0-9]{3})$/i", $hex, $correct) )
                        $hex = '#'.$correct[1].$correct[1];

                    $hex = self::checkHex($hex);
                    $dec = hexdec($hex);
                    return array("r" => (0xFF & ($dec >> 0x10)), "g" => (0xFF & ($dec >> 0x8)), "b" => (0xFF & $dec));
            } catch (Exception $e) {
                    trigger_error($e->getMessage(), E_USER_ERROR);
                    return false;
            }
    }

    /**
     * Convert hexadcimal value to HSV(h, s, v)
     *
     * hex2hsv() first runs hex2rgb() then passes those values to rgb2hsv().
     *
     * @access  public
     * @param   string $hex  hexadecimal color code 
     * @return  array
     */
    public static function hex2hsv($hex) {
            if( preg_match("/\#([A-z0-9]{3})$/i", $hex, $correct) )
                $hex = '#'.$correct[1].$correct[1];

            list($r, $g, $b) = array_values(self::hex2rgb($hex));
            return self::rgb2hsv($r, $g, $b);
    }

    /**
     * Convert hexadcimal value to HSL(h, s, l)
     *
     * hex2hsl() first runs hex2rgb() then passes those values to rgb2hsl().
     *
     * @access  public
     * @param   string $hex  hexadecimal color code 
     * @return  array
     */
    public static function hex2hsl($hex) {
            if( preg_match("/\#([A-z0-9]{3})$/i", $hex, $correct) )
                $hex = '#'.$correct[1].$correct[1];

            list($r, $g, $b) = array_values(self::hex2rgb($hex));
            return self::rgb2hsl($r, $g, $b);
    }

    /**
     * Convert RGB(r, g, b) to hexadecimal value.
     *
     * @access  public
     * @param   int $r  red(0..255)
     * @param   int $g  green(0..255)
     * @param   int $b  blue(0..255)
     * @return  string
     */
    public static function rgb2hex($r, $g, $b) {
            try {
                    self::checkRGB($r, $g, $b);
                    $hex = "";
                    for ($i = 0; $i < 3; $i++) {
                            switch ($i) {
                                    case 0: $d = dechex($r); break;
                                    case 1: $d = dechex($g); break;
                                    case 2: $d = dechex($b); break;
                            }
                            if (strlen($d) == 1) {
                                    $d = "0".$d;
                            }
                            $hex .= $d;
                    }			
                    return strtoupper($hex);
            } catch (Exception $e) {
                    trigger_error($e->getMessage(), E_USER_ERROR);
                    return false;
            }
    }

    /**
     * Convert RGB(r, g, b) to HSV(h, s, v)
     *
     * @access  public
     * @param   int $r  red(0..255)
     * @param   int $g  green(0..255)
     * @param   int $b  blue(0..255)
     * @return  array
     */
    public static function rgb2hsv($r, $g, $b) {
            try {
                    self::checkRGB($r, $g, $b);
                    $r = ($r / 255);
                    $g = ($g / 255);
                    $b = ($b / 255);
                    $min = min($r, $g, $b);
                    $max = max($r, $g, $b);

                    $h = self::parseHue($r, $g, $b);
                    $s = ($max == 0) ? 0 : round((1 - ($min / $max)) * 100);
                    $v = round($max * 100);

                    return array("h" => $h, "s" => $s, "v" => $v);
            } catch (Exception $e) {
                    trigger_error($e->getMessage(), E_USER_ERROR);
                    return false;
            }
    }
    
    /**
     * Cache for rgb2hsl.
     * 
     * @see OnpStrl_Color:rgb2hsl
     * 
     * @since 1.0.0
     * @var mixed
     */
    private static $_rgb2hsl_cache = array();

    /**
     * Convert RGB(r, g, b) to HSL(h, s, l)
     * 
     * @since 1.0.0
     * @access  public
     * @param   int $r  red(0..255)
     * @param   int $g  green(0..255)
     * @param   int $b  blue(0..255)
     * @return  array
     */
    public static function rgb2hsl($r, $g, $b) {
        $key = "$r-$g-$b";
        if ( isset( self::$_rgb2hsl_cache[$key] ) ) return self::$_rgb2hsl_cache[$key];
        
        try {
            self::checkRGB($r, $g, $b);
            $r = ($r / 255);
            $g = ($g / 255);
            $b = ($b / 255);
            $min = min($r, $g, $b);
            $max = max($r, $g, $b);

            $h = self::parseHue($r, $g, $b);
            $l = (0.5 * ($max + $min));

            if ($max == $min) {
                    $s = 0;
            } else {
                    if ($l <= 0.5) {
                            $s = (($max - $min) / (2 * $l));
                    } else {
                            $s = (($max - $min) / (2 - (2 * $l)));
                    }
            }

            $s = round($s * 100);
            $l = round($l * 100);

            return self::$_rgb2hsl_cache[$key] = array("h" => $h, "s" => $s, "l" => $l);
            
        } catch (Exception $e) {
            trigger_error($e->getMessage(), E_USER_ERROR);
            return false;
        }
    }

    /**
     * Convert HSV(h, s, v) to hexadecimal value.
     *
     * hsv2hex() first runs hsv2rgb() then passes those values to rgb2hex().
     *
     * @access  public
     * @param   int $h  hue(0..359)
     * @param   int $s  saturation(0..100)
     * @param   int $v  value(0..100)
     * @return  string
     */
    public static function hsv2hex($h, $s, $v) {
            list($r, $g, $b) = array_values(self::hsv2rgb($h, $s, $v));
            return self::rgb2hex($r, $g, $b);
    }

    /**
     * Convert HSV(h, s, v) to RGB(r, g, b)
     *
     * @access  public
     * @param   int $h  hue(0..359)
     * @param   int $s  saturation(0..100)
     * @param   int $v  value(0..100)
     * @return  array
     */
    public static function hsv2rgb($h, $s, $v) {
            try {
                    self::checkHSV($h, $s, $v);
                    $s /= 100;
                    $v /= 100;

                    $hm = (floor($h / 60) % 6);
                    $f = (($h / 60) - floor($h / 60));
                    $p = ($v * (1 - $s));
                    $q = ($v * (1 - $f * $s));
                    $t = ($v * (1 - (1 - $f) * $s));

                    switch ($hm) {
                            case 0: $rgb = array($v, $t, $p); break;
                            case 1: $rgb = array($q, $v, $p); break;
                            case 2: $rgb = array($p, $v, $t); break;
                            case 3: $rgb = array($p, $q, $v); break;
                            case 4: $rgb = array($t, $p, $v); break;
                            case 5: $rgb = array($v, $p, $q); break;
                    }

                    list($r, $g, $b) = $rgb;
                    $r = round(255 * $r);
                    $g = round(255 * $g);
                    $b = round(255 * $b);
                    return array("r" => $r, "g" => $g, "b" => $b);
            } catch (Exception $e) {
                    trigger_error($e->getMessage(), E_USER_ERROR);
                    return false;
            }
    }

    /**
     * Convert HSV(h, s, v) to HSL(h, s, l)
     *
     * hsv2hsl() first runs hsv2rgb() then passes those values to rgb2hsl().
     *
     * @access  public
     * @param   int $h  hue(0..359)
     * @param   int $s  saturation(0..100)
     * @param   int $v  value(0..100)
     * @return  array
     */
    public static function hsv2hsl($h, $s, $v) {
            list($r, $g, $b) = array_values(self::hsv2rgb($h, $s, $v));
            return self::rgb2hsl($r, $g, $b);
    }

    /**
     * Convert HSL(h, s, l) to hexadecimal value.
     *
     * hsl2hex() first runs hsl2rgb() then passes those values to rgb2hex().
     *
     * @access  public
     * @param   int $h  hue(0..359)
     * @param   int $s  saturation(0..100)
     * @param   int $l  lightness(0..100)
     * @return  string
     */
    public static function hsl2hex($h, $s, $l) {
            list($r, $g, $b) = array_values(self::hsl2rgb($h, $s, $l));
            return self::rgb2hex($r, $g, $b);
    }

    /**
     * Convert HSL(h, s, l) to RGB(r, g, b)
     *
     * @access  public
     * @param   int $h  hue(0..359)
     * @param   int $s  saturation(0..100)
     * @param   int $l  lightness(0..100)
     * @return  array
     */
    public static function hsl2rgb($h, $s, $l) {
            try {
                    self::checkHSL($h, $s, $l);
                    $h /= 360;
                    $s /= 100;
                    $l /= 100;

                    $q = ($l < 0.5) ? ($l * (1 + $s)) : ($l + $s - ($l * $s));
                    $p = ((2 * $l) - $q);

                    $rgb = array();
                    for ($i = 0; $i < 3; $i++) {
                            switch ($i) {
                                    case 0: $t = ($h + (1 / 3)); break;
                                    case 1: $t = $h; break;
                                    case 2: $t = ($h - (1 / 3)); break;
                            }

                            if ($t < 0) { $t += 1.0; }
                            if ($t > 1) { $t -= 1.0; }

                            if ($t < (1 / 6)) {
                                    $rgb[] = ($p + (($q - $p) * 6 * $t));
                            } else if (((1 / 6) <= $t) && ($t < 0.5)) {
                                    $rgb[] = $q;
                            } else if ((0.5 <= $t) && ($t < (2 / 3))) {
                                    $rgb[] = ($p + (($q - $p) * 6 * ((2 / 3) - $t)));
                            } else {
                                    $rgb[] = $p;
                            }
                    }

                    list($r, $g, $b) = $rgb;
                    $r = round(255 * $r);
                    $g = round(255 * $g);
                    $b = round(255 * $b);
                    return array("r" => $r, "g" => $g, "b" => $b);
            } catch (Exception $e) {
                    trigger_error($e->getMessage(), E_USER_ERROR);
                    return false;
            }
    }

    /**
     * Convert HSL(h, s, l) to HSV(h, s, v)
     *
     * hsl2hsv() first runs hsl2rgb() then passes those values to rgb2hsv().
     *
     * @access  public
     * @param   int $h  hue(0..359)
     * @param   int $s  saturation(0..100)
     * @param   int $v  lightness(0..100)
     * @return  array
     */
    public static function hsl2hsv($h, $s, $l) {
            list($r, $g, $b) = array_values(self::hsl2rgb($h, $s, $l));
            return self::rgb2hsv($r, $g, $b);
    }

    /**
     * Parses the hue degree used in converting ro both HSV and HSL values.
     * The RGB(r, g, b) values are from 0..1 in this method since both HSV
     * and HSL converstions require them to be.
     *
     * @access  private
     * @param   int $r  red(0..1)
     * @param   int $g  green(0..1)
     * @param   int $b  blue(0..1)
     * @return  array
     */
    private static function parseHue($r, $g, $b) {
            $min = min($r, $g, $b);
            $max = max($r, $g, $b);
            if ($max == $min) {
                    $h = 0;
            } else {
                    if (($max == $r) && ($g >= $b)) {
                            $h = (60 * (($g - $b) / ($max - $min)));
                    } else {
                            if (($max == $r) && ($g < $b)) {
                                    $h = (60 * (($g - $b) / ($max - $min)) + 360);
                            } else {
                                    if ($max == $g) {
                                            $h = (60 * (($b - $r) / ($max - $min)) + 120);
                                    } else {
                                            $h = (60 * (($r - $g) / ($max - $min)) + 240);
                                    }
                            }
                    }
            }
            return round($h);
    }

    /**
     * Throws an out of bounds exception on each RGB(r, g, b). These values
     * must be between 0 and 255.
     *
     * @access  private
     * @param   int $r  red
     * @param   int $g  green
     * @param   int $b  blue
     * @throws  Out of bounds exception.
     * @return  void
     */
    private static function checkRGB($r, $g, $b) {
            if (($r < 0) || ($r > 255)) {
                    throw new Exception("RGB[R]: ".$r." is not in the 0..255 boundary.");
            }
            if (($g < 0) || ($g > 255)) {
                    throw new Exception("RGB[G]: ".$g." is not in the 0..255 boundary.");
            }
            if (($b < 0) || ($b > 255)) {
                    throw new Exception("RGB[B]: ".$b." is not in the 0..255 boundary.");
            }
    }

    /**
     * Checks the hexadecimal string for illegal characters, after this if the
     * the length of the string is 6, returns this value. If not, will throw an
     * improper hexadicimal exception.
     *
     * @access  private
     * @param   string $hex
     * @throws  Improper hexadicimal value.
     * @return  string
     */
    private static function checkHex($hex) {
            $phex = preg_replace("/[^a-fA-F0-9]/", "", $hex);
            if (strlen($phex) == 6) {
                    return $phex;
            } else {
                    throw new Exception("Hex: ".$hex." is not a proper hexadecimal color code.");
                    return false;
            }
    }

    /**
     * Throws an out of bounds exception on each HSV(h, s, v)
     *
     * H: 0..359
     * S: 0..100
     * V: 0..100
     *
     * @access  private
     * @param   int $h  hue
     * @param   int $g  saturation
     * @param   int $v  value
     * @throws  Out of bounds exception.
     * @return  void
     */
    private static function checkHSV($h, $s, $v) {
            if (($h < 0) || ($h > 359)) {
                    throw new Exception("HSV[H]: ".$h." is not in the 0..359 boundary.");
            }
            if (($s < 0) || ($s > 100)) {
                    throw new Exception("HSV[S]: ".$s." is not in the 0..100 boundary.");
            }
            if (($v < 0) || ($v > 100)) {
                    throw new Exception("HSV[V]: ".$v." is not in the 0..100 boundary.");
            }
    }

    /**
     * Throws an out of bounds exception on each HSL(h, s, l)
     *
     * H: 0..359
     * S: 0..100
     * L: 0..100
     *
     * @access  private
     * @param   int $h  hue
     * @param   int $g  saturation
     * @param   int $l  lightness
     * @throws  Out of bounds exception.
     * @return  void
     */
    private static function checkHSL($h, $s, $l) {
            if (($h < 0) || ($h > 359)) {
                    throw new Exception("HSL[H]: ".$h." is not in the 0..359 boundary.");
            }
            if (($s < 0) || ($s > 100)) {
                    throw new Exception("HSL[S]: ".$s." is not in the 0..100 boundary.");
            }
            if (($l < 0) || ($l > 100)) {
                    throw new Exception("HSL[L]: ".$l." is not in the 0..100 boundary.");
            }
    }



    /**
     * Проверяет, является ли $color hex
     * @param type $color
     * @since 1.0.0   
     * @return boolean - возвращает true если это hex
    */
    public static function is_hex($color) {
        if( !preg_match("/\#[A-z0-9]+/i", $color) ) return false;
        return true;
    }

    /**
     * Проверяет, является ли $color rbg
     * @param type $color
     * @since 1.0.0   
     * @return boolean - возвращает true если это rbg
    */
    public static function is_rgb($color) {
        if( !preg_match("/rbg\([0-9]{1,3},(?:\s)?[0-9]{1,3},(?:\s)?[0-9]{1,3}\)/i", $color) ) return false;
        return true;
    }

    /**
     * Проверяет, является ли $color rgba
     * @param type $color
     * @since 1.0.0   
     * @return boolean - возвращает true если это rbga
    */
    public static function is_rgba($color) {
        if( !preg_match("/rbg\([0-9]{1,3},(?:\s)?[0-9]{1,3},(?:\s)?[0-9]{1,3},(?:\s)?[0-9.]+\)/i", $color) ) return false;
        return true;
    }
        
    public static function getMaxSL( $img ) {
        $width = imagesx($img);
        $height = imagesy($img);
        
        $maxS = 0;
        $maxL = 0;
        $cache = array();
        
        for( $x=0; $x < $width; $x++ ) {
            for( $y=0; $y < $height; $y++ ) {

                $color = imagecolorat($img, $x, $y);
                if ( isset( $cache[$color] ) ) continue;
                $cache[$color] = true;
                
                $rgb = imagecolorsforindex($img, $color);
                $hsl = self::rgb2hsl( $rgb['red'], $rgb['green'], $rgb['blue']);
          
                if ( $hsl['s'] > $maxS ) $maxS = $hsl['s'];
                if ( $hsl['l'] > $maxL ) $maxL = $hsl['l'];
            }
        }
        
        return array('s' => $maxS, 'l' => $maxL);
    }
}
?>