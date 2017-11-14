<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2016/3/11
 * Time: 12:47
 */

namespace App\Http\ViewComposers\Admin;

use Illuminate\Contracts\View\View;
use App;
class LayerProfileComposer
{
    /**
     * 绑定数据到视图.
     *
     * @param View $view
     * @return void
     */
    public function compose(View $view)
    {
        $user = \Auth::guard('web')->user();
        $view->with('user', $user);
    }
}