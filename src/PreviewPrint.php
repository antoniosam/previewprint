<?php

/**
 * Created by PhpStorm.
 * User: marcosamano
 * Date: 02/10/18
 * Time: 4:35 PM
 */

namespace Ast\PreviewPrint;

class PreviewPrint
{

    private $margin = 10;

    private $SIZE_4x6_X = 1500;
    private $SIZE_4x6_Y = 1000;

    private $format;
    private $typeinner;

    private $wrapper_x = 0;
    private $wrapper_y = 0;
    private $size_x = 0;
    private $size_y = 0;

    function __construct($type = 'inner', $format = '4x6')
    {
        $this->typeinner = ($type == 'inner');
        $this->format = $format;
        if ($this->format == '4x6') {
            $this->size_x = $this->SIZE_4x6_X;
            $this->size_y = $this->SIZE_4x6_Y;
        }
    }

    /**
     * @param $filename
     * @param $dir_out
     * @param string $name
     * @param string $type
     * @param string $format
     * @return array|null|string
     */
    static function preview( $filename,$dir_out,$name ='salida', $type = 'inner', $format = '4x6')
    {
        $obj = new self($type, $format);
        return $obj->optimize( $filename,$dir_out,$name);
    }

    /**
     * @param $filename1
     * @param $filename2
     * @param $dir_out
     * @param string $name
     * @param string $type
     * @param string $format
     * @return array|null|string
     */
    static function previewDual( $filename1, $filename2,$dir_out,$name ='salida', $type = 'inner', $format = '4x6')
    {
        $obj = new self($type, $format);
        return $obj->optimizeTwo( $filename1, $filename2,$dir_out,$name);
    }

    static function previewDualList($images,$dir_out,$name ='salida', $type = 'inner', $format = '4x6'){
        $count = count($images);

        $uno = [];
        $dos = [];
        for ($a = 0; $a < $count; $a++) {
            $uno[] = $images[$a];
            $a++;
            if (isset($images[$a])) {
                $dos[] = $images[$a];
            }else{
                $dos[] = $images[($a-1)];
            }

        }
        $obj = new self($type, $format);
        return $obj->optimizeTwo( $uno,$dos,$dir_out,$name);
    }


    /**
     * @return resource
     */
    private function createCanvas(){
        $thumb = imagecreatetruecolor($this->size_x, $this->size_y);
        $fondo = imagecolorallocate($thumb, 255, 255, 255);
        imagefilledrectangle($thumb, 0, 0, $this->size_x, $this->size_y, $fondo);
        return $thumb;
    }
    /**
     * @param $resource
     * @return resource
     */
    private function createThumb($resource)
    {
        $ancho = imagesx($resource);
        $alto = imagesy($resource);

        $info = $this->calcularResizeRatio($ancho, $alto, $this->wrapper_x, $this->wrapper_y,$this->typeinner);
        //echo 'createThumb';
        //print_r($info);
        $thumb = imagecreatetruecolor($this->wrapper_x, $this->wrapper_y);
        $fondo = imagecolorallocate($thumb, 255, 255, 255);
        imagefilledrectangle($thumb, 0, 0, $this->wrapper_x, $this->wrapper_y, $fondo);
        if($this->typeinner){
            imagecopyresized($thumb, $resource, $info[2], $info[3], 0, 0, $info[0], $info[1], $ancho, $alto);
        }else{
            $tmp = imagecreatetruecolor($info[0], $info[1]);
            imagecopyresized($tmp, $resource, 0,0, 0, 0, $info[0], $info[1], $ancho, $alto);
            imagecopyresized($thumb, $tmp,  0, 0,$info[2], $info[3], $this->wrapper_x, $this->wrapper_y, $this->wrapper_x, $this->wrapper_y);
            imagedestroy($tmp);
        }

        return $thumb;
    }

    private function calcularResizeRatio($ancho, $alto, $max_x, $max_y, $inner = true)
    {
        $ratioy = $alto / $ancho;
        $ratiox = $ancho / $alto;
        if($inner){
            $x = $max_x;
            $y = floor($x * $ratioy);
            if($y > $max_y){
                $y = $max_y;
                $x = floor($y * $ratiox);
            }
            $offsetX = floor(($max_x - $x) / 2);
            $offsetY = floor(($max_y - $y) / 2);
        }else{
            $y = $max_y;
            $x = floor($y*$ratiox);
            $offsetX = floor(($x-$max_x) / 2);
            $offsetY = 0;
            if ($max_x > $x){
                $x = $max_x;
                $y = floor($x * $ratioy);
                $offsetX = 0;
                $offsetY = floor(($y-$max_y) / 2);
            }
        }
        return [$x, $y, $offsetX, $offsetY];
    }

    /**
     * @param $filename
     * @return null|resource
     */
    private function getResource($filename)
    {
        $ext = pathinfo($filename)['extension'];
        if ($ext == 'jpeg' || $ext == 'jpg' || $ext == 'png') {
            if ($ext == 'jpeg' || $ext == 'jpg') {
                $resource = imagecreatefromjpeg($filename);
            } else {
                $resource = imagecreatefrompng($filename);
            }
            if ($this->wrapper_x > $this->wrapper_y  ) {
                if (imagesx($resource) < imagesy($resource)) {
                    $resource = imagerotate($resource, -90, 0);
                }
            }else{
                if (imagesx($resource) > imagesy($resource)) {
                    $resource = imagerotate($resource, -90, 0);
                }
            }
            return $resource;
        }
        return null;
    }

    private function calculateWrappers($two_images = false)
    {
        $this->wrapper_x = $this->SIZE_4x6_X;
        $this->wrapper_y = $this->SIZE_4x6_Y;
        if ($this->format == '4x6') {
            if (!$two_images) {
                $this->wrapper_x = $this->SIZE_4x6_X - ($this->margin * 2);
                $this->wrapper_y = $this->SIZE_4x6_Y - ($this->margin * 2);
            } else {
                $this->wrapper_x = floor(($this->SIZE_4x6_X - ($this->margin * 4)) / 2);
                $this->wrapper_y = $this->SIZE_4x6_Y - ($this->margin * 2);
            }
        }
    }

    /**
     * @param $filename
     * @param $destiny
     * @return null|string
     */
    private function sinlge( $filename,$destiny)
    {
        $resource = $this->getResource($filename);
        if (is_resource($resource)) {
            $canvas = $this->createCanvas();
            $thumb = $this->createThumb($resource);
            imagecopyresized($canvas, $thumb, $this->margin, $this->margin, 0, 0, $this->wrapper_x, $this->wrapper_y, $this->wrapper_x, $this->wrapper_y);
            imagedestroy($thumb);
            imagejpeg($canvas, $destiny);
            return $destiny;
        }
        return null;
    }

    /**
     * @param $filename
     * @param $filename2
     * @param $destiny
     * @return null|string
     */
    private function dual( $filename, $filename2,$destiny)
    {
        $resource1 = $this->getResource($filename);
        $resource2 = $this->getResource($filename2);
        if (is_resource($resource1) && is_resource($resource2)) {
            $canvas = $this->createCanvas();
            $thumb = $this->createThumb($resource1);
            imagecopyresized($canvas, $thumb, $this->margin, $this->margin, 0, 0, $this->wrapper_x, $this->wrapper_y, $this->wrapper_x, $this->wrapper_y);
            $thumb = $this->createThumb($resource2);
            imagecopyresized($canvas, $thumb, ($this->wrapper_x+($this->margin*3)), $this->margin, 0, 0, $this->wrapper_x, $this->wrapper_y, $this->wrapper_x, $this->wrapper_y);
            imagedestroy($thumb);
            imagejpeg($canvas, $destiny);
            return $destiny;
        }
        return null;
    }

    /**
     * @param $filenames
     * @param $path_out
     * @param string $name
     * @return array|null|string
     */
    public function optimize($filenames,$path_out,$name='salida'){
        $this->calculateWrappers();
        if(is_array($filenames)) {
            $back = [];
            $i=0;
            foreach ($filenames as $filename):
                $i++;
                $back[] = $this->sinlge($filename ,$path_out.DIRECTORY_SEPARATOR.$name.'_'.$i.'.jpg');
            endforeach;
        }else{
            $back = $this->sinlge($filenames,$path_out.DIRECTORY_SEPARATOR.$name.'.jpg');
        }
        return $back;
    }

    /**
     * @param $filenames1
     * @param $filenames2
     * @param $path_out
     * @param string $name
     * @return array|null|string
     */
    public function optimizeTwo($filenames1,$filenames2,$path_out,$name='salida'){
        $this->calculateWrappers(true);
        $arrays = false;
        $back = null;
        if(is_string($filenames1) && is_string($filenames2)){
            $back = $this->dual($filenames1,$filenames2,$path_out.DIRECTORY_SEPARATOR.$name.'.jpg');
        }elseif(is_array($filenames1) && is_array($filenames2)){
            $arrays = true;
            if(count($filenames1) == count($filenames2)){
            }elseif(count($filenames1) > count($filenames2)) {
                $fixed = [];
                foreach ($filenames1 as $cont=>$filename){
                    $fixed[$cont] = isset($filenames2[$cont])?$filenames2[$cont]:$filename;
                }
                $filenames2 = $fixed;
            }else{
                $fixed = [];
                foreach ($filenames2 as $cont=>$filename){
                    $fixed[] = isset($filenames1[$cont])?$filenames1[$cont]:$filename;
                }
                $filenames1 = $fixed;
            }
        }elseif(is_array($filenames1) || is_array($filenames2)){
            $fixed =[];
            $arrays = true;
            if(is_array($filenames1)){
                foreach ($filenames1 as $cont=>$filename){
                    $fixed[$cont] = $filenames2;
                }
                $filenames2 = $fixed;
            }else{
                foreach ($filenames2 as $cont=>$filename){
                    $fixed[$cont] = $filenames1;
                }
                $filenames1 = $fixed;
            }

        }
        if($arrays){
            $back = [];
            $i = 0;
            foreach ($filenames1 as $cont=>$filename1){
                $i++;
                $back[] = $this->dual($filename1, $filenames2[$cont],$path_out.DIRECTORY_SEPARATOR.$name.'_'.$i.'.jpg');
            }
        }
        return $back;
    }



}