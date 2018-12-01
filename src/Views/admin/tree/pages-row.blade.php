<!-- Stored in resources/views/vender/survloop/admin/tree/pages-row.blade.php -->
<tr><td>
    @if ($tree->TreeOpts%7 > 0)
        <div class="relDiv pL20 mL10"><div class="absDiv" style="left: -5px;">
            <i class="fa fa-share fa-flip-vertical opac20" aria-hidden="true"></i></div>
    @endif
    <div class="fPerc133">{{ str_replace('[[coreID]]', 1111, $tree->TreeName) }}</div>
    <a class="float-right" href="/dashboard/page/{{ $tree->TreeID }}?all=1&alt=1"
        ><i class="fa fa-pencil mL10" aria-hidden="true"></i></a>
    @if ($tree->TreeOpts%3 == 0) <i class="fa fa-eye float-right mT5 mR5" aria-hidden="true"></i>
    @elseif ($tree->TreeOpts%43 == 0) <i class="fa fa-key float-right mT5 mR5" aria-hidden="true"></i>
    @elseif ($tree->TreeOpts%41 == 0) <i class="fa fa-university float-right mT5 mR5" aria-hidden="true"></i>
    @elseif ($tree->TreeOpts%17 == 0) <i class="fa fa-hand-rock-o float-right mT5 mR5" aria-hidden="true"></i>
    @endif
    @if ($tree->TreeOpts%7 == 0) <i class="fa fa-home float-right mT5 mR5"></i> @endif
    @if ($tree->TreeOpts%13 == 0) <i class="fa fa-list-alt float-right mT5 mR5"></i> @endif
    @if ($tree->TreeOpts%31 == 0) <i class="fa fa-search float-right mT5 mR5"></i> @endif
    <a href="{{ $GLOBALS['SL']->x['pageUrls'][$tree->TreeID] }}" target="_blank" class="mL5"
        >{{ $GLOBALS["SL"]->x["pageUrls"][$tree->TreeID] }}</a>
    @if (isset($GLOBALS["SL"]->x["myRedirs"][$tree->TreeSlug]))
        {!! $GLOBALS["SL"]->x["myRedirs"][$tree->TreeSlug] !!}
    @endif
    @if ($tree->TreeDesc) <span class="mL20 slGrey">{{ $tree->TreeDesc }}</span> @endif
    @if ($tree->TreeOpts%7 > 0) </div> @endif
</td></tr>