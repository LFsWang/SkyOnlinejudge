<?php namespace SKYOJ\Challenge;

if (!defined('IN_SKYOJSYSTEM')) {
    exit('Access denied');
}

require_once $_E['ROOT'].'/function/common/pagelist.php';
use  \SKYOJ\PageList;
use \SkyOJ\Core\DataBase\DB as DB;

function listHandle()
{
    global $SkyOJ,$_E;
    $page = $SkyOJ->UriParam(2)??'1';
    $uid = \SKYOJ\safe_get('uid')??null;
    $pid = \SKYOJ\safe_get('pid')??null;
    $result = \SKYOJ\safe_get('result')??null;

    if( !preg_match('/^[1-9][0-9]*$/',$page) )
        $page = '1';

    if( !preg_match('/^[1-9][0-9]*$/',$uid) )
        $uid = null;
    if( !preg_match('/^[1-9][0-9]*$/',$pid) )
        $pid = null;
    if( !preg_match('/^[1-9][0-9]*$/',$result) )
        $result = null;

    $query = "";

    if( isset($uid) ) $query .= " AND `uid` = $uid ";
    if( isset($pid) ) $query .= " AND `pid` = $pid ";
    if( isset($result) ) $query .= " AND `result` = $result";

    $pl = new PageList('challenge');
    $allpage = $pl->all();

    $relpage = $allpage - $page + 1;

    //$data = $pl->GetPageDataByPage($page,'cid','*','DESC');

    if( $query !== "" )
    {
        $table = DB::tname('challenge');
        $rows = DB::fetchAllEx("SELECT * FROM `{$table}` WHERE 1=1 $query");
        $data = [];
        foreach( $rows as $row )
        {
            $p = new \SkyOJ\Challenge\Container(null);
            if( $p->loadByData($row) );
                $data[] = $p;
        }
    }
    else
        $data = \SkyOJ\Challenge\Container::loadRange( ($relpage-1)*PageList::ROW_PER_PAGE , $relpage*PageList::ROW_PER_PAGE-1 );

    //LOG::msg(Level::Debug, '', $data);
    $_E['template']['challenge_list_pagelist'] = $pl;
    $_E['template']['challenge_list_now'] = $page;
    $_E['template']['challenge_info'] = $data ? $data : [];

    \Render::render('challenge_list', 'challenge');
}
