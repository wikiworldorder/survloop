<!-- resources/views/vendor/survloop/admin/tree/node-edit.blade.php -->
<div class="container">
@if ($canEditTree)
    <form name="mainPageForm" method="post" @if (isset($node->nodeRow) && isset($node->nodeRow->NodeID))
        action="/dashboard/surv-{{ $treeID }}/map/node/{{ $node->nodeRow->NodeID }}"
        @else action="/dashboard/surv-{{ $treeID }}/map/node/-3" @endif >
    <input type="hidden" id="csrfTok" name="_token" value="{{ csrf_token() }}">
    <input type="hidden" name="sub" value="1">
    <input type="hidden" name="treeID" value="{{ $treeID }}">
    <input type="hidden" name="nodeParentID" 
        @if ($GLOBALS['SL']->REQ->has('parent') && intVal($GLOBALS['SL']->REQ->input('parent')) > 0) 
            value="{{ $GLOBALS['SL']->REQ->input('parent') }}" @else value="{{ $node->parentID }}" @endif >
    <input type="hidden" name="childPlace" 
        @if ($GLOBALS['SL']->REQ->has('start') && intVal($GLOBALS['SL']->REQ->input('start')) > 0) 
            value="start"
        @else 
            @if ($GLOBALS['SL']->REQ->has('end') && intVal($GLOBALS['SL']->REQ->input('end')) > 0) value="end" 
            @else value="" @endif
        @endif >
    <input type="hidden" name="orderBefore" 
        @if ($GLOBALS['SL']->REQ->has('ordBefore') && intVal($GLOBALS['SL']->REQ->ordBefore) > 0) 
            value="{{ $GLOBALS['SL']->REQ->ordBefore }}" @else value="-3" @endif >
    <input type="hidden" name="orderAfter" 
        @if ($GLOBALS['SL']->REQ->has('ordAfter') && intVal($GLOBALS['SL']->REQ->ordAfter) > 0) 
            value="{{ $GLOBALS['SL']->REQ->ordAfter }}" @else value="-3" @endif >
@endif

<div class="row">
    <div class="col-md-4 mB10">


        <div class="slCard nodeWrap">
            @if (isset($node->nodeRow->NodeID) && $node->nodeRow->NodeID > 0) 
                <h3 class="m0 slBlueDark">Editing <nobr>Node #{{ $node->nodeRow->NodeID }}</nobr></h3>
            @else <h3 class="m0 slBlueDark">Adding Node</h3> @endif
            <a class="fPerc66 mTn10" @if ($GLOBALS['SL']->treeRow->TreeType == 'Page')
                    href="/dashboard/page/{{ $treeID }}?all=1&alt=1&refresh=1#n{{ $node->nodeRow->NodeID }}" 
                @elseif (isset($node->nodeRow) && isset($node->nodeRow->NodeID))
                    href="/dashboard/surv-{{ $treeID }}/map?all=1&alt=1&refresh=1#n{{ $node->nodeRow->NodeID }}" 
                @else
                    href="/dashboard/surv-{{ $treeID }}/map?all=1&alt=1&refresh=1" 
                @endif >Back to Form-Tree Map</a>
        
            {!! view('vendor.survloop.admin.tree.node-edit-type', [
                "node"        => $node,
                "nodeTypes"   => $nodeTypes,
                "parentNode"  => $parentNode,
                "nodeTypeSel" => $nodeTypeSel
                ])->render() !!}
                    
            <div class="mT20 slGreenDark">
                <label>
                    <h4 class="m0 slGreenDark"><i class="fa fa-database mR5"></i> Data Family
                    @if ($node->nodeID == $GLOBALS['SL']->treeRow->TreeRoot) : Core Table @endif </h4>
                    <span class="fPerc66">Node's whole family tree can store data fields related to table.</span>
                    <div class="nFld mT0"><select name="nodeDataBranch" id="nodeDataBranchID" autocomplete="off" 
                        class="form-control slGreenDark">
                        {!! $dataBranchDrop !!}
                    </select></div>
                </label>
            </div>
            <div id="saveBtnGapTop" class="p10"></div>
            <input type="submit" value="Save Changes" class="btn btn-lg btn-primary btn-block" 
                @if (!$canEditTree) DISABLED @endif >
        </div>
    
        <div id="pagePreview" class=" @if ($node->nodeType == 'Page') disBlo @else disNon @endif ">
            <div class="slCard nodeWrap">
                <h4 class="mT0">Social Sharing Preview</h4>
                {!! view('vendor.survloop.admin.seo-meta-editor-preview', [])->render() !!}
            </div>
        </div>
    
        <div class="slCard nodeWrap">
            <label for="nodeConditionsID"><h4 class="mT0">Conditions To Include Node</h4></label>
            @if (sizeof($node->conds) > 0)
                @foreach ($node->conds as $i => $cond)
                    <input type="hidden" id="delCond{{ $i }}ID" name="delCond{{ $cond->CondID }}" value="N">
                    <div id="cond{{ $i }}wrap" class="round10 brd p5 mB10 pL10">
                        <a id="cond{{ $i }}delBtn" href="javascript:;" class="float-right disBlo condDelBtn"
                            ><i class="fa fa-trash-o" aria-hidden="true"></i></a> 
                        <div id="cond{{ $i }}delWrap" href="javascript:;" class="float-right disNon fPerc80 pT5 pL10">
                            <i class="red">Deleted</i> 
                            <a id="cond{{ $i }}delUndo" href="javascript:;" class="condDelBtnUndo fPerc80 mL20">Undo</a> 
                        </div>
                        @if (trim($cond->CondOperator) == 'AB TEST')
                            %AB: {{ $cond->CondDesc }}
                        @else
                            {{ $cond->CondTag }}
                            <span class="fPerc80 mL10">{!! view('vendor.survloop.admin.db.inc-describeCondition', [
                                "nID"  => $node->nodeID,
                                "cond" => $cond,
                                "i"    => $i
                                ])->render() !!}</span>
                        @endif
                    </div>
                @endforeach
            @endif
            {!! view('vendor.survloop.admin.db.inc-addCondition')->render() !!}
            {!! view('vendor.survloop.admin.tree.inc-add-ab-test')->render() !!}
        </div>
        
        {!! view('vendor.survloop.admin.tree.node-edit-response-layout', [ "node" => $node ])->render() !!}
        
        <div class="slCard nodeWrap">
            <label class="w100 pB20">
                <a id="internalNotesBtn" href="javascript:;" class="f12">+ Internal Notes</a> 
                <div id="internalNotes" class=" @if (isset($node->nodeRow->NodeInternalNotes) 
                    && trim($node->nodeRow->NodeInternalNotes) != '') disBlo @else disNon @endif ">
                    <div class="nFld mT0"><textarea name="nodeInternalNotes" autocomplete="off" 
                        class="form-control slGrey" style="height: 100px;" 
                        >@if (isset($node->nodeRow->NodeInternalNotes)){!! 
                            $node->nodeRow->NodeInternalNotes !!}@endif</textarea></div>
                </div>
            </label>
        
            @if ($canEditTree)
                @if (isset($node->nodeRow->NodeID) && $node->nodeRow->NodeID > 0)
                    <div class="mT10 mB10">
                        <input type="checkbox" name="deleteNode" id="deleteNodeID" value="1" class="mR3" > 
                        <label for="deleteNodeID">Delete This Node</label>
                    </div>
                @endif
                </form>
            @else
                <div class="p20 m20"><center><i>
                    Sorry, you do not have permissions to actually edit the tree.
                </i></center></div>
                <div class="p20 m20"></div>
            @endif
        </div>
        
        <div id="emailPreviewStuff" class=" @if ($node->nodeType == 'Send Email') disBlo @else disNon @endif " >
            <div class="slCard nodeWrap">
                <h4 class="slBlueDark m0 mB5"><i>Template Preview:</i></h4>
                <div id="previewEmailDump1" class="
                    @if (intVal($node->nodeRow->NodeDefault) == -69) disBlo @else disNon @endif ">
                    <div class="w100 brdDsh m5 p5">
                        Field Name:<br />User Response<br /><br />
                        Field Name:<br />User Response<br /><br />
                        Field Name:<br />User Response<br /><br />
                    </div>
                </div>
                @forelse ($emailList as $i => $email)
                    <div id="previewEmail{{ $email->EmailID }}" class="
                        @if ($email->EmailID == $node->nodeRow->NodeDefault) disBlo @else disNon @endif ">
                        <div class="w100 brdDsh m5 p5">{!! $email->EmailBody !!}</div>
                    </div>
                @empty
                @endforelse
                <a href="/dashboard/emails">Manage System Email Templates</a>
            </div>
        </div>
    
    
    </div>
    <div class="col-md-8 mB10">
        
        {!! view('vendor.survloop.admin.tree.node-edit-layout', [ "node" => $node ])->render() !!}
    
        <div id="hasInstruct" class="mTn20 
            @if ($node->isInstruct() || $node->isInstructRaw()) disBlo @else disNon @endif ">
            <div class="slCard nodeWrap">
                <div class="nFld w100">
                    @if ($node->isInstruct()) 
                        <textarea name="nodeInstruct" id="nodeInstructID" class="form-control w100" autocomplete="off"
                            style="height: 350px;">@if (isset($node->nodeRow->NodePromptText)){!! 
                                $node->nodeRow->NodePromptText !!}@endif</textarea>
                    @else
                        <textarea name="nodeInstruct" id="nodeInstructID" class="form-control w100" autocomplete="off"
                            style="height: 350px; font-family: Courier New;"
                            >@if (isset($node->nodeRow->NodePromptText)){!! 
                                $node->nodeRow->NodePromptText !!}@endif</textarea>
                    @endif
                </div>
                <label class="w100 pT10 pB10">
                    <a id="extraHTMLbtn2" href="javascript:;" class="f12 fL">+ HTML/JS/CSS Extras</a> 
                    <div id="extraHTML2" class="w100 fC @if (isset($node->nodeRow->NodePromptAfter) 
                        && trim($node->nodeRow->NodePromptAfter) != '') disBlo @else disNon @endif ">
                        <div class="nFld mT0"><textarea name="instrPromptAfter" class="form-control" 
                            style="width: 100%; height: 100px;" autocomplete="off"
                            >@if (isset($node->nodeRow->NodePromptAfter)
                                ){!! $node->nodeRow->NodePromptAfter !!}@endif</textarea></div>
                        <span class="slGrey f12">"[[nID]]" will be replaced with node ID</span>
                    </div>
                </label>
            </div>
        </div>
        
        <div id="hasBranch" class=" @if ($node->isBranch()) disBlo @else disNon @endif ">
            <div class="slCard nodeWrap">
                <h3 class="m0">Branch Title</h3>
                <label for="branchTitleID" class="w100 mT0">
                    <div class="nFld mT0"><input type="text" name="branchTitle" id="branchTitleID" 
                        class="form-control" autocomplete="off" value="@if (isset($node->nodeRow->NodePromptText)
                            ){!! strip_tags($node->nodeRow->NodePromptText) !!}@endif" ></div>
                </label>
                <small class="slGrey">For internal use only.
                Branches are a great way to mark navigation areas, mark key conditions which greatly impact
                user experience, associate data families, and/or just internally organize the the tree. 
                </small>
            </div>
        </div>
        
        {!! view('vendor.survloop.admin.tree.node-edit-loops', [ "node" => $node ])->render() !!}
        
        {!! view('vendor.survloop.admin.tree.node-edit-page', [
            "node"     => $node,
            "currMeta" => $currMeta
            ])->render() !!}
        
        {!! view('vendor.survloop.admin.tree.node-edit-data-manip', [
            "node"     => $node,
            "treeList" => $treeList,
            "resLimit" => $resLimit
            ])->render() !!}
        
        {!! view('vendor.survloop.admin.tree.node-edit-widgets', [ "node" => $node ])->render() !!}
        
        <div id="hasSendEmail" class=" @if ($node->nodeType == 'Send Email') disBlo @else disNon @endif ">
            <div class="slCard nodeWrap">
                <h4 class="mT0">Email Sending Options</h4>
                {!! $widgetEmail !!}
            </div>
        </div>
        
        <div id="hasBigButt" class=" @if ($node->isBigButt()) disBlo @else disNon @endif ">
            <div class="slCard nodeWrap">
                <h4 class="mT0">Big Button Settings</h4>
                <h4>Button Text</h4>
                <div class="nFld m0 mB20">
                    <input type="text" name="bigBtnText" id="bigBtnTextID" class="form-control" 
                        @if (isset($node->nodeRow->NodeDefault)) value="{{ $node->nodeRow->NodeDefault }}" 
                        @endif onKeyUp="return previewBigBtn();" >
                </div>
                <h4 class="mT0">Button On Click JavaScript</h4>
                <div class="nFld m0">
                    <input type="text" name="bigBtnJS" class="form-control" 
                        @if (isset($node->nodeRow->NodeDataStore)) value="{{ $node->nodeRow->NodeDataStore }}" 
                        @endif >
                </div>
                <div class="row mT20 mB20">
                    <div class="col-md-6">
                        <h4 class="mT0">Button Style</h4>
                        <div class="nFld m0">
                            <select name="bigBtnStyle" id="bigBtnStyleID" class="form-control"
                                onChange="return previewBigBtn();">
                                <option value="Default" @if (!isset($node->nodeRow->NodeResponseSet) 
                                    || $node->nodeRow->NodeResponseSet == 'Default') SELECTED @endif 
                                    >Default Button</option>
                                <option value="Primary" @if ($node->nodeRow->NodeResponseSet == 'Primary') 
                                    SELECTED @endif >Primary Button</option>
                                <option value="Text" @if ($node->nodeRow->NodeResponseSet == 'Text') 
                                    SELECTED @endif >Text/HTML Link</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-6 pT20 taR">
                        <label class="mT20"><input type="checkbox" name="opts43" value="43" 
                            @if ($node->nodeRow->NodeOpts%43 == 0) CHECKED @endif > 
                            <h4 class="disIn">Toggle Child Nodes On Click</h4>
                        </label>
                    </div>
                </div>
                Preview:
                <div id="buttonPreview" class="w100 m0"></div>
                <div class="p20">
                    <i>Optionally, you can fill in the "Question or Prompt for User" section below, which can provide
                    information or instructions to the user before the Big Button is printed.</i>
                </div>
            </div>
        </div>
        
        {!! view('vendor.survloop.admin.tree.node-edit-questions', [
            "node"       => $node,
            "defs"       => $defs,
            "resLimit"   => $resLimit,
            "childNodes" => $childNodes
            ])->render() !!}
        
        {!! view('vendor.survloop.admin.tree.node-edit-block', [ "node" => $node ])->render() !!}
        
    </div> <!-- end of right column -->
</div>

</div>