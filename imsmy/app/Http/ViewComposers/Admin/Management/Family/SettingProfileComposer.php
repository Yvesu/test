<?php
/**
 * Created by PhpStorm.
 * User: mabiao
 * Date: 2016/3/28
 * Time: 14:53
 */

namespace app\Http\ViewComposers\Admin\Management\Family;

use App\Models\Admin\Department;
use App\Models\Admin\Position;
use Illuminate\Contracts\View\View;
class SettingProfileComposer
{
    /**
     * 绑定数据到视图.
     *
     * @param View $view
     * @return void
     */
    public function compose(View $view)
    {
        $operating_count = Department::where('active',1)->count();

        $disabled_count  = Department::where('active',0)->count();
        $disabled_count  += Position::where('active',0)
                            ->whereHas('belongsToDepartment',function($q){
                                $q->where('active',1);
                            })->count();

        $review_count  = Department::where('active',2)->count();
        $review_count  += Position::where('active',2)
                            ->whereHas('belongsToDepartment',function($q){
                            $q->where('active',1);
                            })->count();
        $view->with([
            'operating_count'   =>  $operating_count,
            'disabled_count'    =>  $disabled_count,
            'review_count'      =>  $review_count,
        ]);
    }
}