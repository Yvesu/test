<?php
/**
 * Created by PhpStorm.
 * User: 马骉
 * Date: 2016/3/7
 * Time: 20:35
 */

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Admin\Administrator;
use App\Models\Admin\Department;
use App\Models\User;
use App\Services\Email;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Storage;
use DB;
use Image;
class UserController extends BaseSessionController
{
    /**
     * 管理员登录认证
     * @param Request $request
     * @return $this|\Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function signIn(Request $request)
    {
        $remember = false;
        if($request->exists('remember')){
            $remember = true;
        }
        if(\Auth::guard('web')->attempt(
            [
                'email'         =>  $request->get('email').'@goobird.com',
                'password'      =>  $request->get('password'),
                'deleted_at'    =>  null
            ],
            $remember
        )){

            session(['admin'=>DB::table('administrator_b')->where('email',$request->get('email').'@goobird.com')->first()]);
//            dd(session('admin'));
            return redirect('admin/dashboard');
        }
        \Session::flash('user_login_failed','用户名或密码不正确,或用户被停用');
        return redirect('admin/login')->withInput();
    }

    /**
     * 管理员登出
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function logout()
    {
        \Auth::guard('web')->logout();
        return redirect('/admin');
    }

    /**
     * 管理员添加页面
     * @return $this
     */
    public function addPage()
    {
        $departments = Department::where('active',1)->get();
        return view('admin/management/family/addAdmin')
                ->with([
                    'departments'   =>  $departments
                ]);
    }

    /**
     * 管理员添加提交操作
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function add(Request $request)
    {
        $info = $request->except(['add-admin-IDCard','_token']);
        $file = $request->file('add-admin-IDCard');
        $random = str_random(10);
        DB::transaction(function()use($info,$file,$random){
            $id = DB::table('administrator_b')->insertGetId([
                'email'                             =>  $info['add-admin-email'],
                'password'                          =>  bcrypt($random),
                'position_id'                       =>  $info['add-admin-position'],
                'sex'                               =>  $info['add-admin-sex'],
                //TODO permissions
                //TODO avatar
                'avatar'                            =>  'default/default.png',
                'phone'                             =>  $info['add-admin-phone'],
                'name'                              =>  $info['add-admin-name'],
                'secondary_contact_phone'           =>  $info['add-admin-secondary-contact-phone'],
                'secondary_contact_name'            =>  $info['add-admin-secondary-contact-name'],
                'secondary_contact_relationship'    =>  $info['add-admin-secondary-contact-relationship'],
                // 前端注册用户id
                'user_id'                           =>  $info['add-admin-user-id'] === '' ? null : $info['add-admin-user-id'],
                'created_at'                        =>  new Carbon,
                'updated_at'                        =>  new Carbon,
            ]);
            $file->move(storage_path().'/app/public/administrator/' . $id, $file->getClientOriginalName());

            DB::table('administrator_b')
                ->where('id',$id)
                ->update(['ID_card_URL' => $id.'/'.$file->getClientOriginalName()]);
        });

        $data = ['password' => $random];
        Email::register($info['add-admin-email'],$data,trans('common.password'));

        return redirect('/admin/management/family/member');
    }

    /**
     * 停用管理员
     * @param $id
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function disabled($id)
    {
        /**
         * 防止停用自己
         */
        if($id == \Auth::guard('web')->user()->id){
            return redirect('admin/management/family/member');
        }

        Administrator::find($id)->delete();
        return redirect('/admin/management/family/member?departmentID=0');
    }

    /**
     * 启用管理员提交操作
     * @param $id
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function enabled($id)
    {
        Administrator::onlyTrashed()->find($id)->restore();
        return redirect('/admin/management/family/member');
    }

    public function viewMore($id)
    {
        $administrator = Administrator::withTrashed()->find($id);
        return view('admin.management.family.viewAdmin')
                ->with([
                    'administrator'     =>  $administrator
                ]);
    }

    public function editPage($id)
    {
        $administrator = Administrator::with('belongsToPosition.belongsToDepartment')->find($id);
        $departments = Department::with('hasManyPosition')->where('active',1)->get();
        return view('admin/management/family/editAdmin')
            ->with([
                'departments'       =>  $departments,
                'administrator'     =>  $administrator
            ]);
    }

    public function editByID($id,Request $request)
    {
        $info = $request->except(['edit-admin-IDCard','_token']);
        $file = $request->file('edit-admin-IDCard');
        DB::transaction(function()use($info,$file,$id){
            DB::table('administrator_b')->where('id',$id)->update([
                'email'                             =>  $info['edit-admin-email'],
                'position_id'                       =>  $info['edit-admin-position'],
                'sex'                               =>  $info['edit-admin-sex'],
                //TODO permissions
                'phone'                             =>  $info['edit-admin-phone'],
                'name'                              =>  $info['edit-admin-name'],
                'secondary_contact_phone'           =>  $info['edit-admin-secondary-contact-phone'],
                'secondary_contact_name'            =>  $info['edit-admin-secondary-contact-name'],
                'secondary_contact_relationship'    =>  $info['edit-admin-secondary-contact-relationship'],
                'user_id'                           =>  $info['edit-admin-user-id'] === '' ? null : $info['edit-admin-user-id'],
                'updated_at'                        =>  new Carbon,
            ]);

            if(!is_null($file)){
                $file->move(storage_path().'/app/public/administrator/' . $id, $file->getClientOriginalName());
                Storage::delete('public/administrator/' . Administrator::find($id)->ID_card_URL);
                DB::table('administrator_b')
                    ->where('id',$id)
                    ->update(['ID_card_URL' => $id.'/'.$file->getClientOriginalName()]);
            }
        });

        return redirect('/admin/management/family/member');

    }

    public function account()
    {
        $administrator = \Auth::guard('web')->user();
        return view('admin.account')
            ->with([
                'administrator'     =>  $administrator
            ]);
    }

    public function avatarUpload()
    {
        $this->wrongTokenAjax();
        $file = \Request::file('upload-image');
        $input = array('image' => $file);
        $rules = array(
            'image' => 'image'
        );
        $validator = Validator::make($input, $rules);
        if ( $validator->fails() ) {
            $errors = array_flatten($validator->getMessageBag()->toArray());
            foreach($errors as $error){
                $error = trans($error);
            }
            return \Response::json([
                'success' => false,
                'errors' => $errors
            ]);

        }
        $id = \Auth::guard('web')->user()->id;
        $destinationPath = storage_path().'/app/public/administrator/' . $id . '/temp';
        $filename = $file->getClientOriginalName();
        $str_array = explode(' ',$filename);
        $filename = str_random(10).$str_array[sizeof($str_array) - 1];
        $file->move($destinationPath, $filename);
        Image::make($destinationPath . '/' . $filename)->widen(250)->save();


        return \Response::json(
            [
                'success' => true,
                'avatar' => asset('admin/images/'.$id.'/temp/'.$filename),
            ]
        );
    }

    public function avatarCrop(Request $request)
    {
        $photo = $request->get('photo');
        $width = (int)$request->get('w');
        $height = (int)$request->get('h');
        $xAlign = (int)$request->get('x');
        $yAlign = (int)$request->get('y');

        $str = explode("/",$photo);
        $filename = $str[sizeof($str) - 1];
        $id = \Auth::guard('web')->user()->id;
        $path = storage_path().'/app/public/administrator/'.$id.'/temp/';

        Image::make($path.$filename)->crop($width,$height,$xAlign,$yAlign)->save();

        //添加随机数，防止重复
        $random = str_random(6);
        Storage::move('public/administrator/'.$id.'/temp/'.$filename,'public/administrator/'.$id.'/'.$random.$filename);

        //删除多余临时文件 默认24小时前提交的都是无效的
        $files = Storage::files('public/administrator/' . $id . '/temp/');

        foreach($files as $file){
            $time = Storage::lastModified($file);
            if(is_int($time) && time() - $time > 86400){
                Storage::delete($file);
            }
        }

        $administrator = Administrator::find($id);

        if('default/default.png' != $administrator->avatar && Storage::exists('public/administrator/'.$administrator->avatar)){
            Storage::delete('public/administrator/'.$administrator->avatar);
        }


        $administrator->avatar = $id.'/'.$random.$filename;
        $administrator->save();


        return redirect('admin/account');
    }

    public function checkUserID(Request $request)
    {
        $admin = $request->get('admin');
        $user  = $request->get('user');
        if ($user === null || $user == '') {
            return 0;
        }
        $count = User::find($user);
        if (!$count) {
            return -1;
        }
        if ($admin !== null && ctype_digit($admin)) {
            return Administrator::where('id','!=',$admin)->where('user_id',$user)->count();
        } else {
            return Administrator::where('user_id',$user)->count();
        }

    }

    /**
     * 检查_token
     * @return mixed
     */
    public function wrongTokenAjax()
    {
        if ( \Session::token() !== \Request::get('_token') ) {
            $response = [
                'status' => false,
                'errors' => 'Wrong Token',
            ];

            return \Response::json($response);
        }

    }

}