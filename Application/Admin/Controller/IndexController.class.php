<?php
namespace Admin\Controller;
use Think\Controller;
class IndexController extends Controller {

	public function property(){
		$this->display('Property/all');
	}    
}