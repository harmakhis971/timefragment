@include('layout.account-header')
@yield('content')
{{ script('ckeditor') }}
<body id="inbox-page" class="bg-gray-light">

    @include('layout.account-navigation')
    @yield('content')

    @include('layout.account-sidebar')
    @yield('content')

    <div class="preloader">
        <div class="timer"></div>
    </div>


    <div id="container" class="main-content tp-t-60">

        <button class="menu-btn btn btn-bordered text-gray-alt text-bold top-left-corner tm-l-30 pull-left">&#9776; 菜单</button>

        <div class="row">

            <div class="col-sm-9">
                <div class="bg-white p-tb-30">

                    <div class="btn-group">
                        <div class="iconmelon m-r-10 m-l-30">
                            <svg viewBox="0 0 32 32">
                                <g filter="">
                                    <use xlink:href="#speech-talk-user"></use>
                                </g>
                            </svg>
                        </div>

                        <span class="text-gray-dark text-large align-with-button m-r-30">
                                编辑{{ $resourceName }}
                        </span>

                    </div>


                    <div class="pull-right m-r-30 mail-nav">

                        <a href="{{ route('mycreative.index') }}" class="btn btn-bordered text-gray-alt">
                            &laquo; 返回{{ $resourceName }}列表
                        </a>

                    </div>

                    <div class="p-lr-30 p-tb-10 pm-lr-10">
                        @include('layout.notification')
                    </div>

                    <div class="p-lr-30 p-tb-10 pm-lr-10">
                        <ul class="nav nav-tabs">
                            <li class="active">
                                <a href="#tab-general" data-toggle="tab">
                                    <div class="text-small">Main Content</div>
                                    <span class="text-uppercase">主要内容</span>
                                </a>
                            </li>
                            <li>
                                <a href="#tab-images-management" data-toggle="tab">
                                    <div class="text-small">Photo Management</div>
                                    <span class="text-uppercase">照片管理</span>
                                </a>
                            </li>
                            <li>
                                <a href="#tab-info" data-toggle="tab">
                                    <div class="text-small">Article Information</div>
                                    <span class="text-uppercase">文章信息</span>
                                </a>
                            </li>
                        </ul>

                        <form class="form-horizontal" method="POST" action="{{ route($resource.'.update', $data->id) }}" autocomplete="off" style="padding:1em;border:1px solid #ddd;border-top:0;" accept-charset="UTF-8" enctype="multipart/form-data">
                            {{-- CSRF Token --}}
                            <input name="_method" type="hidden" value="PUT" />
                            <input name="_token" type="hidden" value="{{ csrf_token() }}" />


                            {{-- Tabs Content --}}
                            <div class="tab-content">

                                {{-- General tab --}}
                                <div class="tab-pane active fade p-30 in" id="tab-general" style="margin:0 1em;">

                                    <div class="form-group">
                                        <label for="category">分类</label>
                                        {{ $errors->first('category', '<span style="color:#c7254e;margin:0 1em;">:message</span>') }}
                                        {{ Form::select('category', $categoryLists, $data->category->id, array('class' => 'form-control input-sm selectpicker input-light brad')) }}
                                    </div>

                                    <div class="form-group">
                                        <label for="title">标题</label>
                                        {{ $errors->first('title', '<span style="color:#c7254e;margin:0 1em;">:message</span>') }}
                                        <input class="form-control" type="text" name="title" id="title" value="{{ Input::old('title', $data->title) }}" />
                                    </div>

                                    <div class="form-group">
                                        <label for="content">内容</label>
                                        {{ $errors->first('content', '<span style="color:#c7254e;margin:0 1em;">:message</span>') }}
                                        <textarea rows="10" id="editor11" class="ckeditor form-control" name="content" rows="10">{{ Input::old('content', $data->content) }}
                                        </textarea>
                                    </div>

                                </div>

                                {{-- Cover Photo tab --}}
                                <div class="tab-pane fade p-30" id="tab-cover-photo" style="margin:0 1em;">

                                    <div class="table-responsive form-group">
                                        <table class="table table-striped table-bordered table-hover">
                                            <thead>
                                                <tr>
                                                    <th>封面图片</th>
                                                    <th style="width:5em;text-align:center;">操作</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @if ($data->picture)
                                                <tr>
                                                    <td>
                                                        <img width="100" height="100" src="{{ route('home') }}/uploads/creative/single/{{ $data->picture }}">
                                                    </td>
                                                    <td>
                                                        <a href="javascript:void(0)" class="btn btn-xs btn-danger"
                                                        onclick="modal('{{ route($resource.'.deleteUpload', $data->id) }}')">删除图片</a>
                                                    </td>
                                                </tr>
                                                @endif
                                            </tbody>
                                        </table>
                                    </div>
                                </div>

                                {{-- Images Management tab --}}
                                <div class="tab-pane fade p-30" id="tab-images-management" style="margin:0 1em;">

                                    <div class="table-responsive form-group">
                                        <table class="table table-striped table-bordered table-hover">
                                            <thead>
                                                <tr>
                                                    <th style="width:4em;text-align:center;">
                                                        <div class="icheckbox_flat-blue" aria-checked="false" aria-disabled="false">
                                                            <input type="checkbox" name="notification" value="" class="icheck">
                                                            <ins class="iCheck-helper"></ins>
                                                        </div>
                                                    </th>
                                                    <th>图片</th>
                                                    <th style="width:5em;text-align:center;">操作</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach ($creative->pictures as $picture)
                                                <tr>
                                                    <td style="text-align:center; padding: 50px 0;">
                                                        <div class="icheckbox_flat-blue" aria-checked="false" aria-disabled="false">
                                                            <input type="checkbox" name="notification" value="" class="icheck">
                                                            <ins class="iCheck-helper"></ins>
                                                        </div>
                                                    </td>
                                                    <td>
                                                        <img width="100" height="100" src="{{ route('home') }}/uploads/creative/{{ $picture->filename }}">
                                                    </td>
                                                    <td style="text-align:center; padding: 50px 0;">
                                                        <a href="javascript:void(0)" class="btn btn-xs btn-danger"
                                                        onclick="modal('{{ route($resource.'.deleteUpload', $picture->id) }}')">删除图片</a>
                                                    </td>
                                                </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>

                                {{-- Info tab --}}
                                <div class="tab-pane fade p-30" id="tab-info" style="margin:0 1em;">

                                    <div class="form-group">
                                        <label>作者</label>
                                        <p class="form-control-static">{{ $data->user ? $data->user->email : '作者信息丢失' }}</p>
                                    </div>

                                    <div class="form-group">
                                        <label>创建时间</label>
                                        <p class="form-control-static">{{ $data->created_at }}（{{ $data->friendly_created_at }}）</p>
                                    </div>

                                    <div class="form-group">
                                        <label>最后修改时间</label>
                                        <p class="form-control-static">{{ $data->updated_at }}（{{ $data->friendly_updated_at }}）</p>
                                    </div>

                                </div>

                            </div>

                            {{-- Form actions --}}
                            <div class="control-group p-l-30 p-b-30">
                                <div class="controls">
                                    <button type="reset" class="btn btn-bordered text-gray-alt">清 空</button>
                                    <button type="submit" class="btn btn-success">更 新</button>
                                </div>
                            </div>
                        </form>

                    </div>

                    <div class="p-lr-30 p-tb-10 pm-lr-10">上传创意图片，这些图片会显示在创意展示页面，推荐尺寸：585px × 390px</div>
                    <div class="p-lr-30 p-tb-10 pm-lr-10">
                        <form action="{{ route($resource.'.postUpload', $data->id) }}" class="dropzone" id="upload" method="post">
                            <input type="hidden" name="_token" value="{{ csrf_token() }}" />
                        </form>
                    </div>

                </div>
            </div>
            {{-- /.col-lg-9 --}}

            <div class="col-sm-3">

                <div class="bg-white p-t-30 m-b-10 b-bot-2px-gray-light">

                    <div class="iconmelon m-r-10 m-l-30">
                        <svg viewBox="0 0 32 32">
                            <g filter="">
                                <use xlink:href="#speech-talk-user"></use>
                            </g>
                        </svg>
                    </div>

                    <span class="text-gray-dark text-large align-with-button">
							收件箱
						</span>

                    <hr class="m-b-0">

                    <div class="p-lr-30">
                        <button class="btn btn-primary btn-lg btn-block compose-btn">发消息</button>
                    </div>

                    <hr class="m-t-0 m-b-0">

                    <ul class="nav nav-pills nav-stacked">
                        <li class="active"><a href="messages.html#">收件箱</a>
                        </li>
                        <li><a href="messages.html#">已发送</a>
                        </li>
                        <li><a href="messages.html#">草稿箱</a>
                        </li>
                        <li><a href="messages.html#">收藏夹</a>
                        </li>
                        <li><a href="messages.html#">回收站</a>
                        </li>
                        <li><a href="messages.html#">垃圾信息</a>
                        </li>
                    </ul>
                </div>

                <div id="chat" class="bg-white p-t-30 p-b-10 chat-wrapper b-bot-2px-gray-light">

                    <div class="btn-group chat-toggle">
                        <a href="messages.html#" class="p-lr-30 hover-no-underline" data-toggle="dropdown">
                            <img class="img-circle chat-avatar available m-r-10" width="35" src="{{ Auth::user()->portrait_large }}">
                            <span class="hover-no-underline hover-gray-dark text-gray">
									<span class="text-gray-dark text-large align-with-button">
										{{ Auth::user()->nickname }}
									</span>
                            <span class="caret"></span>
                            </span>
                        </a>
                        <ul class="dropdown-menu" role="menu">
                            <li><a href="messages.html#"><span class="circle-green m-r-10"></span>在线</a>
                            </li>
                            <li><a href="messages.html#"><span class="circle-yellow m-r-10"></span>忙碌</a>
                            </li>
                            <li><a href="messages.html#"><span class="circle-gray m-r-10"></span>隐身</a>
                            </li>
                            <li class="divider"></li>
                            <li><a href="messages.html#"><span class="circle-red m-r-10"></span>离线</a>
                            </li>
                        </ul>
                    </div>

                    <hr class="m-b-0">

                    <div class="p-lr-30">
                        <input type="text" class="input-light input-large brad chat-search" placeholder="查找好友...">
                    </div>

                    <hr class="m-t-0">

                    <ul class="unstyled people">
                        <li>
                            <a href="messages.html#" class="p-lr-30 p-tb-10 pm-lr-10 d-block">
                                {{ HTML::image('images/avatars/5.jpg', '', array('class' => 'label label-success m-l-10')); }}
                                <span class="author">Erik Dean</span>
                                <span class="label label-success m-l-10">3</span>
                            </a>
                        </li>

                        <li>
                            <a href="messages.html#" class="p-lr-30 p-tb-10 pm-lr-10 d-block">
                                {{ HTML::image('images/avatars/7.jpg', '', array('class' => 'img-circle chat-avatar available m-r-10')); }}
                                <span class="author">Doug Ross</span>
                            </a>
                        </li>

                        <li>
                            <a href="messages.html#" class="p-lr-30 p-tb-10 d-block">
                                {{ HTML::image('images/avatars/8.jpg', '', array('class' => 'img-circle chat-avatar busy m-r-10')); }}
                                <span class="author">Victor Benson</span>
                            </a>
                        </li>

                        <li>
                            <a href="messages.html#" class="p-lr-30 p-tb-10 d-block">
                                {{ HTML::image('images/avatars/9.jpg', '', array('class' => 'img-circle chat-avatar signedoff m-r-10')); }}
                                <span class="author">Henry Mccormick</span>
                            </a>
                        </li>
                    </ul>
                </div>
            </div>

        </div>
        {{-- /.row --}}
    </div>

    <?php
    $modalData['modal'] = array(
        'id'      => 'myModal',
        'title'   => '系统提示',
        'message' => '确认删除此图片？',
        'footer'  =>
            Form::open(array('id' => 'real-delete', 'method' => 'delete')).'
                <button type="button" class="btn btn-sm btn-default" data-dismiss="modal">取消</button>
                <button type="submit" class="btn btn-sm btn-danger">确认删除</button>'.
            Form::close(),
    );
    ?>
    @include('layout.modal', $modalData)
    <script>
        function modal(href)
        {
            $('#real-delete').attr('action', href);
            $('#myModal').modal();
        }
    </script>

</body>
</html>