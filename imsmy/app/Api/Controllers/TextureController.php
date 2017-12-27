<?php

namespace App\Api\Controllers;

use App\Api\Transformer\TextureTransformer;
use App\Models\Texture;
use App\Models\TextureFolder;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Cache;

class TextureController extends BaseController
{
    private $paginate = 20;

    private $textureTransformer;

    public function __construct
    (
        TextureTransformer $textureTransformer
    )
    {
        $this->textureTransformer = $textureTransformer;
    }

    public function recommend()
    {
        $texture = Cache::remember('texture_recommend','60',function(){
            try{
                $texture = Texture::with(['belongsToFolder'=>function($q){
                    $q->select(['id','name']);
                },
                    'belongsToUser'=>function($q){
                        $q->select(['id','nickname','avatar','cover','verify','signature','verify_info']);
                    }])
                    ->Ofrecommend()
                    ->Ofactive()
                    ->get(['id','folder_id','user_id','name','content','download_count']);

                return response()->json([
                    'data'   =>$this -> textureTransformer -> transformCollection( $texture->all() ),
                ],200);

            }catch (\Exception $e){
                return response()->json(['message'=>'bad_request'],500);
            }
        });

        return $texture;
    }

    /**
     * 分类
     * @param $id
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function folder()
    {
        $folder = Cache::remember('texture_foler','1440',function(){
            try{

                $folder = TextureFolder::where('active','1')
                    ->orderBy('sort','ASC')
                    ->get(['id','name','count']);

                return response()->json([
                    'data'      =>  $folder,
                ],200);
            }catch (\Exception $e){
                return response()->json(['message'=>'bad_request'],500);
            }
        });

        return $folder;

    }


    public function file($id,Request $request)
    {
        $texture = Cache::remember($id.'texture','60',function() use($id,$request){
            try{
                if (!is_numeric($page = $request->get('page',1)))  return response()->json(['message'=>'bad_request'],403);

                $texture =  Texture::with(['belongsToFolder'=>function($q){
                    $q->select(['id','name']);
                },
                    'belongsToUser'=>function($q){
                        $q->select(['id','nickname','avatar','cover','verify','signature','verify_info']);
                    }])
                    ->Offolder($id)
                    -> Ofactive()
//                    ->get(['id','folder_id','user_id','name','content','download_count']);
                    ->paginate($this->paginate,['id','folder_id','user_id','name','content','download_count'],'page',$page);

                return response()->json([
                    'data'   =>$this -> textureTransformer -> transformCollection( $texture->all() ),
                ],200);

            }catch(\Exception $e){
                return response()->json(['message'=>'bad_request'],500);
            }
        });
            return $texture;
    }

}
