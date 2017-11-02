<?php
/**
 * Created by PhpStorm.
 * User: Ma biao
 * Date: 2016/5/31
 * Time: 10:13
 */

namespace App\Http\Controllers\Admin\Content;

use App\Models\Label;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use App\Http\Controllers\Admin\BaseSessionController;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class LabelController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $active = is_null($request->get('active')) || $request->get('active') == 1  ? 1 : 0;
        $labels = Label::where('active',$active)->get();
        return view('admin/content/label/index')
            ->with('labels',$labels);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('admin/content/label/create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $name = $request->get('name');
        if ($name === null) {
            abort(400);
        }
        if (Label::where('name',$name)->count()) {
            \Session::flash('name', '"'.$name.'"'.trans('common.has_been_existed'));
            return redirect('/admin/content/label/create')->withInput();
        }

        Label::create(['name' => $name]);
        return redirect('admin/content/label');
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
            $label = Label::findOrFail($id);
            return view('admin/content/label/show')
                ->with('label',$label);
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
            $label = Label::findOrFail($id);
            if($request->has('name')) {

            } else if($request->has('active')) {
                $active = $request->get('active') == 1 ? 1 : 0;
                $label->active = $active;
            }
            $label->save();
            return redirect('admin/content/label/' . $id);
        } catch (ModelNotFoundException $e) {
            abort(404);
        }
    }
}