<?php
namespace Admin\Controller;
use Think\Controller;
class PersonnelController extends Controller {

	public function all(){
		$Model = M('personnel');
		$r = $Model->select();
    $this->assign('data', json_encode($r));

    $columns = M('system')->field('en_name as data, zh_name')->where("isdisplay='1'")->select();
    array_unshift($columns, array('data' => 'id', 'zh_name' => '编号'));
    array_push($columns, array('data' => 'btn', 'zh_name' => '操作'));
    $this->assign('thead', $columns);//表头
    $this->assign('columns', json_encode($columns));//datatable里的data

    $list = M('system')->select();
    $this->assign('list', $list);//输入字段名和选择字段名
		$this->display('personnel/all');
	}  

	public function file(){
		$this->display('personnel/file');
	} 

	public function get(){
		$param['id'] = I('param.id');
		$Model = M('personnel');
		$r = $Model->where($param)->find();
		echo json_encode($r);
	}

	public function save(){
		$param['id'] = I('param.id');
		$data = I('param.');
		$Model = M('personnel');
		if($param['id'] == ''){
			$r = $Model->add($data);
		}else{
			$r = $Model->where($param)->save($data);
		}
		echo json_encode($r);
	}

	public function del(){
		$param['id'] = I('param.id');
		$Model = M('personnel');
		$r = $Model->where($param)->delete();
		echo json_encode($r);
	}

	public function uploadexcel(){
    if (($_FILES["file"]["type"] == "application/vnd.ms-excel")) {
        if ($_FILES["file"]["error"] > 0) {
            //echo "Return Code: " . $_FILES["file"]["error"] . "<br />";
            $this->error($_FILES["file"]["error"]);
        } else {
          $path = DOC_ROOT .'/Public/upload/'.$_FILES["file"]["name"];
          move_uploaded_file(realpath($_FILES["file"]["tmp_name"]), $path);
          // 操作excel
          import('Org.Util.PHPExcel');
          import('Org.Util.PHPExcel.IOFactory');
          $objPHPExcel = \PHPExcel_IOFactory::load($path);
          $objPHPExcel->setActiveSheetIndex(0);
          $info = array();
          $System = M('system');
          $Personnel = M('personnel');
          $sheet = $objPHPExcel->getActiveSheet();
          $rowIterator = $objPHPExcel->getActiveSheet()->getRowIterator();
          foreach ($rowIterator as $row) {
              $line = $row->getRowIndex();
              if($line < 2){
                  continue;
              }

              unset($info);

              $cellIterator = $row->getCellIterator();
              $cellIterator->setIterateOnlyExistingCells(true); 
              
              $col = 0;
              foreach ($cellIterator as $cell) {
                if (!is_null($cell)) {
                    $linename = $cell->getCoordinate();
										
										//找出excel的名称对应的数据库表中的名字
                    $param['zh_name'] = $sheet->getCellByColumnAndRow($col, 1)->getCalculatedValue();
                    //var_dump ($param['zh_name']);
                    $r = $System->where($param)->find();
                    $info[$r['en_name']] = $cell->getCalculatedValue();
                }
                $col++;
              }
              if(isset($info['idcard'])){
                $r = $Personnel->where("idcard='%s'", $info['idcard'])->find();
                if(count($r) == 0){
                  $r = $Personnel->add($info);
                }else{
                  $r = $Personnel->where("idcard='%s'", $info['idcard'])->save($info);
                }
              }else{
                $r = $Personnel->add($info);
              }
              
              if($r !== false){
            		continue;
            	}else{
            		$this->error('第'.($line).'行数据错误:操作失败');
            		break;
            	}
          }
  				
          $this->success('成功导入'.($line-1).'个人员信息！');
          unlink($path);
        }
    }else {
        //var_dump($_FILES["file"]);
        $this->error('文件信息错误');
    }
  }

  public function exportexcel(){
    //查询所有人员信息
    $param['id'] = I('param.ids');
    $field = I('param.property');
    foreach ($param['id'] as &$id) {
      $id = array('eq', $id);
    }
    array_push($param['id'], 'or');
    $content = M('personnel')->field($field)->where($param)->select();

    //查询所有字段中文名
    foreach ($field as &$en_name) {
      $en_name = array('eq', $en_name);
    }
    array_push($field, 'or');
    $p['en_name'] = $field;
    $property = M('system')->field('en_name, zh_name')->where($p)->select();

    echo json_encode($content);
    echo json_encode($property);

    // 操作excel
    import('Org.Util.PHPExcel');
    import("Org.Util.PHPExcel.Writer.Excel5");
    import("Org.Util.PHPExcel.IOFactory.php");
    import("Org.Util.PHPExcel.*");
    $objPHPExcel = new \PHPExcel();
    // $objPHPExcel->getProperties()
    //   ->setCreator("转弯的阳光")
    //   ->setLastModifiedBy("转弯的阳光")
    //   ->setTitle("数据EXCEL导出")
    //   ->setSubject("数据EXCEL导出")
    //   ->setDescription("备份数据")
    //   ->setKeywords("excel")
    //   ->setCategory("result file");
    $objPHPExcel->setActiveSheetIndex(0);
    $sheet = $objPHPExcel->getActiveSheet();
    for ($i=0; $i < count($property); $i++) {
      $sheet->getStyle(chr(ord('A')+$i))->getNumberFormat()->setFormatCode(\PHPExcel_Style_NumberFormat::FORMAT_TEXT);
      $sheet->setCellValueExplicit(chr(ord('A')+$i).'1', $property[$i]['zh_name'], \PHPExcel_Cell_DataType::TYPE_STRING);
    }


    for ($i=0; $i < count($content); $i++) {
      for ($j=0; $j < count($property); $j++) { 
        $sheet->setCellValueExplicit(chr(ord('A')+$j).($i+2), $content[$i][$property[$j]['en_name']], \PHPExcel_Cell_DataType::TYPE_STRING);
      }
    }

    // 输出  
    $objPHPExcel->setActiveSheetIndex(0);
    ob_end_clean();//清除缓冲区,避免乱码 
    $fileName = "人员信息统计表.xls";
    $fileName = iconv("utf-8", "gb2312", $fileName);
    header('Content-Type: application/vnd.ms-excel');
    header("Content-Disposition: attachment;filename=\"$fileName\"");
    header('Cache-Control: max-age=0');

    $objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
    $objWriter->save('php://output'); //文件通过浏览器下载
    $objWriter->save('../../../'.$fileName);   //人员信息统计表(".date('Ymd-His').")
  }
}