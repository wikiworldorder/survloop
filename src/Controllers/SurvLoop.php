<?php
/**
  * SurvLoop is a core class for routing system access, particularly for loading a
  * client installation's customized extension of TreeSurvForm instead of the default.
  *
  * SurvLoop - All Our Data Are Belong
  * @package  wikiworldorder/survloop
  * @author  Morgan Lesko <wikiworldorder@protonmail.com>
  * @since 0.0
  */
namespace SurvLoop\Controllers;

use Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use Symfony\Component\HttpFoundation\File\File;
use App\Models\User;
use App\Models\SLTree;
use SurvLoop\Controllers\Globals;
use SurvLoop\Controllers\PageLoadUtils;
use SurvLoop\Controllers\SurvLoopInstaller;

class SurvLoop extends PageLoadUtils
{
    // This is where the client installation's extension of TreeSurvForm is loaded
    public $custLoop       = null;
    
    protected function isAdmin()
    {
        return (Auth::user() && Auth::user()->hasRole('administrator'));
    }
    
    public function loadLoop(Request $request, $skipSessLoad = false)
    {
        $this->loadAbbr();
        $class = "SurvLoop\\Controllers\\TreeSurvForm";
        if ($this->custAbbr != 'SurvLoop') {
            $custClass = $this->custAbbr . "\\Controllers\\" . $this->custAbbr . "";
            if (class_exists($custClass)) $class = $custClass;
        }
        eval("\$this->custLoop = new " . $class . "(\$request, -3, " . $this->dbID . ", " 
            . $this->treeID . ", " . (($skipSessLoad) ? "true" : "false") . ");");
        return true;
    }
    
    /**
     * Get a validator for an incoming registration request.
     *
     * @param  array  $data
     * @return \Illuminate\Contracts\Validation\Validator
     */
    public function mainSub(Request $request, $type = '', $val = '')
    {
        if ($request->has('step') && $request->has('tree') && intVal($request->get('tree')) > 0) {
            $this->loadTreeByID($request, $request->tree);
        }
        $this->loadLoop($request);
        return $this->custLoop->index($request, $type, $val);
    }
    
    public function processEmailConfirmToken(Request $request, $token = '', $tokenB = '')
    { 
        $this->loadLoop($request);
        return $this->custLoop->processEmailConfirmToken($request, $token, $tokenB);
    }
    
    public function loadNodeURL(Request $request, $treeSlug = '', $nodeSlug = '')
    {
        if ($this->loadTreeBySlug($request, $treeSlug)) {
            $this->loadLoop($request);
            return $this->custLoop->loadNodeURL($request, $nodeSlug);
        }
        $this->loadDomain();
        return redirect($this->domainPath . '/');
    }
    
    public function loadPageURL(Request $request, $pageSlug = '', $cid = -3, $view = '', $skipPublic = false)
    {
        $redir = $this->chkPageRedir($pageSlug);
//echo '<br /><br /><br />pageSlug: ' .$pageSlug . ', redir: ' . $redir . '<br />'; exit;
        if ($redir != $pageSlug) {
            redirect($redir);
        }
        if ($this->loadTreeBySlug($request, $pageSlug, 'Page')) {
            if ($request->has('edit') && intVal($request->get('edit')) == 1 && $this->isUserAdmin()) {
                echo '<script type="text/javascript"> window.location="/dashboard/page/' 
                    . $this->treeID . '?all=1&alt=1&refresh=1"; </script>';
                exit;
            }
            $this->loadLoop($request);
            if ($cid > 0) {
                $this->custLoop->loadSessionData($GLOBALS["SL"]->coreTbl, $cid, $skipPublic);
                if ($request->has('hideDisclaim') && intVal($request->hideDisclaim) == 1) {
                    $this->custLoop->hideDisclaim = true;
                }
                $GLOBALS["SL"]->x["pageSlugSffx"] = '/read-' . $cid;
                $GLOBALS["SL"]->x["pageView"] = trim($view); // blank results in user default
                if ($GLOBALS["SL"]->x["pageView"] != '') {
                    $GLOBALS["SL"]->x["pageSlugSffx"] .= '/' . $GLOBALS["SL"]->x["pageView"];
                }
            }
            $this->pageContent = $this->custLoop->index($request);
            if ($GLOBALS["SL"]->treeRow->TreeOpts%Globals::TREEOPT_NOCACHE > 0 && $cid <= 0) {
                $this->topSaveCache();
            }
            return $this->addAdmCodeToPage($GLOBALS["SL"]->swapSessMsg($this->pageContent));
        }
        $this->loadDomain();
        return redirect($this->domainPath . '/');
    }
    
    public function loadPageHome(Request $request)
    {
        if ($this->topCheckCache($request) && (!$request->has('edit') || intVal($request->get('edit')) != 1 
            || !$this->isUserAdmin())) {
            return $this->addAdmCodeToPage($GLOBALS["SL"]->swapSessMsg($this->pageContent));
        }
        $this->loadDomain();
        $this->checkHttpsDomain($request);
        $tree = SLTree::where('TreeType', 'Page')
            ->whereRaw("TreeOpts%" . Globals::TREEOPT_HOMEPAGE . " = 0")
            ->whereRaw("TreeOpts%" . Globals::TREEOPT_ADMIN . " > 0")
            ->whereRaw("TreeOpts%" . Globals::TREEOPT_STAFF . " > 0")
            ->whereRaw("TreeOpts%" . Globals::TREEOPT_PARTNER . " > 0")
            ->whereRaw("TreeOpts%" . Globals::TREEOPT_VOLUNTEER . " > 0")
            ->orderBy('TreeID', 'asc')
            ->first();
        if ($tree && isset($tree->TreeID)) {
            $redir = $this->chkPageRedir($tree->TreeSlug);
            if ($redir != $tree->TreeSlug) return redirect($redir);
            if ($request->has('edit') && intVal($request->get('edit')) == 1 && $this->isUserAdmin()) {
                echo '<script type="text/javascript"> window.location="/dashboard/page/' 
                    . $tree->TreeID . '?all=1&alt=1&refresh=1"; </script>';
                exit;
            }
            $this->syncDataTrees($request, $tree->TreeDatabase, $tree->TreeID);
            $this->loadLoop($request);
            $this->pageContent = $this->custLoop->index($request);
            if ($tree->TreeOpts%Globals::TREEOPT_NOCACHE > 0) {
                $this->topSaveCache();
            }
            return $this->addAdmCodeToPage($GLOBALS["SL"]->swapSessMsg($this->pageContent));
        }
        
        // else Home Page not found, so let's create one
        $this->syncDataTrees($request);
        $installer = new SurvLoopInstaller;
        $installer->checkSysInit();
        return '<center><br /><br /><i>Reloading...</i><br /> <iframe src="/dashboard/css-reload" frameborder=0
            style="width: 60px; height: 60px; border: 0px none;"></iframe></center>
            <script type="text/javascript"> setTimeout("window.location=\'/\'", 2000); </script>';
    }
    
    public function testRun(Request $request)
    {
        $this->loadLoop($request);
        return $this->custLoop->testRun($request);
    }
    
    public function ajaxChecks(Request $request, $type = '')
    {
        $this->loadLoop($request);
        return $this->custLoop->ajaxChecks($request, $type);
    }
    
    public function sortLoop(Request $request)
    {
        $this->loadLoop($request);
        return $this->custLoop->sortLoop($request);
    }
    
    public function showProfile(Request $request, $uname = '')
    {
        $tree = SLTree::where('TreeType', 'Page')
            ->whereRaw("TreeOpts%" . Globals::TREEOPT_PROFILE . " = 0")
            ->orderBy('TreeID', 'asc')
            ->first();
        if ($tree && isset($tree->TreeID)) {
            $this->syncDataTrees($request, $tree->TreeDatabase, $tree->TreeID);
            $this->loadLoop($request);
            $this->custLoop->setCurrUserProfile($uname);
            return $this->custLoop->index($request);
        }
        $this->loadDomain();
        return redirect($this->domainPath . '/');
    }

    public function showMyProfile(Request $request)
    {
        $this->loadDomain();
        $this->checkHttpsDomain($request);
        if (Auth::user() && isset(Auth::user()->name)) {
            return $this->showProfile($request);
            //return redirect($this->domainPath . '/profile/' . urlencode(Auth::user()->name));
        }
        return redirect($this->domainPath . '/');
    }
    
    public function holdSess(Request $request)
    {
        $this->loadLoop($request);
        return $this->custLoop->holdSess($request);
    }
    
    public function restartSess(Request $request)  
    {
        $this->loadLoop($request);
        return $this->custLoop->restartSess($request);
    }
    
    public function sessDump(Request $request)
    {
        $this->loadLoop($request);
        return $this->custLoop->sessDump();
    }
    
    public function switchSess(Request $request, $treeID, $cid)
    {
        $this->loadTreeByID($request, $treeID);
        $this->loadLoop($request);
        return $this->custLoop->switchSess($request, $cid);
    }
    
    public function delSess(Request $request, $treeID, $cid)
    {
        $this->loadTreeByID($request, $treeID);
        $this->loadLoop($request);
        return $this->custLoop->delSess($request, $cid);
    }
    
    public function cpySess(Request $request, $treeID, $cid)
    {
        $this->loadTreeByID($request, $treeID);
        $this->loadLoop($request);
        return $this->custLoop->cpySess($request, $cid);
    }
    
    public function afterLogin(Request $request)
    {
        if (session()->has('redir2') && trim(session()->get('redir2')) != '') {
            return redirect(trim(session()->get('redir2')));
        }
        if (session()->has('lastTree')) {
            $tree = SLTree::find(session()->get('lastTree'));
            if ($tree && isset($tree->TreeDatabase)) {
                $this->syncDataTrees($request, $tree->TreeDatabase, $tree->TreeID);
            }
        }
        if (session()->has('sessID') && session()->get('sessID') > 0) {
            
        }
        $this->loadLoop($request);
        return $this->custLoop->afterLogin($request);
    }
    
    public function retrieveUpload(Request $request, $treeSlug = '', $cid = -3, $upID = '')
    {
        if ($this->loadTreeBySlug($request, $treeSlug)) {
            $this->loadLoop($request);
            return $this->custLoop->retrieveUpload($request, $cid, $upID);
        }
        $this->loadDomain();
        return redirect($this->domainPath . '/');
    }
    
    public function byID(Request $request, $treeSlug, $cid, $coreSlug = '')
    {
        if ($this->loadTreeBySlug($request, $treeSlug)) {
            $this->loadLoop($request);
            return $this->custLoop->byID($request, $cid, $coreSlug, $request->has('ajax'));
        }
        $this->loadDomain();
        return redirect($this->domainPath . '/');
    }
    
    public function fullByID(Request $request, $treeSlug, $cid, $coreSlug = '')
    {
        $GLOBALS["fullAccess"] = true;
        return $this->byID($request, $treeSlug, $cid, $coreSlug = '');
    }
    
    public function pdfByID(Request $request, $treeSlug, $cid)
    {
        $GLOBALS["SL"]->x["isPrintPDF"] = true;
        return $this->byID($request, $treeSlug, $cid);
    }
    
    public function fullPdfByID(Request $request, $treeSlug, $cid)
    {
        $GLOBALS["fullAccess"] = true;
        return $this->pdfByID($request, $treeSlug, $cid);
    }
    
    public function fullXmlByID(Request $request, $treeSlug, $cid)
    {
        $GLOBALS["fullAccess"] = true;
        return $this->xmlByID($request, $treeSlug, $cid);
    }
    
    public function tokenByID(Request $request, $pageSlug, $cid, $token)
    {
        return $this->loadPageURL($request, $pageSlug, $cid, 'token-' . trim($token));
        //return $this->byID($request, $treeSlug, $cid);
    }
    
    public function xmlAll(Request $request, $treeSlug = '')
    {
        if ($this->loadTreeBySlug($request, $treeSlug)) {
            $this->loadLoop($request);
            return $this->custLoop->xmlAll($request);
        }
        $this->loadDomain();
        return redirect($this->domainPath . '/');
    }
    
    public function xmlByID(Request $request, $treeSlug, $cid)
    {
        if ($this->loadTreeBySlug($request, $treeSlug)) {
            $this->loadLoop($request);
            return $this->custLoop->xmlByID($request, $cid);
        }
        $this->loadDomain();
        return redirect($this->domainPath . '/');
    }
    
    public function getXmlExample(Request $request, $treeSlug = '')
    {
        if ($this->loadTreeBySlug($request, $treeSlug)) {
            $this->loadLoop($request);
            return $this->custLoop->getXmlExample($request);
        }
        $this->loadDomain();
        return redirect($this->domainPath . '/');
    }
    
    public function genXmlSchema(Request $request, $treeSlug = '')
    {
        if ($this->loadTreeBySlug($request, $treeSlug)) {
            $this->loadLoop($request);
            return $this->custLoop->genXmlSchema($request);
        }
        $this->loadDomain();
        return redirect($this->domainPath . '/');
    }
    
    public function chkEmail(Request $request)
    {
        $this->loadLoop($request);
        return $this->custLoop->chkEmail($request);
    }
    
    
    public function freshUser(Request $request)
    {
        $this->loadLoop($request);
        return $this->custLoop->freshUser($request);
    }
    
    public function freshDB(Request $request)
    {
        $this->loadLoop($request);
        return $this->custLoop->freshDB($request);
    }
    
    // SurvLoop Widgets
    
    public function ajaxMultiRecordCheck(Request $request, $treeID = 1)
    {
        $this->loadTreeByID($request, $treeID);
        $this->loadLoop($request, true);
        return $this->custLoop->multiRecordCheck(true);
    }
    
    public function ajaxRecordFulls(Request $request, $treeID = 1)
    {
        $this->loadTreeByID($request, $treeID);
        $this->loadLoop($request, true);
        return $this->custLoop->printReports($request);
    }
    
    public function ajaxRecordPreviews(Request $request, $treeID = 1)
    {
        $this->loadTreeByID($request, $treeID);
        $this->loadLoop($request, true);
        return $this->custLoop->printReports($request, false);
    }
    
    public function ajaxEmojiTag(Request $request, $treeID = 1, $recID = -3, $defID = -3)
    {
        $this->loadTreeByID($request, $treeID);
        $this->loadLoop($request, true);
        return $this->custLoop->ajaxEmojiTag($request, $recID, $defID);
    }
    
    public function ajaxGraph(Request $request, $gType = '', $treeID = 1, $nID = -3)
    {
        $this->loadTreeByID($request, $treeID);
        $this->loadLoop($request, true);
        return $this->custLoop->ajaxGraph($request, $gType, $nID);
    }
    
    public function searchBar(Request $request, $treeID = 1)
    {
        $this->loadTreeByID($request, $treeID);
        $this->loadLoop($request, true);
        $this->custLoop->initSearcher();
        return $this->custLoop->searcher->searchBar();
    }
    
    public function searchResults(Request $request, $treeID = 1, $ajax = 0)
    {
        $this->loadTreeByID($request, $treeID, true);
        $this->loadLoop($request, true);
        $this->custLoop->initSearcher();
        return $this->custLoop->searcher->searchResults($request, $ajax);
    }
    
    public function searchResultsAjax(Request $request, $treeID = 1)
    {
        return $this->searchResults($request, $treeID, 1);
    }
    
    public function widgetCust(Request $request, $treeID = 1, $nID = -3)
    {
        $this->loadTreeByID($request, $treeID);
        $this->loadLoop($request, true);
        return $this->custLoop->widgetCust($request, $nID);
    }
    
    public function getSetFlds(Request $request, $treeID = 1, $rSet = '')
    {
        $this->loadTreeByID($request, $treeID);
        $this->loadLoop($request, true);
        return $this->custLoop->getSetFlds($request, $rSet);
    }
    
    public function getUploadFile(Request $request, $abbr, $file)
    {
        $filename = '../storage/app/up/' . $abbr . '/' . $file;
        $handler = new File($filename);
        $file_time = $handler->getMTime(); // Get the last modified time for the file (Unix timestamp)
        $lifetime = 86400; // One day in seconds
        $header_etag = md5($file_time . $filename);
        $header_last_modified = gmdate('r', $file_time);
        $headers = array(
            'Content-Disposition' => 'inline; filename="' . $filename . '"',
            'Last-Modified'       => $header_last_modified,
            'Cache-Control'       => 'must-revalidate',
            'Expires'             => gmdate('r', $file_time + $lifetime),
            'Pragma'              => 'public',
            'Etag'                => $header_etag
        );
        
        // Is the resource cached?
        $h1 = (isset($_SERVER['HTTP_IF_MODIFIED_SINCE']) 
            && $_SERVER['HTTP_IF_MODIFIED_SINCE'] == $header_last_modified);
        $h2 = (isset($_SERVER['HTTP_IF_NONE_MATCH']) 
            && str_replace('"', '', stripslashes($_SERVER['HTTP_IF_NONE_MATCH'])) == $header_etag);
        if (($h1 || $h2) && !$request->has('refresh')) {
            return Response::make('', 304, $headers); 
        }
        // File (image) is cached by the browser, so we don't have to send it again
        
        $headers = array_merge($headers, [
            'Content-Type'   => $handler->getMimeType(),
            'Content-Length' => $handler->getSize()
        ]);
        return Response::make(file_get_contents($filename), 200, $headers);
    }
    
    public function jsLoadMenu(Request $request)
    {
        $ret = '';
        if (Auth::user() && isset(Auth::user()->id)) {
            $userName = Auth::user()->name;
            if (strpos($userName, 'Session#') !== false) {
                $userName = substr(Auth::user()->email, 0, strpos(Auth::user()->email, '@'));
            }
            $ret .= "addTopNavItem('" . $userName . "', '/my-profile\" id=\"loginLnk'); ";
            $ret .= 'addSideNavItem("Logout", "/logout"); addSideNavItem("My Profile", "/my-profile"); ';
            if (Auth::user()->hasRole('administrator')) {
                $ret .= 'addTopNavItem("Dashboard", "/dashboard"); '
                    . 'addSideNavItem("Admin Dashboard", "/dashboard"); ';
            }
        } else {
            $ret .= "addTopNavItem('Sign Up', '/register\" id=\"loginLnk'); addTopNavItem('Login', '/login'); "
                . 'addSideNavItem("Login", "/login"); addSideNavItem("Sign Up", "/register"); ';
        }
        return '<script type="text/javascript"> ' . $ret . ' </script>';
    }
    
    public function timeOut(Request $request)
    {
        return view('auth.dialog-check-form-sess', [ "req" => $request ]);
    }
    
    public function getJsonSurvStats(Request $request)
    {
        $this->syncDataTrees($request);
        $this->loadLoop($request);
        header('Content-Type: application/json');
        $stats = $GLOBALS["SL"]->getJsonSurvStats();
    	$stats["Survey1Complete"] = sizeof($this->custLoop->getAllPublicCoreIDs());
        echo json_encode($stats);
        exit;
    }
    
}