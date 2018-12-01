<!-- resources/views/vendor/survloop/admin/system-all-settings.blade.php -->

@extends('vendor.survloop.master')

@section('content')

<div class="disNon"><iframe src="/dashboard/css-reload" ></iframe></div>

<form name="mainPageForm" action="/dashboard/settings" method="post">
<input type="hidden" id="csrfTok" name="_token" value="{{ csrf_token() }}">
<input type="hidden" name="sub" value="1">

<div class="nodeAnchor"><a id="search" name="search"></a></div>

@if (!$GLOBALS["SL"]->isHomestead())
    <div class="fR taR slGrey fPerc80"> {!! str_replace('Current IP Address: ', 'Server IP Address:<br />',
        file_get_contents('http://checkip.dyndns.com/')) !!}
    </div>
@endif

<h1 class="slBlueDark"><i class="fa fa-cogs"></i> System Settings</h1>
<a href="#search" class="hshoo">Search Engine Optimization</a> - 
<a href="#general" class="hshoo">SurvLoop Configuration</a> - 
<a href="#survopts" class="hshoo">SurvLoop Settings</a> - 
<a href="#social" class="hshoo">Social Media</a> - 
<a href="#license" class="hshoo">Licenses</a> - 
<a href="#logos" class="hshoo">Logos & Fonts</a> - 
<a href="#color" class="hshoo">Colors</a> - 
<a href="#hardcode" class="hshoo">Hard Code HTML CSS JS</a> - 
<a href="#custom" class="hshoo">Custom Settings</a>
<div class="fC"></div>
<p>&nbsp;</p>

<h2 class="mB10">Search Engine Optimization</h2>
<div class="row">
    <div class="col-8">
        {!! view('vendor.survloop.admin.seo-meta-editor', [ "currMeta" => $currMeta ])->render() !!}
    </div><div class="col-4">
        <h3 class="slBlueDark" style="margin-top: -40px;">Social Sharing Preview</h3>
        {!! view('vendor.survloop.admin.seo-meta-editor-preview', [])->render() !!}
    </div>
</div>

<div class="p20"></div>
<div class="nodeAnchor"><a id="general" name="general"></a></div>

<hr>
<h2>General Settings</h2>
<h3 class="slBlueDark"><u>SurvLoop Configurations</u></h3>
<div class="row">
    <div class="col-md-6">
        @foreach (['site-name', 'cust-abbr', 'cust-package', 'parent-company'] as $opt)
            {!! view('vendor.survloop.admin.system-one-setting', [
                "opt" => $opt, "val" => $sysDef->v["settingsList"][$opt] ])->render() !!}
        @endforeach
    </div><div class="col-md-6">
        @foreach (['app-url', 'logo-url', 'parent-website'] as $opt)
            {!! view('vendor.survloop.admin.system-one-setting', [
                "opt" => $opt, "val" => $sysDef->v["settingsList"][$opt] ])->render() !!}
        @endforeach
    </div>
</div>
<div class="nodeAnchor"><a id="survopts" name="survopts"></a></div>
<div class="p20"></div>
<h3 class="slBlueDark"><u>SurvLoop Settings</u></h3>
<div class="row">
    <div class="col-md-6">
        @foreach (['has-volunteers', 'has-partners'] as $opt)
            {!! view('vendor.survloop.admin.system-one-setting', [
                "opt" => $opt, "val" => $sysDef->v["settingsList"][$opt] ])->render() !!}
        @endforeach
    </div><div class="col-md-6">
        @foreach (['has-canada', 'users-create-db'] as $opt)
            {!! view('vendor.survloop.admin.system-one-setting', [
                "opt" => $opt, "val" => $sysDef->v["settingsList"][$opt] ])->render() !!}
        @endforeach
    </div>
</div>
<div class="nodeAnchor"><a id="social" name="social"></a></div>
<div class="p20"></div>
<h3 class="slBlueDark"><u>Social Settings</u></h3>
<div class="row">
    <div class="col-md-6">
        {!! view('vendor.survloop.admin.system-one-setting', [
            "opt" => 'twitter', "val" => $sysDef->v["settingsList"]["twitter"] ])->render() !!}
    </div>
    <div class="col-md-6">
        @foreach (['google-analytic', 'google-map-key', 'google-map-key2', 'google-cod-key', 'google-cod-key2'] as $opt)
            {!! view('vendor.survloop.admin.system-one-setting', [
                "opt" => $opt, "val" => $sysDef->v["settingsList"][$opt] ])->render() !!}
        @endforeach
    </div>
</div>
<div class="nodeAnchor"><a id="license" name="license"></a></div>
<div class="p20"></div>
<h3 class="slBlueDark"><u>License Settings</u></h3>
<div class="row">
    <div class="col-md-6">
        @foreach (['app-license', 'app-license-url'] as $opt)
            {!! view('vendor.survloop.admin.system-one-setting', [
                "opt" => $opt, "val" => $sysDef->v["settingsList"][$opt] ])->render() !!}
        @endforeach
    </div><div class="col-md-6">
        {!! view('vendor.survloop.admin.system-one-setting', [
            "opt" => 'app-license-img', "val" => $sysDef->v["settingsList"]["app-license-img"] ])->render() !!}
    </div>
</div>

<div class="p20"></div>
<div class="p20"></div>
<hr>
<div class="nodeAnchor"><a id="logos" name="logos"></a></div>
<h2>Logos & Fonts</h2>
<h3 class="slBlueDark"><u>Logos</u></h3>
<div class="row">
    <div class="col-md-6">
        @foreach (['logo-img-lrg', 'logo-img-md', 'logo-img-sm', 'show-logo-title', 'shortcut-icon'] as $opt)
            {!! view('vendor.survloop.admin.system-one-setting', [
                "opt" => $opt, "val" => $sysDef->v["settingsList"][$opt] ])->render() !!}
        @endforeach
    </div>
    <div class="col-md-6">
        <h3 class="slBlueDark"><u>Fonts</u></h3>
        {!! view('vendor.survloop.admin.system-one-style', [ "sysStyles" => $sysDef->v["sysStyles"],
            "opt" => 'font-main', "val" => $sysDef->v["stylesList"]["font-main"] ])->render() !!}
    </div>
</div>
<div class="row">
    <div class="col-md-6">
        {!! view('vendor.survloop.admin.system-one-setting', [
            "opt" => 'spinner-code', "val" => ((isset($sysDef->v["settingsList"]["spinner-code"]))
            ? $sysDef->v["settingsList"]["spinner-code"] : '') ])->render() !!}
    </div><div class="col-md-6">
        @if (isset($GLOBALS["SL"]->sysOpts["spinner-code"])) {!! $GLOBALS["SL"]->sysOpts["spinner-code"] !!} @endif
    </div>
</div>

<div class="p20"></div>
<div class="p20"></div>
<hr>
<div class="nodeAnchor"><a id="color" name="color"></a></div>
<div class="row">
    <div class="col-8">
    
        <div class="fR pT20 slGrey"><i>BG = Background</i></div>
        <h2><u>Colors</u></h2>
        <div class="row fC">
            <div class="col-md-6">
            @foreach (['color-main-bg', 'color-main-text', 'color-main-link', 'color-main-grey', 
                'color-main-faint', 'color-main-faintr', 'color-field-bg', 'color-form-text', 'color-line-hr', 
                'color-logo', 'color-nav-bg', 'color-nav-text'] as $opt)
                @if (isset($sysDef->v["stylesList"][$opt]))
                    {!! view('vendor.survloop.admin.system-one-style', [ "sysStyles" => $sysDef->v["sysStyles"],
                        "opt" => $opt, "val" => $sysDef->v["stylesList"][$opt] ])->render() !!}
                @endif
            @endforeach
            </div>
            <div class="col-md-6">
            @foreach (['color-main-on', 'color-main-off', 'color-info-on', 'color-info-off', 'color-success-on', 
                'color-success-off', 'color-danger-on', 'color-danger-off', 'color-warn-on', 'color-warn-off'] as $opt)
                @if (isset($sysDef->v["stylesList"][$opt]))
                    {!! view('vendor.survloop.admin.system-one-style', [ "sysStyles" => $sysDef->v["sysStyles"],
                        "opt" => $opt, "val" => $sysDef->v["stylesList"][$opt] ])->render() !!}
                @endif
            @endforeach
            </div>
        </div>

    </div>
    <div class="col-4">
        <div id="previewColors"></div>
    </div>
</div>
    

<div class="nodeAnchor"><a id="hardcode" name="hardcode"></a></div>
<div class="p20"></div>
<div class="p20"></div>
<hr>
<h2>Hard Code HTML, CSS, JS</h2>
{!! view('vendor.survloop.admin.system-one-setting', [
    "opt" => 'header-code', "val" => $sysDef->v["settingsList"]["header-code"] ])->render() !!}
{!! view('vendor.survloop.admin.system-one-setting', [
    "opt" => 'css-extra-files', "val" => $sysDef->v["settingsList"]["css-extra-files"] ])->render() !!}
    
<div class="mB20"><label class="w100">
    <h4 class="m0">Open-Ended Custom CSS:</h4>
    <textarea name="sys-cust-css" class="form-control" autocomplete="off"
        style="height: 400px; font-family: Courier New;">{!! $sysDef->v["custCSS"]->DefDescription !!}</textarea>
</label></div>
<div class="mB20"><label class="w100">
    <h4 class="m0">Custom CSS for Emails:</h4>
    <textarea name="sys-cust-css-email" class="form-control" autocomplete="off" 
        style="height: 200px; font-family: Courier New;">{!! $sysDef->v["custCSSemail"]->DefDescription !!}</textarea>
</label></div>

@if (sizeof($sysDef->v["rawSettings"]) > 0)
    <div class="nodeAnchor"><a id="custom" name="custom"></a></div>
    <div class="p20"></div>
    <div class="p20"></div>
    <hr>
    <h2>Custom Settings</h2>
    <div class="row">
        <div class="col-md-6">
            @foreach ($sysDef->v["rawSettings"] as $i => $s)
                <div class="f22">{{ $s->setting }}</div>
                <label class="mL20">
                    <input type="radio" name="setting{{ $i }}" value="Y"
                        @if ($s->val == 'Y') CHECKED @endif
                        > Yes
                </label>
                <label class="mL20">
                    <input type="radio" name="setting{{ $i }}" value="N"
                        @if ($s->val == 'N') CHECKED @endif
                        > No
                </label>
                @if ($i == ceil(sizeof($sysDef->v["rawSettings"])/2))
                    </div><div class="col-md-6">
                @endif
            @endforeach
        </div>
    </div>
@endif

<div class="p20"></div><div class="p20"></div>

<input type="submit" class="btn btn-xl btn-primary w100" value="Save All Settings Changes">

</form>
<div class="p20"></div><div class="p20"></div>

@endsection