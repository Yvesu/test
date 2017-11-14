<?php
/**
 * Created by PhpStorm.
 * User: Ma biao
 * Date: 2016/5/19
 * Time: 15:11
 */

namespace App\Http\Controllers\Admin\System;


use App\Http\Controllers\Controller;
use App\Http\Controllers\Admin\BaseSessionController;
use App\Models\Channel;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use CloudStorage;
use DB;
class ChannelController extends BaseSessionController
{
    public function index(Request $request)
    {
        $active = is_null($request->get('active')) || $request->get('active') == 1  ? 1 : 0;
        $channels = Channel::where('active',$active)->orderBy('sort')->get();
        return view('admin/system_management/channel/index')
            ->with('channels',$channels);

    }

    public function store(Request $request)
    {
        $name = $request->get('name');
        $ename = $request->get('ename');
        $icon = $request->file('channel-icon');
        if(is_null($name) || is_null($ename) || is_null($icon)){
            return;
        }

        // 判断频道是否存在，如果存在，返回并将信息存至session中
        if(Channel::where('name',$name)->first() || Channel::where('ename',$ename)->first()){
            \Session::flash('name', '"'.$name.'"'.trans('common.has_been_existed'));
            return redirect('/admin/system_management/channel/create')->withInput();
        }

        DB::beginTransaction();
        $channel = Channel::create([
            'name'      => $name,
            'ename'     => $ename,
            'sort'      => ++Channel::orderBy('sort','DESC')->first()->sort,
            'icon'      => 'temp_key',
            'hash_icon' => 'temp_hash',
            'active'    => 0
        ]);

        // 获取随机数
        $rand = mt_rand(1000000,9999999);

        $result = CloudStorage::putFile(
            'channel/' . $channel->id . '/' . getTime() . $rand . '.' . $icon->getClientOriginalExtension(),
            $icon);

        if($result[1] !== null){
            DB::rollBack();
        } else {
            $channel->hash_icon = $result[0]['hash'];
            $channel->icon = $result[0]['key'];
            $channel->save();
            DB::commit();
        }

        return redirect('/admin/system_management/channel/' . $channel->id);
    }

    public function show($id)
    {
        try {
            $channel = Channel::findOrFail($id);
            return view('admin/system_management/channel/show')
                ->with('channel',$channel);
        } catch (ModelNotFoundException $e) {
            abort(404);
        }

    }

    public function update($id,Request $request)
    {
        try {
            $channel = Channel::findOrFail($id);
            if($request->has('name')){
                $name = $request->get('name');
                $ename = $request->get('ename');
                $icon = $request->file('channel-icon');
                if(Channel::where('name',$name)->where('id','!=',$id)->count()){
                    \Session::flash('name', '"'.$name.'"'.trans('common.has_been_existed'));
                    return redirect('/admin/content/channel/edit')->withInput();
                }
                if(isset($icon)){

                    // 获取随机数
                    $rand = mt_rand(1000000,9999999);

                    $result = CloudStorage::putFile(
                        'channel/' . $channel->id . '/' . getTime() . $rand . '.' . $icon->getClientOriginalExtension(),
                        $icon);

                    $channel->icon != $result[0]['key'] ? CloudStorage::delete($channel->icon) : null;

                    if($result[1] === null){
                        $channel->name = $name;
                        $channel->ename = $ename;
                        $channel->hash_icon = $result[0]['hash'];
                        $channel->icon = $result[0]['key'];
                    }
                }else{
                    $channel->name = $name;
                    $channel->ename = $ename;
                }

            } else if($request->has('active')){
                $active = $request->get('active') == 1 ? 1 : 0;
                $channel->active = $active;
            }
            $channel->save();
            return redirect('admin/system_management/channel/' . $id);
        } catch (ModelNotFoundException $e) {
            abort(404);
        }
    }

    public function create()
    {
        return view('admin/system_management/channel/create');
    }

    public function edit($id)
    {
        try {
            $channel = Channel::findOrFail($id);
            return view('admin/system_management/channel/edit')
                ->with('channel',$channel);
        } catch (ModelNotFoundException $e) {
            abort(404);
        }
    }
}