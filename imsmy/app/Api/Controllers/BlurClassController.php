<?php
/**
 * Created by PhpStorm.
 * User: Ma biao
 * Date: 2016/4/22
 * Time: 20:09
 */

namespace App\Api\Controllers;


use App\Api\Transformer\BlurClassesTransformer;
use App\Models\BlurClass;
use Illuminate\Database\Eloquent\ModelNotFoundException;

/**
 * 获取滤镜信息
 *
 * @Resource("BlurClasses",uri="/blur-classes")
 */
class BlurClassController extends BaseController
{
    protected $blurClassesTransformer;

    const  defaultNum = 10;

    public function __construct(BlurClassesTransformer $blurClassesTransformer)
    {
        $this->blurClassesTransformer = $blurClassesTransformer;
    }

    /**
     * 获取全部滤镜类型信息 可含多个
     * @Get("/")
     * @Versions({"v1"})
     * @Transaction({
     *      @Response(200, body={
     *                             "id"        : "滤镜类型ID",
     *                             "nameZh"    : "滤镜类型中文名称",
     *                             "nameEn"    : "滤镜类型英文名称",
     *                             "smallIcon" : "滤镜类型小图标 文件Key",
     *                             "largeIcon" : "滤镜类型大图标 文件Key",
     *                             "count"     : "包含滤镜数量",
     *                             "updated_at": "上次更新时间"
     *                          })
     * })
     */
    public function index()
    {
        //TODO 权限管理 测试滤镜
        $blur_classes = BlurClass::where('active',1)->get();
        return response()->json(
            $this->blurClassesTransformer->transformCollection($blur_classes->all()),
            200,
            [],
            JSON_NUMERIC_CHECK);
    }

    public function show($id,int $type)
    {
        try{
            $blur_class = BlurClass::findOrFail($id);
            return response()->json(
                $this->blurClassesTransformer->transform($blur_class,$type)
                ,
                200,
                [],
                JSON_NUMERIC_CHECK);
        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'blur_class_not_found'],404);
        }
    }

    /**
     * 通过滤镜类型ID获取全部详细信息
     * @Get("/{id}")
     * @Versions({"v1"})
     * @Transaction({
     *      @Response(200, body={
     *                             "id"       : "滤镜类型ID",
     *                             "nameZh"   : "滤镜类型中文名称",
     *                             "nameEn"   : "滤镜类型英文名称",
     *                             "smallIcon": "滤镜类型小图标 文件Key",
     *                             "largeIcon": "滤镜类型大图标 文件Key",
     *                             "count"    : "包含滤镜数量",
     *                             "updated_at": "上次更新时间",
     *                             "data"     : "[] || 参考: [GET /blurs/{id}]"
     *                          }),
     *     @Response(404,body={"error" : "blur_class_not_found"})
     * })
     */
    public function install($id)
    {
        return $this->show($id,1);
    }

    /**
     * 通过滤镜类型ID获取全部预览信息
     * @Get("/{id}/preview")
     * @Versions({"v1"})
     * @Transaction({
     *      @Response(200, body={
     *                             "id"       : "滤镜类型ID",
     *                             "nameZh"   : "滤镜类型中文名称",
     *                             "nameEn"   : "滤镜类型英文名称",
     *                             "smallIcon": "滤镜类型小图标 文件Key",
     *                             "largeIcon": "滤镜类型大图标 文件Key",
     *                             "count"    : "包含滤镜数量",
     *                             "updated_at": "上次更新时间",
     *                             "data"     : "数组格式[] ||数组格式 参考: [GET /blurs/{id}](只包含 id,nameZh,nameEn,preview,thumbnail_preview,updated_at字段)"
     *                          }),
     *      @Response(404,body={"error" : "blur_class_not_found"})
     * })
     */
    public function preview($id)
    {
        return $this->show($id,2);
    }
}