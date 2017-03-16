<?php namespace SKYOJ\Contest;
if (!defined('IN_SKYOJSYSTEM')) {
    exit('Access denied');
}

function resolverHandle()
{
    global $SkyOJ,$_E,$_G;
    
    try{
        $cont_id = $SkyOJ->UriParam(2);
        $contest = GetContestByID($cont_id);

        if( !\userControl::isAdmin($_G['uid']))
            throw new \Exception('Admin Only!');
        if( $contest->ispreparing() )
            throw new \Exception('Contest is preparing!');

        $_E['template']['contest'] = $contest;
        //bangkok_resolver will destory page design
        \Render::renderSingleTemplate('common_header');
        if( $contest->class == "ioi" )
            \Render::renderSingleTemplate('bangkok_resolver_ioi','contest');
        else
            \Render::renderSingleTemplate('bangkok_resolver','contest');
        exit(0);
    }catch(\Exception $e){
        \Render::errormessage('Oops! '.$e->getMessage(),'Contest');
        \Render::render('nonedefine');
    }
}