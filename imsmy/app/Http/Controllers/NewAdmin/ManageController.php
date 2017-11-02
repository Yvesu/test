<?php

namespace App\Http\Controllers\NewAdmin;

use App\Http\Controllers\Controller;
use App\Models\Activity;
use App\Models\Admin\Administrator;
use App\Models\CashWithdrawDetails;
use App\Models\Make\MakeFilterFile;
use App\Models\Make\MakeTemplateFile;
use App\Models\Tweet;
use App\Models\UserVerify;
use App\Models\ZxUserComplains;
use App\Http\Transformer\AdministratorTransformer;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Auth;

// TODO 调试期使用Controller，后期换回 BaseSessionController（访问权限管理）
class ManageController extends Controller
{

    private $administratorTransformer;
    private $paginate = 8;

    public function __construct(
        AdministratorTransformer $administratorTransformer
    )
    {
        $this -> administratorTransformer = $administratorTransformer;
    }

    /**
     * 管理信息
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function manage(Request $request)
    {
        // 管理员信息
        $admin = Auth::guard('api')->user();

        // 临时测试使用，后期添加验证信息
        $admin_info = Administrator::with('hasManyLoginLog')->where('id',$admin->id)->firstOrFail(['name','id','user_id']);

        // 视频总条数
        $count_tweet = Tweet::get(['id']) -> count();
        $count_activity = Activity::get(['id']) -> count();
        $count_template = MakeTemplateFile::get(['id']) -> count();
        $count_filter = MakeFilterFile::get(['id']) -> count();
        $count_verity = UserVerify::where('verify_status', 0)->get(['id']) -> count(); // 认证申请

        $complain = ZxUserComplains::where('status', 0) -> get(['id']) -> count();  // 投诉与反馈

        // 提现申请
        $count_withdraw = CashWithdrawDetails::where('status',0)-> get(['id']) -> count();

        return response() -> json([
            'admin' => $this -> administratorTransformer -> transform($admin_info),
            'count_tweet' => $count_tweet,
            'count_activity' => $count_activity,
            'count_template' => $count_template,
            'count_filter' => $count_filter,
            'count_verity' => $count_verity,
            'count_complain' => $complain,
            'count_withdraw' => $count_withdraw,
        ], 200);
    }


}
