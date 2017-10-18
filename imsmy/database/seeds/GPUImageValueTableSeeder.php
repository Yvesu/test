<?php

use Illuminate\Database\Seeder;
use App\Models\GPUImageValue;
use App\Models\GPUImage;
class GPUImageValueTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $file = fopen(base_path().'/blur-value.csv','r');
        while ($data = fgetcsv($file)) { //每次读取CSV里面的一行内容
            foreach($data as $key =>$item){
                $data[$key] = iconv('gb2312','utf-8',$item);
            }
            $GPUImageValue = new GPUImageValue();
            $GPUImageValue->name_zh = $data[0];
            $GPUImageValue->name_en = $data[1];
            if(!is_null($data[2]) && '' != $data[2]){
                $GPUImageValue->min = $data[2];
            }
            if(!is_null($data[3]) && '' != $data[3]){
                $GPUImageValue->max = $data[3];
            }
            if(!is_null($data[4]) && '' != $data[4]){
                $GPUImageValue->init = $data[4];
            }
            $GPUImage = GPUImage::where('name_en',$data[5])->first();
            $GPUImageValue->GPUImage_id = $GPUImage->id;
            $GPUImageValue->save();
        }
        fclose($file);
    }
}
