<?php

namespace App\Http\Controllers\Admin\Content;

use App\Http\Controllers\Controller;
use App\Models\Admin\Administrator;
use App\Http\Controllers\Admin\BaseSessionController;
use App\Models\Channel;
use App\Models\ChannelTweet;
use App\Models\Label;
use App\Models\Tweet;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use App\Http\Requests;
use Auth;
use DB;
use CloudStorage;

/**
 * 相册
 * Class PhotoAlbumController
 * @package App\Http\Controllers\Admin\Content
 */
class PhotoAlbumController extends BaseSessionController
{
    private $paginate = 8;
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $user = Auth::guard('web')->user();

        $active = is_null($request->get('active')) || $request->get('active') == 1  ? 1 : 0;
        $tweets = Tweet::where('active',$active)->where('type',1);
        $official = $request->get('official');

        if (is_null($official)) {
            $tweets = $tweets->paginate($this->paginate);
        } else if ($official == 1) {
            $users = Administrator::whereNotNull('user_id')->get(['user_id'])->pluck('user_id')->all();

            $tweets = $tweets->whereIn('user_id',$users)->paginate($this->paginate);
        } else {
            $users = Administrator::whereNotNull('user_id')->get(['user_id'])->pluck('user_id')->all();
            $tweets = $tweets->whereNotIn('user_id',$users)->paginate($this->paginate);
        }

        return view('admin/content/photo_album/index')
            ->with('user',$user)
            ->with('tweets',$tweets);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('admin/content/photo_album/create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        try {
            $name = $request->get('name');
            $keys = $request->get('keys');
            $photos = explode(',',$keys);
            $newTweet = [
                'type'     => 1,
                'user_id'  => Auth::guard('web')->user()->user_id,
                'photo'    => json_encode($photos),
                'content'  => $request->get('name') == '' ? null : $request->get('name'),
            ];

            // 开启事务
            DB::beginTransaction();

            $tweet = Tweet::create($newTweet);
            $data = [];

            foreach ($photos as $photo) {

                // 对图片URL进行拆分，七牛路径和宽高
                $newData = explode('@',$photo);

                // 获取图片在七牛上的临时存放路径
                $newPhoto = $newData[0];

                // 获取图片的宽和高
                $size = $newData[sizeof($newData) - 1];

                // 获取图片的后缀
                $brr = explode('/',$photo);
                $arr = strstr($brr[sizeof($brr) - 1],'@',true);
                $crr = explode('.',$arr);

                // 拼接新路径，将宽高拼接在尾部(例:meitu_230*120_.jpg)
                $new_key = 'tweet/' . $tweet->id . '/' . $crr[0] .'_' .$size.'_.'.$crr[sizeof($crr) - 1];

                // 以七牛临时路径为下标，新路径为值
                $data[$newPhoto] = $new_key;
                $result[] = $new_key;
            }
            // 旧代码 搜索号：备忘录
//            $tweet = Tweet::create($newTweet);
//            $data = [];
//            foreach ($photos as $photo) {
//                $arr = explode('/',$photo);
//                $new_key = 'tweet/' . $tweet->id . '/' . $arr[sizeof($arr) - 1];
//                $data[$photo] = $new_key;
//                $result[] = $new_key;
//            }

            $tweet->photo = json_encode($result);

            // 对七牛上的图片进行重命名
            CloudStorage::batchRename($data);
            $tweet->save();
            DB::commit();
            return redirect('admin/content/photo_album/' . $tweet->id);
        } catch (\Exception $e) {
            DB::rollBack();
            dd($e);
            return response()->json(['error' => $e->getMessage()], $e->getCode());
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        try {
            $tweet = Tweet::where('id',$id)->where('type',1)->firstOrFail();
            $tweet->photo = json_decode($tweet->photo,true);
            return view('/admin/content/photo_album/show')
                ->with('tweet',$tweet);
        } catch (ModelNotFoundException $e) {
            abort(404);
        }
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        try {
            $photo_album = Tweet::with('belongsToManyChannel')->where('id',$id)->where('type',1)->firstOrFail();
            $channels = Channel::active()->get();
            $labels = Label::active()->get();
            return view('/admin/content/photo_album/edit')
                ->with('photo_album', $photo_album)
                ->with('channels', $channels)
                ->with('labels', $labels);
        } catch (ModelNotFoundException $e) {
            abort(404);
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        try {
            $tweet = Tweet::where('id',$id)->where('type',1)->whereNull('original')->firstOrFail();
            if ($request->has('active')) {
                $active = $request->get('active') == 1 ? 1 : 0;
                $tweet->active = $active;
            } else {
                $input = $request->all();
                if (strtotime($tweet->updated_at) - $input['_time']) {
                    return redirect('admin/content/photo_album/' . $id);
                }
                if (isset($input['top_check']) && $input['top_check'] === 'on') {
                    $tweet->top_expires = $this->dateToTime($input['top_date'],$input['top_time'],$input['_timezone']);
                }
                if (isset($input['recommend_check']) && $input['recommend_check'] === 'on') {
                    $tweet->recommend_expires = $this->dateToTime($input['recommend_date'],$input['recommend_time'],$input['_timezone']);
                }
                if (! empty($input['label_name']) && Label::where('id',$input['label_name'])->active()->count()) {
                    $tweet->label_id = $input['label_name'];
                    $tweet->label_expires = $this->dateToTime($input['label_date'],$input['label_time'],$input['_timezone']);
                }

                ChannelTweet::ofTweetID($id)->delete();
                if (isset($input['channels']) && ! empty($input['channels'])) {
                    $data = [];
                    $time = new Carbon();
                    foreach ($input['channels'] as $channel) {
                        $data[] = [
                            'channel_id' => $channel,
                            'tweet_id'   => $id,
                            'updated_at' => $time,
                            'created_at' => $time
                        ];
                    }
                    DB::table('channel_tweet')->insert($data);
                }
            }
            $tweet->save();
            return redirect('admin/content/photo_album/' . $id);
        } catch (ModelNotFoundException $e) {
            abort(404);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }

    public function dateToTime($date,$time,$timezone)
    {
        return Carbon::createFromTimestampUTC(
            strtotime(Carbon::createFromFormat('Y-m-d H:i',$date . ' ' . $time)) + $timezone * 60
        );
    }
}
