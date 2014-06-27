<?php

class Admin_TravelResource extends BaseResource
{
    /**
     * 资源视图目录
     * @var string
     */
    protected $resourceView = 'admin.travel';

    /**
     * 资源模型名称，初始化后转为模型实例
     * @var string|Illuminate\Database\Eloquent\Model
     */
    protected $model = 'Travel';

    /**
     * 资源标识
     * @var string
     */
    protected $resource = 'travel';

    /**
     * 资源数据库表
     * @var string
     */
    protected $resourceTable = 'travel';

    /**
     * 资源名称（中文）
     * @var string
     */
    protected $resourceName = '旅行';

    /**
     * 自定义验证消息
     * @var array
     */
    protected $validatorMessages = array(
        'title.required'        => '请填写标题。',
        'title.unique'          => '已有同名标题。',
        'slug.unique'           => '已有同名 sulg。',
        'content.required'      => '请填写内容。',
        'category.exists'       => '请填选择正确的分类。',
    );

    /**
     * 资源列表页面
     * GET         /resource
     * @return Response
     */
    public function index()
    {
        // 获取排序条件
        $orderColumn = Input::get('sort_up', Input::get('sort_down', 'created_at'));
        $direction   = Input::get('sort_up') ? 'asc' : 'desc' ;
        // 获取搜索条件
        switch (Input::get('target')) {
            case 'title':
                $title = Input::get('like');
                break;
        }
        // 构造查询语句
        $query = $this->model->orderBy($orderColumn, $direction);
        isset($title) AND $query->where('title', 'like', "%{$title}%");
        $datas = $query->paginate(15);
        return View::make($this->resourceView.'.index')->with(compact('datas'));
    }

    /**
     * 资源创建页面
     * GET         /resource/create
     * @return Response
     */
    public function create()
    {
        $categoryLists = TravelCategories::lists('name', 'id');
        return View::make($this->resourceView.'.create')->with(compact('categoryLists'));
    }

    /**
     * 资源创建动作
     * POST        /resource
     * @return Response
     */
    public function store()
    {
        // 获取所有表单数据.
        $data   = Input::all();
        // 创建验证规则
        $unique = $this->unique();
        $rules  = array(
            'title'        => 'required|'.$unique,
            'content'      => 'required',
            'category'     => 'exists:travel_categories,id',
        );
        $slug      = Input::input('title');
        $hashslug  = date('H.i.s').'-'.md5($slug).'.html';
        // 自定义验证消息
        $messages  = $this->validatorMessages;
        // 开始验证
        $validator = Validator::make($data, $rules, $messages);
        if ($validator->passes()) {
            // 验证成功
            // 添加资源
            $model                   = $this->model;
            $model->user_id          = Auth::user()->id;
            $model->category_id      = $data['category'];
            $model->title            = e($data['title']);
            $model->slug             = $hashslug;
            $model->content          = e($data['content']);
            $model->meta_title       = e($data['title']);
            $model->meta_description = e($data['title']);
            $model->meta_keywords    = e($data['title']);

            if ($model->save()) {
                // 添加成功
                return Redirect::back()
                    ->with('success', '<strong>'.$this->resourceName.'添加成功：</strong>您可以继续添加新'.$this->resourceName.'，或返回'.$this->resourceName.'列表。');
            } else {
                // 添加失败
                return Redirect::back()
                    ->withInput()
                    ->with('error', '<strong>'.$this->resourceName.'添加失败。</strong>');
            }
        } else {
            // 验证失败
            return Redirect::back()->withInput()->withErrors($validator);
        }
    }

    /**
     * 资源编辑页面
     * GET         /resource/{id}/edit
     * @param  int  $id
     * @return Response
     */
    public function edit($id)
    {
        $data          = $this->model->find($id);
        $categoryLists = TravelCategories::lists('name', 'id');
        $travel        = Travel::where('slug', $data->slug)->first();
        return View::make($this->resourceView.'.edit')->with(compact('data', 'categoryLists', 'travel'));
    }

    /**
     * 资源编辑动作
     * PUT/PATCH   /resource/{id}
     * @param  int  $id
     * @return Response
     */
    public function update($id)
    {
        // 获取所有表单数据.
        $data = Input::all();
        // 创建验证规则
        $rules  = array(
            'title'        => 'required',
            'content'      => 'required',
            'category'     => 'exists:travel_categories,id',
        );
        $slug = Input::input('title');
        $hashslug = date('H.i.s').'-'.md5($slug).'.html';
        // 自定义验证消息
        $messages = $this->validatorMessages;
        // 开始验证
        $validator = Validator::make($data, $rules, $messages);
        if ($validator->passes()) {

            // 验证成功
            // 更新资源
            $model = $this->model->find($id);
            $model->user_id          = Auth::user()->id;
            $model->category_id      = $data['category'];
            $model->title            = e($data['title']);
            $model->slug             = $hashslug;
            $model->content          = e($data['content']);
            $model->meta_title       = e($data['title']);
            $model->meta_description = e($data['title']);
            $model->meta_keywords    = e($data['title']);

            if ($model->save()) {
                // 更新成功
                return Redirect::back()
                    ->with('success', '<strong>'.$this->resourceName.'更新成功：</strong>您可以继续编辑'.$this->resourceName.'，或返回'.$this->resourceName.'列表。');
            } else {
                // 更新失败
                return Redirect::back()
                    ->withInput()
                    ->with('error', '<strong>'.$this->resourceName.'更新失败。</strong>');
            }
        } else {
            // 验证失败
            return Redirect::back()->withInput()->withErrors($validator);
        }
    }

    /**
     * 动作：添加去旅行封面图片
     * @return Response
     */
    public function postUpload($id){

        $input = Input::all();
        $rules = array(
            'file' => 'image|max:3000',
        );

        $validation = Validator::make($input, $rules);

        if ($validation->fails())
        {
            return Response::make($validation->errors->first(), 400);
        }
        // 查找旧图片
        $model      = $this->model->find($id);
        $oldpicture = $model->picture;
        // 删除旧图片
        File::delete(
            public_path('uploads/travel/'.$oldpicture));

        $file            = Input::file('file');
        $destinationPath = 'uploads/travel/';
        $ext             = $file->guessClientExtension();  // Get real extension according to mime type
        $fullname        = $file->getClientOriginalName(); // Client file name, including the extension of the client
        $hashname        = date('H.i.s').'-'.md5($fullname).'.'.$ext; // Hash processed file name, including the real extension
        $picture         = Image::make($file->getRealPath());
        $upload_success  = $picture->resize(585, 347)->save(public_path($destinationPath.$hashname));

        $model           = $this->model->find($id);
        $model->picture  = $hashname;
        $model->save();

        if( $upload_success ) {
           return Response::json('success', 200);
        } else {
           return Response::json('error', 400);
        }
    }

    /**
     * 动作：删除去旅行封面图片
     * @return Response
     */
    public function deleteTravelPicture($id)
    {
        // 仅允许对当前创意分享的封面图片进行删除操作
        $model   = $this->model->find($id);
        $picture = $model->picture;
        if (is_null($picture))
            return Redirect::back()->with('error', '没有找到对应的图片');
        elseif ($picture) {

        // 删除图片
        File::delete(
            public_path('uploads/travel/'.$picture)
        );
        $model->picture = 'staff/default.jpg';
        $model->save();
            return Redirect::back()->with('success', '图片删除成功。');
        }

        else
            return Redirect::back()->with('warning', '图片删除失败。');
    }

}
