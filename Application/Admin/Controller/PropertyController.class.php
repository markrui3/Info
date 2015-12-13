<?php
namespace Admin\Controller;
use Think\Controller;
use Think\Model;
class PropertyController extends Controller {

	public function all(){
		$Model = M('system');
		$r = $Model->select();
    $this->assign('data', json_encode($r));
		$this->display('Property/all');
	} 

	public function get(){
		$param['id'] = I('param.id');
		$Model = M('system');
		$r = $Model->where($param)->find();
		echo json_encode($r);
	}   

	public function save(){
		$param['id'] = I('param.id');
		$data['en_name'] = I('param.en_name');
		$data['zh_name'] = I('param.zh_name');
		$data['isdisplay'] = I('param.isdisplay', '0');

		$Model = new Model();
		if($param['id'] == ''){
			$r1 = $Model->table('system')->add($data);
			$sql = "alter table personnel add ".$data['en_name']." varchar(255)";  
			$r2 = $Model->execute($sql);
		}else{
			$r = $Model->table('system')->where($param)->find();
			$r1 = $Model->table('system')->where($param)->save($data);
			$sql = "alter table personnel change ".$r['en_name']." ".$data['en_name']." varchar(255)";  
			$r2 = $Model->execute($sql);
		}
		echo json_encode($r1);
	}

	public function del(){
		$param['id'] = I('param.id');
		$Model = new Model();
		$r = $Model->table('system')->where($param)->find();
		$r1 = $Model->table('system')->where($param)->delete();
		$sql = "alter table personnel drop column ".$r['en_name'];  
		$r2 = $Model->execute($sql);
		echo json_encode($r2);
	}
}