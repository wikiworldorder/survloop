<!-- resources/views/vendor/survloop/forms/formtree-looproot-row.blade.php -->

@if ($node->isStepLoop())
    <a id="editLoopItem{{ $itemID }}" class="btn btn-secondary btn-lg btn-xl w100 taL mB20 editLoopItem" href="javascript:;">
    @if (trim($ico) != '')
        <span class=" @if (strpos($ico, 'gryC') !== false) slBlueFaint @else slBlueDark @endif "
            >{!! $ico !!}</span>
    @endif
    {!! $itemLabel !!}</a>
@else 
    <div class="wrapLoopItem"><a name="item{{ $setIndex }}"></a>
        <div id="wrapItem{{ $itemID }}On" class="slCard nodeWrap">
            <h3 class="mT0">{!! $itemLabel !!}</h3>
            @if ($canEdit)
                <div class="mT5">
                <a href="javascript:;" id="editLoopItem{{ $itemID }}" class="editLoopItem btn btn-secondary loopItemBtn"
                    ><i class="fa fa-pencil fa-flip-horizontal"></i> Edit</a>
                <a href="javascript:;" id="delLoopItem{{ $itemID }}" class="delLoopItem nFormLnkDel nobld 
                    btn btn-secondary loopItemBtn"><i class="fa fa-trash-o"></i> Delete</a>
                <input type="checkbox" class="disNon" name="delItem[]" id="delItem{{ $itemID }}" value="{{ $itemID }}">
                </div>
            @endif
        </div>
        @if (!$node->isStepLoop())
            <div id="wrapItem{{ $itemID }}Off" class="wrapItemOff brdGrey round20 mB20">
                <i class="mR20 fL">Deleted: {!! $itemLabel !!}</i> 
                <a href="javascript:;" id="unDelLoopItem{{ $itemID }}" class="unDelLoopItem nFormLnkEdit mL20 fR"
                    ><i class="fa fa-undo"></i> Undo</a>
                <div class="fC"></div>
            </div>
        @endif
    </div>
@endif