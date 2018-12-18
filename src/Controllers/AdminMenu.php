<?php
/**
  * AdminMenu is responsible for building the menu inside the dashboard area for all user types.
  *
  * SurvLoop - All Our Data Are Belong
  * @package  wikiworldorder/survloop
  * @author  Morgan Lesko <wikiworldorder@protonmail.com>
  * @since 0.0
  */
namespace SurvLoop\Controllers;

use App\Models\SLTree;
use App\Models\SLContact;

class AdminMenu
{
    private $currUser = null;
    private $currPage = '';
    
    public function loadAdmMenu($currUser = null, $currPage = '')
    {
        $this->currUser = $currUser;
        $this->currPage = $currPage;
        $treeMenu = [
            $this->addAdmMenuHome(),
            $this->admMenuLnk('javascript:;', 'Submissions', '<i class="fa fa-star"></i>', 1, [
                $this->admMenuLnk('/dashboard/subs/all',        'All Complete'), 
                $this->admMenuLnk('/dashboard/subs/incomplete', 'Incomplete Sessions')
            ])
        ];
        return $this->addAdmMenuBasics($treeMenu);
    }
    
    protected function addAdmMenuHome()
    {
        return $this->admMenuLnk('/dashboard', 'Dashboard', '<i class="fa fa-home" aria-hidden="true"></i>');
    }
    
    protected function addAdmMenuBasics($treeMenu = [])
    {
        list($treeID, $treeLabel, $dbName) = $this->loadDbTreeShortNames();
        $treeMenu[] = $this->admMenuLnk('javascript:;', 'Site Content', 
            '<i class="fa fa-file-text-o" aria-hidden="true"></i>', 1, [
            $this->admMenuLnk('/dashboard/pages/list',     'Pages & Reports'), 
            $this->admMenuLnk('javascript:;', 'Surveys & Forms', '', 1, [
                $this->admMenuLnk('/dashboard/surveys/list', 'All Surveys'),
                $this->admMenuLnk('javascript:;', $treeLabel, '', 1, [
                    $this->admMenuLnk('/dashboard/surv-' . $treeID . '/map?all=1&alt=1', 'Full Survey Map'), 
                    $this->admMenuLnk('/dashboard/surv-' . $treeID . '/sessions',        'Session Stats'), 
                    $this->admMenuLnk('/dashboard/surv-' . $treeID . '/stats?all=1',     'Response Stats'),
                    $this->admMenuLnk('/dashboard/surv-' . $treeID . '/data',            'Data Structures'), 
                    $this->admMenuLnk('/dashboard/surv-' . $treeID . '/xmlmap',          'XML Map') 
                    ])
                ]),
            $this->admMenuLnk('/dashboard/pages/snippets', 'Content Snippets'), 
            $this->admMenuLnk('/dashboard/pages/menus',    'Navigation Menus'), 
            $this->admMenuLnk('/dashboard/images/gallery', 'Media Gallery'),
            $this->admMenuLnk('/dashboard/emails',         'Email Templates')
            ]);
        $treeMenu[] = $this->admMenuLnk('javascript:;', 'Database', '<i class="fa fa-database"></i>', 1, [
            $this->admMenuLnk('javascript:;', 'Data Tables', '', 1, [
                $this->admMenuLnk('/dashboard/db',           'Full Table List'),
                $this->admMenuLnk('/dashboard/db/addTable',  'Add A New Table'),
                $this->admMenuLnk('/dashboard/db/sortTable', 'Re-Order Tables'),
                $this->admMenuLnk('/dashboard/db/diagrams',  'Data Diagrams')
                ]), 
            $this->admMenuLnk('javascript:;', 'Data Fields', '', 1, [
                $this->admMenuLnk('/dashboard/db/all',                'Full Field Map'), 
                $this->admMenuLnk('/dashboard/db/field-matrix?alt=1', 'Field Matrix: English'),
                $this->admMenuLnk('/dashboard/db/field-matrix',       'Field Matrix: Geek'),
                $this->admMenuLnk('/dashboard/db/bus-rules',          'Business Rules')
                ]), 
            $this->admMenuLnk('/dashboard/db/definitions', 'Definition Lists'),
            $this->admMenuLnk('/dashboard/db/conds',       'Filters / Conditions'),
            $this->admMenuLnk('/dashboard/db/fieldDescs',  'Field Descriptions'), 
            $this->admMenuLnk('/dashboard/db/fieldXML',    'Field Privacy Settings'), 
            $this->admMenuLnk('/dashboard/db/workflows',   'Process Workflows'),
            $this->admMenuLnk('javascript:;',              'Export', '', 1, [
                $this->admMenuLnk('/dashboard/db/export',         'Full Database Export'),
                $this->admMenuLnk('/dashboard/sl/export/laravel', 'SurvLoop Package')
                ]),
            $this->admMenuLnk('/dashboard/db/switch', '<i class="slGrey">All Databases</i>')
            ]);
        $treeMenu[] = $this->admMenuLnk('javascript:;', 'Users', '<i class="fa fa-users"></i>', 1, [
            $this->admMenuLnk('/dashboard/users', 'All Users'),
            $this->admMenuLnkContact(false)
            ]);
        $treeMenu[] = $this->admMenuLnk('javascript:;', 'Settings', '<i class="fa fa-cogs"></i>', 1, [
            $this->admMenuLnk('javascript:;',      'System Settings', '', 1, [
                $this->admMenuLnk('/dashboard/settings#search',   'Search Engines'),
                $this->admMenuLnk('/dashboard/settings#general',  'General Settings'),
                $this->admMenuLnk('/dashboard/settings#logos',    'Logos & Fonts'),
                $this->admMenuLnk('/dashboard/settings#color',    'Colors'),
                $this->admMenuLnk('/dashboard/settings#hardcode', 'Code HTML CSS JS')
                ]),
            $this->admMenuLnk('javascript:;',       'System Logs', '', 1, [
                $this->admMenuLnk('/dashboard/logs', 'All Logs'),
                $this->admMenuLnk('/dashboard/logs/session-stuff', 'Session Stuff')
                ]),
            $this->admMenuLnk('/dashboard/systems-check',     'System Check'),
            $this->admMenuLnk('/dashboard/systems-update',    'System Updates')
            ]);
        return $treeMenu;
    }
    
    static public function admMenuLnk($url = '', $text = '', $ico = '', $opt = 1, $children = [])
    {
        return [ $url, $text, $ico, $opt, $children ];
    }
    
    protected function admMenuLnkContact($icon = true)
    {
        $cnt = $this->admMenuLnkContactCnt();
        $lnk = 'Contact Form' . (($cnt > 0) ? '<sup id="contactPush" class="red mL5">' . $cnt . '</sup> ' : '');
        $ico = (($icon) ? '<i class="fa fa-envelope-o" aria-hidden="true"></i> ' : '');
        $ret = [ '/dashboard/contact', $lnk, $ico, 1, [] ];
        return $ret;
    }
    
    protected function admMenuLnkContactCnt()
    {
        $chk = SLContact::where('ContFlag', 'Unread')
            ->select('ContID')
            ->get();
        return $chk->count();
    }
    
    protected function loadDbTreeShortNames()
    {
        $dbName = ((isset($GLOBALS["SL"]->dbRow->DbName)) ? $GLOBALS["SL"]->dbRow->DbName : '');
        if (strlen($dbName) > 20 && isset($GLOBALS["SL"]->dbRow->DbName)) {
            $dbName = str_replace($GLOBALS["SL"]->dbRow->DbName, 
                str_replace('_', '', $GLOBALS["SL"]->dbRow->DbPrefix), $dbName);
        }
        $treeID = $GLOBALS["SL"]->treeRow->TreeID;
        $treeName = ((isset($GLOBALS["SL"]->treeName)) ? $GLOBALS["SL"]->treeName : '');
        if ($GLOBALS["SL"]->treeRow->TreeType == 'Page') {
            $tree = SLTree::find(1);
            $treeID = $tree->TreeID;
            $treeName = 'Tree: ' . $tree->TreeName;
        }
        return [ $treeID, $treeName, $dbName ];
    }
    
}