{%- macro parse_case(EDITOR_JS_GLOBAL_OBJ, bbcodeName, parent, childData) -%}
	{%- import _self as exec -%}

	{%- for var, one in childData.caseVars %}
		xslt.setParameter('{{ var }}', attributes['{{ var }}']);
	{% endfor %}
	var conditionResult = xslt.transformToFragment(
		'<{{ bbcodeName }} d="{{ childData.num }}"></{{ bbcodeName }}>',
		document
	).firstChild.nodeValue;
	
	switch(conditionResult[0]){
	{% for caseVal, caseData in childData.case %}
		case '{{ caseVal }}':
			{{ exec.parse_node(EDITOR_JS_GLOBAL_OBJ, bbcodeName, parent, attribute(childData.case, caseVal).children) }}
		break;
	{% endfor %}
	}
{%- endmacro -%}

{%- macro parse_node(EDITOR_JS_GLOBAL_OBJ, bbcodeName, append_to, children) -%}
	{%- import _self as exec -%}

	{%- for child in children -%}
		{% if child.js.type == 'NODE_DEFINITION' %}

			var {{ child.js.nodeName }} = document.createElement("{{ child.tagName }}")
			{{ append_to }}.appendChild({{ child.js.nodeName }});
			{% for attrName, attrValue in child.js.attributes %}
				{{ child.js.nodeName }}.setAttribute("{{ attrName }}", {{ attrValue }});
				{% if attribute(child.js.bbcodeAttributes, attrName) is defined %}
					addBBCodeDataToElement(
						{{ child.js.nodeName }},
						"{{ attribute(child.js.bbcodeAttributes, attrName) }}",
						attributes["{{ attribute(child.js.bbcodeAttributes, attrName) }}"]
					);
				{% endif %}
				{% if child.js.parentEditable %}
					addBBCodeDataToElement(
						{{ child.js.nodeName }},
						"{{ child.js.varName }}",
						editorConstants.VALUE_IN_CONTENT
					);
					{{ child.js.nodeName }}.contentEditable = "true";

				{% endif -%}
			{% endfor %}
			
		{% elseif child.js.type == 'ATTRIBUTE_TEXT_NODE_DEFINITION' %}
			{% if child.vars[0].isAttribute %}
				var {{ child.js.nodeName }} = document.createTextNode(attributes["{{child.vars[0].name}}"]);
			{% else %}
				var {{ child.js.nodeName }} = document.createTextNode({{ EDITOR_JS_GLOBAL_OBJ }}.{{ attribute(child.vars[0], 'prefixedName') }});
			{% endif %}
			
			{{ append_to }}.appendChild({{ child.js.nodeName }});
			
			{% if child.js.parentEditable %}
				addBBCodeDataToElement(
					{{ append_to }},
					"{{ child.js.varName }}",
					editorConstants.VALUE_IN_CONTENT
				);
				{{ append_to }}.contentEditable = 'true';

			{% endif %}
		{% elseif child.js.type == 'CONSTANT_TEXT_NODE_DEFINITION' %}
			var {{ child.js.nodeName }} = document.createTextNode("{{ child.js.nodeText }}");
			{{ append_to }}.appendChild({{ child.js.nodeName }});
		{% elseif child.js.type == 'PARSED_CHILDREN_SET' %}
			previousType = {{ append_to }}.getAttribute('data-bbcode-type');
						{{ append_to }}.setAttribute('data-bbcode-type', 
							(previousType && previousType + '|content')|| 'content');
						{{ append_to }}.contentEditable = 'true';
						{{ append_to }}.innerHTML += content;
		
		{% elseif child.js.type == 'SWITCH_DEFINITION' %}
			{{ exec.parse_case(EDITOR_JS_GLOBAL_OBJ, bbcodeName, append_to, child) }}
		{% else %}
			ERROR: Got into else with type "{{ child.js.type }}".
		{% endif %}
		{{ exec.parse_node(EDITOR_JS_GLOBAL_OBJ, bbcodeName, child.js.nodeName, child.children) }}
	{%- endfor -%}
{%- endmacro -%}

{%- import _self as exec -%}




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

	var xslt = editor.xslt('{{ XSLT }}');

// $.sceditor.command.set('b', {
	// // exec: function() {
		// // this.insert('[b]', '[/b]');
	// // },
	// exec: function() {
		// this.insert('[b]', '[/b]');
	// },
	// txtExec: function() {
		// this.insert('[b]', '[/b]');
	// },
	// tooltip: "B"
// });
// $.sceditor.command.set('n', {
	// // exec: function() {
		// // this.insert('[b]', '[/b]');
	// // },
	// exec: function() {
		// this.wysiwygEditorInsertHtml('</span>', '<span data-bbcode-type="content" style="font-weight: bold" contenteditable="true">');
	// },
	// txtExec: function() {
		// this.insert('[/b]', '[b]');
	// },
	// tooltip: "n"
// });
// $.sceditor.command.set('i', {
	// // exec: function() {
		// // this.insert('[b]', '[/b]');
	// // },
	// exec: function() {
		// this.insert('[i]', '[/i]');
	// },
	// txtExec: function() {
		// this.insert('[i]', '[/i]');
	// },
	// tooltip: "I"
// });
// $.sceditor.command.set('u', {
	// // exec: function() {
		// // this.insert('[b]', '[/b]');
	// // },
	// exec: function() {
		// this.insert('[u]', '[/u]');
	// },
	// txtExec: function() {
		// this.insert('[u]', '[/u]');
	// },
	// tooltip: "U"
// });

var makeDropdown = (function(){
	
	var makeSelectBox = function (options, selectMultiple, separator){
		var select = document.createElement('select');
		select.multiple = !!selectMultiple;
		
		for (var i = 0; i < options.length; i++){
			var option = new Option(
					options[i].text,
					options[i].value,
					this.undefined,
					!!options[i].selected
				);
			select.add(option);
		}
		var returner = {
			element: select
		};
		if(selectMultiple){
			returner.getValue = function (){
				return $(select).val();
			};
		}else{
			returner.getValue = function (){
				return ($(select).val() || []).join(separator);
			};
		}
		return returner;
	};
	var makeInput = function (type, defaultValue){
		var input = document.createElement('input');
		input.type = type || 'text';
		input.value = defaultValue || '';
		
		return {
			element: input,
			getValue: function (){
				return input.value;
			}
		};
	};
	
	return function (button, BBCodeName, attributes, buttonToAlignTo, oKCallback, errorCallback){
		var elements = [];
		var container = document.createElement('div');
		container.className = 'editorDropdownContainer';
		
		for (var i = 0; i < attributes.length; i++){
			var attributeRequestContainer = document.createElement('div');
			
			var text = document.createElement('span');
			text.textContent = attributes[i].name;
			attributeRequestContainer.appendChild(text);
			
			var dataElement;
			
			switch(attributes[i].type){
				case 'chooseMany':
				case 'choose1':
					dataElement = makeSelectBox(attributes[i].options,
						attributes[i].type === 'chooseMany',
						attributes[i].separator)
				break;
				default:
					dataElement = makeInput(attributes[i].type,
						attributes[i].value);
				break;
			}
						
			elements.push(dataElement);
			attributeRequestContainer.appendChild(dataElement);
			container.appendChild(attributeRequestContainer);
			
		}
	
		var confirm = document.createElement('button');
		confirm.textContent = EDITOR_JS_GLOBAL_OBJ.L_SUBMIT;
		confirm.onclick = function (e){
			var data = {};
			for (var i = 0; i < elements.length; i++){
				data[attributes[i].name] = elements[i].getValue();
			}
			editor.closeDropDown(true);
			oKCallback(data);
		}
		container.appendChild(confirm);
		
		editor.createDropDown(button, 'dropdown-' + BBCodeName, container);
	};
})();

{% set toolbar = '' %}
	
{% for bbcode in BBCODES %}
	
	$.sceditor.command.set('{{ bbcode.name }}',
		{
			state: function (parent, blockParent){
				parent = $(this.currentNode());
				return (parent.attr('data-tag-id') === '{{ bbcode.tagId }}' ||
					parent.closest('[data-tag-id={{ bbcode.tagId }}]', blockParent)[0]) ? 1 : 0;
			},
			exec: function() {
				this.insert('[{{ bbcode.name }}]', '[/{{ bbcode.name }}]');
			},
			txtExec: function() {
				this.insert('[{{ bbcode.name }}]', '[/{{ bbcode.name }}]');
			},
		}
	);
	
	$.sceditor.plugins.bbcode.bbcode.set('{{ bbcode.name }}',
			{
				tags: {
				{% for containerTag in bbcode.containerTags %}
					'{{ containerTag }}': {
						'data-tag-id': ["{{ bbcode.tagId }}"]
					},
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
					{%- if not bbcode.data.ignoreTextInside -%}
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
						console.log("reverting {{ bbcode.name }}");
						return editor.revertBackToBBCode("{{ bbcode.name }}", originalAttributes, originalContent);
					}
					{% endif %}
				{% endfor %}

				var mainContainerFragment = document.createDocumentFragment();

				{{ exec.parse_node(EDITOR_JS_GLOBAL_OBJ, bbcode.name, 'mainContainerFragment', bbcode.parsedTemplate) }}
					
				if(mainContainerFragment.firstChild.getAttribute('contentEditable') !== 'true'){
					mainContainerFragment.firstChild.contentEditable = 'false';
				}
				mainContainerFragment.firstChild.setAttribute('data-tag-id', "{{ bbcode.tagId }}");
				return mainContainerFragment.firstChild.outerHTML;
			},
			format: function (element) {
				var infos = element[0].querySelectorAll('[data-bbcode-type]');
				var params = [];
				var content = '';
				console.log(infos);
				for(var i = 0; i < infos.length; i++){
					var current = infos[i];
					var type = current.getAttribute('data-bbcode-type');
					var data = current.getAttribute('data-bbcode-data');
					if(!type){
						console.error("To BBCode translation error at BBCode {{ bbcode.name }}.\n" 
									+ 'Unexpected empty data-bbcode-type parameter. Value and node as follows:');
						console.error(type);
						console.error(current);
						return;
					}
					var types = type.split("|");
					var data = JSON.parse(data);
					var extraOffset = 0;
					for(var j = 0; j < types.length; j++){
						if(types[j] === 'content'){
							content = this.elementToBbcode($(current));
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
					']' + content + '[/{{ bbcode.name }}]';
				}
			});
		
		
		{% set toolbar = toolbar ~ bbcode.name %}
		{% if loop.index0 is divisible by(4) %}
			{% set toolbar = toolbar ~ '|'  %}
		{% else %}
			{% set toolbar = toolbar ~ ',' %}
		{% endif %}
		
{% endfor %}


// Loadup and start SCE
var messageTextarea = $("#signature, #message");
messageTextarea.sceditor({
	plugins: 'bbcode,undo',
	style: {{ EDITOR_JS_GLOBAL_OBJ }}.stylePath,

{% if OVERRIDES.toolbar is defined %}
	toolbar: '{{ OVERRIDES.toolbar }}|removeformat|' +
				'cut,copy,paste,pastetext|' +
				'unlink|print,maximize,source'
{% else %}
	toolbar: '{{ toolbar }}|indent,outdent,removeformat|' +
				'cut,copy,paste,pastetext|' +
				'unlink|print,maximize,source'
{% endif %}
});

})(jQuery, window, document); // Avoid conflicts with other libraries