<?php
/**
 * Created by PhpStorm.
 * User: Ma biao
 * Date: 2016/5/20
 * Time: 14:07
 */

namespace App\Services;

use Image;
class ImageProcess
{
    private $bg_color;

    private $size;

    private $font;

    public function __construct()
    {
        $this->bg_color = [
            '#1BBC9D', '#2FCC71', '#3598DC', '#9C59B8', '#34495E',
            '#16A086', '#27AE61', '#2A80B9', '#8F44AD', '#2D3E50',
            '#F1C40F', '#E77E23', '#E84C3D'           , '#96A6A6',
            '#F49C14', '#D55401', '#C1392B'           , '#929BA4',
        ];
        $this->size    = 150;
        $this->font    = [
            'size'   => 100,
            'align'  => 'center',
            'valign' => 'middle',
            'color'  => [255,255,255,1],
            'file'   => public_path('font/msyhbd.ttf')
        ];
    }

    public function text2Image($str)
    {
        $random = rand(0,sizeof($this->bg_color) - 1);
        $img = $img = Image::canvas($this->size, $this->size, $this->bg_color[$random]);
        $position = $this->size / 2;
        $style = $this->font;
        $img->text($str, $position, $position, function($font) use ($style) {
            $font->file($style['file']);
            $font->size($style['size']);
            $font->align($style['align']);
            $font->valign($style['valign']);
            $font->color($style['color']);
        });
        return $img;
    }
}