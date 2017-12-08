<?php

use Illuminate\Database\Seeder;
use App\Models\GPUImage;
class GPUImageTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $file = fopen(base_path().'/blur.csv','r');
        while ($data = fgetcsv($file)) { //每次读取CSV里面的一行内容
            foreach($data as $key =>$item){
                $data[$key] = iconv('gb2312','utf-8',$item);
            }
            $GPUImage = new GPUImage();
            $GPUImage->name_zh = $data[0];
            $GPUImage->name_en = $data[1];
            /*if(!is_null($data[2]) && '' != $data[2]){
                $GPUImage->min = $data[2];
            }
            if(!is_null($data[3]) && '' != $data[3]){
                $GPUImage->max = $data[3];
            }
            if(!is_null($data[4]) && '' != $data[4]){
                $GPUImage->init = $data[4];
            }
            if('YES' == $data[5]){
                $GPUImage->texture = 1;
            }*/
            if('YES' == $data[2]){
                $GPUImage->texture = 1;
            }
            $GPUImage->save();
        }
        fclose($file);
    }
}
