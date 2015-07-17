<?php  
class ControllerRestCategory extends Controller {
	public function index() {
		
		$cate = new stdClass();
		$cate->result = 0;
		$cate->info = array();
		
		$this->load->model('catalog/category');
		
		$results = $this->model_catalog_category->getCategories(0);
		
		foreach ($results as $result) {
			$subcats = $this->model_catalog_category->getCategories($result['category_id']);
			$childs = array();
			$c = new stdClass();
			$c->cat_id = $result['parent_id'];
			$c->parent_id = $result['parent_id'];
			$c->name = '全部';
			$childs[] = $c;
			
			foreach($subcats as $sub) {
				$c = new stdClass();
				$c->cat_id = $sub['category_id'];
				$c->parent_id = $sub['parent_id'];
				$c->name = $sub['name'];
				$childs[] = $c;
			}
		
			$c = new stdClass();
			$c->cat_id = $result['category_id'];
			$c->parent_id = $result['parent_id'];
			$c->name = $result['name'];
			$c->child = $childs;
			$cate->info[] = $c;
		}
		
		$this->response->setOutput(json_encode($cate));
	}
}
?>