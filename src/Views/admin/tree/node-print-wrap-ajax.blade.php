/* resources/views/vendor/survloop/admin/tree/node-print-wrap-ajax.blade.php */

$(document).on("click", ".adminNodeExpand", function() {
    var nID = $(this).attr("id").replace("adminNode", "").replace("Expand", "");
    $("#nodeKids"+nID+"").slideToggle("fast");
    window.location='#n'+nID+'';
    return true;
});
$(document).on("click", ".circleBtn", function() {
    var nID = $(this).attr("id").replace("showBtns", "");
    if (document.getElementById("showBtns"+nID+"") && document.getElementById("nodeBtns"+nID+"")) {
        if (document.getElementById("nodeBtns"+nID+"").style.display=='inline') {
            document.getElementById("nodeBtns"+nID+"").style.display='none';
            document.getElementById("nodeBtns"+nID+"edit").className='slGrey';
        } else {
            document.getElementById("nodeBtns"+nID+"").style.display='inline';
            document.getElementById("nodeBtns"+nID+"edit").className='slBlueDark';
        }
    }
    return true;
});
$(document).on("click", ".adminNodeShowAdds", function() {
    var nID = $(this).attr("id").replace("showAdds", "");
    $("#nodeKids"+nID+"").slideDown("fast");
    $("#addChild"+nID+"").slideToggle("fast");
    if (document.getElementById("addSib"+nID+""))         $("#addSib"+nID+"").slideToggle("fast");
    if (document.getElementById("addSib"+nID+"B"))         $("#addSib"+nID+"B").slideToggle("fast");
    if (document.getElementById("addChild"+nID+"B"))     $("#addChild"+nID+"B").slideToggle("fast");
    return true;
});
$(document).on("click", ".adminNodeShowMove", function() {
    var nID = $(this).attr("id").replace("showMove", "");
    document.getElementById("moveNodeID").value = nID;
    $(".nodeMover").slideToggle(0);
    document.getElementById("adminMenuExtra").style.position="fixed";
    document.getElementById("adminMenuExtra").innerHTML="<i class=\'f18\'>Moving Node #"+nID+"</i>";
    window.location="#n"+nID+"";
    return true;
});
$(document).on("click", ".adminNodeMoveTo", function() {
    var loc = $(this).attr("id").replace("moveTo", "").split("ord");
    document.getElementById("moveToParentID").value = loc[0];
    document.getElementById("moveToOrderID").value = loc[1];
    @if (!$canEditTree) 
        alert("Sorry, you do not have permissions to actually edit the tree.");
    @else
        document.nodeManip.action+="#n"+document.getElementById("moveNodeID").value+""; 
        document.nodeManip.submit();
    @endif
    return true;
});
