@extends('layouts.default')
@section('bodyClass', $content->theme)
<?php $activeTranslation = $content->translation($lang->code); ?>
<?php $url = $content->routeUrl($lang->code); ?>

@section('metaData')
    @if(isProviderLoaded('Gzero\Social\ServiceProvider') && function_exists('fbOgTags'))
        {!! fbOgTags($url, $activeTranslation) !!}
    @endif
@stop

@section('title'){{ $activeTranslation->seoTitle() }}@stop
@section('seoDescription'){{ $activeTranslation->seoDescription() }}@stop
@section('head')
    @parent
    @include('includes.canonical')
    @include('includes.alternateLinks', ['content' => $content])
    @if(method_exists($content, 'stDataMarkup'))
        {!! $content->stDataMarkup($lang->code) !!}
    @endif
@stop
@section('breadcrumbs')
    <div class="utility-container">
        <div class="container text-center-xs">
            {!! Breadcrumbs::render('content') !!}
        </div>
    </div>
@stop
@section('content')
    @include('includes.notPublishedContentMsg')
    <h1 class="content-title page-header">
        {{ $activeTranslation->title }}
    </h1>
    <div class="row content-meta">
        <div class="col-sm-7">
            <p class="content-author text-muted">
                <i>@lang('common.posted_by') {{ $content->authorName() }}</i>
                <i>@lang('common.posted_on') {{ $content->publishDate() }}</i>
            </p>
        </div>
        <div class="col-sm-5 text-right text-left-sm text-left-xs">
            @if(isProviderLoaded('Gzero\Social\ServiceProvider') && function_exists('shareButtons'))
                <div class="social-buttons mb15">
                    {!! shareButtons($url, $activeTranslation) !!}
                </div>
            @endif
        </div>
    </div>
    @if($content->thumb)
        <?php $thumbTranslation = $content->thumb->translation($lang->code); ?>
        <div class="thumb mb20">
            <img class="img-responsive"
                 title="{{($thumbTranslation)? $thumbTranslation->title : ''}}"
                 src="{{croppaUrl($content->thumb->getFullPath(),
                  config('gzero.image.thumb.width'), config('gzero.image.thumb.height'), ['resize'])}}"
                 alt="{{($thumbTranslation)? $thumbTranslation->title : ''}}">
        </div>
    @endif
    {!! $activeTranslation->body !!}
    @include('includes.gallery', ['images' => $images, 'thumb' => $content->thumb])
    @if(config('disqus.enabled') && $content->isCommentAllowed)
        <div class="text-center">
            @include('includes.disqus.disqus', ['contentId' => $content->id, 'url' => $url])
        </div>
    @endif
    <hr>
    @if(isProviderLoaded('Gzero\Social\ServiceProvider') && function_exists('likeButtons'))
        <div class="social-buttons mb15">
            {!! likeButtons($url, $activeTranslation) !!}
        </div>
    @endif
    <div class="text-muted text-right">
        @lang('common.rating') {!! $content->ratingStars() !!}
    </div>
@stop
