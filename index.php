<?php 
require __DIR__ . '/vendor/autoload.php';
require_once 'include/DbConnect.php';

use \Slim\App;

$app = new App();

$db = $software;

$app-> get('/', function(){
    echo "API Cluster";
});


// CORS
$app->options('/{routes:.+}', function ($request, $response, $args) {
    return $response;
});


$app->add(function ($req, $res, $next) {
    $response = $next($req, $res);
    return $response
            ->withHeader('Access-Control-Allow-Origin', '*')
            ->withHeader('Access-Control-Allow-Headers', 'X-Requested-With, Content-Type, Accept, Origin, Authorization')
            ->withHeader('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS');
});


/* *
 * URL: https://localhost/api-cluster/index.php/login
 * Parameters: email, password
 * Method: POST
 * */
$app->post('/login', function($request, $response, $args) use($app, $db){
    $data = $request->getParams();
    
    $login = $db->view_data()
        ->where("email", $data['email'])
        ->where("password", $data['password']);
    
    if ($login->fetch()){
        foreach($login as $data){    
            $responseJson["status"]   		         = true;
            $responseJson["message"] 		         = "OK";
            $responseJson['result']['name']          = $data['nama'];
	        $responseJson['result']['email']         = $data['email'];
	        $responseJson['result']['alamat']        = $data['alamat'];
            $responseJson['result']['namaCluster']   = $data['namaCluster'];
	        $responseJson['result']['namaPerumahan'] = $data['namaPerumahan'];
            
            return $response->withJson($responseJson, 200);
        }
    } else {
        $responseJson['status']            = false;
        $responseJson['message']           = "Unauthorized";
        $responseJson['result']['title']   = "Login Gagal";
        $responseJson['result']['message'] = "Email atau password salah";
        
        return $response->withJson($responseJson, 401);
    }
});


/* *
 * URL: https://localhost/api-cluster/index.php/tagihan/id_user
 * Parameters: none
 * Method: GET
 * */
$app->get('/tagihan/{id_user}', function($request, $response, $args) use($app, $db){
    
    $tagihan = $db->tagihan()
	->where('id_user', $args['id_user'])
    ->where("status", "terbayar");

    if ($tagihan->count() == 0) {
        $responseJson["status"]  = false;
        $responseJson["message"] = "Belum ada data foto";
    } else {
        foreach($tagihan as $data){
            $responseJson[] = array(
                'id'        => $data['id_tagihan'],
                'month'     => $data['months'],
                'countDown' => $data['countdown'],
                'dueDate'   => $data['duedate'],
                'total'     => $data['total'],
                'status'    => $data['status']		
            );
        }
    }

    return $response->withJson($responseJson);
});

//run App
$app->run();