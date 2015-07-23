<?php //é

	function strip_single_tag($str,$tag){
		
		$str=preg_replace('/<'.$tag.'[^>]*>/i', '', $str);
		
		$str=preg_replace('/<\/'.$tag.'>/i', '', $str);
		
		return $str;
	}	
	
	function parse_dom_contents($doc_contents='',$doc_type='html',$doc_options=array()){
		
		$dom_contents= [];
		
		if($doc_type=='html'){
			
			//-----------------parse html contents---------------
			
			$dom_contents['html:head'] = [];
			$dom_contents['html:links'] = [];
			$dom_contents['html:scripts'] = [];
			$dom_contents['html:styles'] = [];
			$dom_contents['html:body'] = [];
			$dom_contents['html:xpath'] = [];
			
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
						
						$dom_contents['html:links'][$url]['string']=trim($dom->savehtml($links->item($i)));
					}
				}
			}
			
			//----------parse scripts---------
		
			$scripts = $dom->getElementsByTagName('script');
			
			if($scripts && 0 < $scripts->length){
			
				foreach($scripts as $i => $script){
					
					if($script->hasAttributes()){
						
						foreach($script->attributes as $attr){
							
							$name = $attr->nodeName;
							$value = $attr->nodeValue;

							$dom_contents['html:scripts'][$i][$name]=$value;
						}
					}

					$dom_contents['html:scripts'][$i]['string']=trim($dom->savehtml($scripts->item($i)));
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
					
					$dom_contents['html:styles'][$i]['string']=strip_single_tag(trim($dom->savehtml($styles->item($i))),'style');
				}
			}				
			
			//----------parse body---------
			
			$body = $dom->getElementsByTagName('body');
			
			if($body && 0 < $body->length){

				$dom_contents['html:body'] = strip_single_tag($dom->savehtml($body->item(0)),'body');
			}
			
			//----------parse xpath---------
			
			if(isset($doc_options['xpath'])){
				
				$xpath = new DOMXPath($dom);
				
				$query = $xpath->query($doc_options['xpath']);
				
				if(!empty($query)){
					
					$dom_contents['html:xpath'] = $dom->savehtml($query->item(0));
				}
			}			
		}
		elseif($doc_type=='css'){
			
			$dom_contents['css']=parse_css_selectors($doc_contents);
		}
		
		return $dom_contents;	
	}
	
	function parse_css_selectors($css,$media_queries=true){
		
		$result = $media_blocks = [];
		
		//---------------parse css media queries------------------
		
		if($media_queries==true){
		
			$media_blocks=parse_css_media_queries($css);
		}

		$b=0;		
		
		if(!empty($media_blocks)){
			
			//---------------get css blocks-----------------
			
			$css_blocks=$css;
			
			foreach($media_blocks as $media_block){
				
				$css_blocks=str_ireplace($media_block,'~£&#'.$media_block.'~£&#',$css_blocks);
			}
			
			$css_blocks=explode('~£&#',$css_blocks);
			
			//---------------parse css blocks-----------------
			
			foreach($css_blocks as $css_block){
				
				preg_match('/(\@media[^\{]+)\{(.*)\}\s+/ims',$css_block,$block);
				
				if(isset($block[2])&&!empty($block[2])){
					
					$result[$block[1]]=parse_css_selectors($block[2],false);
				}
				else{
					
					$result[$b]=parse_css_selectors($css_block,false);
				}
				
				++$b;
			}
		}
		else{
			
			//---------------escape base64 images------------------
			
			$css=preg_replace('/(data\:[^;]+);/i','$1~£&#',$css);
			
			//---------------parse css selectors------------------
			
			preg_match_all('/([^\{\}]+)\{([^\}]*)\}/ims', $css, $arr);

			foreach ($arr[0] as $i => $x){
				
				$selector = trim($arr[1][$i]);
				
				$rules = explode(';', trim($arr[2][$i]));
				
				$rules_arr = [];
				
				foreach($rules as $strRule){
					
					if(!empty($strRule)){
						
						$rule = explode(":", $strRule,2);
				
						if(isset($rule[1])){
							
							$rules_arr[trim($rule[0])] = str_replace('~£&#',';',trim($rule[1]));
						}
						else{
							//debug
						}
					}
				}

				$selectors = explode(',', trim($selector));
				
				foreach ($selectors as $strSel){
					
					$result[$b][$strSel] = $rules_arr;
				}
			}
		}
		
		vdump($result);
		return $result;
	}
	
	function parse_css_media_queries($css){
		
		$mediaBlocks = array();

		$start = 0;
		while(($start = strpos($css, "@media", $start)) !== false){
			
			// stack to manage brackets
			$s = array();

			// get the first opening bracket
			$i = strpos($css, "{", $start);

			// if $i is false, then there is probably a css syntax error
			if ($i !== false){
				
				// push bracket onto stack
				array_push($s, $css[$i]);

				// move past first bracket
				$i++;

				while (!empty($s)){
					
					// if the character is an opening bracket, push it onto the stack, otherwise pop the stack
					if ($css[$i] == "{"){
						
						array_push($s, "{");
					}
					elseif ($css[$i] == "}"){
						
						array_pop($s);
					}

					$i++;
				}

				// cut the media block out of the css and store
				$mediaBlocks[] = substr($css, $start, ($i + 1) - $start);

				// set the new $start to the end of the block
				$start = $i;
			}
		}

		return $mediaBlocks;
	}
