<?php
namespace app\index\controller;
use think\Controller;
 /**
 * 
 */
 class Test extends Controller
 {
 	public function index()
 	{echo "string";
 	//return view('index',['name'=>'thinkphp']);
 	//var_dump('aa');
 	return $this->fetch();
 	}
 }