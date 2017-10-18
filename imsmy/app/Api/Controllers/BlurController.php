<?php
/**
 * Created by PhpStorm.
 * User: mabiao
 * Date: 2016/4/13
 * Time: 18:38
 */

namespace App\Api\Controllers;


use App\Api\Transformer\BlursTransformer;
use Illuminate\Http\Request;
use App\Models\Blur;
use Illuminate\Database\Eloquent\ModelNotFoundException;

/**
 * 获取滤镜信息
 *
 * @Resource("Blurs",uri="/blurs")
 */
class BlurController extends BaseController
{
    protected $blursTransformer;

    const  defaultNum = 10;

    public function __construct(BlursTransformer $blursTransformer)
    {
        $this->blursTransformer = $blursTransformer;
    }

    /**
     * 分页获取滤镜
     * @Get("/{?page,per_page}")
     * @Versions({"v1"})
     * @Parameters({
     *      @Parameter("page", description="现实查询结果的页数.", default=1),
     *      @Parameter("per_page", description="每页滤镜的数量", default=10)
     * })
     * @Transaction({
     *      @Response(200, body={
     *                            {
     *                               "perPage": 1,
     *                               "total": 2,
     *                               "nextPage": "滤镜下一页 :http://goobird.dev/api/blurs?per_page=1&page=2 || null",
     *                               "previousPage": "滤镜前一页 : null || Url",
     *                               "data": "参考: [GET /blurs/{id}]"
     *                             }
     *                          })
     * })
     */
    public function index(Request $request)
    {
        $page = $request->get('page');
        if(is_null($page) || !is_int((int)$page)){
            $request->merge(array('page' => 1));
        }

        $per_page = $request->get('per_page');
        $per_page = !is_null($per_page) && is_int((int)$per_page) ? $per_page : self::defaultNum;

        $blurs = Blur::with('belongsToBlurClass')->paginate($per_page);

        $blurs->appends('per_page',$per_page);
        return response()->json([
            'perPage'       =>  $blurs->perPage(),
            'total'         =>  $blurs->total(),
            'nextPage'      =>  $blurs->nextPageUrl(),
            'previousPage'  =>  $blurs->previousPageUrl(),
            'data'          =>  $this->blursTransformer->transformCollection($blurs->all())
        ],
            200,
            [],
            JSON_NUMERIC_CHECK);
    }

    /**
     * 通过ID获取单个滤镜
     * @Get("/{id}")
     * @Versions({"v1"})
     * @Transaction({
     *     @Response(200,body={
     *                                 "id": "ID",
     *                                 "classNameZh": "类型名称--中文",
     *                                 "classNameEn": "类型名称--英文",
     *                                 "nameZh": "名称--中文",
     *                                 "nameEn": "名称--英文",
     *                                 "GPUImage": {
     *                                    {
     *                                       "name": "GPUIMAGE_SATURATION (英文名称)",
     *                                       "data": {
     *                                         {
     *                                            "name": "GPUIMAGE_SATURATION (现在默认只有一个的值时，是与父名称相同，如有意见可以在csv中修改)",
     *                                            "value": "1"
     *                                         }
     *                                       }
     *                                    },
     *                                    {
     *                                       "name": "GPUIMAGE_RGB",
     *                                       "data": {
     *                                         {
     *                                            "name": "GPUIMAGE_RGB_R(因为要区分不同值与名称，RGB默认是这三种名称)",
     *                                            "value": "1"
     *                                         },
     *                                         {
     *                                            "name": "GPUIMAGE_RGB_G",
     *                                            "value": "1"
     *                                         },
     *                                         {
     *                                            "name": "GPUIMAGE_RGB_B",
     *                                            "value": "1"
     *                                         }
     *                                       }
     *                                    },
     *                                    {
     *                                        "name": "GPUIMAGE_MOSAIC",
     *                                        "data": {
     *                                          {
     *                                               "name": "GPUIMAGE_MOSAIC",
     *                                               "value": "0.05"
     *                                          }
     *                                        },
     *                                        "imgUrl": "文件Key (现在认为一个滤镜有多个值，但素材图片只有一张，放在此位置，如有意见请沟通)"
     *                                    }
     *                                 },
     *                                 "sequenceDiagram": {
     *                                         "文件Key",
     *                                         "该字段可空"
     *                                 },
     *                                 "dynamicImage": "文件Key(图片地址URL--可空)",
     *                                 "faceTracking": "文件Key(图片地址URL--可空)",
     *                                 "audio": "音频文件Key--可空",
     *                                 "preview":"预览图片的key,如需使用请再次请求",
     *                                 "thumbnail_preview":"预览图片Url",
     *                                 "gravitySensing": "重力感应(0:关闭，1:开启)",
     *                                 "xAlignOffset": "0.00(x轴偏移量)",
     *                                 "yAlignOffset": "0.00(y轴偏移量)",
     *                                 "scalingRatio": "100(缩放比例 0 - 100)",
     *                                 "updated_at": "上次更新时间"
     *                       }),
     *     @Response(404,body={"error":"not_found"}),
     * })
     */
    public function show($id)
    {
        try{
            $blur = Blur::with('belongsToBlurClass')->findOrFail($id);
            return response()->json(
                $this->blursTransformer->transform($blur),
                200,
                [],
                JSON_NUMERIC_CHECK);
        } catch (ModelNotFoundException $e){
            return response()->json(['error' => 'not_found'], 404);
        }
    }


}