
/**
* Shows the help messages in the helpline window
*/
function helpline(help) {
	document.forms[form_name].helpbox.value = help_line[help];
}


/**
* Allow to use tab character when typing code
* Keep indentation of last line of code when typing code
*/
(function($) {
	$(document).ready(function() {
		
	});
})(jQuery);

/**
 * @return A javascript object that allows adding parameters and 
 * @source Based on s9e\TextFormatter\render.js
*/
var xslt = (function (xsl){
	// older IE has its own way of doing it
	var MSXML = (typeof DOMParser === 'undefined' || typeof XSLTProcessor === 'undefined');
	if (MSXML) {
		var ieStylesheet = new ActiveXObject('MSXML2.FreeThreadedDOMDocument.6.0');
		ieStylesheet.async = false;
		ieStylesheet.validateOnParse = false;
		ieStylesheet.loadXML(xsl);

		var ieGenerator = new ActiveXObject("MSXML2.XSLTemplate.6.0");
		ieGenerator.stylesheet = ieStylesheet;
		var ieTransformer = ieGenerator.createProcessor();
		
		return {
			'setParameter' : function (name, value){
				ieTransformer.addParameter(name, value, '');
			},
			
			'transformToFragment' : function (xml, onDocument){
				var div = onDocument.createElement('div'),
					fragment = onDocument.createDocumentFragment();

				var ieTargetStylesheet = new ActiveXObject('MSXML2.FreeThreadedDOMDocument.6.0');
				ieTargetStylesheet.async = false;
				ieTargetStylesheet.validateOnParse = false;
				ieTargetStylesheet.loadXML(xml);
				
				ieTransformer.input = ieTargetStylesheet
				ieTransformer.transform();
				
				div.innerHTML = ieTransformer.output;
				while (div.firstChild){
					fragment.appendChild(div.removeChild(div.firstChild));
				}

				return fragment;
			}
		};
	}else{
		var xslDoc = (new DOMParser).parseFromString(xml, 'text/xml');

		var processor = new XSLTProcessor();
		processor.importStylesheet(xslDoc);
		
		return {
			'setParameter': function (name, value){
				processor.setParameter(null, name, value);
			},
			
			'transformToFragment' : function (xml, onDocument){
				var xmlDoc = (new DOMParser).parseFromString(xml, 'text/xml');
				// NOTE: importNode() is used because of https://code.google.com/p/chromium/issues/detail?id=266305
				return onDocument.importNode(processor.transformToFragment(xmlDoc, onDocument), true);
			}
		};
		
	}	
})("");