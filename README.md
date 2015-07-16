# php-dom-scraper
A simple PHP DOM scraper based on the DOMDocument class

#### Support

- html
- css

## USAGE

    $html_contents = file_get_contents($url);
    
    $dom_contents = parse_dom_contents($html_contents,'html');

## OUTPUT

### HTML

	$dom_contents['html:head'] = '';
	$dom_contents['html:links'] = '';
	$dom_contents['html:scripts'] = '';
	$dom_contents['html:styles'] = '';
	$dom_contents['html:body'] = '';
	
### CSS

	$dom_contents['css'][$selector] = $value;

## TODO

- html -> <a>
- css  -> @(media|import|local)
- xml
- rss
- atom
