<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It's a breeze. Simply tell Laravel the URIs it should respond to
| and give it the controller to call when that URI is requested.
|
*/

Route::group(['middleware' => ['web']], function () {
    
    Route::post('/',              'SurvLoop\\Controllers\\SurvLoop@loadPageHome');
    Route::get( '/',              'SurvLoop\\Controllers\\SurvLoop@loadPageHome');
    Route::post('/sub',           'SurvLoop\\Controllers\\SurvLoop@index');
    
    Route::get( '/ajax',          'SurvLoop\\Controllers\\SurvLoop@ajaxChecks');
    Route::get( '/ajax/{type}',   'SurvLoop\\Controllers\\SurvLoop@ajaxChecks');
    Route::get( '/sortLoop',      'SurvLoop\\Controllers\\SurvLoop@sortLoop');
    Route::get( '/holdSess',      'SurvLoop\\Controllers\\SurvLoop@holdSess');
    Route::get( '/restart',       'SurvLoop\\Controllers\\SurvLoop@restartSess');
    Route::get( '/sessDump',      'SurvLoop\\Controllers\\SurvLoop@sessDump');
    Route::get( '/switch/{cid}',  'SurvLoop\\Controllers\\SurvLoop@switchSess');
    Route::get( '/delSess/{cid}', 'SurvLoop\\Controllers\\SurvLoop@delSess');
    Route::get( '/test',          function () { return redirect('/?test=1'); });
    
    // main survey process for primary database, primary tree
    Route::post('/u/{nodeSlug}',  'SurvLoop\\Controllers\\SurvLoop@loadNodeURL');
    Route::get( '/u/{nodeSlug}',  'SurvLoop\\Controllers\\SurvLoop@loadNodeURL');
    
    // survey process for any database or tree
    Route::get( '/start/{treeSlug}',         'SurvLoop\\Controllers\\SurvLoop@loadNodeTreeURL');
    Route::post('/u/{treeSlug}/{nodeSlug}',  'SurvLoop\\Controllers\\SurvLoop@loadNodeURL');
    Route::get( '/u/{treeSlug}/{nodeSlug}',  'SurvLoop\\Controllers\\SurvLoop@loadNodeURL');
    
    Route::get( '/up/{treeID}/{cid}/{upID}', 'SurvLoop\\Controllers\\SurvLoop@retrieveUpload');

    Route::get( '/search-bar',               'SurvLoop\\Controllers\\SurvLoop@searchBar');
    Route::get( '/search-results/{treeID}',  'SurvLoop\\Controllers\\SurvLoop@searchResultsAjax');
    
    Route::get( '/records-full/{treeID}',    'SurvLoop\\Controllers\\SurvLoop@ajaxRecordFulls');
    Route::get( '/record-prevs/{treeID}',    'SurvLoop\\Controllers\\SurvLoop@ajaxRecordPreviews');
    Route::get( '/record-check/{treeID}',    'SurvLoop\\Controllers\\SurvLoop@ajaxMultiRecordCheck');
    
    Route::get( '/ajax-emoji-tag/{treeID}/{recID}/{defID}', 'SurvLoop\\Controllers\\SurvLoop@ajaxEmojiTag');
    
    Route::get( '/{treeSlug}-read/{cid}/{ComSlug}',   'SurvLoop\\Controllers\\SurvLoop@byID');
    Route::get( '/{treeSlug}-read/{cid}',             'SurvLoop\\Controllers\\SurvLoop@byID');
    Route::get( '/{treeSlug}-report/{cid}/{ComSlug}', 'SurvLoop\\Controllers\\SurvLoop@byID');
    Route::get( '/{treeSlug}-report/{cid}',           'SurvLoop\\Controllers\\SurvLoop@byID');
    
    Route::get( '/{treeSlug}-xml-all',         'SurvLoop\\Controllers\\SurvLoop@xmlAll');
    Route::get( '/{treeSlug}-xml-example',      'SurvLoop\\Controllers\\SurvLoop@getXmlExample');
    Route::get( '/{treeSlug}-xml-example.xml',  'SurvLoop\\Controllers\\SurvLoop@getXmlExample');
    Route::get( '/{treeSlug}-xml-schema',       'SurvLoop\\Controllers\\SurvLoop@genXmlSchema');
    Route::get( '/{treeSlug}-xml-schema.xsd',   'SurvLoop\\Controllers\\SurvLoop@genXmlSchema');
    Route::get( '/{treeSlug}-report-xml/{cid}', 'SurvLoop\\Controllers\\SurvLoop@xmlByID');
    Route::get( '/xml-example',                 'SurvLoop\\Controllers\\SurvLoop@getXmlExample');
    Route::get( '/xml-schema',                  'SurvLoop\\Controllers\\SurvLoop@genXmlSchema');
    
    Route::get( '/fresh/creator',         'SurvLoop\\Controllers\\AdminTreeController@freshUser');
    Route::post('/fresh/database',        'SurvLoop\\Controllers\\AdminTreeController@freshDB');
    Route::get( '/fresh/database',        'SurvLoop\\Controllers\\AdminTreeController@freshDB');
    Route::post('/fresh/user-experience', 'SurvLoop\\Controllers\\AdminTreeController@freshUX');
    Route::get( '/fresh/user-experience', 'SurvLoop\\Controllers\\AdminTreeController@freshUX');
    
    
    ///////////////////////////////////////////////////////////
    
    Route::post('/register',   'SurvLoop\Controllers\Auth\SurvRegisterController@register');
    Route::post('/afterLogin', 'SurvLoop\\Controllers\\SurvLoop@afterLogin');
    Route::get( '/afterLogin', 'SurvLoop\\Controllers\\SurvLoop@afterLogin');
    Route::get( '/logout',     'SurvLoop\\Controllers\\Auth\\AuthController@getLogout');
    Route::get( '/chkEmail',   'SurvLoop\\Controllers\\SurvLoop@chkEmail');
    
    // Authentication routes...
    Route::post('/login',                  'SurvLoop\Controllers\Auth\AuthController@postLogin');
    //Route::get( '/login',                  'SurvLoop\Controllers\Auth\AuthController@getLogin');
    
    // Registration routes...
    //Route::get( '/register',               'SurvLoop\Controllers\Auth\AuthController@getRegister');
    
    // Password reset link request routes...
    //Route::post('/password/email',         'SurvLoop\Controllers\Auth\PasswordController@postEmail');
    //Route::get( '/password/email',         'SurvLoop\Controllers\Auth\PasswordController@getEmail');
    
    // Password reset routes...
    //Route::post('/password/reset',         'SurvLoop\Controllers\Auth\PasswordController@postReset');
    //Route::get( '/password/reset/{token}', 'SurvLoop\Controllers\Auth\PasswordController@getReset');
    
    
    ///////////////////////////////////////////////////////////
    
    
    Route::get('/admin', [
        'uses'       => 'SurvLoop\\Controllers\\SurvLoop@dashboardDefault', 
        'middleware' => ['auth']
    ]);
    
    Route::get('/dashboard', [
        'uses'       => 'SurvLoop\\Controllers\\SurvLoop@dashboardDefault', 
        'middleware' => ['auth']
    ]);
    

    Route::post('/home', 'SurvLoop\\Controllers\\SurvLoop@loadPageHome');
    Route::get( '/home', 'SurvLoop\\Controllers\\SurvLoop@loadPageHome');
    
    Route::post('/{pageSlug}', 'SurvLoop\\Controllers\\SurvLoop@loadPageURL');
    Route::get( '/{pageSlug}', 'SurvLoop\\Controllers\\SurvLoop@loadPageURL');
    
    
    ///////////////////////////////////////////////////////////
    
    
    Route::post('/profile/{uid}',     [
        'uses'       => 'SurvLoop\Controllers\SurvLoop@updateProfile',     
        'middleware' => 'auth'
    ]);
    
    Route::get( '/profile/{uid}',     [
        'uses'       => 'SurvLoop\Controllers\SurvLoop@showProfile',             
        'middleware' => 'auth'
    ]);
    
    
    
    
    
    Route::get('/dashboard/subs', [
        'uses'       => 'SurvLoop\Controllers\SurvLoop@listSubsAll',    
        'middleware' => ['auth']
    ]);
    
    Route::get('/dashboard/subs/all', [
        'uses'       => 'SurvLoop\Controllers\SurvLoop@listSubsAll',    
        'middleware' => ['auth']
    ]);
    
    Route::get('/dashboard/subs/unpublished', [
        'uses'       => 'SurvLoop\Controllers\SurvLoop@listUnpublished',    
        'middleware' => ['auth']
    ]);
    
    Route::get('/dashboard/subs/incomplete', [
        'uses'       => 'SurvLoop\Controllers\SurvLoop@listSubsIncomplete',    
        'middleware' => ['auth']
    ]);
    
    Route::post('/dashboard/subs/{treeID}/{cid}', [
        'uses'       => 'SurvLoop\Controllers\SurvLoop@printSubView',    
        'middleware' => ['auth']
    ]);
    Route::get('/dashboard/subs/{treeID}/{cid}', [
        'uses'       => 'SurvLoop\Controllers\SurvLoop@printSubView',    
        'middleware' => ['auth']
    ]);
    
    
    Route::post('/dashboard/emails', [
        'uses'       => 'SurvLoop\Controllers\AdminController@manageEmails', 
        'middleware' => ['auth']
    ]);
    
    Route::get('/dashboard/emails', [
        'uses'       => 'SurvLoop\Controllers\AdminController@manageEmails', 
        'middleware' => ['auth']
    ]);
    
    Route::post('/dashboard/email/{emailID}', [
        'uses'       => 'SurvLoop\Controllers\AdminController@manageEmailsPost', 
        'middleware' => ['auth']
    ]);
    
    Route::get('/dashboard/email/{emailID}', [
        'uses'       => 'SurvLoop\Controllers\AdminController@manageEmailsForm', 
        'middleware' => ['auth']
    ]);
    
    Route::post('/dashboard/blurbs/{blurbID}', [
        'uses' => 'SurvLoop\Controllers\AdminController@blurbEditSave', 
        'middleware' => ['auth']
    ]);
    
    Route::get('/dashboard/blurbs/{blurbID}', [
        'uses' => 'SurvLoop\Controllers\AdminController@blurbEdit', 
        'middleware' => ['auth']
    ]);
    
    Route::get( '/dashboard/users/email', [
        'uses' => 'SurvLoop\Controllers\AdminController@userEmailing', 
        'middleware' => ['auth']
    ]);
    
    Route::post('/dashboard/users', [
        'uses' => 'SurvLoop\Controllers\SurvLoop@userManagePost', 
        'middleware' => ['auth']
    ]);
    
    Route::get('/dashboard/users', [
        'uses' => 'SurvLoop\Controllers\SurvLoop@userManage', 
        'middleware' => ['auth']
    ]);
    
    
    ///////////////////////////////////////////////////////////
    
    
    Route::post('/dashboard/settings', [
        'uses'       => 'SurvLoop\Controllers\AdminController@sysSettings',
        'middleware' => ['auth']
    ]);
    
    Route::get('/dashboard/settings', [
        'uses'       => 'SurvLoop\Controllers\AdminController@sysSettings',
        'middleware' => ['auth']
    ]);
    
    
    
    Route::get('/tree/{treeSlug}', 'SurvLoop\Controllers\AdminTreeController@adminPrintFullTreePublic');
    
    Route::get('/dashboard/tree', [
        'uses'       => 'SurvLoop\Controllers\AdminTreeController@treeSessions',    
        'middleware' => ['auth']
    ]);
    
    Route::get('/dashboard/tree/stats', [
        'uses'       => 'SurvLoop\Controllers\AdminTreeController@treeStats', 
        'middleware' => ['auth']
    ]);
    
    Route::post('/dashboard/tree/map', [
        'uses'       => 'SurvLoop\Controllers\AdminTreeController@index', 
        'middleware' => ['auth']
    ]);
    
    Route::get('/dashboard/tree/map', [
        'uses'       => 'SurvLoop\Controllers\AdminTreeController@index', 
        'middleware' => ['auth']
    ]);
    
    Route::post('/dashboard/tree/data', [
        'uses'       => 'SurvLoop\Controllers\AdminTreeController@data', 
        'middleware' => ['auth']
    ]);
    
    Route::get('/dashboard/tree/data', [
        'uses'       => 'SurvLoop\Controllers\AdminTreeController@data', 
        'middleware' => ['auth']
    ]);
    
    Route::post('/dashboard/tree/conds', [
        'uses'       => 'SurvLoop\Controllers\AdminTreeController@conditions', 
        'middleware' => ['auth']
    ]);
    
    Route::get('/dashboard/tree/conds', [
        'uses'       => 'SurvLoop\Controllers\AdminTreeController@conditions', 
        'middleware' => ['auth']
    ]);
    
    Route::post('/dashboard/tree/map/node/{nID}', [
        'uses'       => 'SurvLoop\Controllers\AdminTreeController@nodeEdit', 
        'middleware' => ['auth']
    ]);
    
    Route::get('/dashboard/tree/map/node/{nID}', [
        'uses'       => 'SurvLoop\Controllers\AdminTreeController@nodeEdit', 
        'middleware' => ['auth']
    ]);
    
    Route::post('/dashboard/tree/xmlmap', [
        'uses'       => 'SurvLoop\Controllers\AdminTreeController@xmlmap', 
        'middleware' => ['auth']
    ]);
    
    Route::get('/dashboard/tree/{treeSlug}-xmlmap', [
        'uses'       => 'SurvLoop\Controllers\AdminTreeController@xmlmapInner', 
        'middleware' => ['auth']
    ]);
    
    Route::get('/dashboard/tree/xmlmap', [
        'uses'       => 'SurvLoop\Controllers\AdminTreeController@xmlmap', 
        'middleware' => ['auth']
    ]);
    
    Route::post('/dashboard/tree/xmlmap/node/{nodeIN}', [
        'uses'       => 'SurvLoop\Controllers\AdminTreeController@xmlNodeEdit', 
        'middleware' => ['auth']
    ]);
    
    Route::get('/dashboard/tree/xmlmap/node/{nodeIN}', [
        'uses'       => 'SurvLoop\Controllers\AdminTreeController@xmlNodeEdit', 
        'middleware' => ['auth']
    ]);
    
    Route::get('/dashboard/tree/workflows', [
        'uses'       => 'SurvLoop\Controllers\AdminTreeController@workflows', 
        'middleware' => ['auth']
    ]);
    
    Route::get('/dashboard/tree/switch/{treeID}', [
        'uses'       => 'SurvLoop\Controllers\AdminTreeController@switchTreeAdmin', 
        'middleware' => ['auth']
    ]);
    
    Route::get('/dashboard/tree/switch', [
        'uses'       => 'SurvLoop\Controllers\AdminTreeController@switchTreeAdmin', 
        'middleware' => ['auth']
    ]);
    
    Route::post('/dashboard/tree/new', [
        'uses'       => 'SurvLoop\Controllers\AdminTreeController@newTree', 
        'middleware' => ['auth']
    ]);
    
    Route::get('/dashboard/tree/new', [
        'uses'       => 'SurvLoop\Controllers\AdminTreeController@newTree', 
        'middleware' => ['auth']
    ]);
    
    Route::post('/dashboard/pages/list', [
        'uses'       => 'SurvLoop\Controllers\AdminTreeController@pagesList', 
        'middleware' => ['auth']
    ]);
    
    Route::get('/dashboard/pages/list', [
        'uses'       => 'SurvLoop\Controllers\AdminTreeController@pagesList', 
        'middleware' => ['auth']
    ]);
    
    Route::post('/dashboard/page/{treeID}', [
        'uses'       => 'SurvLoop\Controllers\AdminTreeController@indexPage', 
        'middleware' => ['auth']
    ]);
    
    Route::get('/dashboard/page/{treeID}', [
        'uses'       => 'SurvLoop\Controllers\AdminTreeController@indexPage', 
        'middleware' => ['auth']
    ]);
    
    
    
    
    ///////////////////////////////////////////////////////////
    
    
    Route::get('/db/{database}', 'SurvLoop\Controllers\AdminDBController@adminPrintFullDBPublic');
    
    Route::get('/dashboard/db', [
        'uses'       => 'SurvLoop\Controllers\AdminDBController@index', 
        'middleware' => ['auth']
    ]);
    
    Route::get('/dashboard/db/all', [
        'uses'       => 'SurvLoop\Controllers\AdminDBController@full', 
        'middleware' => ['auth']
    ]);
    
    Route::get('/dashboard/db/field-matrix', [
        'uses'       => 'SurvLoop\Controllers\AdminDBController@fieldMatrix', 
        'middleware' => ['auth']
    ]);
    
    Route::post('/dashboard/db/addTable', [
        'uses'       => 'SurvLoop\Controllers\AdminDBController@addTable', 
        'middleware' => ['auth']
    ]);
    
    Route::get('/dashboard/db/addTable', [
        'uses'       => 'SurvLoop\Controllers\AdminDBController@addTable', 
        'middleware' => ['auth']
    ]);
    
    Route::get('/dashboard/db/sortTable', [
        'uses'       => 'SurvLoop\Controllers\AdminDBController@tblSort', 
        'middleware' => ['auth']
    ]);
    
    Route::post('/dashboard/db/table/{tblName}/edit', [
        'uses'       => 'SurvLoop\Controllers\AdminDBController@editTable', 
        'middleware' => ['auth']
    ]);
    
    Route::get('/dashboard/db/table/{tblName}/edit', [
        'uses'       => 'SurvLoop\Controllers\AdminDBController@editTable', 
        'middleware' => ['auth']
    ]);
    
    Route::get('/dashboard/db/table/{tblName}/sort', [
        'uses'       => 'SurvLoop\Controllers\AdminDBController@fldSort', 
        'middleware' => ['auth']
    ]);
    
    Route::get('/dashboard/db/table/{tblName}', [
        'uses'       => 'SurvLoop\Controllers\AdminDBController@viewTable', 
        'middleware' => ['auth']
    ]);
    
    Route::post('/dashboard/db/field/{tblAbbr}/{FldName}', [
        'uses'       => 'SurvLoop\Controllers\AdminDBController@editField', 
        'middleware' => ['auth']
    ]);
    
    Route::get('/dashboard/db/field/{tblAbbr}/{FldName}', [
        'uses'       => 'SurvLoop\Controllers\AdminDBController@editField', 
        'middleware' => ['auth']
    ]);
    
    Route::post('/dashboard/db/field/{tblAbbr}', [
        'uses'       => 'SurvLoop\Controllers\AdminDBController@addTableFld', 
        'middleware' => ['auth']
    ]);
    
    Route::get('/dashboard/db/field/{tblAbbr}', [
        'uses'       => 'SurvLoop\Controllers\AdminDBController@addTableFld', 
        'middleware' => ['auth']
    ]);
    
    Route::get('/dashboard/db/ajax-field/{FldID}', [
        'uses'       => 'SurvLoop\Controllers\AdminDBController@fieldAjax', 
        'middleware' => ['auth']
    ]);
    
    Route::get('/dashboard/db/fieldDescs', [
        'uses'       => 'SurvLoop\Controllers\AdminDBController@fieldDescs', 
        'middleware' => ['auth']
    ]);
    
    Route::get('/dashboard/db/fieldDescs/all', [
        'uses'       => 'SurvLoop\Controllers\AdminDBController@fieldDescsAll', 
        'middleware' => ['auth']
    ]);
    
    Route::get('/dashboard/db/fieldDescs/{view}', [
        'uses'       => 'SurvLoop\Controllers\AdminDBController@fieldDescs', 
        'middleware' => ['auth']
    ]);
    
    Route::get('/dashboard/db/fieldDescs/{view}/all', [
        'uses'       => 'SurvLoop\Controllers\AdminDBController@fieldDescsAll', 
        'middleware' => ['auth']
    ]);
    
    Route::post('/dashboard/db/fieldDescs/save', [
        'uses'       => 'SurvLoop\Controllers\AdminDBController@fieldDescsSave',    
        'middleware' => ['auth']
    ]);
    
    Route::get('/dashboard/db/ajax/tblFldSelT/{rT}', [
        'uses' => 'SurvLoop\Controllers\AdminDBController@tblSelector'
    ]);
    
    Route::get('/dashboard/db/ajax/tblFldSelF/{rF}', [
        'uses' => 'SurvLoop\Controllers\AdminDBController@fldSelector'
    ]);
    
    Route::get('/dashboard/db/ajax/getSetFlds/{rSet}', [
        'uses' => 'SurvLoop\Controllers\AdminDBController@getSetFlds'
    ]);
    
    Route::get('/dashboard/db/ajax/getSetFldVals/{FldID}', [
        'uses' => 'SurvLoop\Controllers\AdminDBController@getSetFldVals'
    ]);
    
    Route::post('/dashboard/db/fieldXML/save', [
        'uses'       => 'SurvLoop\Controllers\AdminDBController@fieldXMLsave', 
        'middleware' => ['auth']
    ]);
    
    Route::get('/dashboard/db/fieldXML', [
        'uses'       => 'SurvLoop\Controllers\AdminDBController@fieldXML', 
        'middleware' => ['auth']
    ]);
    
    Route::get('/dashboard/db/definitions', [
        'uses'       => 'SurvLoop\Controllers\AdminDBController@definitions', 
        'middleware' => ['auth']
    ]);
    
    Route::get('/dashboard/db/definitions/add/{subset}', [
        'uses'       => 'SurvLoop\Controllers\AdminDBController@defAdd', 
        'middleware' => ['auth']
    ]);
    
    Route::get('/dashboard/db/definitions/add', [
        'uses'       => 'SurvLoop\Controllers\AdminDBController@defAdd', 
        'middleware' => ['auth']
    ]);
    
    Route::get('/dashboard/db/definitions/edit/{defID}', [
        'uses'       => 'SurvLoop\Controllers\AdminDBController@defEdit', 
        'middleware' => ['auth']
    ]);
    
    Route::post('/dashboard/db/definitions/add-sub', [
        'uses'       => 'SurvLoop\Controllers\AdminDBController@defAdd', 
        'middleware' => ['auth']
    ]);
    
    Route::post('/dashboard/db/definitions/add-sub/{subset}', [
        'uses'       => 'SurvLoop\Controllers\AdminDBController@defAdd', 
        'middleware' => ['auth']
    ]);
    
    Route::post('/dashboard/db/definitions/edit-sub/{defID}', [
        'uses'       => 'SurvLoop\Controllers\AdminDBController@defEdit', 
        'middleware' => ['auth']
    ]);
    
    Route::get('/dashboard/db/definitions/sort/{subset}', [
        'uses'       => 'SurvLoop\Controllers\AdminDBController@defSort', 
        'middleware' => ['auth']
    ]);
    
    Route::get('/dashboard/db/bus-rules', [
        'uses'       => 'SurvLoop\Controllers\AdminDBController@businessRules', 
        'middleware' => ['auth']
    ]);
    
    Route::post('/dashboard/db/bus-rules/add', [
        'uses'       => 'SurvLoop\Controllers\AdminDBController@ruleAdd', 
        'middleware' => ['auth']
    ]);
    
    Route::get('/dashboard/db/bus-rules/add', [
        'uses'       => 'SurvLoop\Controllers\AdminDBController@ruleAdd', 
        'middleware' => ['auth']
    ]);
    
    Route::post('/dashboard/db/bus-rules/edit/{ruleID}', [
        'uses'       => 'SurvLoop\Controllers\AdminDBController@ruleEdit', 
        'middleware' => ['auth']
    ]);
    
    Route::get('/dashboard/db/bus-rules/edit/{ruleID}',    [
        'uses'       => 'SurvLoop\Controllers\AdminDBController@ruleEdit', 
        'middleware' => ['auth']
    ]);
    
    Route::get('/dashboard/db/diagrams', [
        'uses'       => 'SurvLoop\Controllers\AdminDBController@diagrams', 
        'middleware' => ['auth']
    ]);
    
    Route::get('/dashboard/db/network-map', [
        'uses'       => 'SurvLoop\Controllers\AdminDBController@networkMap', 
        'middleware' => ['auth']
    ]);
    
    Route::get('/dashboard/db/export', [
        'uses'       => 'SurvLoop\Controllers\DatabaseInstaller@export', 
        'middleware' => ['auth']
    ]);
    
    Route::get('/dashboard/db/export/laravel', [
        'uses'       => 'SurvLoop\Controllers\DatabaseInstaller@printExportLaravel', 
        'middleware' => ['auth']
    ]);
    
    Route::post('/dashboard/db/install', [
        'uses'       => 'SurvLoop\Controllers\DatabaseInstaller@autoInstallDatabase', 
        'middleware' => ['auth']
    ]);
    
    Route::get('/dashboard/db/install', [
        'uses'       => 'SurvLoop\Controllers\DatabaseInstaller@autoInstallDatabase', 
        'middleware' => ['auth']
    ]);
    
    Route::post('/dashboard/db/db/db', [
        'uses'       => 'SurvLoop\Controllers\DatabaseInstaller@manualMySql', 
        'middleware' => ['auth']
    ]);
    
    Route::get('/dashboard/db/db/db', [
        'uses'       => 'SurvLoop\Controllers\DatabaseInstaller@manualMySql', 
        'middleware' => ['auth']
    ]);
    
    Route::get('/dashboard/db/switch/{dbID}', [
        'uses'       => 'SurvLoop\Controllers\AdminDBController@switchDB', 
        'middleware' => ['auth']
    ]);
    
    Route::get('/dashboard/db/switch', [
        'uses'       => 'SurvLoop\Controllers\AdminDBController@switchDB', 
        'middleware' => ['auth']
    ]);
    
    Route::post('/dashboard/db/new', [
        'uses'       => 'SurvLoop\Controllers\AdminTreeController@newDB', 
        'middleware' => ['auth']
    ]);
    
    Route::get('/dashboard/db/new', [
        'uses'       => 'SurvLoop\Controllers\AdminTreeController@newDB', 
        'middleware' => ['auth']
    ]);
    
    Route::get('/dashboard/css-reload', 'SurvLoop\\Controllers\\AdminController@getCSS');
    
    
    
    // survey process for any admin tree
    Route::get('/dashboard/start/{treeSlug}', [
        'uses'       => 'SurvLoop\Controllers\AdminController@loadNodeTreeURL', 
        'middleware' => ['auth']
    ]);
    Route::post('/dash/sub', [
        'uses'       => 'SurvLoop\Controllers\AdminController@postNodeURL', 
        'middleware' => ['auth']
    ]);
    Route::post('/dash/{treeSlug}/{nodeSlug}', [
        'uses'       => 'SurvLoop\Controllers\AdminController@loadNodeURL', 
        'middleware' => ['auth']
    ]);
    Route::get('/dash/{treeSlug}/{nodeSlug}', [
        'uses'       => 'SurvLoop\Controllers\AdminController@loadNodeURL', 
        'middleware' => ['auth']
    ]);
    
    
    Route::post('/dash/{pageSlug}', 'SurvLoop\\Controllers\\AdminController@loadPageURL');
    Route::get( '/dash/{pageSlug}', 'SurvLoop\\Controllers\\AdminController@loadPageURL');
    

});    
