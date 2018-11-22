<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Article_model extends CI_Model {

	public function __construct()
	{
		parent::__construct();
	}

	public function lists($field = "*", $where = '', $order_by = "article_id", $order_by_dire = "DESC", $offset = 0, $limit = 20){	
		return $this->db->select($field)
			->from('article')
			->where($where)
			->order_by($order_by, $order_by_dire)
			->limit($limit, $offset)
			->get()
			->result_array();
	}

	public function count($where = ''){	
		return $this->db->select("count(1) as num")
			->from('article')
			->where($where)
			->get()
			->row_array();
	}

	public function detail($article_id = ''){	
		if(empty($article_id)){
			return array();
		}
		return $this->db->select('*')
						->get_where('article', array('article_id'=>$article_id))
						->row_array();
	}

	public function content($article_id = ''){	
		if(empty($article_id)){
			return array();
		}
		return $this->db->select('content')
						->get_where('article_content', array('article_id'=>$article_id))
						->row_array();
	}

	public function edit($data = "")
	{	
		if(empty($data)){
			return array();
		}
		return $this->db->update('article', $data, array('article_id' => $data['article_id']));
	}

	public function getArticleTagsList($cate_id = ""){	
		$sql = "select r.tag_id, t.tag_val, count(r.article_id) as t_num from hp_article_tag_rel r INNER JOIN hp_tags t ON t.tag_id = r.tag_id ";
		if($cate_id){
			$sql .= " INNER JOIN hp_article a ON a.article_id = r.article_id WHERE a.cate_id = '{$cate_id}' ";
		} 
		$sql .= " GROUP BY r.tag_id ORDER BY t_num DESC ";
		return $this->db->query($sql)->result_array();
	}

	public function getArticleIdByTags($tag_id = ""){	
		if(!$tag_id){
			return array();
		}

		$sql = "select article_id from hp_article_tag_rel WHERE tag_id = '{$tag_id}' order by article_id desc";
		return $this->db->query($sql)->result_array();
	}

	public function getTagsByArticleId($article_id = ""){	
		if(!$article_id){
			return array();
		}

		$sql = "select r.tag_id, t.tag_val from hp_article_tag_rel r INNER JOIN hp_tags t ON t.tag_id = r.tag_id WHERE r.article_id = '{$article_id}'";
		return $this->db->query($sql)->result_array();
	}
}
