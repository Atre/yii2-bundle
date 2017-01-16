<?php
namespace dezmont765\yii2bundle\helpers;

use dezmont765\yii2bundle\components\AcImage;
use InvalidArgumentException;

/**
 * Created by JetBrains PhpStorm.
 * User: DezMonT
 * Date: 18.08.14
 * Time: 14:19
 * To change this template use File | Settings | File Templates.
 */
class HelperImage
{
    public static function deleteImage($original_file) {
        if(file_exists($original_file) && !is_dir($original_file)) {
            unlink($original_file);
        }
    }


    public static function deleteDir($dirPath) {
        if(!is_dir($dirPath)) {
            throw new InvalidArgumentException("$dirPath must be a directory");
        }
        if(substr($dirPath, strlen($dirPath) - 1, 1) != '/') {
            $dirPath .= '/';
        }
        $files = glob($dirPath . '*', GLOB_MARK);
        foreach($files as $file) {
            if(is_dir($file)) {
                self::deleteDir($file);
            }
            else {
                unlink($file);
            }
        }
        rmdir($dirPath);
    }


    public static function imgCrop($outfile, $infile, $x1, $y1, $w, $h, $bound) {
        $scale = self::getScaleByDesiredBound($infile, $bound);
        $img = AcImage::createImage($infile);
        $img->setRewrite(true);
        $img->crop((int)($x1 * $scale), (int)($y1 * $scale), (int)($w * $scale), (int)($h * $scale));
        $img->save($outfile);
    }


    public static function imgCropByScale($outfile, $infile, $x1, $y1, $w, $h, $scale) {
        if($w == 0 || $h == 0) {
            return false;
        }
        $img = AcImage::createImage($infile);
        $img->setRewrite(true);
        $img->crop((int)($x1 * $scale), (int)($y1 * $scale), (int)($w * $scale), (int)($h * $scale));
        $img->save($outfile);
    }


    public static function imgSetDimension($outfile, $infile, $w, $h) {
        $img = AcImage::createImage($infile);
        $img->setRewrite(true);
        $img->resize($w, $h);
        $img->save($outfile);
    }


    public static function getScaleByDesiredBound($infile, $bound) {
        $img = AcImage::createImage($infile);
        $new_size = self::getSizeByBound($infile, $bound);
        $scale = $img->getWidth() / $new_size->getWidth();
        return $scale;
    }


    public static function getSizeByBound($file, $bound) {
        $img = AcImage::createImage($file);
        if($img->getWidth() > $img->getHeight()) {
            $new_size = $img->getSize()->getByWidth($bound);
        }
        else {
            $new_size = $img->getSize()->getByHeight($bound);
        }
        return $new_size;
    }


    public static function rotate($in_file, $angle, $out_file) {
        $img = AcImage::createImage($in_file);
        $img->setRewrite(true);
        $img->rotate($angle);
        $img->save($out_file);
    }


    public static function addLogo($in_file, $string, $out_file) {
        $img = AcImage::createImage($in_file);
        $img->setRewrite(true);
        $img->drawLogo($string, AcImage::CENTER);
        $img->save($out_file);
    }


    public static function addTextLogo($in_file, $string, $out_file) {
        $img = AcImage::createImage($in_file);
        $watermark_width = $img->getWidth() / 2;
        $watermark_height = $img->getHeight() / 18;
        $image = imagecreatetruecolor($watermark_width, $watermark_height);
        imagesavealpha($image, true);
        $color = imagecolorallocatealpha($image, 255, 255, 255, 127);
        imagefill($image, 0, 0, $color);
        $black = imagecolorallocate($image, 0, 0, 0);
//        $blue = imagecolorallocate($image, 79, 166, 185);
        // imagettftext($image, $font_size, 0, 30, 190, $black, $font_path, $water_mark_text_1);
        $length = strlen($string);
        $font_size = round(($watermark_width - 10) / $length);
        imagettftext($image, $font_size + 2, 0, 10, $watermark_height / 2, $black,
                     \Yii::getAlias('@frontend/web/fonts/arial.ttf'), $string);
        imagecopymerge($img->getResource(), $image, $img->getWidth() - $watermark_width,
                       $img->getHeight() - $watermark_height, 0, 0, $watermark_width, $watermark_height, 30);
        $img->setRewrite(true);
        $img->save($out_file);
        return true;
    }


}