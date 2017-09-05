<!-- resources/views/vendor/survloop/admin/db/inc-condition-edit.blade.php -->

@extends('vendor.survloop.master')

@section('content')

<h2><i class="fa fa-snowflake-o"></i> Conditions / Filters</h2>

<ul id="pageTabs" class="nav nav-tabs">
    <li><a href="/dashboard/db/conds">All Conditions</a></li>
    <li><a href="/dashboard/db/conds?only=public">Public Only</a></li>
    <li><a href="/dashboard/db/conds?only=articles">Articles Only</a></li>
    <li class="active"><a href="javascript:;">Edit Condition</a></li>
</ul>

<div id="addCond" style="overflow: hidden;">
    <div class="round10 brd p20 mB20 mTn20" style="padding-top: 40px;">
        <form name="nodeEditor" method="post" action="/dashboard/db/conds/edit/{{ $cond->CondID }}" >
        <input type="hidden" name="_token" value="{{ csrf_token() }}">
        <input type="hidden" name="editCond" value="1">
        {!! view('vendor.survloop.admin.db.inc-addCondition', [
            "newOnly" => false, "cond" => $cond, "condArticles" => $condArticles
        ])->render() !!}
        </form>
    </div>
</div>

<div class="adminFootBuff"></div>
@endsection