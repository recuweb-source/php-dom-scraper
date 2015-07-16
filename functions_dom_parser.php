<?php
	
	function strip_single_tag($str,$tag){
		
		$str=preg_replace('/<'.$tag.'[^>]*>/i', '', $str);
		
		$str=preg_replace('/<\/'.$tag.'>/i', '', $str);
		
		return $str;
	}	
	
	function parse_dom_contents($doc_contents='',$doc_type='html'){
		
		$dom_contents=[];
		
		if($doc_type=='html'){
			
			//-----------------parse html contents---------------
			
			$dom_contents['html:head'] = '';
			$dom_contents['html:links'] = '';
			$dom_contents['html:scripts'] = '';
			$dom_contents['html:styles'] = '';
			$dom_contents['html:body'] = '';
			
			//-----------------parse doc_contents---------------
			
			$dom = new DOMDocument;
			
			$dom->loadHTML($doc_contents);
			
			//----------parse head---------
			
			$head = $dom->getElementsByTagName('head');
			
			if($head && 0 < $head->length){
				
				$dom_contents['html:head'] =  strip_single_tag($dom->savehtml($head->item(0)),'head');
			}
			
			//----------parse link---------
		
			$links = $dom->getElementsByTagName('link');
			
			if($links && 0 < $links->length){
			
				foreach($links as $i => $link){

					if($link->hasAttributes() && $url = $link->getAttribute('href')){
						
						foreach($link->attributes as $attr){
							
							$name = $attr->nodeName;
							$value = $attr->nodeValue;									
							$dom_contents['html:links'][$url][$name]=$value;
						}
						
						$dom_contents['html:links'][$url]['string']=custom_trim($dom->savehtml($links->item($i)));
					}
				}
			}
			
			//----------parse script---------
		
			$scripts = $dom->getElementsByTagName('script');
			
			if($scripts && 0 < $scripts->length){
			
				foreach($scripts as $i => $script){

					if($script->hasAttributes() && $url = $script->getAttribute('src')){
						
						foreach($script->attributes as $attr){
							
							$name = $attr->nodeName;
							$value = $attr->nodeValue;									
							$dom_contents['html:scripts'][$url][$name]=$value;
						}
						
						$dom_contents['html:scripts'][$url]['string']=custom_trim($dom->savehtml($scripts->item($i)));
					}
				}
			}

			//----------parse style---------
		
			$styles = $dom->getElementsByTagName('style');
			
			if($styles && 0 < $styles->length){
			
				foreach($styles as $i => $style){

					if($style->hasAttributes()){
						
						foreach($style->attributes as $attr){
							
							$name = $attr->nodeName;
							$value = $attr->nodeValue;									
							$dom_contents['html:styles'][$i][$name]=$value;
						}
					}
					
					$dom_contents['html:styles'][$i]['string']=strip_single_tag(custom_trim($dom->savehtml($styles->item($i))),'style');
				}
				
			}				
			
			//----------parse body---------
			
			$body = $dom->getElementsByTagName('body');
			
			if($body && 0 < $body->length){

				$dom_contents['html:body'] = strip_single_tag($dom->savehtml($body->item(0)),'body');
			}
		}
		elseif($doc_type=='css'){
			
			$dom_contents['css']=parse_css_selectors($doc_contents);
		}
		
		return $dom_contents;	
	}

	function parse_css_selectors($css){
		
		preg_match_all('/([^\{\}]+)\{([^\}]*)\}/ims', $css, $arr);
		
		// TODO: @(media|import|local)
		
		$result = [];
		
		foreach ($arr[0] as $i => $x){
			
			$selector = trim($arr[1][$i]);
			$rules = explode(';', trim($arr[2][$i]));
			$rules_arr = [];
			
			foreach ($rules as $strRule){
				
				if (!empty($strRule)){
					
					$rule = explode(":", $strRule);
					$rules_arr[trim($rule[0])] = trim($rule[1]);
				}
			}

			$selectors = explode(',', trim($selector));
			
			foreach ($selectors as $strSel){
				
				$result[$strSel] = $rules_arr;
			}
		}
		
		return $result;
	}
