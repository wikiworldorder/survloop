<?php
namespace SurvLoop\Controllers;

use Auth;
use Storage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use Symfony\Component\HttpFoundation\File\File;

use App\Models\User;       
use App\Models\SLNodeResponses;

class SurvFormTree extends SurvUploadTree
{
    
    public $classExtension      = 'SurvFormTree';

    public $nodeTypes           = [ 
        'Radio', 'Checkbox', 'Drop Down', 'Text', 'Long Text', 'Text:Number', 'Email', 'Password', 
        'Date', 'Date Picker', 'Date Time', 'Time', 'Gender', 'Gender Not Sure', 'Feet Inches', 
        'U.S. States', 'Countries', 'Hidden Field', 'Big Button', 'User Sign Up',
        'Spambot Honey Pot', 'Uploads', 'Other/Custom' 
    ];
    
    public $nodeSpecialTypes    = [
        'Instructions', 'Instructions Raw', 'Page', 'Branch Title', 'Loop Root', 'Loop Cycle', 'Loop Sort', 
        'Data Manip: New', 'Data Manip: Update', 'Data Manip: Wrap',
        'Search', 'Search Results', 'Search Featured', 
        'Record Previews', 'Incomplete Sess Check', 'Back Next Buttons',
        'Layout Row', 'Layout Column'
    ];
    
    protected $pageJSnode       = '';
    protected $pageHasUpload    = [];
    protected $pageHasReqs      = '';
    protected $pageFldList      = [];
    protected $page1stVisib     = '';
    protected $newLoopItem      = -3;
    protected $currLoopCycle    = ['', '', -3];
    
    protected $nextBtnOverride  = '';
    protected $loopItemsCustBtn = '';
    
    protected function getNodeCurrSessData($nID)
    {
        $this->allNodes[$nID]->fillNodeRow();
        list($tbl, $fld) = $this->allNodes[$nID]->getTblFld();
        return $this->sessData->currSessData($nID, $tbl, $fld);
    }
    
    protected function isPromptNotesSpecial($nodePromptNotes = '')
    {
        return (substr($nodePromptNotes, 0, 1) == '[' 
            && substr($nodePromptNotes, strlen($nodePromptNotes)-1) == ']');
    }
    
    protected function printSpecial($nID, $promptNotesSpecial = '', $currNodeSessData = '')
    {
        return '';
    }
    
    protected function customNodePrintButton($nID = -3, $nodeRow = array())
    {
        return '';
    }
    
    protected function nodePrintButton($nID = -3, $tmpSubTier = array(), $promptNotesSpecial = '', $printBack = true)
    { 
        $ret = $this->customNodePrintButton($nID, $promptNotesSpecial);
        if ($ret != '') return $ret;
        if ($GLOBALS["SL"]->treeRow->TreeType == 'Page') return '';
        
        // else print standard button variations
        $ret .= '<div class="fC"></div><div class="nodeSub">';
        if (isset($this->loopItemsCustBtn) && $this->loopItemsCustBtn != '') {
            $ret .= $this->loopItemsCustBtn;
        } elseif ($this->allNodes[$nID]->nodeType != 'Page' || $this->allNodes[$nID]->nodeOpts%29 > 0) {
            $nextLabel = 'Next';
            if ($this->nodePrintJumpTo($nID) > 0
                || ($this->allNodes[$nID]->nodeType == 'Instructions' && sizeof($tmpSubTier[1]) == 0)) {
                $nextLabel = 'OK';
            }
            if (trim($this->nextBtnOverride) != '') {
                $nextLabel = $this->nextBtnOverride;
            }
            $itemCnt = 0;
            if (isset($this->sessData->loopItemIDs[$GLOBALS["SL"]->closestLoop["loop"]])) {
                $itemCnt = sizeof($this->sessData->loopItemIDs[$GLOBALS["SL"]->closestLoop["loop"]]);
            }
            if ($this->allNodes[$nID]->isStepLoop() && $itemCnt != sizeof($this->sessData->loopItemIDsDone)) {
                $ret .= '<a href="javascript:;" class="fR btn btn-lg btn-primary nFormNext" id="nFormNext" 
                    ><i class="fa fa-arrow-circle-o-right"></i> ' . $nextLabel . '</a>';
            } else {
                $ret .= '<input type="submit" value="' . $nextLabel 
                    . '" class="fR btn btn-lg btn-primary nFormNext" id="nFormNext">';
            }
        }
        if ($this->nodePrintJumpTo($nID) <= 0 && $printBack && $GLOBALS["SL"]->treeRow->TreeFirstPage != $nID
            && ($this->allNodes[$nID]->nodeType != 'Page' || $this->allNodes[$nID]->nodeOpts%29 > 0)) {
            $ret .= '<input type="button" value="Back" class="fL nFormBack btn btn-lg btn-default" id="nFormBack">';
        }
        $ret .= '<div class="clearfix p5"></div></div>';
        return $ret; 
    }
    
    protected function printNodePublicFormStart($nID)
    {
        if ($GLOBALS["SL"]->treeRow->TreeType != 'Page') {
            return view('vendor.survloop.formtree-form-start', [
                "nID"             => $nID, 
                "nSlug"           => $this->allNodes[$nID]->nodeRow->NodePromptNotes, 
                "pageHasUpload"   => $this->pageHasUpload, 
                "nodePrintJumpTo" => $this->nodePrintJumpTo($nID), 
                "zoomPref"        => ((isset($this->sessInfo->SessZoomPref)) ? intVal($this->sessInfo->SessZoomPref) : 0), 
                "hasRegisterNode" => (isset($this->v["hasRegisterNode"]) && $this->v["hasRegisterNode"])
            ])->render();
        }
        return '';
    }
    
    protected function printNodePublicFormEnd($nID, $promptNotesSpecial = '')
    {
        if ($GLOBALS["SL"]->treeRow->TreeType != 'Page') {
            $loopRootJustLeft = -3;
            if (isset($this->sessInfo->SessLoopRootJustLeft) && intVal($this->sessInfo->SessLoopRootJustLeft) > 0) {
                $loopRootJustLeft = $this->sessInfo->SessLoopRootJustLeft;
                $this->sessInfo->SessLoopRootJustLeft = -3;
                $this->sessInfo->save();
            }
            $spinner = json_encode($this->loadCustView('inc-spinner'));
            $spinner = substr($spinner, 1, strlen($spinner)-2);
            return view('vendor.survloop.formtree-form-end', [
                "nID"              => $nID, 
                "isLoopRoot"       => $this->allNodes[$nID]->isLoopRoot(), 
                "pageURL"          => $this->allNodes[$nID]->nodeRow->NodePromptNotes, 
                "pageHasUpload"    => $this->pageHasUpload, 
                "pageFldList"      => $this->pageFldList, 
                "pageJSextra"      => $this->pageJSextra, 
                "pageJSvalid"      => $this->pageJSvalid, 
                "pageAJAX"         => $this->pageAJAX, 
                "loopRootJustLeft" => $loopRootJustLeft, 
                "hasFixedHeader"   => $this->v["hasFixedHeader"], 
                "hasRegisterNode"  => (isset($this->v["hasRegisterNode"]) && $this->v["hasRegisterNode"]),
                "spinner"          => $spinner
            ])->render();
        }
        return '';
    }
    
    private $pageNodes = array();
    private $pagePrevLiners = array();
    
    protected function getPrintableNodeList($nID = -3, $tmpSubTier = array())
    {
        if (!isset($this->allNodes[$nID])) return false;
        if (sizeof($tmpSubTier) == 0) $tmpSubTier = $this->loadNodeSubTier($nID);
        $this->pageNodes[] = $this->allNodes[$nID];
        if (sizeof($tmpSubTier[1]) > 0) {
            foreach ($tmpSubTier[1] as $childNode) { // recurse these bitches
                if (!$this->allNodes[$childNode[0]]->isPage()) {
                    $this->getPrintableNodeList($childNode[0], $childNode);
                }
            }
        }
        return true;
    }
    
    protected function getPrintSpecs($nID = -3, $tmpSubTier = array())
    {
        $this->pageNodes = array();
        $this->getPrintableNodeList($nID, $tmpSubTier);
        return true;
    }
    
    protected function customLabels($nID = -3, $str = '')
    {
        return $str;
    }
    
    protected function swapLabels($nID = -3, $str = '', $itemID = -3, $itemInd = -3)
    {
        if (trim($str) == '') return '';
        $str = $this->customLabels($nID, $str);
        $str = $GLOBALS["SL"]->swapBlurbs($str);
        $str = str_replace('[[nID]]', $nID, $str);
        $str = str_replace('[[coreID]]', $this->coreID, $str);
        $str = str_replace('[[DOMAIN]]', $GLOBALS["SL"]->sysOpts["app-url"], $str);
        if ($itemID > 0 && $itemInd >= 0) {
            if (strpos($str, '[LoopItemLabel]') !== false) {
                $label = $this->getLoopItemLabel($GLOBALS["SL"]->closestLoop["loop"], 
                    $this->sessData->getRowById($GLOBALS["SL"]->closestLoop["obj"]->DataLoopTable, $itemID), $itemInd);
                $str = str_replace('[LoopItemLabel]', '<span class="slBlueDark"><b>' . $label . '</b></span>', $str);
            }
            $str = str_replace('[LoopItemCnt]', '<span class="slBlueDark"><b>' . (1+$itemInd) . '</b></span>', $str);
        }
        $labelPos = strpos($str, '[LoopItemLabel:');
        if (($itemID <= 0 || $itemInd < 0) && $labelPos !== false) {
            $strPre = substr($str, 0, $labelPos);
            $loopName = substr($str, $labelPos+15);
            $labelEndPos = strpos($loopName, ']');
            $strPost = substr($loopName, $labelEndPos+1);
            $loopName = substr($loopName, 0, $labelEndPos);
            $loopRows = $this->sessData->getLoopRows($loopName);
            if (sizeof($loopRows) == 1) {
                $label = $this->getLoopItemLabel($loopName, $loopRows[0], $itemInd);
                $str = $strPre . '<span class="slBlueDark"><b>' . $label . '</b></span>' . $strPost;
            }
        }
        if (strpos($str, '[[PreviewPrivate]]') !== false || strpos($str, '[[PreviewPublic]]') !== false) {
            list($previewPublic, $previewPrivate) = $this->previewReportPubPri(true);
            $str = trim(str_replace('[[PreviewPublic]]', $previewPublic, 
                str_replace('[[PreviewPrivate]]', $previewPrivate, $str)));
        }
        return $this->cleanLabel($str);
    }
    
    protected function cleanLabel($str = '')
    {
        $str = str_replace('<span class="slBlueDark"><b>You</b></span>', 
            '<span class="slBlueDark"><b>you</b></span>', $str);
        $str = str_replace('<span class="slBlueDark"><b>you</b></span>&#39;s', 
            '<span class="slBlueDark"><b>your</b></span>', $str);
        $str = str_replace('Was <span class="slBlueDark"><b>you</b></span>', 
            'Were <span class="slBlueDark"><b>you</b></span>', $str);
        $str = str_replace('was <span class="slBlueDark"><b>you</b></span>', 
            'were <span class="slBlueDark"><b>you</b></span>', $str);
        $str = str_replace('<span class="slBlueDark"><b>you</b></span>\'s', 
            '<span class="slBlueDark"><b>your</b></span>', $str);
        $str = str_replace('<span class="slBlueDark"><b>you</b></span> was', 
            '<span class="slBlueDark"><b>you</b></span> were', $str);
        $str = str_replace(', [LoopItemLabel]:', ':', 
            str_replace(', <span class="slBlueDark"><b>[LoopItemLabel]</b></span>:', ':', $str));
        $str = str_replace(', <span class="slBlueDark"><b></b></span>:', ':', 
            str_replace(', <span class="slBlueDark"><b>&nbsp;</b></span>:', ':', $str));
        $str = trim(str_replace(', :', ':', $str));
        return $str;
    }
    
    protected function customNodePrintWrap($nID, $bladeRender = '')
    {
        return $this->printNodePublicFormStart($nID) . $bladeRender
            . $this->nodePrintButton($nID) . $this->printNodePublicFormEnd($nID) 
            . '<div class="fC p20"></div>';
    }

    
    //************  hidden field with nested nodes, flagged on/off when visible, 
    //    so i know which versions of duplicated questions to store during post processing  ************
    
    protected function customNodePrint($nID = -3, $tmpSubTier = array()) { return ''; }
    protected function printNodePublic($nID = -3, $tmpSubTier = array(), $currVisib = true)
    {
        if (!$this->checkNodeConditions($nID)) return '';
        if (sizeof($tmpSubTier) == 0) $tmpSubTier = $this->loadNodeSubTier($nID);
        if (!isset($this->v["hasFixedHeader"])) $this->v["hasFixedHeader"] = false;
        
        // copy node object; load field info and current session data
        $this->allNodes[$nID]->fillNodeRow($nID);
        $curr = $this->allNodes[$nID];
        list($tbl, $fld) = $curr->getTblFld();
        $fldForeignTbl = $GLOBALS["SL"]->fldForeignKeyTbl($tbl, $fld);
        if (($curr->isPage() || $curr->isInstruct()) && isset($GLOBALS['SL']->closestLoop['obj']->DataLoopTable)) {
            $tbl = $GLOBALS['SL']->closestLoop['obj']->DataLoopTable;
        }
        if ($tbl == '' && $this->hasCycleAncestor($nID) && trim($this->currLoopCycle[1]) != '') {
            $tbl = $this->currLoopCycle[0];
        }
        if (substr($curr->nodeType, 0, 11) == 'Data Manip:') $this->loadManipBranch($nID);
        $hasParentDataManip = $this->hasParentDataManip($nID);
        
        $itemInd = $itemID = -3;
        if ($this->hasCycleAncestor($nID) && $tbl == $this->currLoopCycle[0] 
            && trim($this->currLoopCycle[1]) != '' && intVal($this->currLoopCycle[2]) > 0) {
            $itemInd = intVal(str_replace('cyc', '', $this->currLoopCycle[1]));
            $itemID = $this->currLoopCycle[2];
            $currNodeSessData = $this->sessData->currSessData($nID, $tbl, $fld, 'get', '', $hasParentDataManip, 
                $itemInd, $itemID);
        } else { // not LoopCycle logic
            list($itemInd, $itemID) = $this->sessData->currSessDataPos($tbl, $hasParentDataManip);
            if (trim($GLOBALS['SL']->closestLoop['loop']) != '' 
                && $tbl == $this->sessData->isCheckboxHelperTable($tbl)) {
                // In this context, relevant item index is item's index with the loop, not the table's whole data set
                $itemInd = $this->sessData->getLoopIndFromID($GLOBALS['SL']->closestLoop['loop'], $itemID);
            }
            $currNodeSessData = $this->sessData->currSessData($nID, $tbl, $fld, 'get', '', $hasParentDataManip);
        }
        if ($itemID <= 0) $currNodeSessData = ''; // override false profit ;-P
        if ($currNodeSessData == '' && trim($curr->nodeRow->NodeDefault) != '') {
            $currNodeSessData = $curr->nodeRow->NodeDefault;
        }
        
        // print the button, and form initialization which only happens once per page
        if ($curr->isPage() || $curr->isLoopRoot()) {
            // make sure these are reset, in case of redirect
            $this->pageJSnode = $this->pageJSextra = $this->pageJSvalid = $this->pageHasReqs = '';
            $this->pageHasUpload = $this->pageFldList = array();
        }
        
        // check for extra custom PHP code stored with the node; check for standardized techniques
        $nodeOverrides = $this->printNodeSessDataOverride($nID, $tmpSubTier, $currNodeSessData);
        if (sizeof($nodeOverrides) > 1) $currNodeSessData = $nodeOverrides;
        elseif (sizeof($nodeOverrides) == 1 && isset($nodeOverrides[0])) $currNodeSessData = $nodeOverrides[0];
        
        $showKidsResponded = true;
        if (sizeof($tmpSubTier[1]) > 0) {
            if ($curr->nodeType == 'Countries') {
                $nxtNode = $this->nextNode($nID);
                if ($nxtNode > 0 && isset($this->allNodes[$nxtNode])) {
                    if ($this->allNodes[$nxtNode]->nodeType == 'U.S. States') {
                        $curr->hasShowKids = true;
                        $curr->responses = $GLOBALS["SL"]->states->getCountryResponses($nID, ['United States']);
                    }
                }
            }
            if ($curr->hasShowKids && sizeof($curr->responses) > 0) { // then displaying children on page is conditional
                $showKidsResponded = false;
                if ($currNodeSessData != '') {
                    foreach ($curr->responses as $res) {
                        if (intVal($res->NodeResShowKids) == 1 
                            && $this->isCurrDataSelected($currNodeSessData, $res->NodeResValue, $curr->nodeType)) {
                            $showKidsResponded = true;
                        }
                    }
                }
                $this->pageAJAX .= 'conditionNodes[' . $nID . '] = true;' . "\n";
            }
            $childList = array();
            foreach ($tmpSubTier[1] as $childNode) $childList[] = $childNode[0];
            $this->pageAJAX .= 'nodeKidList[' . $nID . '] = ['.implode(', ', $childList).'];' . "\n";
        } else {
            $this->pageAJAX .= 'nodeKidList[' . $nID . '] = new Array();' . "\n";
        }
        if (sizeof($curr->responses) == 3 && $curr->responses[1]->NodeResValue == '...') {
            $start = intVal($curr->responses[0]->NodeResValue);
            $finish = intVal($curr->responses[2]->NodeResValue);
            $curr->responses = [];
            if ($start < $finish) {
                for ($i=$start; $i<=$finish; $i++) $curr->responses[] = $curr->genTmpNodeRes($i);
            } else {
                for ($i=$start; $i>=$finish; $i--) $curr->responses[] = $curr->genTmpNodeRes($i);
            }
        }
        
        $nIDtxt = trim($nID . trim($this->currLoopCycle[1]));
        $visibilityField = '<input type="hidden" name="n' . $nIDtxt . 'Visible" id="n' . $nIDtxt . 'VisibleID" value="'
            . (($currVisib) ? 1 : 0) . '">';
        if (!$showKidsResponded) $currVisib = false;
        if ($this->page1stVisib == '' && $currVisib) {
            if (in_array($curr->nodeType, ['Radio', 'Checkbox', 'Gender', 'Gender Not Sure'])) {
                $this->page1stVisib = 'n' . $nID . 'fld0';
            } elseif (in_array($curr->nodeType, ['Date', 'Date Time'])) {
                $this->page1stVisib = 'n' . $nID . 'fldMonthID';
            } elseif (in_array($curr->nodeType, ['Time'])) {
                $this->page1stVisib = 'n' . $nID . 'fldHrID';
            } elseif (in_array($curr->nodeType, ['Feet Inches'])) {
                $this->page1stVisib = 'n' . $nID . 'fldFeetID';
            } elseif (in_array($curr->nodeType, ['Drop Down', 'Text', 'Long Text', 'Text:Number', 
                'Email', 'Password', 'U.S. States', 'Countries'])) {
                $this->page1stVisib = 'n' . $nID . 'FldID';
            }
        }
        
        if ($curr->isRequired()) $this->pageHasReqs++;
        
        $ret = $this->customNodePrint($nID, $tmpSubTier);
        if ($ret != '') return $visibilityField . $ret;
        $ret .= $visibilityField;
        // else print standard node output...
        
        // check for extra custom HTML/JS/CSS code stored with the node; check for standardized techniques
        $onKeyUp = ' checkNodeUp(\'' . $nIDtxt . '\', -1, 0); ';
        if (trim($curr->nodeRow->NodePromptAfter) != '' && !$curr->isWidget()) {
            if (stripos($curr->nodeRow->NodePromptAfter, '/'.'* formAJAX *'.'/') !== false) {
                $this->pageAJAX .= $curr->nodeRow->NodePromptAfter;
            } else {
                $this->pageJSnode .= str_replace('[[nID]]', $nID, $curr->nodeRow->NodePromptAfter);
                if (strpos($curr->nodeRow->NodePromptAfter, 'function reqFormNode[[nID]](') !== false) {
                    $this->pageJSvalid .= "if (document.getElementById('n" . $nIDtxt 
                        . "VisibleID').value == 1) reqFormNode" . $nID . "();\n";
                }
                if (strpos($curr->nodeRow->NodePromptAfter, 'function fldOnKeyUp[[nID]](') !== false) {
                    $onKeyUp .= ' fldOnKeyUp' . $nIDtxt . '(); ';
                }
            }
        }
        $charLimit = '';
        if (intVal($curr->nodeRow->NodeCharLimit) > 0 && $curr->nodeRow->NodeOpts%31 > 0 
            && $curr->nodeType != 'Uploads') {
            $onKeyUp .= ' charLimit(\'' . $nIDtxt . '\', ' . $curr->nodeRow->NodeCharLimit . '); ';
            $charLimit = "\n" . '<div id="charLimit' . $nID . 'Msg" class="slRedDark f12 opac33"></div>';
            $this->pageJSextra .= 'setTimeout("charLimit(\'' . $nIDtxt . '\', ' 
                . $curr->nodeRow->NodeCharLimit . ')", 50);' . "\n";
        }
        if ($curr->nodeRow->NodeOpts%31 == 0) {
            if (intVal($curr->nodeRow->NodeCharLimit) == 0) $curr->nodeRow->NodeCharLimit = 10000000000;
            $onKeyUp .= ' wordCountKeyUp(\'' . $nIDtxt . '\', ' 
                . intVal($curr->nodeRow->NodeCharLimit) . '); ';
            $this->pageJSextra .= 'setTimeout("wordCountKeyUp(\'' . $nIDtxt . '\', ' 
                . intVal($curr->nodeRow->NodeCharLimit) . ')", 50);' . "\n";
        }
        if (trim($onKeyUp) != '') $onKeyUp = ' onKeyUp="'.$onKeyUp.'" ';
        
        // check notes settings for any standardized techniques
        $promptNotesSpecial = '';
        if ($this->isPromptNotesSpecial($curr->nodeRow->NodePromptNotes)) {
            $promptNotesSpecial = $curr->nodeRow->NodePromptNotes;
            $curr->nodeRow->NodePromptNotes = '';
        }
        
        // write basic node field labeling
        $nodePromptText  = $this->swapLabels($nID, $curr->nodeRow->NodePromptText, $itemID, $itemInd);
        $nodePromptNotes = $this->swapLabels($nID, $curr->nodeRow->NodePromptNotes, $itemID, $itemInd);
        if (trim($nodePromptNotes) != '' && !$curr->isLoopRoot()) {
            $nodePromptText .= '<h5 id="nLabel' . $nIDtxt . 'notes">' . $nodePromptNotes . '</h5>' . "\n";
        }
        if ($curr->isRequired() && $curr->nodeType != 'Hidden Field') {
            $nodePromptText = $this->addPromptTextRequired($curr, $nodePromptText);
        }
        if (strpos($nodePromptText, 'fixedHeader') !== false) $this->v["hasFixedHeader"] = true;
        
        $nodePrompt = '';
        if (strpos($curr->nodeRow->NodePromptText, '[[PreviewPrivate]]') !== false 
            || strpos($curr->nodeRow->NodePromptText, '[[PreviewPublic]]') !== false) {
            $nodePrompt = $nodePromptText;
        } elseif (trim($nodePromptText) != '') {
            $nodePrompt = "\n" . '<div id="nLabel' . $nIDtxt . '" class="nPrompt"><label for="n' . $nIDtxt . 'FldID">'
                . $nodePromptText . '</label></div>' . "\n";
        }
        
        if ($curr->nodeType == 'Layout Column') {
            $ret .= '<div class="col-md-' . $curr->nodeRow->NodeCharLimit . '">';
        }
        
        if ($curr->isPage() || $curr->isLoopRoot()) $ret .= '<div class="pageTopGap"></div>';
        
        $ret .= '<div class="fC"></div><div class="nodeAnchor"><a name="n' . $nIDtxt 
            . '"></a></div><div id="node' . $nIDtxt . '" class="nodeWrap'
            . (($GLOBALS["SL"]->treeRow->TreeType == 'Page' 
                && ($curr->isInstruct() || $curr->isInstructRaw())) ? ' w100' : '') . '">' . "\n";
        
        // write the start of the main node wrapper
        if ($curr->nodeRow->NodeOpts%37 == 0) $ret .= '<div class="jumbotron">';
            
        if ($GLOBALS["SL"]->treeRow->TreeType != 'Page' && !$curr->isPage() 
            && !$curr->isLoopRoot() && !$curr->isLoopCycle() && !$curr->isDataManip()) {
            $ret .= '<div class="nodeHalfGap"></div>';
        }
        
        
        if (!$curr->isLayout() && !$curr->isBranch()) {
            
            // check for if we're at the root of a Loop Root, we've got special handling
            if ($curr->isLoopRoot()) {
                
                $ret .= $nodePrompt . $this->printSetLoopNav($nID, $curr->dataBranch);
                    
            } elseif ($curr->isLoopCycle()) {
                
                list($tbl, $fld, $newVal) = $this->nodeBranchInfo($nID);
                $loop = str_replace('LoopItems::', '', $curr->nodeRow->NodeResponseSet);
                $loopCycle = $this->sessData->getLoopRows($loop);
                if (sizeof($tmpSubTier[1]) > 0 && $loopCycle && sizeof($loopCycle) > 0) {
                    $this->currLoopCycle[0] = $GLOBALS["SL"]->getLoopTable($loop);
                    foreach ($loopCycle as $i => $loopItem) {
                        $this->currLoopCycle[1] = 'cyc' . $i;
                        $this->currLoopCycle[2] = $loopItem->getKey();
                        $this->sessData->startTmpDataBranch($tbl, $loopItem->getKey());
                        $GLOBALS["SL"]->fakeSessLoopCycle($loop, $loopItem->getKey());
                        foreach ($tmpSubTier[1] as $childNode) {
                            if (!$this->allNodes[$childNode[0]]->isPage()) {
                                $ret .= $this->printNodePublic($childNode[0], $childNode, $currVisib);
                            }
                        }
                        $GLOBALS["SL"]->removeFakeSessLoopCycle($loop, $loopItem->getKey());
                        $this->sessData->endTmpDataBranch($tbl);
                        $this->currLoopCycle[1] = '';
                        $this->currLoopCycle[2] = -3;
                    }
                    $this->currLoopCycle[0] = '';
                }
                
            } elseif ($curr->isLoopSort()) {
                
                $loop = str_replace('LoopItems::', '', $curr->nodeRow->NodeResponseSet);
                $loopCycle = $this->sessData->getLoopRows($loop);
                if ($loopCycle && sizeof($loopCycle) > 0) {
                    $this->v["needsJqUi"] = true;
                    $this->pageAJAX .= '$("#sortable").sortable({ axis: "y", update: function (event, ui) {
                        var url = "/sortLoop/?n=' . $nID . '&"+$(this).sortable("serialize")+"";
                        document.getElementById("hidFrameID").src=url;
                    } }); $("#sortable").disableSelection();';
                    $ret .= '<div class="nFld">' . $this->sortableStart($nID) . '<ul id="sortable">' . "\n";
                    foreach ($loopCycle as $i => $loopItem) {
                        $ret .= '<li id="item-' . $loopItem->getKey() . '" class="sortOff" onMouseOver="'
                            . 'this.className=\'sortOn\';" onMouseOut="this.className=\'sortOff\';">'
                            . '<span><i class="fa fa-sort slBlueLight"></i></span> ' 
                            . $this->getLoopItemLabel($loop, $loopItem, $i) . '</li>' . "\n";
                    }
                    $ret .= '</ul>' . $this->sortableEnd($nID) . '</div>' . "\n";
                }
                
            } elseif ($curr->isDataManip()) {
                
                $ret .= '<input type="hidden" name="dataManip' . $nIDtxt . '" value="1">';
                
                
            } elseif ($curr->nodeType == 'Back Next Buttons') {
                
                    $ret .= view('vendor.survloop.inc-extra-back-next-buttons', [])->render();
                
            } elseif (in_array($curr->nodeType, ['Search', 'Search Results', 'Search Featured', 
                'Records Full', 'Record Previews', 'Incomplete Sess Check'])) {
            
                if (intVal($curr->nodeRow->NodeResponseSet) > 0) {
                    $widgetTreeID = $curr->nodeRow->NodeResponseSet;
                    $widgetLimit  = intVal($curr->nodeRow->NodeCharLimit);
                    if ($curr->nodeType == 'Search') {
                        $ret .= $this->printSearchBar('', $widgetTreeID, 
                            trim($curr->nodeRow->NodePromptText), trim($curr->nodeRow->NodePromptAfter), $nID, 0);
                    } else {
                        $loadURL = 'records-full/' . $widgetTreeID;
                        $search = (($GLOBALS["SL"]->REQ->has('s')) ? $GLOBALS["SL"]->REQ->get('s') : '');
                        if ($curr->nodeType == 'Search Featured') {
                            $ret .= $this->searchResultsFeatured($search, $widgetTreeID);
                        } else { 
                            if ($curr->nodeType == 'Search Results') {
                                $this->getSearchFilts($GLOBALS["SL"]->REQ);
                                $loadURL = '/search-results/' . $widgetTreeID . '?s=' . urlencode($search) . $this->searchFiltsURL();
                                $curr->nodeRow->NodePromptText = str_replace('[[search]]', $search, 
                                    $curr->nodeRow->NodePromptText);
                                $curr->nodeRow->NodePromptAfter = str_replace('[[search]]', $search, 
                                    $curr->nodeRow->NodePromptAfter);
                            } else {
                                if ($curr->nodeType == 'Record Previews') {
                                    $loadURL = '/record-prevs/' . $widgetTreeID; 
                                } elseif ($curr->nodeType == 'Incomplete Sess Check') {
                                    $loadURL = '/record-check/' . $widgetTreeID;
                                }
                                $loadURL .= '?limit=' . $widgetLimit;
                            }
                            $spinner = '';
                            if ($curr->nodeType != 'Incomplete Sess Check') {
                                $spinner = $this->loadCustView('inc-spinner');
                            }
                            $ret .= view('vendor.survloop.inc-node-ajax-load', [
                                "nID"     => $nID, 
                                "treeID"  => $curr->nodeRow->NodeResponseSet, 
                                "load"    => $loadURL,
                                "pre"     => trim($curr->nodeRow->NodePromptText),
                                "post"    => trim($curr->nodeRow->NodePromptAfter),
                                "spinner" => $spinner
                            ])->render();
                        }
                    }
                }
                
            } elseif (!$curr->isPage()) { // otherwise, the main Node printer...
                
                // Start normal data field checks
                $dateStr = $timeStr = '';
                if ($fld != '' && isset($GLOBALS["SL"]->tblAbbr[$tbl]) && $fld != ($GLOBALS["SL"]->tblAbbr[$tbl] . 'ID') 
                    && trim($currNodeSessData) != '' && isset($GLOBALS["SL"]->fldTypes[$tbl][$fld])) {
                    // convert current session data for dates and times
                    if ($GLOBALS["SL"]->fldTypes[$tbl][$fld] == 'DATETIME') {
                        list($dateStr, $timeStr) = explode(' ', $currNodeSessData);
                        $dateStr = $this->cleanDateVal($dateStr);
                        if (trim($dateStr) != '') $dateStr = date("m/d/Y", strtotime($dateStr));
                    } elseif ($GLOBALS["SL"]->fldTypes[$tbl][$fld] == 'DATE') {
                        $dateStr = $this->cleanDateVal($currNodeSessData);
                        if (trim($dateStr) != '') $dateStr = date("m/d/Y", strtotime($dateStr));
                    }
                    if ($dateStr == '12/31/1969') $dateStr = '';
                } // end normal data field checks
                
                $mobileCheckbox = ($curr->nodeRow->NodeOpts%2 > 0);
                
                // check if this field's label and field is to be printed on the same line
                $isOneLiner = $isOneLinerFld = '';
                if ($curr->isOneLiner()) $isOneLiner = ' disIn mR20';
                elseif ($curr->isOneLiner() || $curr->isOneLineResponses()) $isOneLinerFld = ' disIn mR20';
                if (trim($isOneLiner) != '') {
                    $nodePrompt = str_replace('class="nPrompt"', 'class="nPrompt' . $isOneLiner . '"', $nodePrompt);
                }
                
                if (!in_array($curr->nodeType, ['Radio', 'Checkbox', 'Instructions', 'Other/Custom'])) {
                    $this->pageFldList[] = 'n' . $nID . 'FldID';
                }
                
                // print out each of the various field types
                if ($curr->nodeType == 'Hidden Field') {
                    
                    $ret .= $nodePrompt . '<input type="hidden" name="n' . $nIDtxt . 'fld" id="n' . $nIDtxt 
                        . 'FldID" value="' . $currNodeSessData . '">' . "\n"; 
                        
                } elseif ($curr->nodeType == 'Big Button') {
                    
                    $btn = '<div class="nFld"><a id="nBtn' . $nIDtxt . '" class="btn btn-lg btn-' 
                        . (($curr->nodeRow->NodeOpts%47 == 0) ? 'default' : 'primary') . ' nFldBtn' 
                        . (($curr->nodeRow->NodeOpts%43 == 0) ? '' : ' nFormNext') . '" ' 
                        . ((trim($curr->nodeRow->NodeDataStore) != '')
                            ? 'onClick="' . $curr->nodeRow->NodeDataStore . '"' : '')
                        . ' >' . $curr->nodeRow->NodeDefault . '</a></div>' . "\n";
                    $lastDivPos = strrpos($nodePrompt, "</div>\n            </label></div>");
                    if (strpos($nodePrompt, 'jumbotron') > 0 && $lastDivPos > 0) {
                        $ret .= substr($nodePrompt, 0, $lastDivPos) 
                            . '<center>' . $btn . '</center>' 
                            . substr($nodePrompt, $lastDivPos) 
                            . '<input type="hidden" name="n' . $nIDtxt . 'fld" id="n' . $nIDtxt 
                            . 'FldID" value="' . $currNodeSessData . '">' . "\n"; 
                    } else {
                        $ret .= $nodePrompt . '<input type="hidden" name="n' . $nIDtxt . 'fld" id="n' . $nIDtxt 
                            . 'FldID" value="' . $currNodeSessData . '">' . $btn . "\n"; 
                    }
                    if ($curr->nodeRow->NodeOpts%43 == 0) {
                        $curr->hasShowKids = true;
                        $showKidsResponded = false;
                        $this->pageAJAX .= '$(document).on("click", "#nBtn' . $nIDtxt . '", function() { '
                            . 'if (document.getElementById("node' . $nIDtxt . 'kids")) { '
                                . 'if (document.getElementById("node' . $nIDtxt . 'kids").style.display=="none") { '
                                    . '$("#node' . $nIDtxt . 'kids").slideDown("50"); '
                                    . 'kidsVisible("' . $nIDtxt . '", true, true); '
                                . '} else { '
                                    . '$("#node' . $nIDtxt . 'kids").slideUp("50"); '
                                    . 'kidsVisible("' . $nIDtxt . '", false, true); '
                                . '} '
                            . '} });' . "\n";
                    }
                    
                } elseif ($curr->nodeType == 'User Sign Up') {
                    
                    $this->v["hasRegisterNode"] = true;
                    $this->pageJSvalid .= view('vendor.survloop.auth.register-node-jsValid', [
                        "coreID"         => $this->coreID
                    ])->render();
                    $ret .= view('vendor.survloop.auth.register-node', [
                        "coreID"         => $this->coreID, 
                        "anonyLogin"     => $this->isAnonyLogin(), 
                        "anonyPass"      => uniqid(),
                        "currAdmPage"    => '', 
                        "user"           => Auth::user(),
                        "inputMobileCls" => $this->inputMobileCls($nID)
                    ])->render();
                    
                } elseif (in_array($curr->nodeType, array('Text', 'Email', 'Text:Number', 'Spambot Honey Pot'))) {
                    
                    $ret .= $nodePrompt . '<div class="nFld' . $isOneLinerFld 
                        . '"><input class="form-control input-lg" type="' . (($curr->nodeType == 'Email') ? 'email' 
                            : (($curr->nodeType == 'Text:Number') ? 'number' : 'text'))
                        . '" name="n' . $nIDtxt . 'fld" id="n' . $nIDtxt . 'FldID" value="' . $currNodeSessData 
                        . '" ' . $onKeyUp . ' ></div>' . $charLimit . "\n"; 
                    if ($curr->isRequired()) {
                        $this->pageJSvalid .= "if (document.getElementById('n" . $nIDtxt . "VisibleID').value == 1) " 
                            . (($curr->nodeType == 'Email') ? "reqFormFldEmail('" . $nIDtxt . "');\n" 
                                : "reqFormFld('" . $nIDtxt . "');\n");
                    }
                    if ($curr->nodeType == 'Spambot Honey Pot') {
                        $this->pageJSextra .= "\n" . 'nFldHP("' . $nIDtxt . '");';
                    }
                    if (trim($curr->nodeRow->NodeTextSuggest) != '') {
                        $this->v["needsJqUi"] = true;
                        $this->pageAJAX .= '$( "#n' . $nIDtxt . 'FldID" ).autocomplete({ source: [';
                        foreach ($GLOBALS["SL"]->getDefSet($curr->nodeRow->NodeTextSuggest) as $i => $def) {
                            $this->pageAJAX .= (($i > 0) ? ',' : '') . ' "' . json_encode($def->DefValue) . '"';
                        }
                        $this->pageAJAX .= ' ] });' . "\n";
                    }
                    if ($curr->nodeRow->NodeOpts%41 == 0) { // copy input to extra div
                        $this->pageJSextra .= "\n" . 'setTimeout("copyNodeResponse(\'n' . $nIDtxt . 'FldID\', \'nodeEcho' 
                            . $nIDtxt . '\')", 50);';
                        $this->pageAJAX .= '$("#n' . $nIDtxt . 'FldID").keyup(function() { copyNodeResponse(\'n' 
                            . $nIDtxt . 'FldID\', \'nodeEcho' . $nIDtxt . '\'); });' . "\n";
                    }
                    
                } elseif ($curr->nodeType == 'Long Text') {
                    
                    $ret .= $nodePrompt . '<div class="nFld' . $isOneLinerFld . '">
                        <textarea class="form-control input-lg" name="n' . $nIDtxt 
                        . 'fld" id="n' . $nIDtxt . 'FldID" ' . $onKeyUp . ' >' . $currNodeSessData 
                        . '</textarea></div>' . $charLimit . "\n";
                    if ($curr->nodeRow->NodeOpts%31 == 0) {
                        $ret .= '<div class="fR slGrey p0 m0">
                            <i>word count: <div id="wordCnt' . $nID . '" class="disIn"></div></i>
                        </div><div class="fC"></div>';
                    }
                    if ($curr->isRequired()) {
                        $this->pageJSvalid .= "if (document.getElementById('n" . $nIDtxt 
                            . "VisibleID').value == 1) reqFormFld('" . $nIDtxt . "');\n";
                    }
                    
                } elseif ($curr->nodeType == 'Password') {
                    
                    $ret .= $nodePrompt . '<div class="nFld' . $isOneLinerFld . '"><input type="password" name="n' 
                        . $nIDtxt . 'fld" id="n' . $nIDtxt . 'FldID" value="" ' . $onKeyUp 
                        . ' autocomplete="off" class="form-control input-lg" ></div>' . $charLimit . "\n"; 
                    if ($curr->isRequired()) {
                        $this->pageJSvalid .= "if (document.getElementById('n" . $nIDtxt 
                            . "VisibleID').value == 1) reqFormFld('" . $nIDtxt . "');\n";
                    }
                    
                } elseif (in_array($curr->nodeType, ['Drop Down', 'U.S. States', 'Countries'])) {
                    
                    $curr = $this->checkResponses($curr, $fldForeignTbl);
                    if (sizeof($curr->responses) > 0 || in_array($curr->nodeType, ['U.S. States', 'Countries'])) {
                        $ret .= $nodePrompt . "\n" . '<div class="nFld' . $isOneLinerFld . '">
                            <select name="n' . $nIDtxt . 'fld" id="n' . $nIDtxt . 'FldID" class="form-control input-lg' 
                            . (($isOneLinerFld != '') ? ' w33' : '') 
                            . '" onChange="checkNodeUp(\'' . $nIDtxt . '\', -1, 0);' 
                            . (($curr->isDropdownTagger()) ? ' selectTag(' . $nID . ', this.value); this.value=\'\';' : '') 
                            . '" ><option class="slGrey" value="" ' 
                            . ((trim($currNodeSessData) == '' || $curr->isDropdownTagger()) ? 'SELECTED' : '') . ' >'
                            . ((isset($curr->nodeRow->NodeTextSuggest)) ? $curr->nodeRow->NodeTextSuggest : '') 
                            . '</option>' . "\n"; 
                        if ($curr->hasShowKids) {
                            $this->pageAJAX .= '$("#n' . $nIDtxt 
                                . 'FldID").change(function(){ var foundKidResponse = false;' . "\n";
                        }
                        if ($curr->nodeType == 'U.S. States') {
                            $ret .= $GLOBALS["SL"]->states->stateDrop($currNodeSessData);
                        }
                        else { 
                            foreach ($curr->responses as $j => $res) {
                                $select = $this->isCurrDataSelected($currNodeSessData, $res->NodeResValue, $curr->nodeType);
                                if ($curr->isDropdownTagger()) {
                                    $this->pageJSextra .= "\n" . 'addTagOpt(' . $nID . ', ' . $res->NodeResValue 
                                        . ', ' . json_encode($res->NodeResEng) . ', ' . (($select) ? 1 : 0) . ');';
                                }
                                $ret .= '<option value="' . $res->NodeResValue . '" ' 
                                    . (($select && !$curr->isDropdownTagger()) ? 'SELECTED' : '') 
                                    . ' >' . $res->NodeResEng . '</option>' . "\n"; 
                                if ($curr->hasShowKids && intVal($res->NodeResShowKids) == 1) {
                                    $this->pageAJAX .= 'if (document.getElementById("n' . $nIDtxt . 'FldID").value == "' 
                                        . $res->NodeResValue . '") foundKidResponse = true;' . "\n";
                                }
                            }
                        }
                        if ($curr->hasShowKids) {
                            $this->pageAJAX .= "\n" . 'if (foundKidResponse) { $("#node' . $nIDtxt 
                                . 'kids").slideDown("50"); kidsVisible("' . $nIDtxt . '", true, true); } else { $("#node' 
                                . $nIDtxt . 'kids").slideUp("50"); kidsVisible("' . $nIDtxt 
                                . '", false, true); }' . "\n" . ' }); ' . "\n";
                        }
                        if ($curr->isRequired()) {
                            $this->pageJSvalid .= "if (document.getElementById('n" . $nIDtxt 
                                . "VisibleID').value == 1) reqFormFld('" . $nIDtxt . "');\n";
                        }
                        $ret .= '</select></div>' . "\n"; 
                        if ($curr->isDropdownTagger()) {
                            $ret .= '<input type="hidden" name="n' . $nID . 'tagIDs" id="n' . $nID . 'tagIDsID" value=",">'
                                . '<div id="n' . $nID . 'tags" class="slTagList"></div>';
                            $this->pageJSextra .= "\n" . 'setTimeout("updateTagList(' . $nID . ')", 50);';
                        }
                    }
                    
                } elseif (in_array($curr->nodeType, ['Radio', 'Checkbox'])) {
                    
                    $curr = $this->checkResponses($curr, $fldForeignTbl);
                    if ($curr->nodeType == 'Radio') {
                        $ret .= '<input type="hidden" name="n' . $nID . 'radioCurr" id="n' . $nID 
                            . 'radioCurrID" value="' . $currNodeSessData . '">';
                        $this->pageJSextra .= "\n" . 'addRadioNode("' . $nIDtxt . '");';
                    }
                    if (sizeof($curr->responses) > 0) {
                        $ret .= (($curr->isOneLiner()) ? '<div class="pB20">' : '') 
                            . str_replace('<label for="n' . $nIDtxt . 'FldID">', '', 
                                str_replace('</label>', '', $nodePrompt))
                            . '<div class="nFld';
                        if ($mobileCheckbox) $ret .= '" style="margin-top: 20px;">' . "\n";
                        else $ret .= $isOneLiner . ' pB0 mBn5">' . "\n";
                        $respKids = (($curr->hasShowKids) ? ' class="n' . $nIDtxt . 'fldCls" ' : ''); 
                            // onClick="return check' . $nID . 'Kids();"
                        if ($curr->hasShowKids) {
                            $this->pageAJAX .= '$(".n' . $nIDtxt 
                                . 'fldCls").click(function(){ var foundKidResponse = false;' . "\n";
                        }
                        $this->pageJSextra .= "\n" . 'addResTot("' . $nIDtxt . '", ' . sizeof($curr->responses) . ');';
                        $fldHasOther = false;
                        foreach ($curr->responses as $j => $res) {
                            if (in_array(strtolower(strip_tags($res->NodeResValue)), ['other', 'other:']) 
                                && isset($GLOBALS["SL"]->fldOthers[$fld . 'Other'])
                                && intVal($GLOBALS["SL"]->fldOthers[$fld . 'Other']) > 0) {
                                $fldHasOther = true;
                            }
                        }
                        if ($curr->nodeRow->NodeOpts%61 == 0) {
                            $ret .= '<div class="row">';
                            $mobileCheckbox = true;
                        }
                        foreach ($curr->responses as $j => $res) {
                            $otherFld = ['', '', '', ''];
                            if ($fldHasOther 
                                && in_array(strtolower(strip_tags($res->NodeResValue)), ['other', 'other:'])) {
                                $otherFld[0] = $fld . 'Other';
                                $otherFld[1] = $this->sessData->currSessData($nID, $tbl, $otherFld[0], 'get', '', 
                                    $hasParentDataManip);
                                $otherFld[2] = '<input type="text" name="n' . $nID . 'fldOther" id="n' . $nID 
                                    . 'fldOtherID" class="form-control input-lg disIn otherFld mL10" value="' 
                                    . $otherFld[1] . '" onKeyUp="formKeyUpOther(' . $nID . ', ' . $j . ');">';
                            }
                            
                            if ($curr->nodeType == 'Checkbox' && $curr->indexMutEx($j)) {
                                $this->pageJSextra .= "\n" . 'addMutEx("' . $nIDtxt . '", ' . $j . ');';
                            }
                            $this->pageFldList[] = 'n' . $nIDtxt . 'fld' . $j;
                            $resNameCheck = '';
                            $boxChecked 
                                = $this->isCurrDataSelected($currNodeSessData, $res->NodeResValue, $curr->nodeType);
                            if ($curr->nodeType == 'Radio') {
                                $resNameCheck = 'name="n' . $nIDtxt . 'fld" ' . (($boxChecked) ? 'CHECKED' : '');
                                if ($fldHasOther && $otherFld[1] == '') {
                                    $otherFld[3] = ' document.getElementById(\'n' . $nID . 'fldOtherID\').value=\'\'; ';
                                }
                            } else {
                                $resNameCheck = 'name="n' . $nIDtxt . 'fld[]" ' . (($boxChecked) ? 'CHECKED' : '');
                            }
                            
                            if ($curr->nodeRow->NodeOpts%61 == 0) {
                                $ret .= '<div class="col-md-' . $this->getColsWidth(sizeof($curr->responses)) . '">';
                            }
                            if ($mobileCheckbox) {
                                $ret .= '<label for="n' . $nIDtxt . 'fld' . $j . '" id="n' . $nIDtxt . 'fld' . $j 
                                    . 'lab" class="finger' . (($boxChecked) ? 'Act' : '') . '">
                                    <div class="disIn mR5"><input id="n' . $nIDtxt . 'fld' . $j 
                                    . '" value="' . $res->NodeResValue . '" type="' . strtolower($curr->nodeType) . '" ' 
                                    . $resNameCheck . $respKids . ' autocomplete="off" onClick="checkNodeUp(\'' 
                                    . $nIDtxt . '\', ' . $j . ', 1);' . $otherFld[3] . '" ></div> ' 
                                    . $res->NodeResEng . ' ' . $otherFld[2] . '</label>' . "\n";
                            } else {
                                $ret .= '<div class="' . $isOneLinerFld . '">' . ((strlen($res) < 40) ? '<nobr>' : '') 
                                    . '<label for="n' . $nIDtxt . 'fld' . $j . '" class="mR10"><div class="disIn mR5">
                                    <input id="n' . $nIDtxt . 'fld' . $j . '" value="' . $res->NodeResValue . '" type="' 
                                    . strtolower($curr->nodeType) . '" ' . $resNameCheck . $respKids 
                                    . ' autocomplete="off" onClick="checkNodeUp(\'' . $nIDtxt . '\', ' . $j . ', 0);' 
                                    . $otherFld[3] . '" ></div> ' . $res->NodeResEng . ' ' 
                                    . $otherFld[2] . '</label>' . ((strlen($res) < 40) ? '</nobr>' : '') 
                                    . '</div>' . "\n";
                            }
                            if ($curr->nodeRow->NodeOpts%61 == 0) {
                                $ret .= '</div> <!-- end col -->' . "\n";
                            }
                            if ($curr->hasShowKids && intVal($res->NodeResShowKids) == 1) {
                                $this->pageAJAX .= 'if (document.getElementById("n' . $nIDtxt . 'fld' . $j 
                                    . '").checked) foundKidResponse = true;' . "\n";
                            }
                        }
                        if ($curr->hasShowKids) {
                            $this->pageAJAX .= "\n" . 'if (foundKidResponse) { $("#node' . $nIDtxt 
                                . 'kids").slideDown("50"); kidsVisible("' . $nIDtxt . '", true, true); } ' . "\n"
                                . 'else { $("#node' . $nIDtxt . 'kids").slideUp("50"); kidsVisible("' 
                                . $nIDtxt . '", false, true); }' . "\n" . ' }); ' . "\n";
                        }
                        if ($curr->isRequired()) {
                            $this->pageJSvalid .= "if (document.getElementById('n" . $nIDtxt 
                                . "VisibleID').value == 1) reqFormFldRadio('" . $nIDtxt . "', " 
                                . sizeof($curr->responses) . ");\n";
                        }
                        
                        if ($curr->nodeRow->NodeOpts%61 == 0) {
                            $ret .= '</div> <!-- end row -->';
                        }
                        
                        $ret .= '</div>' . (($curr->isOneLiner()) ? '</div>' : '') . "\n"; 
                    }
                    
                } elseif ($curr->nodeType == 'Date') {
                    
                    $ret .= $nodePrompt . '<div class="nFld' . $isOneLinerFld . '">' 
                        . $this->formDate($nIDtxt, $dateStr) . '</div>' . "\n";
                    if ($this->nodeHasDateRestriction($curr->nodeRow)) { // then enforce time validation
                        $this->pageJSvalid .= "if (document.getElementById('n" . $nIDtxt
                            . "VisibleID').value == 1) reqFormFldDate" 
                            . (($curr->isRequired()) ? "And" : "") . "Limit('" . $nIDtxt . "', " 
                            . $curr->nodeRow->NodeCharLimit . ", '" . date("Y-m-d") . "', 1);\n";
                    } elseif ($curr->isRequired()) {
                        $this->pageJSvalid .= "if (document.getElementById('n" . $nIDtxt 
                            . "VisibleID').value == 1) reqFormFldDate('" . $nIDtxt . "');\n";
                    }
                    
                } elseif ($curr->nodeType == 'Date Picker') {
                    
                    $this->v["needsJqUi"] = true;
                    $this->pageAJAX .= '$( "#n' . $nIDtxt . 'FldID" ).datepicker({ maxDate: "+0d" });' . "\n";
                    $ret .= $nodePrompt . '<div class="nFld' . $isOneLinerFld . '"><input name="n' . $nIDtxt . 'fld" id="n' 
                        . $nIDtxt . 'FldID" value="' . $dateStr . '" ' . $onKeyUp 
                        . ' type="text" class="dateFld form-control input-lg" ></div>' . "\n";
                    if ($curr->isRequired()) {
                        $this->pageJSvalid .= "if (document.getElementById('n" . $nIDtxt 
                            . "VisibleID').value == 1) reqFormFld('" . $nIDtxt . "');\n";
                    }
                    
                } elseif ($curr->nodeType == 'Time') {
                    
                    $this->pageFldList[] = 'n' . $nIDtxt . 'fldHrID'; 
                    $this->pageFldList[] = 'n' . $nIDtxt . 'fldMinID';
                    $ret .= str_replace('<label for="n' . $nIDtxt . 'FldID">', '<label for="n' . $nIDtxt 
                        . 'fldHrID"><label for="n' . $nIDtxt . 'fldMinID"><label for="n' . $nIDtxt . 'fldPMID">', 
                        str_replace('</label>', '</label></label></label>', $nodePrompt)) 
                        . '<div class="nFld' . $isOneLinerFld . '">' . $this->formTime($nIDtxt, $timeStr) . '</div>' . "\n";
                    if ($curr->isRequired()) {
                        $this->pageJSvalid .= "if (document.getElementById('n" . $nIDtxt 
                            . "VisibleID').value == 1) reqFormFld('" . $nIDtxt . "');\n";
                    }
                    
                } elseif ($curr->nodeType == 'Date Time') {
                    
                    $ret .= view('vendor.survloop.formtree-form-datetime', [
                        "nID"            => $nIDtxt,
                        "dateStr"        => $dateStr,
                        "onKeyUp"        => $onKeyUp,
                        "isOneLinerFld"  => $isOneLinerFld,
                        "inputMobileCls" => $this->inputMobileCls($nID),
                        "formTime"       => $this->formTime($nID, $timeStr),
                        "nodePrompt"     => str_replace('<label for="n' . $nIDtxt . 'FldID">', 
                            '<label for="n' . $nIDtxt . 'FldID"><label for="n' . $nIDtxt . 'fldHrID"><label for="n' 
                            . $nIDtxt . 'fldMinID"><label for="n' . $nIDtxt . 'fldPMID">', 
                            str_replace('</label>', '</label></label></label></label>', $nodePrompt))
                    ])->render();
                    $this->pageFldList[] = 'n' . $nIDtxt . 'FldID'; 
                    $this->pageFldList[] = 'n' . $nIDtxt . 'fldHrID'; 
                    $this->pageFldList[] = 'n' . $nIDtxt . 'fldMinID';
                    if ($curr->isRequired()) {
                        $this->pageJSvalid .= "if (document.getElementById('n" . $nIDtxt
                            . "VisibleID').value == 1) reqFormFld('" . $nIDtxt . "');\n";
                    }
                    
                } elseif ($curr->nodeType == 'Feet Inches') {
                    
                    $this->pageFldList[] = 'n' . $nIDtxt . 'fldFeetID'; 
                    $this->pageFldList[] = 'n' . $nIDtxt . 'fldInchID';
                    $feet = ($currNodeSessData > 0) ? floor($currNodeSessData/12) : 0; 
                    $inch = ($currNodeSessData > 0) ? intVal($currNodeSessData)%12 : 0;
                    $ret .= view('vendor.survloop.formtree-form-feetinch', [
                        "nID"              => $nIDtxt,
                        "feet"             => $feet,
                        "inch"             => $inch,
                        "isOneLinerFld"    => $isOneLinerFld,
                        "currNodeSessData" => $currNodeSessData,
                        "inputMobileCls"   => $this->inputMobileCls($nID),
                        "nodePrompt"       => str_replace('<label for="n' . $nIDtxt . 'FldID">', 
                            '<label for="n' . $nIDtxt . 'fldFeetID"><label for="n' . $nIDtxt . 'fldInchID">', 
                            str_replace('</label>', '</label></label>', $nodePrompt))
                    ])->render();
                    if ($curr->isRequired()) {
                        $this->pageJSvalid .= "if (document.getElementById('n" . $nIDtxt 
                            . "VisibleID').value == 1) formRequireFeetInches('" . $nIDtxt . "');\n";
                    }
                    
                } elseif (in_array($curr->nodeType, ['Gender', 'Gender Not Sure'])) {
                    
                    $currSessDataOther = $this->sessData->currSessData($nID, $tbl, $fld . 'Other');
                    $coreResponses = [ ["F", "Female"], ["M", "Male"], ["O", "Other: "] ];
                    if ($curr->nodeType == 'Gender Not Sure') $coreResponses[] = ["?", "Not Sure"];
                    foreach ($coreResponses as $j => $res) $this->pageFldList[] = 'n' . $nIDtxt . 'fld' . $j;
                    $ret .= view('vendor.survloop.formtree-form-gender', [
                        "nID"               => $nIDtxt,
                        "nodeRow"           => $curr->nodeRow,
                        "isOneLinerFld"     => $isOneLinerFld,
                        "coreResponses"     => $coreResponses,
                        "currNodeSessData"  => $currNodeSessData,
                        "currSessDataOther" => $currSessDataOther
                    ])->render();
                    $genderSuggest = '';
                    foreach ($GLOBALS["SL"]->getDefSet('Gender Identity') as $i => $gen) {
                        if (!in_array($gen->DefValue, ['Female', 'Male', 'Other', 'Not sure'])) {
                            $genderSuggest .= ', "' . $gen->DefValue . '"';
                        }
                    }
                    $this->v["needsJqUi"] = true;
                    $this->pageAJAX .= '$( "#n' . $nIDtxt . 'fldOtherID" ).autocomplete({ source: [' 
                        . substr($genderSuggest, 1) . '] });' . "\n";
                    $this->pageJSextra .= 'nodeResTot[' . $nID . '] = ' . sizeof($coreResponses) . ';' . "\n";
                    if ($curr->isRequired()) {
                        $this->pageJSvalid .= "if (document.getElementById('n" . $nIDtxt 
                            . "VisibleID').value == 1) formRequireGender(" . $nIDtxt . ");\n";
                    }
                    
                } elseif ($curr->nodeType == 'Uploads') {
                    
                    $this->pageHasUpload[] = $nID;
                    $ret .= $nodePrompt . '<div class="nFld">' . $this->uploadTool($nID) . '</div>';
                    
                } else { // instruction only
                    
                    $ret .= "\n" . $nodePrompt . "\n";
                    
                } // end all node input field types
                
                
            } // end default Node printer
            
            if (trim($promptNotesSpecial) != '') {
                $ret .= $this->printSpecial($nID, $promptNotesSpecial, $currNodeSessData);
            }
            
        } // end non-Layout node processing

        if ($GLOBALS["SL"]->treeRow->TreeType != 'Page' 
            && !$curr->isLoopRoot() && !$curr->isLoopCycle() && !$curr->isPage() && !$curr->isDataManip()) {
            $ret .= '<div class="nodeHalfGap"></div>';
        }
        
        $retKids = '';
        if (sizeof($tmpSubTier[1]) > 0 && !$curr->isLoopRoot() && !$curr->isLoopCycle()) {
            foreach ($tmpSubTier[1] as $childNode) { // recurse deez!..
                if (!$this->allNodes[$childNode[0]]->isPage()) {
                    $retKids .= $this->printNodePublic($childNode[0], $childNode, $currVisib);
                }
            } 
        }
        if ($curr->nodeType == 'Layout Row') {
                $retKids = '<div class="row">' . $retKids . '</div>';
        }
        if (trim($retKids) != '' && $curr->hasShowKids) { // then displaying children on page is conditional
            $ret .= "\n" . '<div id="node' . $nIDtxt . 'kids" class="nKids" style="display: ' 
                . (($showKidsResponded) ? 'block' : 'none') . ';">' . $retKids . '</div>' . "\n";
        } else {
            $ret .= $retKids;
        }
        
        if ($curr->nodeRow->NodeOpts%37 == 0) $ret .= '</div> <!-- end jumbotron -->' . "\n";
        $ret .= "\n" . '</div> <!-- end #node' . $nIDtxt . ' -->' . "\n";
        if ($curr->isPage() || $curr->isLoopRoot()) $ret .= '<div class="pageBotGap"></div>';
        
        if ($curr->nodeType == 'Layout Column') {
            $ret .= '</div>';
        }
        
        if (substr($curr->nodeType, 0, 11) == 'Data Manip:') $this->closeManipBranch($nID);
        
        if ($curr->isPage() || $curr->isLoopRoot()) { // then wrap completed page in form
            if (intVal($curr->nodeRow->NodeCharLimit) > 0) {
                $this->pageJSextra .= 'setTimeout("focusNodeID(' . $curr->nodeRow->NodeCharLimit 
                    . ').focus()", 100);' . "\n";
            } elseif (trim($this->page1stVisib) != '' && intVal($curr->nodeRow->NodeCharLimit) == 0) {
                $this->pageJSextra .= 'setTimeout("document.getElementById(\'' 
                    . $this->page1stVisib . '\')' . '.focus()", 100);' . "\n";
            }
            $ret = $this->printNodePublicFormStart($nID) . $ret . '
            <div id="pageBtns"><div id="formErrorMsg"></div>
                ' . $this->nodePrintButton($nID, $tmpSubTier, $promptNotesSpecial) . '
            </div>
            ' . $this->printNodePublicFormEnd($nID, $promptNotesSpecial)
            . $this->pageJSnode; // extra JS/HTML/CSS tagged on the end of specific nodes
        }
        return $ret;
    }
    
    protected function isCurrDataSelected($currNodeSessData, $value, $nodeType = 'Text')
    {
        $selected = false;
        $resValCyc = trim($this->currLoopCycle[1]) . $value;
        if (is_array($currNodeSessData)) {
            $selected = (in_array($value, $currNodeSessData) || in_array($resValCyc, $currNodeSessData));
        } else {
            if ($nodeType == 'Checkbox') {
                $selected = (strpos(';' . $currNodeSessData . ';', ';' . $value . ';') !== false 
                    || strpos(';' . $currNodeSessData . ';', ';' . $resValCyc . ';') !== false);
            } else {
                $selected = ($currNodeSessData == $value || $currNodeSessData == $resValCyc);
            }
        }
        return $selected;
    }
    
    protected function postNodePublic($nID = -3, $tmpSubTier = array())
    {
        if (!$this->checkNodeConditions($nID)) return '';
        if (isset($this->sessData->dataSets[$GLOBALS["SL"]->coreTbl])) {
            $this->sessData->dataSets[$GLOBALS["SL"]->coreTbl][0]->update(["updated_at" => date("Y-m-d H:i:s")]);
        }
        $this->allNodes[$nID]->fillNodeRow($nID);
        $curr = $this->allNodes[$nID];
        if ($curr->isLoopSort()) { // actual storage happens with with each change /loopSort/
            $list = '';
            $loop = str_replace('LoopItems::', '', $curr->nodeRow->NodeResponseSet);
            $loopCycle = $this->sessData->getLoopRows($loop);
            if ($loopCycle && sizeof($loopCycle) > 0) {
                foreach ($loopCycle as $i => $loopItem) $list .= ','.$loopItem->getKey();
            }
            $this->sessData->logDataSave($nID, $loop, -3, 'Sorting ' . $loop . ' Items', $list);
            return '';
        }
        $ret = '';
        if (sizeof($tmpSubTier) == 0) {
            $tmpSubTier = $this->loadNodeSubTier($nID);
            // then we're at the page's root, so let's check this once
            if ($GLOBALS["SL"]->REQ->has('delItem') && sizeof($GLOBALS["SL"]->REQ->input('delItem')) > 0) {
                foreach ($GLOBALS["SL"]->REQ->input('delItem') as $delID) {
                    $loopTable = $GLOBALS["SL"]->closestLoop["obj"]->DataLoopTable;
                    $this->sessData->deleteDataItem($GLOBALS["SL"]->REQ->node, $loopTable, $delID);
                }
            }
        }
        
        if (substr($curr->nodeType, 0, 11) == 'Data Manip:') $this->loadManipBranch($nID);
        $hasParentDataManip = $this->hasParentDataManip($nID);
        
        if (!$this->postNodePublicCustom($nID, $tmpSubTier)) { // then run standard post
            if ($GLOBALS["SL"]->REQ->has('loop')) {
                $this->settingTheLoop(trim($GLOBALS["SL"]->REQ->input('loop')), intVal($GLOBALS["SL"]->REQ->loopItem));
            }
            if ($curr->nodeType == 'Uploads') {
                $ret .= $this->postUploadTool($nID);
            } elseif ($curr->isDataManip()) {
                if ($GLOBALS["SL"]->REQ->has('dataManip' . $nID . '') 
                    && intVal($GLOBALS["SL"]->REQ->input('dataManip' . $nID . '')) == 1) {
                    if ($GLOBALS["SL"]->REQ->has('n' . $nID . 'Visible') 
                        && intVal($GLOBALS["SL"]->REQ->input('n' . $nID . 'Visible')) == 1) {
                        $this->runDataManip($nID);
                    } else {
                        $this->reverseDataManip($nID);
                    }
                }
            } elseif (strpos($curr->dataStore, ':') !== false) {
                list($tbl, $fld) = $curr->getTblFld();
                if ($GLOBALS["SL"]->REQ->has('loopItem') && intVal($GLOBALS["SL"]->REQ->loopItem) == -37) {
                    // signal from previous form to start a new row in the current set
                    $this->newLoopItem($nID);
                    //$this->updateCurrNode($this->nextNode($this->currNode()));
                } elseif (!$curr->isInstruct() && $tbl != '' && $fld != '') {
                    $newVal = (($GLOBALS["SL"]->REQ->has('n' . $nID . 'fld')) 
                        ? $GLOBALS["SL"]->REQ->input('n' . $nID . 'fld') : '');
                    if ($curr->nodeType == 'Checkbox' || $curr->isDropdownTagger()) {
                        $input = [];
                        if ($curr->nodeType == 'Checkbox') {
                            if ($GLOBALS["SL"]->REQ->has('n' . $nID . 'fld')) {
                                $input = $GLOBALS["SL"]->REQ->input('n' . $nID . 'fld');
                            }
                        } elseif ($GLOBALS["SL"]->REQ->has('n' . $nID . 'tagIDs')) { // $curr->isDropdownTagger()
                            $input = $this->mexplode(',', $GLOBALS["SL"]->REQ->get('n' . $nID . 'tagIDs'));
                        }
                        $this->sessData->currSessDataCheckbox($nID, $tbl, $fld, 'update', $input);
                    } else {
                        if ($curr->nodeType == 'Text:Number') {
                            if (!$GLOBALS["SL"]->REQ->has('n' . $nID . 'fld')) { $newVal = 0; }
                        } elseif (in_array($curr->nodeType, array('Date', 'Date Picker'))) {
                            $newVal = date("Y-m-d", strtotime($newVal));
                        } elseif ($curr->nodeType == 'Date Time') {
                            $newVal = date("Y-m-d", strtotime($newVal)) . ' ' . $this->postFormTimeStr($nID);
                        } elseif ($curr->nodeType == 'Password') {
                            $newVal = md5($newVal);
                        }
                        $this->sessData->currSessData($nID, $tbl, $fld, 'update', $newVal, $hasParentDataManip);
                    }
                    $fldHasOther = false;
                    foreach ($curr->responses as $j => $res) {
                        if (in_array(strtolower(strip_tags($res->NodeResValue)), ['other', 'other:']) 
                            && isset($GLOBALS["SL"]->fldOthers[$fld . 'Other'])
                            && intVal($GLOBALS["SL"]->fldOthers[$fld . 'Other']) > 0) {
                            $fldHasOther = true;
                        }
                    }
                    if ($fldHasOther && in_array($curr->nodeType, ['Checkbox', 'Radio', 'Gender', 'Gender Not Sure'])) {
                        $this->sessData->currSessData($nID, $tbl, $fld . 'Other', 'update', 
                            (($GLOBALS["SL"]->REQ->has('n' . $nID . 'fldOther')) 
                                ? $GLOBALS["SL"]->REQ->input('n' . $nID . 'fldOther') 
                                : ''), $hasParentDataManip);
                    }
                }
            }
        }
        if (sizeof($tmpSubTier[1]) > 0) {
            foreach ($tmpSubTier[1] as $childNode) {
                if (!$this->allNodes[$childNode[0]]->isPage()) {
                    $this->postNodePublic($childNode[0], $childNode);
                }
            }
        }
        
        if (substr($curr->nodeType, 0, 11) == 'Data Manip:') $this->closeManipBranch($nID);
        
        return $ret;
    }
    
    public function sortLoop(Request $request)
    {
        $ret = date("Y-m-d H:i:s");
        $this->survLoopInit($request, '');
        $this->loadTree();
        if ($request->has('n') && intVal($request->n) > 0) {
            $nID = intVal($request->n);
            if (isset($this->allNodes[$nID]) && $this->allNodes[$nID]->isLoopSort()) {
                $this->allNodes[$nID]->fillNodeRow();
                $loop = str_replace('LoopItems::', '', $this->allNodes[$nID]->nodeRow->NodeResponseSet);
                $loopTbl = $GLOBALS["SL"]->dataLoops[$loop]->DataLoopTable;
                $sortFld = str_replace($loopTbl . ':', '', $this->allNodes[$nID]->nodeRow->NodeDataStore);
                foreach ($GLOBALS["SL"]->REQ->input('item') as $i => $value) {
                    eval("\$recObj = " . $GLOBALS["SL"]->modelPath($loopTbl) . "::find(" . $value . ");");
                    $recObj->{ $sortFld } = $i;
                    $recObj->save();
                }
            }
            $ret .= ' ?-)';
        }
        return $ret;
    }
    
    public function addPromptTextRequired($currNode = array(), $nodePromptText = '')
    {
        if (!isset($currNode) || sizeof($currNode) == 0 || !isset($currNode->nodeRow->NodeOpts)) return '';
        $txt = '*required';
        if ($this->nodeHasDateRestriction($currNode->nodeRow)) {
            if ($currNode->nodeRow->NodeCharLimit < 0) $txt = '*past date required';
            elseif ($currNode->nodeRow->NodeCharLimit > 0) $txt = '*future date required';
        }
        if ($currNode->nodeRow->NodeOpts%13 == 0) {
            return $nodePromptText . '<p class="red">' . $txt . '</p>';
        } else {
            $swapPos = -1;
            $lastP = strrpos($nodePromptText, '</p>');
            $lastDiv = strrpos($nodePromptText, '</div>');
            if ($lastP > 0)       $swapPos = $lastP;
            elseif ($lastDiv > 0) $swapPos = $lastDiv;
            if ($swapPos > 0) {
                return substr($nodePromptText, 0, $swapPos) . ' <span class="red">' . $txt . '</span>' 
                    . substr($nodePromptText, $swapPos);
            }
            else {
                $lastH3 = strrpos($nodePromptText, '</h3>');
                $lastH2 = strrpos($nodePromptText, '</h2>');
                $lastH1 = strrpos($nodePromptText, '</h1>');
                if ($lastH3 > 0)  $swapPos = $lastH3;
                elseif ($lastH2 > 0)  $swapPos = $lastH2;
                elseif ($lastH1 > 0)  $swapPos = $lastH1;
                if ($swapPos > 0) {
                    return substr($nodePromptText, 0, $swapPos) 
                        . ' <small class="red">' . $txt . '</small>' 
                        . substr($nodePromptText, $swapPos);
                }
            }
            return $nodePromptText . ' <span class="red">' . $txt . '</span>';
        }
        return '';
    }
    
    public function nodeHasDateRestriction($nodeRow)
    {
        return (in_array($nodeRow->NodeType, ['Date', 'Date Picker', 'Date Time']) 
                && $nodeRow->NodeOpts%31 > 0 // Character limit means word count, if enabled
                && $nodeRow->NodeCharLimit != 0);
    }
    
    public function inputMobileCls($nID)
    {
        return (isset($this->allNodes[$nID]) && $this->allNodes[$nID]->nodeRow->NodeOpts%2 > 0) ? ' fingerTxt' : '';
    }
    
    protected function checkResponses($curr, $fldForeignTbl)
    {
        if (isset($curr->responseSet) && strpos($curr->responseSet, 'LoopItems::') !== false) {
            $loop = str_replace('LoopItems::', '', $curr->responseSet);
            $currLoopItems = $this->sessData->getLoopRows($loop);
            if ($currLoopItems && sizeof($currLoopItems) > 0) {
                foreach ($currLoopItems as $i => $row) {
                    $curr->responses[$i] = new SLNodeResponses;
                    $curr->responses[$i]->NodeResValue = $row->getKey();
                    $curr->responses[$i]->NodeResEng = $this->getLoopItemLabel($loop, $row, $i);
                }
            }
        } elseif (sizeof($curr->responses) == 0 && trim($fldForeignTbl) != '' 
            && isset($this->sessData->dataSets[$fldForeignTbl]) 
            && sizeof($this->sessData->dataSets[$fldForeignTbl]) > 0) {
            foreach ($this->sessData->dataSets[$fldForeignTbl] as $i => $row) {
                $loop = ((isset($GLOBALS["SL"]->tblLoops[$fldForeignTbl])) 
                    ? $GLOBALS["SL"]->tblLoops[$fldForeignTbl] : $fldForeignTbl);
                // what about tables with multiple loops??
                $curr->responses[$i] = new SLNodeResponses;
                $curr->responses[$i]->NodeResValue = $row->getKey();
                $curr->responses[$i]->NodeResEng = $this->getLoopItemLabel($loop, $row, $i);
            }
        }
        return $curr;
    }
    
    protected function getLoopItemLabel($loop, $itemRow = array(), $itemInd = -3)
    {
        $name = $this->getLoopItemLabelCustom($loop, $itemRow, $itemInd);
        if (trim($name) != '') return $name;
        return '';
    }
    
    protected function getLoopItemLabelCustom($loop, $itemRow = array(), $itemInd = -3)
    {
        return '';
    }
    
    protected function getLoopItemCntLabelCustom($loop, $itemInd = -3)
    {
        return -3;
    }
    
    protected function printSetLoopNav($nID, $loopName)
    {
        $this->settingTheLoop($loopName);
        if ($this->allNodes[$nID]->isStepLoop()) {
            $this->sessData->getLoopDoneItems($loopName);
            if ($this->sessData->loopItemsNextID > 0) {
                $this->loopItemsCustBtn = '<a href="javascript:;" class="fR btn btn-lg btn-primary" '
                    . 'id="nFormNextStepItem"><i class="fa fa-arrow-circle-o-right"></i> Next ' 
                    . $GLOBALS["SL"]->closestLoop["obj"]->DataLoopSingular . ' Details</a>';
                $this->pageAJAX .= '$("#nFormNextStepItem").click(function() { document.getElementById("loopItemID")'
                    . '.value="' . $this->sessData->loopItemsNextID . '"; document.getElementById("jumpToID")'
                    . '.value="-3"; document.getElementById("stepID").value="next"; return runFormSub(); });' . "\n";
            }
        }
        
        $labelFirstLet = substr(strtolower($GLOBALS["SL"]->closestLoop["obj"]->DataLoopSingular), 0, 1);
        $limitTxt = '';
        if ($GLOBALS["SL"]->closestLoop["obj"]->DataLoopMaxLimit > 0 
            && isset($this->sessData->loopItemIDs[$loopName])
            && sizeof($this->sessData->loopItemIDs[$loopName]) 
                > $GLOBALS["SL"]->closestLoop["obj"]->DataLoopWarnLimit) {
            $limitTxt .= '<div class="gry6 pT20 fPerc133">Limit of ' 
                . $GLOBALS["SL"]->closestLoop["obj"]->DataLoopMaxLimit . ' '
                . $GLOBALS["SL"]->closestLoop["obj"]->DataLoopPlural . '</div>';
        }
        $ret = '<h3 class="slGrey">';
            if ($this->allNodes[$nID]->isStepLoop()) {
                $ret .= $GLOBALS["SL"]->closestLoop["obj"]->DataLoopPlural . ' to add details for:' . "\n";
            } elseif (sizeof($this->sessData->loopItemIDs[$loopName]) == 0) {
                $ret .= '<i>No ' . strtolower($GLOBALS["SL"]->closestLoop["obj"]->DataLoopPlural) 
                    . ' added yet.</i>' . "\n";
            } else {
                /* $ret .= 'Current ' . strtolower($GLOBALS["SL"]->closestLoop["obj"]->DataLoopPlural) 
                    . ' added:' . "\n"; */
            }
        $ret .= '</h3>' . "\n";
        if (sizeof($this->sessData->loopItemIDs[$loopName]) > 0) {
            if (!$this->allNodes[$nID]->isStepLoop() && sizeof($this->sessData->loopItemIDs[$loopName]) > 10) {
                $ret .= '<div class="mTn10 mB20">' . $this->printSetLoopNavAddBtn($nID, $loopName, $labelFirstLet) 
                    . '</div>';
            }
            foreach ($this->sessData->loopItemIDs[$loopName] as $setIndex => $loopItem) {
                $tbl = $GLOBALS["SL"]->dataLoops[$loopName]->DataLoopTable;
                $ret .= $this->printSetLoopNavRow($nID, 
                    $this->sessData->getRowById($tbl, $loopItem), 
                    $setIndex
                );
            }
        }
        $this->pageAJAX .= '$(".editLoopItem").click(function() {
            var id = $(this).attr("id").replace("editLoopItem", "").replace("arrowLoopItem", "");
            document.getElementById("loopItemID").value=id;
            return runFormSub();
        });' . "\n";
        if (!$this->allNodes[$nID]->isStepLoop()) {
            $ret .= $this->printSetLoopNavAddBtn($nID, $loopName, $labelFirstLet)
                . $limitTxt . '<div class="p20"></div>' . "\n";
            $this->pageAJAX .= view('vendor.survloop.formtree-looproot-ajax', [
                "loopSize" => sizeof($this->sessData->loopItemIDs[$loopName])
            ])->render();
        }
        /* if (!$this->allNodes[$nID]->isStepLoop()) {
            $this->nextBtnOverride = 'Done Adding ' . $GLOBALS["SL"]->closestLoop["obj"]->DataLoopPlural;
        } elseif (sizeof($this->sessData->loopItemIDs[$loopName]) == sizeof($this->sessData->loopItemIDsDone)) {
            $this->nextBtnOverride = 'Done With ' . $GLOBALS["SL"]->closestLoop["obj"]->DataLoopPlural;
        } */
        return $ret;
    }
    
    protected function printSetLoopNavAddBtn($nID, $loopName, $labelFirstLet)
    {
        return '<button type="button" id="nFormAdd" class="btn btn-lg btn-default mT20 w100 fPerc133 '
            . (($GLOBALS["SL"]->closestLoop["obj"]->DataLoopMaxLimit == 0 || 
                sizeof($this->sessData->loopItemIDs[$loopName]) < $GLOBALS["SL"]->closestLoop["obj"]->DataLoopMaxLimit) 
                ? 'disBlo' : 'disNon')
            . '"><i class="fa fa-plus-circle"></i> Add ' . ((sizeof($this->sessData->loopItemIDs[$loopName]) == 0) 
                ? 'a'.((in_array($labelFirstLet, array('a', 'e', 'i', 'o', 'u'))) ? 'n' : '') : 'another') . ' ' 
            . strtolower($GLOBALS["SL"]->closestLoop["obj"]->DataLoopSingular) . '</button>' ;
    }
    
    protected function printSetLoopNavRowCustom($nID, $loopItem, $setIndex)
    {
        return '';
    }
    
    protected function printSetLoopNavRow($nID, $loopItem, $setIndex)
    {
        $ret = $this->printSetLoopNavRowCustom($nID, $loopItem, $setIndex);
        if ($ret != '') return $ret;
        $itemLabel = $this->getLoopItemLabel($GLOBALS["SL"]->closestLoop["loop"], $loopItem, $setIndex);
        $ico = '';
        if ($this->allNodes[$nID]->isStepLoop()) {
            if ($this->sessData->loopItemsNextID > 0 && $this->sessData->loopItemsNextID == $loopItem->getKey()) {
                $ico = '<i class="fa fa-arrow-circle-o-right"></i>';
            } elseif (in_array($loopItem->getKey(), $this->sessData->loopItemIDsDone)) {
                $ico = '<i class="fa fa-check"></i>';
            } else {
                $ico = '<i class="fa fa-check gryA opac10"></i>';
            }
        }
        return view('vendor.survloop.formtree-looproot-row', [
            "nID"            => $nID,
            "setIndex"       => $setIndex,
            "itemID"         => $loopItem->getKey(),
            "itemLabel"      => $itemLabel,
            "ico"            => $ico, 
            "node"           => $this->allNodes[$nID]
        ])->render();
    }
    
    protected function isNodeJustH1($nodePrompt)
    {
        return (substr($nodePrompt, 0, 3) == '<h1' && substr($nodePrompt, strlen($nodePrompt)-5) == '</h1>');
    }
    
    protected function cleanDateVal($dateStr)
    {
        if ($dateStr == '0000-00-00' || $dateStr == '1970-01-01' || trim($dateStr) == '') return '';
        return $dateStr;
    }
    
    protected function formDate($nID, $dateStr = '00/00/0000')
    {
        list($month, $day, $year) = array('', '', '');
        if (trim($dateStr) != '') {
            list($month, $day, $year) = explode('/', $dateStr);
            if (intVal($month) == 0 || intVal($day) == 0 || intVal($year) == 0) {
                list($month, $day, $year) = array('', '', '');
            }
        }
        return view('vendor.survloop.formtree-form-date', [
            "nID"            => $nID,
            "dateStr"        => $dateStr,
            "month"          => $month,
            "day"            => $day,
            "year"           => $year,
            "inputMobileCls" => $this->inputMobileCls($nID)
        ])->render();
    }
    
    protected function formTime($nID, $timeStr = '00:00:00')
    {
        $timeArr = explode(':', $timeStr); 
        foreach ($timeArr as $i => $t) $timeArr[$i] = intVal($timeArr[$i]);
        if (!isset($timeArr[0])) $timeArr[0] = 0; if (!isset($timeArr[1])) $timeArr[1] = 0;
        $timeArr[3] = 'AM';
        if ($timeArr[0] > 11) {
            $timeArr[3] = 'PM'; 
            if ($timeArr[0] > 12) $timeArr[0] = $timeArr[0]-12;
        }
        if ($timeArr[0] == 0 && $timeArr[1] == 0) {
            $timeArr[0] = -1; 
            $timeArr[1] = 0; 
        }
        return view('vendor.survloop.formtree-form-time', [
            "nID"            => $nID,
            "timeArr"        => $timeArr,
            "inputMobileCls" => $this->inputMobileCls($nID)
        ])->render();
    }
    
    protected function postFormTimeStr($nID)
    {
        $hr = intVal($GLOBALS["SL"]->REQ->input('n' . $nID . 'fldHr'));
        if ($GLOBALS["SL"]->REQ->input('n' . $nID . 'fldPM') == 'PM' && $hr < 12) $hr += 12;
        $min = intVal($GLOBALS["SL"]->REQ->input('n' . $nID . 'fldMin'));
        return ((intVal($hr) < 10) ? '0' : '') . $hr . ':' . ((intVal($min) < 10) ? '0' : '') . $min . ':00';
    }
    
    
    protected function printValList($val)
    {
        return str_replace(';;', ', ', $val);
    }
    
    protected function printYN($val)
    {
        if ($val == 'Y') return 'Yes';
        if ($val == 'N') return 'No';
        if ($val == '?') return 'Not sure';
    }
    
    protected function printMF($val)
    {
        if ($val == 'M') return 'Male';
        if ($val == 'F') return 'Female';
        if ($val == '?') return 'Not sure';
    }
    
    protected function printHeight($val)
    {
        return (floor($val/12)) . "' " . floor($val%12) . '"';
    }
    
    public function chkEmail()
    {
        $ret = '';
        if ($GLOBALS["SL"]->REQ->has('email') && trim($GLOBALS["SL"]->REQ->email) != '') {
            $chk = User::where('email', 'LIKE', $GLOBALS["SL"]->REQ->email)->get();
            if ($chk && sizeof($chk) > 0) {
                $ret .= 'found';
            }
        }
        return $ret;
    }
    
    public function limitWordCount($str, $wordLimit)
    {
        $ret = '';
        $words = $this->mexplode(' ', $str);
        if (sizeof($words) > 0) {
            foreach ($words as $i => $w) {
                if ($i < $wordLimit) $ret .= ' ' . $w;
            }
        }
        return trim($ret);
    }
    
    public function sortableStart($nID)
    {
        return '';
    }
    
    public function sortableEnd($nID)
    {
        return '';
    }
    
} // end of SurvFormTree class
