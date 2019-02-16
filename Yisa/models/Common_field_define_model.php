<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
 * 常用字段定义模型
 */
class common_field_define_model extends CI_Model
{
	
	public function __construct(){
        parent::__construct();
        $this->load->database();
    }
	
    public function select($table){
		$this->db->trans_start();
        $this->db->select('*');
        $this->db->from($table);
        $result = $this->db->get();
        if($result && $result->num_rows()>0){
			$this->db->trans_complete();
            return $result->result_array();
        }else{
			$this->db->trans_rollback();
            return FALSE;
        }
    }
	
	public function selectItem($table,$id){
		$this->db->select('field_name,field_mean');
		$this->db->where('id',$id);
        $this->db->from($table);
        $result = $this->db->get();
        if($result && $result->num_rows()>0){
            return $result->result_array()[0];
        }
	}

    public function delete($table,$id){
		$this->db->trans_start();
        if($this->db->delete($table,array('id'=>$id))){
        	$max_id = $this->get_max_id($table);
        	for($i=$id+1;$i<=$max_id;$i++){
				$sql = 'UPDATE '.$table.' SET id=id-1 WHERE id='.$i;
				if(!$this->db->query($sql)){
					$this->db->trans_rollback();
				}
			}
			$this->db->trans_complete();
            return TRUE;
        }else{
			$this->db->trans_rollback();
            return FALSE;
        }
    }
	
    public function update($table,$id,$field_name,$field_mean){
		$this->db->trans_start();
        $data = array('field_name'=>$field_name,'field_mean'=>$field_mean);
        if($this->db->update($table,$data,array('id'=>$id))){
			$this->db->trans_complete();
            return true;
        }else{
			$this->db->trans_rollback();
            return false;
        }

    }
	
	public function get_max_id($table){
		$this->db->trans_start();
		$sql = "SELECT id FROM ".$table." ORDER BY id DESC LIMIT 0,1;";
		$result = $this->db->query($sql)->result_array();
		if(!empty($result))
		{
			$id = $result[0]['id'];
			$this->db->trans_complete();
			return $id;
		}else{
			$this->db->trans_rollback();
			return false;
		}
	}
	
    public function insert($table,$id,$field_name,$field_mean){
    	$max_id = $this->get_max_id($table);
    	$this->db->trans_start();
		if (is_numeric($id) && $id != 0 &&$id<=$max_id) {
			for($i=$max_id;$i>=$id;$i--){
				$sql = 'UPDATE '.$table.' SET id=id+1 WHERE id='.$i;
				if(!$this->db->query($sql)){
					$this->db->trans_rollback();
				}
			}
			$sql = 'INSERT INTO '.$table.' VALUES ("'.$id.'","'.$field_name.'","'.$field_mean.'")';
			if($this->db->query($sql)){
				$this->db->trans_complete();
				return true;
			}else{
				$this->db->trans_rollback();
				return false;
			}
		}else{
			$sql = 'INSERT INTO '.$table.' VALUES ("'.($max_id+1).'","'.$field_name.'","'.$field_mean.'")';
			if ($this->db->query($sql)) {
				$this->db->trans_complete();
				return true;
			}else{
				$this->db->trans_rollback();
				return false;
			}
		}
    }
}