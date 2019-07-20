<?php
/**
  * PageLoadUtils assists the SurvLoop-level routing processes in SurvRoutes and AdminController.
  *
  * SurvLoop - All Our Data Are Belong
  * @package  wikiworldorder/survloop
  * @author  Morgan Lesko <wikiworldorder@protonmail.com>
  * @since 0.0
  */
namespace SurvLoop\Controllers;

use DB;
use Auth;
use Storage;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use App\Models\User;
use App\Models\SLNode;
use App\Models\SLTree;
use App\Models\SLDefinitions;
use App\Models\SLSess;
use SurvLoop\Controllers\Globals\Globals;

class PageLoadUtils extends Controller
{
    protected $isAdminPage = false;
    public $dashPrfx       = '';
    public $domainPath     = 'http://homestead.test';
    public $custAbbr       = 'SurvLoop';
    public $dbID           = 1;
    public $treeID         = 1;
    public $cacheKey       = '';
    public $pageContent    = '';
    
    public function __construct($isAdminPage = false)
    {
        $this->isAdminPage = $isAdminPage;
        if ($isAdminPage) {
            $this->dashPrfx = '/dash';
        }
    }
    
    public function loadDomain()
    {
        $appUrl = SLDefinitions::select('DefDescription')
            ->where('DefDatabase', 1)
            ->where('DefSet', 'System Settings')
            ->where('DefSubset', 'app-url')
            ->first();
        if ($appUrl && isset($appUrl->DefDescription)) {
            $this->domainPath = $appUrl->DefDescription;
        }
        return $this->domainPath;
    }
    
    public function checkHttpsDomain(Request $request)
    {
        if (isset($this->domainPath) && strpos($request->fullUrl(), $this->domainPath) === false) {
            if (strpos($this->domainPath, 'https://') !== false 
                && strpos($request->fullUrl(), str_replace('https://', 'http://', $this->domainPath)) !== false) {
                header("Location: " . str_replace('http://', 'https://', $request->fullUrl()));
                exit;
            }
        }
        return true;
    }
    
    public function loadAbbr()
    {
        $chk = SLDefinitions::select('DefDescription')
            ->where('DefDatabase', 1)
            ->where('DefSet', 'System Settings')
            ->where('DefSubset', 'cust-abbr')
            ->first();
        if ($chk && isset($chk->DefDescription)) {
            $this->custAbbr = trim($chk->DefDescription);
        }
        return $this->custAbbr;
    }
    
    public function syncDataTrees(Request $request, $dbID = 1, $treeID = 1)
    {
        $this->dbID = $dbID;
        $this->treeID = $treeID;
        $GLOBALS["SL"] = new Globals($request, $dbID, $treeID, $treeID);
        return true;
    }
    
    protected function userHasTreePerms($treeOpts = 1)
    {
        if ($treeOpts%Globals::TREEOPT_ADMIN == 0) {
            return $this->isUserAdmin();
        }
        if ($treeOpts%Globals::TREEOPT_STAFF == 0) {
            return ($this->isUserStaff() || $this->isUserAdmin());
        }
        if ($treeOpts%Globals::TREEOPT_PARTNER == 0) {
            return ($this->isUserPartn() || $this->isUserAdmin());
        }
        if ($treeOpts%Globals::TREEOPT_VOLUNTEER == 0) {
            return ($this->isUserVolun() || $this->isUserAdmin());
        }
        return true;
    }
    
    public function getMaxPermsPrime()
    {
        $ret = ((!Auth::user() || !isset(Auth::user()->id)) ? -1 : 0);
        if (Auth::user()) {
            if (Auth::user()->hasRole('administrator')) {
                $ret = Globals::TREEOPT_ADMIN;
            } elseif (Auth::user()->hasRole('staff|databaser|brancher')) {
                $ret = Globals::TREEOPT_STAFF;
            } elseif (Auth::user()->hasRole('partner')) {
                $ret = Globals::TREEOPT_PARTNER;
            } elseif (Auth::user()->hasRole('volunteer')) {
                $ret = Globals::TREEOPT_VOLUNTEER;
            }
        }
        return $ret;
    }
    
    public function getPermOpts()
    {
        $ret = [];
        if (Auth::user() && isset(Auth::user()->id) && intVal(Auth::user()->id) > 0) {
            if (Auth::user()->hasRole('administrator')) {
                $ret[] = Globals::TREEOPT_ADMIN;
            }
            if (Auth::user()->hasRole('administrator|staff|databaser|brancher')) {
                $ret[] = Globals::TREEOPT_STAFF;
            }
            if (Auth::user()->hasRole('administrator|staff|databaser|brancher|partner')) {
                $ret[] = Globals::TREEOPT_PARTNER;
            }
            if (Auth::user()->hasRole('administrator|staff|databaser|brancher|partner|volunteer')) {
                $ret[] = Globals::TREEOPT_VOLUNTEER;
            }
        }
        return $ret;
    }
    
    public function getPageDashPrefix($treeOpts = 1)
    {
        if ($treeOpts%Globals::TREEOPT_ADMIN == 0 || $treeOpts%Globals::TREEOPT_STAFF == 0
            || $treeOpts%Globals::TREEOPT_PARTNER == 0 || $treeOpts%Globals::TREEOPT_VOLUNTEER == 0) {
            return '/dash';
        }
        return '';
    }
    
    public function chkNoTreePerms($tree)
    {
        if (!$tree || !isset($tree->TreeOpts)) {
            return false;
        }
        return ($tree->TreeOpts%Globals::TREEOPT_ADMIN > 0 && $tree->TreeOpts%Globals::TREEOPT_STAFF > 0
            && $tree->TreeOpts%Globals::TREEOPT_PARTNER > 0 && $tree->TreeOpts%Globals::TREEOPT_VOLUNTEER > 0);
    }
    
    public function loadTreeByID(Request $request, $treeID = -3)
    {
        if (intVal($treeID) > 0) {
            $tree = SLTree::find($treeID);
            if ($tree && isset($tree->TreeOpts)) {
                if ($this->okToLoadTree($tree->TreeOpts)) {
                    $this->syncDataTrees($request, $tree->TreeDatabase, $treeID);
                    return true;
                }
            }
        }
        return false;
    }
    
    public function loadTreeBySlug(Request $request, $treeSlug = '', $type = 'Survey')
    {
        if (!$request->has('edit') || intVal($request->get('edit')) != 1 || !$this->isUserAdmin()) {
            if ($this->topCheckCache($request)) {
                return $this->addAdmCodeToPage($GLOBALS["SL"]->swapSessMsg($this->pageContent));
            }
        }
        if (trim($treeSlug) != '') {
            $urlTrees = SLTree::where('TreeType', $type)
                ->where('TreeSlug', $treeSlug)
                ->orderBy('TreeID', 'asc')
                ->get();
            if ($urlTrees->isNotEmpty()) {
                foreach ($urlTrees as $t) {
                    if ($t && isset($t->TreeOpts) && $this->okToLoadTree($t->TreeOpts)) {
                        $this->syncDataTrees($request, $t->TreeDatabase, $t->TreeID);
                        return true;
                    }
                }
            }
        }
        return false;
    }
    
    public function searchRun(Request $request)
    {
        $searchTree = null;
        if ($request->has('searchData') && is_array($request->get('searchData'))) {
            $perms = $this->getPermOpts();
            $searchDataTbl = $request->get('searchData');
            if (sizeof($searchDataTbl) == 1 && intVal($searchDataTbl[0]) > 0) {
                $trees = DB::table('SL_Tree')
                    ->join('SL_Node', function ($join) {
                        $join->on('SL_Tree.TreeID', '=', 'SL_Node.NodeTree')
                            ->where('SL_Node.NodeParentID', '<=', 0)
                            ->where('SL_Node.NodeType', 'Page');
                    })
                    ->where('SL_Tree.TreeType', 'Page')
                    ->where('SL_Tree.TreeCoreTable', intVal($searchDataTbl[0]))
                    ->select('SL_Tree.*', 'SL_Node.NodeResponseSet')
                    ->get();
                $searchTree = $this->chkSearchRunTrees($trees, $perms);
            }
            if ($searchTree === null) {
                $trees = SLTree::where('TreeType', 'Page')
                    ->get();
                $searchTree = $this->chkSearchRunTrees($trees, $perms);
            }
            if ($searchTree !== null && isset($searchTree->TreeOpts)) {
                $redir = $this->getPageDashPrefix($searchTree->TreeOpts) . '/' . $searchTree->TreeSlug 
                    . '?s=' . (($request->has('s')) ? $request->get('s') : '');
                if ($request->has('sFilt') && trim($request->get('sFilt')) != '') {
                    $redir .= '&sFilt=' . $request->get('sFilt');
                }
                if ($request->has('sSort') && trim($request->get('sSort')) != '') {
                    $redir .= '&sSort=' . $request->get('sSort');
                }
                if ($request->has('sSortDir') && trim($request->get('sSortDir')) != '') {
                    $redir .= '&sSortDir=' . $request->get('sSortDir');
                }
                if ($request->has('sView') && trim($request->get('sView')) != '') {
                    $redir .= '&sView=' . $request->get('sView');
                }
                return redirect($redir);
            }
        }
        $this->loadDomain();
        return redirect($this->domainPath . '/');
    }
    
    protected function chkSearchRunTrees($trees, $perms)
    {
        $searchTree = null;
        if ($trees->isNotEmpty()) {
            if (sizeof($perms) > 0) {
                foreach ($perms as $perm) {
                    if ($searchTree === null) {
                        foreach ($trees as $tree) {
                            if ($searchTree === null && $tree->TreeOpts%$perm == 0
                                && $tree->TreeOpts%Globals::TREEOPT_SEARCH == 0) {
                                $searchTree = $tree;
                            }
                        }
                    }
                }
            }
            if ($searchTree === null) {
                foreach ($trees as $tree) {
                    if ($searchTree === null && $tree->TreeOpts%Globals::TREEOPT_SEARCH == 0
                        && $tree->TreeOpts%Globals::TREEOPT_ADMIN > 0
                        && $tree->TreeOpts%Globals::TREEOPT_STAFF > 0
                        && $tree->TreeOpts%Globals::TREEOPT_PARTNER > 0
                        && $tree->TreeOpts%Globals::TREEOPT_VOLUNTEER > 0) {
                        $searchTree = $tree;
                    }
                }
            }
        }
        return $searchTree;
    }
    
    protected function chkPageRedir($treeSlug = '')
    {
        if (trim($treeSlug) != '') {
            $redirTree = SLTree::where('TreeSlug', $treeSlug)
                ->where('TreeType', 'Redirect')
                ->orderBy('TreeID', 'asc')
                ->first();
            if ($redirTree && isset($redirTree->TreeDesc) && trim($redirTree->TreeDesc) != '') {
                $redirURL = $redirTree->TreeDesc;
                if (strpos($redirURL, $this->domainPath) === false && substr($redirURL, 0, 1) != '/'
                    && strpos($redirURL, 'http://') === false && strpos($redirURL, 'https://') === false) {
                    $redirURL = '/' . $redirURL;
                }
                return $redirURL;
            }
        }
        return $treeSlug;
    }
    
    public function loadNodeTreeURL(Request $request, $treeSlug = '')
    {
        return $this->loadNodeTreeURLInner($request, $treeSlug);
    }
    
    public function loadNodeTreeURLedit(Request $request, $cid = -3, $treeSlug = '')
    {
        return $this->loadNodeTreeURLInner($request, $treeSlug, $cid);
    }
    
    public function loadNodeTreeURLInner(Request $request, $treeSlug = '', $cid = -3)
    {
        $this->loadDomain();
        $this->checkHttpsDomain($request);
        if (trim($treeSlug) != '') {
            $urlTrees = SLTree::where('TreeSlug', $treeSlug)
                ->get();
            if ($urlTrees->isNotEmpty()) {
                foreach ($urlTrees as $t) {
                    if ($t && isset($t->TreeOpts) && $this->okToLoadTree($t->TreeOpts)) {
                        $rootNode = SLNode::find($t->TreeFirstPage);
                        if ($rootNode && isset($t->TreeSlug) && isset($rootNode->NodePromptNotes)) {
                            $redir = $this->dashPrfx . '/u/' . $t->TreeSlug . '/' . $rootNode->NodePromptNotes;
                            if ($cid > 0) {
                                $redir .= '?cid=' . $cid;
                            } else {
                                $redir .= '?start=1&new=' . rand(100000000, 1000000000);
                            }
                            $paramTxt = str_replace($this->domainPath . '/start/' . $t->TreeSlug, '', 
                                str_replace($this->domainPath . '/dashboard/start/' . $t->TreeSlug, '', 
                                $request->fullUrl()));
                            if (substr($paramTxt, 0, 1) == '/') {
                                $paramTxt = substr($paramTxt, 1);
                            }
                            if (trim($paramTxt) != '' && substr($paramTxt, 0, 1) == '?') {
                                $redir .= '&' . substr($paramTxt, 1);
                            }
                            if (intVal($cid) > 0) {
                                $this->loadPageCID($request, $t, $cid);
                            }
                            return redirect($this->domainPath . $redir);
                        }
                    }
                }
            }
        }
        return redirect($this->domainPath . '/');
    }
    
    public function loadPageCID(Request $request, $tree, $cid)
    {
        if ($cid > 0 && $tree && isset($tree->TreeID)) {
            $sess = SLSess::where('SessUserID', Auth::user()->id)
                ->where('SessTree', $tree->TreeID)
                ->where('SessCoreID', $cid)
                ->where('SessIsActive', 1)
                ->orderBy('updated_at', 'desc')
                ->first();
            if (!$sess || !isset($sess->SessID)) {
                $sess = new SLSess;
                $sess->SessUserID   = Auth::user()->id;
                $sess->SessTree     = $tree->TreeID;
                $sess->SessCoreID   = $cid;
                $sess->SessIsActive = 1;
                $sess->save();
            }
            if ($request->has("n") && intVal($request->get("n")) > 0) {
                $sess->update([ 'SessCurrNode' => intVal($request->get("n")) ]);
            } elseif ($sess->SessCurrNode == -86) { // last session deactivate (hopefully completed)
                $sess->update([ 'SessCurrNode' => $tree->TreeRoot ]);
            }
            session()->put('sessID' . $tree->TreeID, $sess->SessID);
            session()->put('coreID' . $tree->TreeID, $cid);
        }
        return true;
    }
    
    public function loadPageURLrawID(Request $request, $pageSlug = '', $cid = -3, $view = '')
    {
        return $this->loadPageURL($request, $pageSlug, $cid, $view, true);
    }
    
    protected function chkGenCacheKey()
    {
        if (trim($this->cacheKey) == '') {
            return $this->topGenCacheKey();
        }
        return $this->cacheKey;
    }
    
    protected function topGenCacheKey()
    {
        $this->cacheKey = '/cache/page-' . substr($_SERVER["REQUEST_URI"], 1) . '.html';
        return $this->cacheKey;
    }
    
    public function topCheckCache(Request $request)
    {
        $this->topGenCacheKey();
        if ($request->has('refresh')) {
            if (file_exists($this->cacheKey)) {
                Storage::delete($this->cacheKey);
            }
            return false;
        }
        if (file_exists($this->cacheKey)) {
            $this->pageContent = Storage::get($this->cacheKey);
            return true;
        }
        return false;
    }
    
    protected function topSaveCache()
    {
        $this->chkGenCacheKey();
        Storage::put($this->cacheKey, $this->pageContent);
        return true;
    }
    
    public function addAdmCodeToPage($pageContent)
    {
        $extra = '';
        if (Auth::user() && isset(Auth::user()->id) && Auth::user()->hasRole('administrator|staff|brancher')) {
            $extra .= ' setTimeout(\'addSideNavItem("Edit Page", "?edit=1")\', 2000); ';
        }
        if (trim($extra) != '') {
            $extra = '<script async defer type="text/javascript"> ' . $extra . ' </script>';
        }
        return str_replace("</body>", $extra . "\n</body>", $pageContent);
    }
    
    protected function okToLoadTree($treeOpts = 1)
    {
        return ($this->treeRightType($treeOpts) && $this->userHasTreePerms($treeOpts));
    }
    
    protected function treeRightType($treeOpts = 1)
    {
        if ($this->isAdminPage) {
            return ($treeOpts%Globals::TREEOPT_ADMIN == 0 || $treeOpts%Globals::TREEOPT_STAFF == 0
                || $treeOpts%Globals::TREEOPT_PARTNER == 0 || $treeOpts%Globals::TREEOPT_VOLUNTEER == 0);
        }
        return ($treeOpts%Globals::TREEOPT_ADMIN > 0 && $treeOpts%Globals::TREEOPT_STAFF > 0
            && $treeOpts%Globals::TREEOPT_PARTNER > 0 && $treeOpts%Globals::TREEOPT_VOLUNTEER > 0);
    }
    
    public function isUserAdmin()
    {
        return (Auth::user() && Auth::user()->hasRole('administrator'));
    }
    
    protected function isUserStaff()
    {
        return (Auth::user() && Auth::user()->hasRole('staff'));
    }
    
    protected function isUserVolun()
    {
        return (Auth::user() && Auth::user()->hasRole('volunteer'));
    }
    
    protected function isUserPartn()
    {
        return (Auth::user() && Auth::user()->hasRole('partner'));
    }

    protected function urlNotResourceFile($str)
    {
        $str = trim($str);
        if ($str == '') {
            return false;
        }
        $dot = strrpos($str, '.');
        if ($dot > 0) {
            $sffx = substr($str, $dot);
            if (in_array(strtolower($sffx), ['css', 'js', 'png', 'jpg', 'jpeg', 'gif', 'svg', 'woff', 'woff2'])) {
                return false;
            }
        }
        return true;
    }
    
}