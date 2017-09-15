<?php namespace SKYOJ\User;

if (!defined('IN_SKYOJSYSTEM')) {
    exit('Access denied');
}

function loginHandle()
{
    global $_E,$_G,$_config;
    if( $_G['uid'] ) {
        \Render::ShowMessage('你不是登入了!?');
        exit(0);
    }

    $email = \SKYOJ\safe_post('email');
    $AESenpass = \SKYOJ\safe_post('password');
    $GB = \SKYOJ\safe_post('GB');
	$user_ip = \SKYOJ\get_ip();

    if( isset($email,$AESenpass,$GB) ) {
        if (!\userControl::CheckToken('LOGIN')) {
            \SKYOJ\throwjson('error', 'token error, please refresh page');
        }
		
		//ip check
		$acctable = \DB::tname('account');
		$res = \DB::fetchEx("SELECT `allow_ip` FROM `$acctable` WHERE `nickname`=?", $email);
		if (ip2long($user_ip)!=ip2long($res['allow_ip']) && $res['allow_ip']!='%' && $_config['iplock']) {
			
            \SKYOJ\throwjson('error', 'this ip is not allowed to login');
        }

        //recover password
        $exkey = unserialize($_SESSION['dhkey']);
        $key = md5($exkey->decode($GB));
        $iv = $_SESSION['iv'];

        $decode = openssl_decrypt($AESenpass,'aes-256-cbc',$key,OPENSSL_ZERO_PADDING,$iv);
        $password = rtrim($decode, "\0");

        $user = login($email, $password);
        if (!$user[0]) {
            $_E['template']['alert'] = $user[1];
            \LOG::msg(\Level::Notice, "<$email> want to login but fail.(".$user[1].')');
            \SKYOJ\throwjson('error', $user[1]);
        } else {
            $user = $user[1];
            \userControl::SetLoginToken($user['uid']);
            \SKYOJ\throwjson('SUCC', 'index.php');
        }
    }else{
        \userControl::RegisterToken('LOGIN', 600);
        $exkey = new \SKYOJ\DiffieHellman();
        $_SESSION['dhkey'] = serialize($exkey);
        $_SESSION['iv'] = \SKYOJ\GenerateRandomString(16, SET_HEX);
        $_E['template']['dh_ga'] = $exkey->getGA();
        $_E['template']['dh_prime'] = $exkey->getPrime();
        $_E['template']['dh_g'] = $exkey->getG();
        $_E['template']['iv'] = $_SESSION['iv'];

        \Render::setbodyclass('loginbody');
        \Render::render('user_login_box', 'user');
        exit(0);
    }
}
