@extends('errors.layout')
@section('code', '419')
@section('title', 'الصفحة قعدت مفتوحة كتير')
@section('heading', 'الصفحة قعدت مفتوحة كتير')
@section('message', 'انتهت صلاحية الجلسة. ارجع للصفحة وحاول تاني.')
@section('actions')<button class="secondary" type="button" onclick="history.back()">رجوع وحاول تاني</button>@endsection
