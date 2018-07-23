<?php
error_reporting(E_ALL);
mb_internal_encoding("UTF-8");
use Symfony\Component\DomCrawler\Crawler;
require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php';

echo "<pre>";

$capsule = new \Illuminate\Database\Capsule\Manager;
$capsule->addConnection([
    'driver' => 'mysql',
    'host' => 'localhost',
    'database' => 'tm_internship',
    'username' => 'root',
    'password' => '',
    'charset' => 'utf8',
    'collation' => 'utf8_general_ci',
    'prefix' => '',
]);
$capsule->setAsGlobal();
$capsule->bootEloquent();
$curl = new \parser\utils\Curl();
$list=[];
$baseUrl = 'https://top100.rambler.ru';
$isContent=true;
$pageIndex=1;

do{
    $isContent=true;
	$params=[
		'query'=>'веб-студия',
 		'page'=>$pageIndex
 	];
	$response=$curl->call("https://top100.rambler.ru/?".http_build_query( $params));
	$crawler = new Crawler($response);
	if($crawler->filter('table.projects-table_catalogue')===null){
	 	$isContent=false;
	}

    if(!$isContent){
        break;
    }	
	$crawler->filter('tr.projects-table__row')->each(function(Crawler $node,$i) use (&$list,$baseUrl) {
		 $_node = $node->getNode(0);
    	$list[$i] = isset($list[$i]) ? $list[$i] : [];
    	$list[$i]['url'] = '';
    	$list[$i]['name'] = '';
    	$list[$i]['uid'] = '';    
    	$list[$i]['visitors'] = 0;
    	$list[$i]['popularity'] = 0;   
    	$list[$i]['views'] = 0;   
    	$node
        	->filter('.link_catalogue-site-link')
        	->each(function (Crawler $node) use ($i, &$list, $baseUrl) {
            	$_node = $node->getNode(0);
                $url = $_node->getAttribute('href');
                $name = $_node->getAttribute('title');
                $id = $_node->getAttribute('name'); 
                $list[$i]['url'] = \parser\helpers\StringHelper::clean($url);
                $list[$i]['name'] = \parser\helpers\StringHelper::clean($name);
                $list[$i]['uid'] = \parser\helpers\StringHelper::clean($id);
        });
        $node
            ->filter('.projects-table__cell[data-content="visitors"] .projects-table__textline')
            ->each(function (Crawler $node) use ($i, &$list, $baseUrl) {
                $_node = $node->getNode(0); 
                $vstrs = $_node->nodeValue;
                $vstrs = preg_replace("/[^x\d|*\.]/", "", $vstrs);
                $list[$i]['visitors'] = $vstrs; 
            });	
        $node
           ->filter('.projects-table__cell[data-content="views"] .projects-table__textline')
           ->each(function (Crawler $node) use ($i, &$list, $baseUrl) {
               $_node = $node->getNode(0);
                $vws = $_node->nodeValue;
                $vws = preg_replace("/[^x\d|*\.]/", "", $vws);
                $list[$i]['views'] = $vws;
            });
        $node
        ->filter('.projects-table__cell[data-content="popularity"] .projects-table__textline')
        ->each(function (Crawler $node) use ($i, &$list, $baseUrl) {
            $_node = $node->getNode(0);
            $pop = $_node->nodeValue;
            $pop = preg_replace("/[^x\d|*\.]/", "", $pop);
            $list[$i]['popularity'] =  $pop;
        });
    });
		
    foreach ($list as $i =>$item) {
        if(empty($item['url'])){
            continue;
        }

        $model = \parser\models\Parse::query()
            ->where('url', '=', $item['url'])
            ->where('uid', '=', $item['uid'])
            ->first();

            if($model === null){
                $model = new \parser\models\Parse();
            }
            $model->name = $item['name'];
            $model->url = $item['url'];
            $model->uid = $item['uid'];
            $model->visitors = $item['visitors'];
            $model->views = $item['views'];
            $model->popularity = $item['popularity'];
            $model->save();
    }
	$pageIndex++;		
}while($isContent);
