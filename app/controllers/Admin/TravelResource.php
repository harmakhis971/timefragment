<?php

/**
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @uses        Laravel The PHP frameworks for web artisans http://laravel.com
 * @author      Ri Xu http://xuri.me <xuri.me@gmail.com>
 * @copyright   Copyright (c) TimeFragment
 * @link        http://www.timefragment.com
 * @since       25th Nov, 2014
 * @license     Licensed under The MIT License http://www.opensource.org/licenses/mit-license.php
 * @version     0.1
 */

class Admin_TravelResource extends BaseResource
{
	/**
	 * Resource view directory
	 * @var string
	 */
	protected $resourceView = 'admin.travel';

	/**
	 * Model name of the resource, after initialization to a model instance
	 * @var string|Illuminate\Database\Eloquent\Model
	 */
	protected $model = 'Travel';

	/**
	 * Resource identification
	 * @var string
	 */
	protected $resource = 'travel';

	/**
	 * Resource database tables
	 * @var string
	 */
	protected $resourceTable = 'travel';

	/**
	 * Resource name (Chinese)
	 * @var string
	 */
	protected $resourceName = '去旅行';

	/**
	 * Custom validation message
	 * @var array
	 */
	protected $validatorMessages = array(
		'title.required'	=> '请填写标题。',
		'title.unique'		=> '已有同名标题。',
		'slug.unique'		=> '已有同名 sulg。',
		'content.required'	=> '请填写内容。',
		'category.exists'	=> '请填选择正确的话题。',
	);

	/**
	 * Resource list view
	 * GET         /resource
	 * @return Response
	 */
	public function index()
	{
		// Get sort conditions
		$orderColumn = Input::get('sort_up', Input::get('sort_down', 'created_at'));
		$direction   = Input::get('sort_up') ? 'asc' : 'desc' ;
		// Get search conditions
		switch (Input::get('target')) {
			case 'title':
				$title = Input::get('like');
				break;
		}
		// Construct query statement
		$query = $this->model->orderBy($orderColumn, $direction);
		isset($title) AND $query->where('title', 'like', "%{$title}%");
		$datas = $query->where('post_status', 'open')->paginate(15);
		return View::make($this->resourceView.'.index')->with(compact('datas'));
	}

	/**
	 * Resource create
	 * Redirect         {id}/new-post
	 * @return Response
	 */
	public function create()
	{
		$exist = $this->model->where('user_id', Auth::user()->id)->where('post_status', 'close')->first();
		if($exist)
		{
			return Redirect::route($this->resource.'.newPost', $exist->id);
		} else {
			$model						= $this->model;
			$model->user_id				= Auth::user()->id;
			$model->category_id			= '';
			$model->title				= '';
			$model->slug				= '';
			$model->content				= '';
			$model->meta_title			= '';
			$model->meta_description	= '';
			$model->meta_keywords		= '';
			$model->save();
			return Redirect::route($this->resource.'.newPost', $model->id);
		}
	}

	/**
	 * Resource create view
	 * GET         /resource/create
	 * @return Response
	 */
	public function newPost($id)
	{
		$data			= $this->model->find($id);
		$categoryLists	= TravelCategories::lists('name', 'id');
		$travel			= $this->model->where('id', $id)->first();
		return View::make($this->resourceView.'.create')->with(compact('data', 'categoryLists', 'travel'));
	}

	/**
	 * Resource create action
	 * POST        /resource
	 * @return Response
	 */
	public function store($id)
	{
		// Get all form data.
		$data	= Input::all();
		// Create validation rules
		$unique	= $this->unique();
		$rules	= array(
			'title'		=> 'required|'.$unique,
			'content'	=> 'required',
			'category'	=> 'exists:travel_categories,id',
		);
		$slug		= Input::input('title');
		$hashslug	= date('H.i.s').'-'.md5($slug).'.html';
		// Custom validation message
		$messages	= $this->validatorMessages;
		// Begin verification
		$validator	= Validator::make($data, $rules, $messages);
		if ($validator->passes()) {
			// Verification success
			// Add resource
			$model						= $this->model->find($id);
			$model->category_id			= $data['category'];
			$model->title				= e($data['title']);
			$model->slug				= $hashslug;
			$model->content				= e($data['content']);
			$model->meta_title			= e($data['title']);
			$model->meta_description	= e($data['title']);
			$model->meta_keywords		= e($data['title']);
			$model->post_status			= 'open';

			$timeline					= new Timeline;
			$timeline->slug				= $hashslug;
			$timeline->model			= 'Travel';
			$timeline->user_id			= Auth::user()->id;
			if ($model->save() && $timeline->save()) {
				// Add success
				return Redirect::route($this->resource.'.edit', $model->id)
					->with('success', '<strong>'.$this->resourceName.'添加成功：</strong>您可以继续编辑'.$this->resourceName.'，或返回'.$this->resourceName.'列表。');
			} else {
				// Add fail
				return Redirect::back()
					->withInput()
					->with('error', '<strong>'.$this->resourceName.'添加失败。</strong>');
			}
		} else {
			// Verification fail
			return Redirect::back()->withInput()->withErrors($validator);
		}
	}

	/**
	 * Resource edit view
	 * GET         /resource/{id}/edit
	 * @param  int  $id
	 * @return Response
	 */
	public function edit($id)
	{
		$data			= $this->model->find($id);
		$categoryLists	= TravelCategories::lists('name', 'id');
		$travel			= $this->model->where('slug', $data->slug)->first();
		return View::make($this->resourceView.'.edit')->with(compact('data', 'categoryLists', 'travel'));
	}

	/**
	 * Resource edit action
	 * PUT/PATCH   /resource/{id}
	 * @param  int  $id
	 * @return Response
	 */
	public function update($id)
	{
		// Get all form data.
		$data = Input::all();
		// Create validation rules
		$rules  = array(
			'title'		=> 'required',
			'slug'		=> 'required|'.$this->unique('slug', $id),
			'category'	=> 'exists:travel_categories,id',
			'content'	=> 'required',
		);
		$model		= $this->model->find($id);
		$oldSlug	= $model->slug;
		// Custom validation message
		$messages	= $this->validatorMessages;
		// Begin verification
		$validator	= Validator::make($data, $rules, $messages);
		if ($validator->passes()) {

			// Verification success
			// Update resource
			$model						= $this->model->find($id);
			$model->user_id				= Auth::user()->id;
			$model->category_id			= $data['category'];
			$model->title				= e($data['title']);
			$model->slug				= e($data['slug']);
			$model->content				= e($data['content']);
			$model->meta_title			= e($data['title']);
			$model->meta_description	= e($data['title']);
			$model->meta_keywords		= e($data['title']);

			$timeline					= Timeline::where('slug', $oldSlug)->where('user_id', Auth::user()->id)->first();
			$timeline->slug				= e($data['slug']);

			if ($model->save() && $timeline->save()) {
				// Update success
				return Redirect::back()
					->with('success', '<strong>'.$this->resourceName.'更新成功：</strong>您可以继续编辑'.$this->resourceName.'，或返回'.$this->resourceName.'列表。');
			} else {
				// Update fail
				return Redirect::back()
					->withInput()
					->with('error', '<strong>'.$this->resourceName.'更新失败。</strong>');
			}
		} else {
			// Verification fail
			return Redirect::back()->withInput()->withErrors($validator);
		}
	}

	/**
	 * Resource destory action
	 * DELETE      /resource/{id}
	 * @param  int  $id
	 * @return Response
	 */
	public function destroy($id)
	{
		$data = $this->model->find($id);
		if (is_null($data))
			return Redirect::back()->with('error', '没有找到对应的'.$this->resourceName.'。');
		elseif ($data)
		{
			$model		= $this->model->find($id);
			$thumbnails	= $model->thumbnails;
			File::delete(public_path('uploads/travel_large_thumbnails/'.$thumbnails));

			$timeline	= Timeline::where('slug', $model->slug)->where('user_id', Auth::user()->id)->first();
			$timeline->delete();

			$data->delete();
			return Redirect::back()->with('success', $this->resourceName.'删除成功。');
		}
		else
			return Redirect::back()->with('warning', $this->resourceName.'删除失败。');
	}

	/**
	 * Action: Add resource images
	 * @return Response
	 */
	public function postUpload($id)
	{
		$input = Input::all();
		$rules = array(
			'file' => 'image|max:3000',
		);

		$validation = Validator::make($input, $rules);

		if ($validation->fails())
		{
			return Response::make($validation->errors->first(), 400);
		}

		$file				= Input::file('file');
		$destinationPath	= 'uploads/travel/';
		$ext				= $file->guessClientExtension();  // Get real extension according to mime type
		$fullname			= $file->getClientOriginalName(); // Client file name, including the extension of the client
		$hashname			= date('H.i.s').'-'.md5($fullname).'.'.$ext; // Hash processed file name, including the real extension
		$picture			= Image::make($file->getRealPath());
		// crop the best fitting ratio and resize image
		$picture->fit(1024, 683)->save(public_path($destinationPath.$hashname));
		$picture->fit(430, 645)->save(public_path('uploads/travel_small_thumbnails/'.$hashname));
		$picture->fit(585, 1086)->save(public_path('uploads/travel_large_thumbnails/'.$hashname));

		$model				= $this->model->find($id);
		$oldThumbnails		= $model->thumbnails;
		$model->thumbnails	= $hashname;

		File::delete(
			public_path('uploads/travel_small_thumbnails/'.$oldThumbnails),
			public_path('uploads/travel_large_thumbnails/'.$oldThumbnails)
		);

		$models				= new TravelPictures;
		$models->filename	= $hashname;
		$models->travel_id	= $id;
		$models->user_id	= Auth::user()->id;

		if($model->save() && $models->save()) {
		   return Response::json('success', 200);
		} else {
		   return Response::json('error', 400);
		}
	}

	/**
	 * Action: Delete resource images
	 * @return Response
	 */
	public function deleteUpload($id)
	{
		// Only allows you to share pictures on the cover of the current resource being deleted
		$filename	= TravelPictures::where('id', $id)->where('user_id', Auth::user()->id)->first();
		$oldImage	= $filename->filename;

		if (is_null($filename)) {
			return Redirect::back()->with('error', '没有找到对应的图片');
		} elseif ($filename->delete()) {
			File::delete(
				public_path('uploads/travel/'.$oldImage)
			);
			return Redirect::back()->with('success', '图片删除成功。');
		} else {
			return Redirect::back()->with('warning', '图片删除失败。');
		}
	}


}