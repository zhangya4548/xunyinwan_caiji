<?php
header("Content-type:text/html;charset=utf-8");
//note 设置PHP超时时间
set_time_limit(0);

//引入必要类库
use Illuminate\Database\Capsule\Manager as Capsule;
use Curl\Curl;
use QL\QueryList;


// 定义 BASE_PATH
define('BASE_PATH', __DIR__);
// Autoload 自动载入
require BASE_PATH.'/vendor/autoload.php';


// 初始化数据库链接
$capsule = new Capsule;
$capsule->addConnection([
  'driver'    => 'mysql',
  'host'      => 'localhost',
  'database'  => 'caiji',
  'username'  => 'root',
  'password'  => "xxx",
  'charset'   => 'utf8',
  'collation' => 'utf8_general_ci',
  'prefix'    => ''
  ]);
$capsule->setAsGlobal();
$capsule->bootEloquent();


//根据cli方式传参,执行对应操作    php index.php -m[list,info,url]
$opt = getopt('m:');
if (FALSE === empty($opt['m'])) {
	if($opt['m'] == 'list'){
		$list = [];
		for ($i	=1; $i < 2; $i++) { 
			$list[$i] = 'http://www.xunyingwang.com/movie/?page='.$i;
		}
		get_list($list);
	}elseif($opt['m'] == 'info'){
		$info_res  = Capsule::table('caiji_url')->where('status',0)->get();	
		$info_list = [];
		$i=0;
		foreach ($info_res as $key => $value) {
			$info_list[$i]     = "http://www.xunyingwang.com/movie/".$value->num.".html";
			$i++;
		}
		get_info($info_list);
	}elseif($opt['m'] == 'url'){
		$info_res  = Capsule::table('caiji_url')->where('status',0)->get();	
		$info_list_url = [];
		$i=0;
		foreach ($info_res as $key => $value) {
			$info_list_url[$i] = "http://www.xunyingwang.com/videos/resList/".$value->num;
			$i++;
		}
		get_info_url($info_list_url);
		//var_dump($info_res);	
	}
}




//采集列表
function get_list($list = []){
	//多线程采集文章信息
	QueryList::run('Multi',[
	    //待采集链接集合
	    'list' => $list,
	    'curl' => [
	        'opt' => array(
	            //这里根据自身需求设置curl参数
				CURLOPT_SSL_VERIFYPEER => false,
				CURLOPT_SSL_VERIFYHOST => false,
				CURLOPT_FOLLOWLOCATION => true,
				CURLOPT_AUTOREFERER    => true,
				CURLOPT_HEADER         => 1,
				CURLOPT_RETURNTRANSFER => 1,
				CURLOPT_CONNECTTIMEOUT => 10,
				CURLOPT_REFERER        => "http://www.xunyingwang.com",
				CURLOPT_COOKIE         => "upv2=20161019%2C1; cscpvcouplet_fidx=1; cscpvrich_fidx=1",
				CURLOPT_USERAGENT      => "Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/52.0.2743.116 Safari/537.36",
				CURLOPT_HTTPHEADER     => ["Accept:*/*", "Accept-Language:zh-CN,zh;q=0.8,en;q=0.6,zh-TW;q=0.4", "Connection:keep-alive", "X-Requested-With:XMLHttpRequest|"],
	                ),
	        //设置线程数
	        'maxThread' => 100,
	        //设置最大尝试数
	        'maxTry' => 2 
	    ],
	    'success' => function($a,$info){
	        //采集规则
	        $guizhe   = [
				'title'=> ['.row .col-xs-12 .col-xs-1-5 .meta h1 a','text'],
				'url'  => ['.row .col-xs-12 .col-xs-1-5 .meta h1 a','href'],
				'img'  => ['.row .col-xs-12 .col-xs-1-5 .movie-item-in a img','src'],
	        ]; 
			$rang = '';
			$ql   = QueryList::Query($a['content'],$guizhe,$rang);
			$data = $ql->getData();
	        //打印结果，实际操作中这里应该做入数据库操作
	        
	        if (TRUE === empty($data)) {
	        	return false;
	        }
	         
	        //var_dump($data);die;
	        $new_data = $new_tr = [];
	        $i = 0;
	        $time = date("Y-m-d H:i:s",time());
			foreach ($data as $key => $value) {
				if(FALSE === empty($value['title']) && FALSE === empty($value['url']) && FALSE === empty($value['img'])){
					$new_data[$i]['num']        = ($value['url']) ? substr($value['url'], strripos($value['url'],"/")+1 ,-5 ) : 0;
					$new_data[$i]['title']      = ($value['title']) ? loseSpace($value['title']) : '';
					$new_data[$i]['url']        = ($value['url']) ? loseSpace($value['url']) : '';
					$new_data[$i]['img']        = ($value['img']) ? loseSpace($value['img']) : '';
					$new_data[$i]['status']     = 0;
					$new_data[$i]['created_at'] = $time;
					$new_data[$i]['updated_at'] = $time;
			    	$i++;	
				}
			}
			add_list($new_data);
			echo "采集列表完成" . PHP_EOL;
			// var_dump($new_data);
	    }
	]);
}




//采集详情
function get_info($info_list = []){
	//多线程采集文章信息
	QueryList::run('Multi',[
	    //待采集链接集合
	    'list' => $info_list,
	    'curl' => [
	        'opt' => array(
	            //这里根据自身需求设置curl参数
				CURLOPT_SSL_VERIFYPEER => false,
				CURLOPT_SSL_VERIFYHOST => false,
				CURLOPT_FOLLOWLOCATION => true,
				CURLOPT_AUTOREFERER    => true,
				CURLOPT_HEADER         => 1,
				CURLOPT_RETURNTRANSFER => 1,
				CURLOPT_CONNECTTIMEOUT => 10,
				CURLOPT_REFERER        => "http://www.xunyingwang.com",
				CURLOPT_COOKIE         => "upv2=20161019%2C1; cscpvcouplet_fidx=1; cscpvrich_fidx=1",
				CURLOPT_USERAGENT      => "Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/52.0.2743.116 Safari/537.36",
				CURLOPT_HTTPHEADER     => ["Accept:*/*", "Accept-Language:zh-CN,zh;q=0.8,en;q=0.6,zh-TW;q=0.4", "Connection:keep-alive", "X-Requested-With:XMLHttpRequest|"],
	                ),
	        //设置线程数
	        'maxThread' => 100,
	        //设置最大尝试数
	        'maxTry' => 2 
	    ],
	    'success' => function($a,$info){
	        //采集规则
	        $guizhe   = [
				'ids'       => [':input[name="mid"]','value'],
				'title'     => ['h1 ','text'],
				'img'       => ['.img-thumbnail','src'],
				'daoyan'    => ['.row .col-xs-8 .table-striped tbody tr:eq(0) ','text'],
				'bianju'    => ['.row .col-xs-8 .table-striped tbody tr:eq(1) ','text'],
				'zhuyan'    => ['.row .col-xs-8 .table-striped tbody tr:eq(2) ','text'],
				'leixin'    => ['.row .col-xs-8 .table-striped tbody tr:eq(3) ','text'],
				'guojia'    => ['.row .col-xs-8 .table-striped tbody tr:eq(4) ','text'],
				'yuyan'     => ['.row .col-xs-8 .table-striped tbody tr:eq(5) ','text'],
				'shangyin'  => ['.row .col-xs-8 .table-striped tbody tr:eq(6) ','text'],
				'pianchang' => ['.row .col-xs-8 .table-striped tbody tr:eq(7) ','text'],
				'youming'   => ['.row .col-xs-8 .table-striped tbody tr:eq(8) ','text'],
				'pinfen'    => ['.row .col-xs-8 .table-striped tbody tr:eq(9) ','text'],
	        	'jieshao'   => ['.movie-introduce','text'],
	        	'list_img'   => ['.movie-screenshot ','html'],
	        ]; 
			$rang = '.container';
			$ql   = QueryList::Query($a['content'],$guizhe,$rang);
			$data = $ql->getData();
	        //打印结果，实际操作中这里应该做入数据库操作
	        
	        if (TRUE === empty($data)) {
	        	return false;
	        }
	         
	        //var_dump($data);
	        $new_data = $new_tr = [];
	        $i = 0;
	        $time = date("Y-m-d H:i:s",time());
			foreach ($data as $key => $value) {
				if(FALSE === empty($value['ids'])){
					$new_data[$i]['ids']        = ($value['ids']) ? loseSpace($value['ids']) : 0;
					$new_data[$i]['title']      = ($value['title']) ? loseSpace($value['title']) : '';
					$new_data[$i]['img']        = ($value['img']) ? loseSpace($value['img']) : '';
					$new_data[$i]['jieshao']    = ($value['jieshao']) ? loseSpace($value['jieshao']) : '';
					$new_data[$i]['list_img']   = ($value['list_img']) ? loseSpace_img($value['list_img']) : '';
					$new_tr['tr1']              = ($value['daoyan']) ? loseSpace($value['daoyan']) : '';
					$new_tr['tr2']              = ($value['bianju']) ? loseSpace($value['bianju']) : '';
					$new_tr['tr3']              = ($value['zhuyan']) ? loseSpace($value['zhuyan']) : '';
					$new_tr['tr4']              = ($value['leixin']) ? loseSpace($value['leixin']) : '';
					$new_tr['tr5']              = ($value['guojia']) ? loseSpace($value['guojia']) : '';
					$new_tr['tr6']              = ($value['yuyan']) ? loseSpace($value['yuyan']) : '';
					$new_tr['tr7']              = ($value['shangyin']) ? loseSpace($value['shangyin']) : '';
					$new_tr['tr8']              = ($value['pianchang']) ? loseSpace($value['pianchang']) : '';
					$new_tr['tr9']              = ($value['youming']) ? loseSpace($value['youming']) : '';
					$new_tr['tr10']             = ($value['pinfen']) ? loseSpace($value['pinfen']) : '';	
					$new_data[$i]['sku']        = json_encode($new_tr);
					$new_data[$i]['status']     = 1;
					$new_data[$i]['created_at'] = $time;
					$new_data[$i]['updated_at'] = $time;
					echo "采集详情:http://www.xunyingwang.com/movie/".$new_data[$i]['ids'].".html,成功" . PHP_EOL;
			    	$i++;	
				}
			}
			add_info($new_data);
			//var_dump($new_data);
	    }
	]);
}



//采集详情电影url
function get_info_url($info_list = []){

	//多线程采集文章信息
	QueryList::run('Multi',[
	    //待采集链接集合
	    'list' => $info_list,
	    'curl' => [
	        'opt' => array(
                //这里根据自身需求设置curl参数
				CURLOPT_SSL_VERIFYPEER => false,
				CURLOPT_SSL_VERIFYHOST => false,
				CURLOPT_FOLLOWLOCATION => true,
				CURLOPT_AUTOREFERER    => true,
				CURLOPT_HEADER         => 1,
				CURLOPT_RETURNTRANSFER => 1,
				CURLOPT_CONNECTTIMEOUT => 10,
				CURLOPT_REFERER        => "http://www.xunyingwang.com",
				CURLOPT_COOKIE         => "upv2=20161019%2C1; cscpvcouplet_fidx=1; cscpvrich_fidx=1",
				CURLOPT_USERAGENT      => "Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/52.0.2743.116 Safari/537.36",
				CURLOPT_HTTPHEADER     => ["Accept:*/*", "Accept-Language:zh-CN,zh;q=0.8,en;q=0.6,zh-TW;q=0.4", "Connection:keep-alive", "X-Requested-With:XMLHttpRequest|"],
            ),
	        //设置线程数
	        'maxThread' => 100,
	        //设置最大尝试数
	        'maxTry' => 2 
	    ],
	    'success' => function($a,$info){
	        //采集规则
	        $guizhe   = [
				'title'     => ['a ','title'],
				'url'       => ['a','href'],
				'ids'       => ['a','mid'],
	        ]; 
			// $rang = '.table-striped tbody';
			$rang = '';
			$ql   = QueryList::Query($a['content'],$guizhe,$rang);
			$data = $ql->getData();
	        if (TRUE === empty($data)) {
	        	return false;
	        }
	        // var_dump($data);
	        // var_dump('-------------------------------');die;
	        $new_data = [];
	        $ids = 0;
	        $i=0;
			foreach ($data as $key => $value) {
				if(FALSE === empty($value['ids']) && FALSE === empty($value['title']) && FALSE === empty($value['url'])){
					$ids = $value['ids'];
					$new_data[$i]['title'] = ($value['title']) ? loseSpace($value['title']) : 0;
					$new_data[$i]['url']   = ($value['url']) ? loseSpace($value['url']) : 0;
					$i++;
				}
			}
			if($ids){
				add_info_url($ids,['bt'=>json_encode($new_data),'updated_at'=>date("Y-m-d H:i:s",time())]);	
			}
			echo "采集详情电影url:http://www.xunyingwang.com/videos/resList/".$ids.",成功" . PHP_EOL;
			// var_dump($new_data);die;
	    }
	]);
}



//列表数据入库
function add_list($arr = []){
	Capsule::table('caiji_url')->insert($arr);
}

//详情数据入库
function add_info($arr = []){
	Capsule::table('dianyin')->insert($arr);
}

//详情url数据入库
function add_info_url($ids = 0,$arr = []){
	Capsule::table('dianyin')->where('ids',$ids)->update($arr);
	Capsule::table('caiji_url')->where('num',$ids)->update(['status'=>1]);
}



//取出所有图片链接
function loseSpace_img($pcon){
	//var_dump($pcon);
	$bai_pattern="#<(.*?)href=\"(.*?)\"><img#is";
    preg_match_all($bai_pattern,$pcon,$bai_match);    
    //var_dump($bai_match);die;
    if(TRUE === empty($bai_match[2])){
    	return false;
    }
	return json_encode($bai_match[2]);
}

//过滤空格，过滤回车，过滤换行
function loseSpace($pcon){
	$pcon = preg_replace("/ /","",$pcon);
	$pcon = preg_replace("/&nbsp;/","",$pcon);
	$pcon = preg_replace("/　/","",$pcon);
	$pcon = preg_replace("/\r\n/",":",$pcon);
	$pcon = str_replace(array("/r/n", "/r", "/n"), ":", $pcon);
	$pcon = str_replace(chr(13),"",$pcon);
	$pcon = str_replace(chr(10),"",$pcon);
	$pcon = str_replace(chr(9),"",$pcon);
	$pcon=preg_replace("/\s+/", " ", $pcon);     
	return $pcon;
}



