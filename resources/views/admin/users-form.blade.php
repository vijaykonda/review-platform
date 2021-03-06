@extends('admin')

@section('content')

<h1 class="page-header"> {{ trans('admin.page.'.$current_page.'.title-edit') }} </h1>
<!-- end page-header -->


<div class="row">
    <div class="col-md-12">
        <div class="panel panel-inverse">
            <div class="panel-heading">
                <div class="panel-heading-btn">
                    <a href="javascript:;" class="btn btn-xs btn-icon btn-circle btn-default" data-click="panel-expand"><i class="fa fa-expand"></i></a>
                </div>
                <h4 class="panel-title"> {{ trans('admin.page.'.$current_page.'.title-edit') }} </h4>
            </div>
            <div class="panel-body">
                {!! Form::open(array('url' => url('cms/'.$current_page.'/edit/'.$item->id), 'method' => 'post', 'class' => 'form-horizontal')) !!}
                    {!! csrf_field() !!}

                        <div class="form-group">
                        @foreach( $fields as $key => $info)
                            <label class="col-md-2 control-label">{{ trans('admin.page.'.$current_page.'.form-'.$key) }}</label>
                            <div class="col-md-4">
                                @if( $info['type'] == 'text')
                                    {{ Form::text( $key, $item->$key, array('class' => 'form-control', (!empty($info['disabled']) ? 'disabled' : 'nothing') => 'disabled' )) }}
                                @elseif( $info['type'] == 'textarea')
                                    {{ Form::textarea( $key, $item->$key, array('class' => 'form-control', (!empty($info['disabled']) ? 'disabled' : 'nothing') => 'disabled' )) }}
                                @elseif( $info['type'] == 'bool')
                                    {{ Form::checkbox( $key, 1, $item->$key, array('class' => 'form-control', (!empty($info['disabled']) ? 'disabled' : 'nothing') => 'disabled' )) }}
                                @elseif( $info['type'] == 'datepicker')
                                    {{ Form::text( $key, !empty($item->$key) ? $item->$key->format('Y.m.d') : '' , array('class' => 'form-control datepicker' , (!empty($info['disabled']) ? 'disabled' : 'nothing') => 'disabled')) }}
                                @elseif( $info['type'] == 'datetimepicker')
                                    {{ Form::text( $key, !empty($item->$key) ? $item->$key->format('Y.m.d H:i:s') : '' , array('class' => 'form-control datetimepicker' , (!empty($info['disabled']) ? 'disabled' : 'nothing') => 'disabled')) }}
                                @elseif( $info['type'] == 'country')  
                                    {{ Form::select( $key , \App\Models\Country::get()->pluck('name', 'id')->toArray() , $item->$key , array('class' => 'form-control country-select') ) }}
                                @elseif( $info['type'] == 'city')  
                                    {{ Form::select( $key , $item->country_id ? \App\Models\City::where('country_id', $item->country_id)->get()->pluck('name', 'id')->toArray() : [] , $item->$key , array('class' => 'form-control city-select') ) }}
                                @elseif( $info['type'] == 'avatar')
                                    @if($item->hasimage)
                                        <a class="thumbnail" href="{{ $item->getImageUrl() }}" target="_blank">
                                            <img src="{{ $item->getImageUrl(true) }}">
                                        </a>
                                        <a class="btn btn-primary" href="{{ url('cms/'.$current_page.'/edit/'.$item->id.'/deleteavatar') }}" onclick="return confirm('{{ trans('admin.common.sure') }}')">
                                            <i class="fa fa-remove"></i> {{ trans('admin.page.'.$current_page.'.delete-avatar') }}
                                        </a>
                                    @else
                                        <div class="alert alert-info">
                                            {{ trans('admin.page.'.$current_page.'.no-avatar') }}
                                        </div>
                                    @endif
                                @elseif( $info['type'] == 'select')  
                                    {{ Form::select( $key , $info['values'] , $item->$key , array(
                                        'class' => 'form-control'.(!empty($info['multiple']) ? ' multiple' : '') , 
                                        (!empty($info['disabled']) ? 'disabled' : 'nothing') => 'disabled' , 
                                        (!empty($info['multiple']) ? 'multiple' : 'nothing') => 'multiple',
                                        'style' => ''.(!empty($info['multiple']) ? 'height: 200px;' : '')
                                    )) }}
                                @endif
                            </div>
                            @if($loop->index%2)
                        </div>
                        <div class="form-group">
                            @endif
                        @endforeach
                        </div>

                    <div class="form-group">
                        <div class="col-md-6">
                            <a href="{{ url('cms/users/loginas/'.$item->id) }}" target="_blank" class="btn btn-sm btn-primary form-control"> {{ trans('admin.page.profile.loginas') }} </a>
                        </div>
                        <div class="col-md-6">
                            <button type="submit" name="update" class="btn btn-block btn-sm btn-success form-control"> {{ trans('admin.common.save') }} </button>
                        </div>
                    </div>

                {!! Form::close() !!}
            </div>
        </div>
        <!-- end panel -->
    </div>
</div>

@if($item->photos->isNotEmpty())
    <div class="row">
        <div class="col-md-12">
            <div class="panel panel-inverse">
                <div class="panel-heading">
                    <div class="panel-heading-btn">
                        <a href="javascript:;" class="btn btn-xs btn-icon btn-circle btn-default" data-click="panel-expand"><i class="fa fa-expand"></i></a>
                    </div>
                    <h4 class="panel-title"> {{ trans('admin.page.'.$current_page.'.title-photos') }} </h4>
                </div>
                <div class="panel-body">
                    <div class="row">
                        @foreach($item->photos as $photo)
                            <div class="col-md-3">
                                <div class="thumbnail">
                                    <img src="{{ $photo->getImageUrl(true) }} ">
                                </div>
                                <a class="btn btn-primary" href="{{ url('cms/'.$current_page.'/edit/'.$item->id.'/deletephoto/'.$loop->index) }}" onclick="return confirm('{{ trans('admin.common.sure') }}')">
                                    <i class="fa fa-remove"></i> {{ trans('admin.page.'.$current_page.'.delete-photo') }}
                                </a>
                            </div>
                            @if($loop->index==3 && !$loop->last)
                                </div>
                                <div class="row">
                            @endif
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>

@endif

@if($item->is_dentist)
    <div class="row">
        <div class="col-md-12">
            <div class="panel panel-inverse">
                <div class="panel-heading">
                    <div class="panel-heading-btn">
                        <a href="javascript:;" class="btn btn-xs btn-icon btn-circle btn-default" data-click="panel-expand"><i class="fa fa-expand"></i></a>
                    </div>
                    <h4 class="panel-title"> {{ trans('admin.page.'.$current_page.'.title-reviews-in') }} </h4>
                </div>
                <div class="panel-body">
                    @include('admin.parts.table', [
                        'table_id' => 'users',
                        'table_fields' => [
                            'created_at'        => array('format' => 'datetime'),
                            'user'              => array('template' => 'admin.parts.table-reviews-user'),
                            'dentist'           => array('template' => 'admin.parts.table-reviews-dentist'),
                            'rating'            => array(),
                            'upvotes'            => array(),
                            'verified'              => array('format' => 'bool'),
                            'link'              => array('template' => 'admin.parts.table-reviews-link'),
                            'delete'            => array('format' => 'delete'),
                        ],
                        'table_data' => $item->reviews_in,
                        'table_pagination' => false,
                        'pagination_link' => array()
                    ])
                </div>
            </div>
        </div>
    </div>

@else

    <div class="row">
        <div class="col-md-12">
            <div class="panel panel-inverse">
                <div class="panel-heading">
                    <div class="panel-heading-btn">
                        <a href="javascript:;" class="btn btn-xs btn-icon btn-circle btn-default" data-click="panel-expand"><i class="fa fa-expand"></i></a>
                    </div>
                    <h4 class="panel-title"> {{ trans('admin.page.'.$current_page.'.title-reviews-out') }} </h4>
                </div>
                <div class="panel-body">
                    @include('admin.parts.table', [
                        'table_id' => 'users',
                        'table_fields' => [
                            'created_at'        => array('format' => 'datetime'),
                            'user'              => array('template' => 'admin.parts.table-reviews-user'),
                            'dentist'           => array('template' => 'admin.parts.table-reviews-dentist'),
                            'rating'            => array(),
                            'upvotes'            => array(),
                            'verified'              => array('format' => 'bool'),
                            'link'              => array('template' => 'admin.parts.table-reviews-link'),
                            'delete'            => array('format' => 'delete'),
                        ],
                        'table_data' => $item->reviews_out,
                        'table_pagination' => false,
                        'pagination_link' => array()
                    ])
                </div>
            </div>
        </div>
    </div>

@endif

@endsection