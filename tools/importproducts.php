<?php
ini_set('display_errors', 1);
require_once("xmlparse.php");

require('../config.php');
require(DIR_SYSTEM . 'library/db.php');
// Database 
$db = new DB(DB_DRIVER, DB_HOSTNAME, DB_USERNAME, DB_PASSWORD, DB_DATABASE);

$xmlstr = file_get_contents("products.xml");

$xml = parse_xml($xmlstr);

function add_category($category, $parent_id = 0) {
	global $db;
	
	$cat_id = 0;
	$cat = $db->query("select cd.name,c.category_id from oc_category_description cd 
		left outer join oc_category c on cd.category_id = c.category_id where name='$category'");
	if ($cat->num_rows == 0 || $cat->row["category_id"] == null) {
		$db->query("insert into oc_category set parent_id=0, top=0, image='', `column`=1, sort_order=10, status=1,
			date_added=now(), date_modified=now()");
		$cat_id = $db->getLastId();
		$db->query("insert into oc_category_description set category_id=$cat_id, language_id=1,
			name='$category'");
		$db->query("insert into oc_category_to_store set category_id=$cat_id, store_id=0");
		$db->query("insert into oc_category_path set category_id=$cat_id, path_id=$cat_id, level=1");
		if ($parent_id > 0) {
			$db->query("insert into oc_category_path set category_id=$cat_id, path_id=$parent_id, level=0");
		}
	}
	else {
		$cat_id = $cat->row["category_id"];
	}
	return $cat_id;
}

function make_sql($type, $row) {
	global $db;
	
	$sql = array();
	if ($type == "products") {
		// 产品类别导入
		if ($row[0] && $row[1]) {
			$cat_id = add_category($row[0]);
			if ($cat_id > 0) {
				$cat_id = add_category($row[1], $cat_id);
				if ($cat_id == 0) {
					echo "category import error.\n";
					return;
				}
			}
		}
		
		// 产品信息导入
		$product = $db->query("select product_id from oc_product_description where name = '".$row[2]."'");
		if ($product == false) return;
		if ($product->num_rows > 0)
			$product_id = $product->row["product_id"];
		else
			$product_id = 0;
		
		if ($product_id > 0) {
			if ( $db->query("update oc_product set ".
				"model='".((isset($row[3]))?$row[3]:"").
				"', cost='".((isset($row[4]))?$row[4]:"0").
				"', price='".((isset($row[6]))?$row[6]:"0").
				"', mpn='".((isset($row[8]))?$row[8]:"0").
				"', sellprice='".((isset($row[8]))?$row[8]:"0").
				"', weight='".((isset($row[9]))?$row[9]:"0").
				"', sku='".((isset($row[10]))?$row[10]:"").
				"', upc='".((isset($row[11]))?$row[11]:"").
				"', status=1 where product_id=$product_id") == false)
				return;
			
			if ($cat_id > 0) {
				if ($db->query("update oc_product_to_category set category_id=$cat_id where product_id=$product_id") == false)
					return;
			}
		}
		else {
			if ( $db->query("insert into oc_product set ".
				"model='".((isset($row[3]))?$row[3]:"").
				"', cost='".((isset($row[4]))?$row[4]:"0").
				"', price='".((isset($row[6]))?$row[6]:"0").
				"', mpn='".((isset($row[8]))?$row[8]:"0").
				"', sellprice='".((isset($row[8]))?$row[8]:"0").
				"', weight='".((isset($row[9]))?$row[9]:"0").
				"', sku='".((isset($row[10]))?$row[10]:"").
				"', upc='".((isset($row[11]))?$row[11]:"").
				"', status=1, quantity=100, manufacturer_id=0, shipping=1, tax_class_id=0, date_added=now()") == false)
				return;
			
			$product_id = $db->getLastId();
			
			if ($db->query("insert into oc_product_description set product_id=$product_id, language_id=1,
				name='".$row[2]."'") == false)
				return;
				
			if ($cat_id > 0) {
				if ($db->query("insert into oc_product_to_category set product_id=$product_id, category_id=$cat_id") == false)
					return;
			}
		}

	} else if ($type == "provider") {
	}
}

function getLeaf($node) {
	if ($node->childs && $node->childs[0])
		return getLeaf($node->childs[0]);
	else
		return $node;
}

function echo_data($node) {
	global $db;
	
	if ( is_array($node) ) {
		foreach ( $node as $n) {
			echo_data( $n );
		}
	}

	if ( is_object($node) ) {
		if ($node->label == "Row") {
			$r = array();
			$i = 1;
			foreach($node->childs as $row) {
				if (isset($row->attrib["ss:Index"])) {
					$idx = (int)$row->attrib["ss:Index"];
					for(; $i < $idx; $i++) {
						$r[] = null;
					}
				}

				$r[] = $db->escape(getLeaf($row)->value);
				$i++;
			}
			
			if (count($r) > 5)
				$type = "products";
			else if (count($r) == 3)
				$type = "provider";
			
			if (isset($type)) {
				//print_r($r);
				if ($r[2] != "品名" && $r[2] != "补差") {
					make_sql($type, $r);
				}
			}
		}
		else {
			if ($node->label == "Worksheet") {
				if ($node->attrib && $node->attrib["ss:Name"])
					echo $node->attrib["ss:Name"] . "\n";
			}

			if ($node->childs) {
				foreach ( $node->childs as $n) {
					echo_data( $n );
				}
			}
		}
	}
}

echo_data($xml);
?>