<?php
/**
 * @author    ThemePunch <info@themepunch.com>
 * @link      http://www.themepunch.com/
 * @copyright 2017 ThemePunch
 */

namespace Nwdthemes\Revslider\Model\Revslider\Framework;

class TPColorpicker {

    protected static $framework;

    /**
     * @since 5.3.1.6
     */
    public function __construct(
        \Nwdthemes\Revslider\Helper\Framework $framework
    ){
        self::$framework = $framework;
    }


    /**
     * TODO: What does it do?
     * @since 5.3.1.6
     */
    public static function get($val) {

        if(!$val || empty($val)) return 'transparent';
        $process = self::process($val, true);
        return $process[0];

    }

    /**
     * TODO: What does it do?
     * @since 5.3.1.6
     */
    public static function parse($val, $prop, $returnColorType){

        $val = self::process($val, true);
        $ar = array();

        if(!$prop) $ar[0] = $val[0];
        else $ar[0] = $prop + ': ' + $val[0] + ';';

        if($returnColorType) $ar[1] = $val[1];
        return $ar;

    }


    /**
     * TODO: What does it do?
     * @since 5.3.1.6
     */
    public static function convert($color, $opacity){
        if( $opacity == "transparent" ){
            return 'rgba(0,0,0,0)';
        }
        if($color=="" ) return '';
        if(strpos($color, "[{") !== false  || strpos($color,'gradient') !== false ) return self::get($color);

        if(!is_bool($opacity) && "".$opacity === "0"){
            return 'transparent';
        }

        if($opacity==-1 || !$opacity || empty($opacity) || !is_numeric($opacity) || $color == "transparent" || $opacity === 1 || $opacity === 100 ) {
            if(strpos($color,'rgba') === false && strpos($color,'#') !== false) {
                return self::processRgba(self::sanitizeHex($color), $opacity);
            }
            else {
                return self::process($color, true)[0];
            }
        }

        $opacity = floatval($opacity);
        if($opacity < 1) $opacity = $opacity * 100;
        $opacity = round($opacity);
        $opacity = $opacity > 100 ? 100 : $opacity;
        $opacity = $opacity < -1 ? 0 : $opacity;

        if($opacity === 0) return 'transparent';

        if(strpos($color,'#') !== false ) {

            return self::processRgba(self::sanitizeHex($color), $opacity);

        }
        else {
            $color = self::rgbValues($color, 3);
            return self::rgbaString($color[0], $color[1], $color[2], $opacity);

        }
    }


    /**
     * TODO: What does it do?
     * @since 5.3.1.6
     */
    public static function process($clr, $processColor){


        if( !is_string($clr) ) {
            if($processColor) $clr = self::sanatizeGradient($clr);
            return array( self::processGradient($clr), 'gradient', $clr );
        }
        else if( trim($clr) == 'transparent' ) {

            return array('transparent', 'transparent');

        }
        else if( strpos( $clr, "[{" ) !== false ) {
            try {
                $clr = json_decode( str_replace("amp;", '',str_replace("&", '"', $clr)) );

                if($processColor) $clr = self::sanatizeGradient($clr);

                return array(self::processGradient($clr), 'gradient', $clr);
            }
            catch (Exception $e) {
                return '{"type":"linear","angle":"0","colors":[{"r":"255","g":"255","b":"255","a":"1","position":"0","align":"bottom"},{"r":"0","g":"0","b":"0","a":"1","position":"100","align":"bottom"}]}';
            }
        }

        else if( strpos($clr,'#') !== false ) {

            return array(self::sanitizeHex($clr), 'hex');

        }
        else if( strpos($clr,'rgba') !== false ) {
            $clr = preg_replace( '/\s+/', '', $clr ) ;
            return array($clr, 'rgba');

        }
        else {
            $clr = preg_replace('/\s+/', '', $clr);
            return array($clr, 'rgb');

        }

    }

    /**
     * TODO: What does it do?
     * @since 5.3.1.6
     */
    public static function sanatizeGradient($obj) {

        $colors = $obj->colors;
        $len = sizeof($colors);
        $ar = array();

        for($i = 0; $i < $len; $i++) {

            $cur = $colors[$i];
            unset($cur->align);

            if( isset($prev) ) {

                if(json_encode($cur) !== json_encode($prev)) {

                    $ar[sizeof($ar)] = $cur;

                }

            }
            else {

                $ar[sizeof($ar)] = $cur;

            }

            $prev = $cur;

        }

        $obj->colors = $ar;
        return $obj;

    }

    /**
     * TODO: What does it do?
     * @since 5.3.1.6
     */
    public static function processGradient($obj){
        $tpe = $obj->type;
        $begin = $tpe . '-gradient(';
        $middle = $tpe === 'linear' ? $obj->angle . 'deg, ' : 'ellipse at center, ';
        $colors = $obj->colors;
        $len = sizeof($colors);
        $end = '';

        for($i = 0; $i < $len; $i++) {

            if($i > 0) $end .= ', ';
            $clr = $colors[$i];
            $end .= 'rgba(' . $clr->r . ',' . $clr->g . ',' . $clr->b . ',' . $clr->a . ') ' . $clr->position . '%';

        }

        return $begin . $middle . $end . ')';
    }


    /**
     * TODO: What does it do?
     * @since 5.3.1.6
     */
    public static function rgbValues($values, $num) {

        $values = substr( $values, strpos($values, '(') + 1  , strpos($values, ')')-strpos($values, '(') - 1 );
        $values = explode(",", $values);

        if(sizeof($values) == 3 && $num == 4) $values[3] = '1';
        for($i = 0; $i < $num; $i++) {
            if(isset($values[$i])) $values[$i] = trim($values[$i]);
        }

        return $values;

    }

    /**
     * TODO: What does it do?
     * @since 5.3.1.6
     */
    public static function rgbaString($r, $g, $b, $a) {
        if($a > 1){
            $a = "".number_format($a * 0.01 ,  2);
            $a = str_replace(".00", "", $a);
        }
        return 'rgba(' . $r . ',' . $g . ',' . $b . ',' . $a . ')';

    }

    /**
     * TODO: What does it do?
     * @since 5.3.1.6
     */
    public static function rgbToHex($clr) {

        $values = self::rgbValues($clr, 3);
        return self::getRgbToHex($values[0], $values[1], $values[2]);

    }

    /**
     * TODO: What does it do?
     * @since 5.3.1.6
     */
    public static function rgbaToHex($clr) {

        $values = self::rgbValues($clr, 4);

        return array(self::getRgbToHex($values[0], $values[1], $values[2]), $values[3]);

    }

    /**
     * TODO: What does it do?
     * @since 5.3.1.6
     */
    public static function getOpacity($val){
        $rgb = self::rgbValues($val, 4);
        return intval($rgb[3] * 100, 10) + '%';
    }

    /**
     * TODO: What does it do?
     * @since 5.3.1.6
     */
    public static function getRgbToHex($r, $g, $b){
        $rgb = array($r, $g, $b);
        $hex = "#";
        $hex .= str_pad(dechex($rgb[0]), 2, "0", STR_PAD_LEFT);
        $hex .= str_pad(dechex($rgb[1]), 2, "0", STR_PAD_LEFT);
        $hex .= str_pad(dechex($rgb[2]), 2, "0", STR_PAD_LEFT);
        return $hex;
    }

    /**
     * TODO: What does it do?
     * @since 5.3.1.6
     */
    public static function joinToRgba($val){
        $val = explode('||', $val);
        return self::convert($val[0], $val[1]);
    }

    /**
     * TODO: What does it do?
     * @since 5.3.1.6
     */
    public static function processRgba($hex, $opacity=false){

        $hex = trim(str_replace('#', '' , $hex));

        $rgb = $opacity!==false ? 'rgba' : 'rgb';
        $r = hexdec(substr($hex,0,2));
        $g = hexdec(substr($hex,2,2));
        $b = hexdec(substr($hex,4,2));

        $color = $rgb . "(" . $r . "," . $g . "," . $b ;

        if($opacity!==false){
            if($opacity > 1)
                $opacity = "".number_format($opacity * 0.01 ,  2);
            $opacity = str_replace(".00", "", $opacity);
            $color .= ',' . $opacity;
        }

        $color .= ')';

        return $color;
    }

    /**
     * TODO: What does it do?
     * @since 5.3.1.6
     */
    public static function sanitizeHex($hex){
        $hex = trim(str_replace('#', '' , $hex));
        if (strlen($hex) == 3) {
            $hex[5] = $hex[2]; // f60##0
            $hex[4] = $hex[2]; // f60#00
            $hex[3] = $hex[1]; // f60600
            $hex[2] = $hex[1]; // f66600
            $hex[1] = $hex[0]; // ff6600
        }
        return '#'.$hex;
    }


    /**
     * Save presets
     * @since 5.3.2
     */
    public static function save_color_presets($presets){

        self::$framework->update_option('tp_colorpicker_presets', $presets);

        return self::get_color_presets();
    }


    /**
     * Load presets
     * @since 5.3.2
     */
    public static function get_color_presets(){

        return self::$framework->get_option('tp_colorpicker_presets', array());

    }

}


