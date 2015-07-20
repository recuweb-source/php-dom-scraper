# php-dom-scraper
A simple PHP DOM scraper based on the DOMDocument class and `preg_match()` functions

#### Supported

- html
- css

## SCHEMA

### HTML

	$dom_contents['html:head'] = [];
	$dom_contents['html:links'] = [];
	$dom_contents['html:scripts'] = [];
	$dom_contents['html:styles'] = [];
	$dom_contents['html:body'] = [];
	$dom_contents['html:xpath'] = [];
	
### CSS

	$dom_contents['css'][$media_query][$selector] = $value;

## USAGE

### HTML

    $html_contents = file_get_contents($url);
    
    $dom_contents = parse_dom_contents($html_contents,'html');
    
### CSS

    	$css_contents = file_get_contents($url);
    
    	$dom_contents = parse_dom_contents($css_contents,'css');

	foreach($css_selectors as $block => $selector){
							
		if(!is_numeric($block)){
								
			// open css media query
			
			$css.= $block.' {';		
		}
		
		foreach($selector as $selector_name => $selector_data){
			
			//open css selector
			
			$css.=$selector_name.' {';
			
			foreach($selector_data as $name => $value){
				
				$css.=$name.':'.$value.';';
			}
			
			//close css selector
			
			$css.= '}';
		}
		
		if(!is_numeric($block)){
								
			// close css media query
			
			$css.= '}';				
		}
	}

## TODO

- html -> ~~xpath~~, a, meta
- css  -> ~~@media~~
- xml
- rss
- atom
