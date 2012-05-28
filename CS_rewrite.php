<?php
/*
* This class is used to create rewrites for ClickSold with respect to Wordpress. Using this class one can
* get the rewrite rules in the form of an array (getRewriteRuleArray) that can be used to add to the Wordpress rewrite rules array
* by adding a filter and hooking onto the rewrite_rules_array event: add_filter('rewrite_rules_array','aFunctionThatCallsThisClass')
*
* Copyright (C) 2012 ClickSold.com
*
* This program is free software; you can redistribute it and/or
* modify it under the terms of the GNU General Public License
* as published by the Free Software Foundation; either version 2
* of the License, or (at your option) any later version.
* 
* This program is distributed in the hope that it will be useful,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
* GNU General Public License for more details.
* 
* You should have received a copy of the GNU General Public License
* along with this program; if not, write to the Free Software
* Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
*/

class CS_rewrite {

	protected $parameters = array(); //array containing all the parameters
	protected $sub_pages  = array(); //array of sub_pages with only the subpage ID and postname parameter as a column
	protected $pagename;
	protected $show_parameter_in_key;    // boolean to indicate if we will include the parameter in the rewrite rule/.
					 // for example: CS_rewrite('listings', array("mlsnum"), true);
					 //              ...
					 //              $rewrite_array = getRewriteRule(); 
					 // rewrite array would contain for the:
					 // 1. key: listings/mlsnum/([^/]+)
					 //              CS_ewrite('listings',array("mlsnum"), false);
					 // then the key would just be: listings/([^/]+)
	/**
     * constructor. Takes in a pagename, array of parameters that the rewrite rule supports, subpages for this post,
	 * and a boolean value used to indicate wheather or not to display these parameters in the rewrite rule.
     */
	public function __construct($pagename, $params, $sub_pages, $show_parameter_in_key){

		$this->pagename = $pagename;
		$this->show_parameter_in_key = $show_parameter_in_key;
		$this->sub_pages = $sub_pages;
		//loop through and add all the parameters in $param array into $this->parameters array
		for($i = 0; $i < count($params); $i++)
			$this->add($i, $params[$i]);
	}
	/**
	 * function that adds an element to $this->parameters array
     */
	public function add($i, $param){

		$this->parameters[$i] = $param;

	}
	/**
     * Get the rewrite rule array for this.
     * example: if pagename =  listings, params = ["mlsnum"], and show_parameter_in_key =  true, then
	 * format of returned array:
     * array("listings/$?" => "index.php?pagename=listings", 
     *       "listings/mlsnum/([^/]+)/$?" => 'index.php?pagename=listings&mlsnum=$matches[1]');
	 * if show_parameter_in_key = false
     * array("listings/$?" => "index.php?pagename=listings", 
     *       "listings/([^/]+)/$?" => 'index.php?pagename=listings&mlsnum=$matches[1]'); <--- just doesnt have mlsnum in pattern
     */	
	public function getRewriteRuleArray(){
		
		$rewrite_rule_array = array();

		//first add the rewrite rules for the sub pages before the rewrite rules 
		//for the parameters since wordpress rewriting adds the more specific
		//rewrite rules before the less specific rules		
		foreach($this->sub_pages as $subpage){
			$array = array();
			$array[$this->pagename."/".$subpage->post_name."/?$"] = "index.php?page_id=$subpage->ID";
			$rewrite_rule_array = $rewrite_rule_array + $array;
		}

		//finally, form rewrite rules for the specific page
		$parameter_count = count($this->parameters);
		for($i = 0; $i <= $parameter_count; $i++){
			$array = array();
			$patternPart   = $this->__getPatternPartOfArray($i);
			$rewritePart = $this->__getRewritePartOfArray($i); 
			$array[$patternPart] = $rewritePart;

			$rewrite_rule_array = $rewrite_rule_array + $array;
		}
		
		return $rewrite_rule_array;
	}
	/**
	 * returns the pattern portion for specfic rewrite rule
	 */
	private function __getPatternPartOfArray($count){
		$aParameter = "(.*)"; //match anything after the post name.
		if($count == 0) return $this->pagename . "/?$";		

		$keyPart = $this->pagename . "/";
		for($i = 0; $i < $count; $i++){

			if($this->show_parameter_in_key)
				$keyPart = $keyPart . $this->parameters[$i] . "/" . $aParameter . "/";
			else $keyPart = $keyPart . $aParameter . "/";
		}
		
		$keyPart = $keyPart . "?$"; //append end string
		//echo "keyPart:".$keyPart."\n";
		return $keyPart;
		
	}
	/**
	 * returns the rewrite portion of a specfic rewrite rule.
	 */
	private function __getRewritePartOfArray($count){

		if($count == 0) return 'index.php?pagename=' . $this->pagename;
		
		$valuePart = 'index.php?pagename=' . $this->pagename;
		for($i = 0; $i < $count; $i++){
			$index = $i+1;
			$aParameter = "\$matches[$index]"; //escape the $ symbol here is we don't want php
						           //to confuse $match as a variable in this function
			$valuePart = $valuePart . "&" . $this->parameters[$i] . "=" . $aParameter;
		}
		//echo "valuePart:".$valuePart."\n";
		return $valuePart;
		
	}
	
}

?>
