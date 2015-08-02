<?php
//include_once "chinese/class.Chinese.php";

class XmlNode {
	public $label;
	public $attrib;
	public $value;
	public $childs;
};

function _parse_xml( &$node, &$vals, $begin, $n)
{
	$trim_char = " \t\xD\xA";
	for ( $i = $begin; $i < $n; $i++ )
	{
		$val = $vals[$i];

		switch ( $val["type"] ):
		case "open":
			$new_node = new XmlNode;
			$new_node->label = $val["tag"];
			if ( isset($val["attributes"]) )
				$new_node->attrib = $val["attributes"];

			$i = _parse_xml( $new_node, $vals, $i+1, $n);

			if ( $begin == 0 )
			{
				$node = $new_node;
			}
			else
			{
				if ( is_array($node->childs) == false )
					$node->childs = array();
				array_push( $node->childs, $new_node );
			}
			break;
		case "complete":
			if ($i == 0)
			{
				$node->label = $val["tag"];
				if ( isset($val["attributes"]) )
					$node->attrib = $val["attributes"];
				$node->value = trim($val["value"], $trim_char);
			}
			else 
			{
				$new_node = new XmlNode;
				$new_node->label = $val["tag"];
				if ( isset($val["attributes"]) )
					$new_node->attrib = $val["attributes"];
				if ( isset($val["value"]) )
					$new_node->value = trim($val["value"], $trim_char);

				if ( is_array($node->childs) == false )
					$node->childs = array();
				array_push( $node->childs, $new_node );
			}
			
			break;
		case "close":
			return $i;
		endswitch;
	}
}

function _export_attrib( &$node )
{
	$ret = "";
	if ( is_array($node->attrib) )
	{
		foreach ( $node->attrib as $key => $val )
		{
			$ret .= " $key=\"$val\"";
		}
	}
	return $ret;
}

function _export_xml( &$node, $black = "" )
{
	$ret = "";
	if ( is_object($node) )
	{
		if ( is_null($node->value) ||  trim($node->value) == "")
		{
			$attr = _export_attrib($node);
			$ret .= "$black<$node->label$attr>\n";
			$ret .= _export_xml( $node->childs, "$black\t" );
			$ret .= "$black</$node->label>\n";
		}
		else
		{
			$attr = _export_attrib($node);
			$ret .= "$black<$node->label$attr>$node->value</$node->label>\n";
		}
	}
	else
	{
		for ( $i = 0; $i < count($node); $i++ )
		{
			$ret .= _export_xml( $node[$i], "$black" );
		}
	}

	return $ret;
}

function export_xml( $xmldom )
{
	$xml = "<?xml version=\"1.0\" encoding=\"GBK\"?>\n";
	/*$xml = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n";*/
	$xml .= _export_xml( $xmldom );

	return $xml;
}


function translate_encode($xmlstr)
{
	$tmp = explode("\n", $xmlstr);
	$xmlstr = "";
	$i = 0;
	foreach($tmp as $val)
	{
		if ($i++ == 0)
		{
			$xmlstr = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n";
			continue;
		}
		$xmlstr .= $val."\n";
	}
	$xmlstr = iconv("GBK", "UTF-8//IGNORE", $xmlstr);
	unset($tmp);
	return $xmlstr;
}


function parse_xml( $xmlstr )
{
//	$xmlstr = translate_encode($xmlstr);
	$p = xml_parser_create("UTF-8");
	xml_parser_set_option($p,XML_OPTION_CASE_FOLDING,0);
	xml_parser_set_option($p,XML_OPTION_SKIP_WHITE,1);
	xml_parse_into_struct($p, $xmlstr, $vals, $tags);
	xml_parser_free($p);// < 1s
	
	$ret = new XmlNode;
	_parse_xml( $ret, $vals, 0,count($vals));
	return $ret;
}



?>
