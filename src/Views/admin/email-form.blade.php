<!-- resources/views/vendor/survloop/admin/email-form.blade.php -->
@extends('vendor.survloop.master')
@section('content')

<div class="container"><div class="slCard nodeWrap">
@if ($currEmailID > 0) 
    <h2 class="mB0">Editing Email Template: {{ $currEmail->email_name }}</h2> 
@else
    <h2>Create New Email Template</h2>
@endif
<div class="p5"></div>

<form name="mainPageForm" action="/dashboard/email/{{ $currEmailID }}" method="post">
<input type="hidden" id="csrfTok" name="_token" value="{{ csrf_token() }}">
<input type="hidden" name="emailID" value="{{ $currEmailID }}" >

<div class="row mB20">
    <div class="col-3">
        <h4 class="m0 slGrey">Auto-Email Type</h4>
    </div>
    <div class="col-9">
        <select name="emailType" class="form-control form-control-lg" 
            onChange="if (this.value == 'Blurb') { document.getElementById('subj').style.display='none'; } else { document.getElementById('subj').style.display='block'; }" >
            <option value="To Complainant" 
                @if ($currEmail->email_type == 'To Complainant' || trim($currEmail->email_type) == '') SELECTED @endif
                >Sent To Complainant</option>
            <option value="To Oversight" @if ($currEmail->email_type == 'To Oversight') SELECTED @endif 
                >Sent To Oversight Agency</option>
            <option value="Blurb" @if ($currEmail->email_type == 'Blurb') SELECTED @endif 
                >Excerpt used within other emails</option>
        </select>
    </div>
</div>

<div class="row mB20">
    <div class="col-3">
        <h4 class="m0 slGrey">Internal Name</h4>
    </div>
    <div class="col-9">
        <input type="text" name="emailName" value="{{ $currEmail->email_name }}" 
            class="form-control form-control-lg" >
    </div>     
</div>

<div id="subj" class="row pB20 @if ($currEmail->email_type == 'Blurb') disNon @else disFlx @endif ">
    <div class="col-3">
        <h4 class="m0 slGrey">Email Subject Line</h4>
    </div>
    <div class="col-9">
        <input type="text" name="emailSubject" value="{{ $currEmail->email_subject }}" 
            class="form-control form-control-lg" >
    </div>
</div>

<div class="row mB20">
    <div class="col-3">
        <h4 class="m0 slGrey">Email Body</h4>
        <div class="p20"></div>
        <input type="submit" class="btn btn-lg btn-xl btn-primary btn-block" value="Save Email Template">
    </div>
    <div class="col-9">
        <textarea name="emailBody" id="emailBodyID" class="form-control form-control-lg" style="height: 500px;"
            >{{ $currEmail->email_body }}</textarea>
    </div>
</div>
</form>
<!--- {{ $currEmail->email_opts }} --->
</div></div>
<div class="adminFootBuff"></div>
@endsection