{%- macro parseCase(bbcodeName, parent, childData) -%}
	{%- import _self as exec -%}

	{%- for var, one in data.caseVars %}
		xslt.setParameter('{{ var }}', attributes['{{ var }}']);
	{% endfor %}
	var conditionResult = xslt.transformToFragment(
		'<{{ bbcodeName }} d="{{ data.num }}"></{{ bbcodeName }}>',
		document
	).firstChild.nodeValue;

	switch(conditionResult[0]){
	{% for caseVal, caseData in data.case %}
		case '{{ caseVal }}':
			{{- exec.parse_node(bbcodeName, parent, childData.children) }}
		break;
	{% endfor %}
	}
{%- endmacro -%}

{%- macro parse_node(bbcodeName, append_to, children) -%}
	{%- import _self as exec -%}
	{%- for child in children -%}
		{% set node_name = child.js.nodeName %}
		
		{% if child.js.type == 'NODE_DEFINITION' %}
			{% set tag_attributes = child.js.attributes %}

			var {{ node_name }} = document.createElement("{{ child.tagName }}")
			{% for attrName, attrValue in tag_attributes %}
				{{ node_name }}.setAttribute("{{ attrName }}", "{{ attrValue }}");
				{% if attribute(child.js.bbcodeAttributes, attrName) is defined %}
					addBBCodeDataToElement(
						{{ node_name }},
						"{{ attribute(child.js.bbcodeAttributes, attrName) }}",
						attributes["{{ attribute(child.js.bbcodeAttributes, attrName) }}"]
					);
				{% endif %}
				{% if child.js.parentEditable %}
					addBBCodeDataToElement(
						{{ append_to }},
						"{{ child.js.varName }}",
						editorConstants.VALUE_IN_CONTENT
					);
					{{ append_to }}.contentEditable = "true";

				{% endif %}
			{% endfor %}
			
		{% elseif child.js.type == 'ATTRIBUTE_TEXT_NODE_DEFINITION' %}
			
			{% if child.js.var[0].isAttribute %}
				var {{ node_name }} = document.createTextNode(attributes["{{child.js.var[0].name}}"]);
			{% else %}
				var {{ node_name }} = document.createTextNode({{ EDITOR_JS_GLOBAL_OBJ }}.{{child.js.var[0].prefixedName}});
			{% endif %}
			{% if child.js.parentEditable %}
				addBBCodeDataToElement(
					{{ append_to }},
					"{{ child.js.varName }}",
					editorConstants.VALUE_IN_CONTENT
				);
				{{ append_to }}.contentEditable = "true";

			{% endif %}
		{% elseif child.js.type == 'CONSTANT_TEXT_NODE_DEFINITION' %}
			var {{ node_name }} = document.createTextNode({{ EDITOR_JS_GLOBAL_OBJ }}.{{ child.js.nodeText }});
		{% elseif child.js.type == 'PARSED_CHILDREN_SET' %}
			previousType = {{ append_to }}.getAttribute('data-bbcode-type');
						{{ append_to }}.setAttribute('data-bbcode-type', 
							(previousType && previousType + '|content')|| 'content');
						{{ append_to }}.contentEditable = 'true';
						{{ append_to }}.innerHTML += content;
			
		{% else %}
			ERROR: Got into else with type "{{ child.js.type }}".
		{% endif %}
		{% if child.children is defined %}
			{{ exec.parse_node(bbcodeName, node_name, child.children) }}
		{% else %}
			{{ exec.parse_case(bbcodeName, node_name, child.children) }}
		{% endif %}
	{%- endfor -%}
{%- endmacro -%}




(function($, window, document, undefined) {  // Avoid conflicts with other libraries

	var addBBCodeDataToElement = function (element, bbcodeParamName, bbcodeParamValue){
		previousType = element.getAttribute('data-bbcode-type');
		attrData = element.getAttribute('data-bbcode-data') || [];

		attrData.push({
			name : bbcodeParamName,
			value: bbcodeParamValue
		});

		element.setAttribute('data-bbcode-type',
			(previousType && previousType + '|attr')|| 'attr');
		element.setAttribute('data-bbcode-data', JSON.stringify(attrData));
	}

	var xslt = xslt('{{ XSLT }}');

{% for bbcode in BBCODES %}
	$.sceditor.plugins.bbcode.bbcode.set('{{ bbcode.name }}',
			{
				tags: {
				{% for containerTag in bbcode.containerTags %}
					'{{ containerTag }}': {
						'data-tag-id': "{{ bbcode.tagId }}"
					}
				{% endfor %}
				},
				// TODO: This needs improvement as it might simply not be true
				isInline:
					{%- for containerTag in bbcode.containerTags %}  editor.getElementDefaultDisplay('{{ containerTag }}') !== 'block' && {% endfor %}true,

				{% if bbcode.data.autoCloseOn %}
				excludeClosing: true,
				{% endif %}
				{% if (not bbcode.data.useContent is empty and
					(bbcode.data.autoClose is empty or
						(bbcode.data.ignoreBBCodeInside is empty and bbcode.data.ignoreTextInside is empty)
					)
					)
				%}
				excludeClosing: true,
				{% elseif bbcode.data.ignoreBBCodeInside is not empty %}
				allowedChildren: ['#'],
				{% elseif bbcode.data.allowedChildren is not empty %}
				allowedChildren: ['
					{%- if bbcode.data.ignoreTextInside -%}
						{{- "#','"-}}
					{%- endif -%}
					{{- bbcode.data.allowedChildren|join("','") -}}
					'],
				{% endif %}
				allowsEmpty: true,

				html: function (token, attributes, content) {
					var originalAttributes = attributes;
					var originalContent = content;
					var previousType;
					var attrData;
					var usedContents = [];

				{% if bbcode.data.defaultAttribute %}
					if(!attributes["{{ bbcode.data.defaultAttribute }}"] &&
						attributes["{{ bbcode.data.defaultAttribute }}"] !== '' && attributes.defaultattr){
						attributes["{{ bbcode.data.defaultAttribute }}"] = attributes.defaultattr;
					}
				{% endif %}
				{% for useContentAttr in bbcode.data.useContent %}
					if(!attributes["{{ useContentAttr }}"] &&
						attributes["{{ useContentAttr }}"] !== '' &&
						(content || content === '')
						){
						attributes["{{ useContentAttr }}"] = content;
						usedContents.push("{{ useContentAttr }}");
					}
				{% endfor %}
				{% for name, value in bbcode.data.attrPresets %}
					if(!attributes['{{ name }}'] && attributes['{{ name }}'] !== ''){
						attributes['{{ name }}'] = "{{ value }}";
					}
				{% endfor %}
				{% if bbcode.data.preProcessors is not empty %}
					var searchResult;
					{% for preProcessor in bbcode.data.preProcessors %}
						searchResult = /{{ preProcessor.regexFixed }}/{{ preProcessor.modifiersFixed }}.exec(attributes['{{ preProcessor.sourceAttribute }}']);
					if(searchResult){
					{% for num, attr in preProcessor.matchNumVsAttr %}
						if(!attributes["{{ attr }}"] && attributes["{{ attr }}"] !== ''){
							attributes['{{ attr }}'] = searchResult[{{ num }}];
						}
					{% endfor %}
					}
					{% endfor %}
				{% endif %}
				{% for attrName, attrData in bbcode.data.attr %}
					{% for filter in attrData.filters %}
						{% if filter.name is defined %}
					attributes['{{ attrName }}'] =
						editor.paramFilters['{{ filter.name }}'](attributes['{{ attrName }}']{{ filter.extraVars }});
						{% else %}
					attributes['{{ attrName }}'] =
						({{ filter.inlineFunc }})(attributes['{{ attrName }}']{{ filter.extraVars }});
						{% endif %}
					if(attributes['{{ attrName }}'] === false){
						console.warn("Attribute {{ attrName }} from BBCode {{ bbcode.name }} failed to validate {% if filter.name is defined %}{{ filter.name }}{% else %}{{ filter.inlineFunc|e('js') }}{% endif %}");
					}
					{% endfor %}
					{% if attrData.defaultValue %}
					if(!attributes['{{ attrName }}'] && attributes['{{ attrName }}'] !== ''){
						attributes['{{ attrName }}'] = "{{ attrData.defaultValue }}";
					}
					{% endif %}
					{% if attrData.required %}
					if(!attributes['{{ attrName }}'] && attributes['{{ attrName }}'] !== ''){
						return editor.revertBackToBBCode("{{ bbcode.name }}", originalAttributes, originalContent);
					}
					{% endif %}
				{% endfor %}

				var mainContainerFragment = document.createDocumentFragment();

				{{ exec.parse_node(bbcode.name, 'mainContainerFragment', bbcode.parsedTemplate) }}
					
				if(mainContainerFragment.firstChild.getAttribute('contentEditable') !== 'yes'){
					mainContainerFragment.firstChild.contentEditable = 'false';
				}
				mainContainerFragment.firstChild.setAttribute('data-tag-id', "{{ bbcode.tagId }}");
				return mainContainerFragment.firstChild.outerHTML;
			},
			format: function (element, content) {
				var infos = element.querySelectorAll('[data-bbcode-type]');
				var params = [];
				var useContent = false;
				
				for(var i = 0; i < infos.length; i++){
					var current = infos[i];
					var type = current.getAttribute('data-bbcode-type');
					var data = current.getAttribute('data-bbcode-data');
					if(!type){
						console.error("To BBCode translation error at BBCode {{ bbcode.name }}.\n" 
									+ "Unexpected empty data-bbcode-type parameter. Value and node as follows:");
						console.error(type);
						console.error(current);
						return;
					}
					var types = type.split("|");
					var data = JSON.parse(data);
					var extraOffset = 0;
					for(var j = 0; j < types.length; j++){
						if(types[j] === 'content'){
							useContent = true;
							extraOffset--;
						}else if(types[j] === 'attr'){
							var name = data[j + extraOffset].name;
							var value = data[j + extraOffset].value;
							if(value === editorConstants.VALUE_IN_CONTENT){
								value = current.textContent;
							}
							params.push(
								name + '="' + value + '"'
							);
						}else{
							console.warn("To BBCode translation warning at BBCode {{ bbcode.name }}.\n" + 
										 "Unexpected value for data-bbcode-type parameter." + 
										 "Skipping to the next value. Value and node were as follows:");
							console.warn(types[j]);
							console.warn(types);
							console.warn(current);
							continue;
						}
					}
				}
				
				return '[{{ bbcode.name }}' + 
					(params ? ' ' : '') +
					params.join(' ') +
					']' + (useContent ? content : '') + '[/{{ bbcode.name }}]';
				}
			});
{% endfor %}

})(jQuery, window, document); // Avoid conflicts with other libraries