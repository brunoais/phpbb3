/**
* Hide and show all checkboxes
* status = true (show boxes), false (hide boxes)
*/
function display_checkboxes(status) 
{
	var form = document.getElementById('set-permissions');
	var cb = document.getElementsByTagName('input');
	var display;

	//show
	if (status)
	{
		display = 'inline';
	}
	//hide
	else
	{
		display = 'none';
	}
	
	for (var i = 0; i < cb.length; i++ )
	{
		if (cb[i].className == 'permissions-checkbox')
		{
			cb[i].style.display = display;
		}
		
	}	
	
}


/**
* Change opacity of element
* e = element
* value = 0 (hidden) till 10 (fully visible)
*/
function set_opacity(e, value) {
	e.style.opacity = value/10;
	
	//IE opacity currently turned off, because of its astronomical stupidity
	//e.style.filter = 'alpha(opacity=' + value*10 + ')';
}

/**
* Reset the opacity and checkboxes
* block_id = id of the element that needs to be toggled
*/
function toggle_opacity(block_id) {
	var cb = document.getElementById('checkbox' + block_id);
	var fs = document.getElementById('perm' + block_id);
	
	if (cb.checked) 
	{
		set_opacity(fs, 5);
	} 
	else 
	{
		set_opacity(fs, 10);
	}
}

/**
* Reset the opacity and checkboxes
* value = 0 (checked) and 1 (unchecked)
* except_id = id of the element not to hide
*/
function reset_opacity(status, except_id) {
	var perm = document.getElementById('set-permissions');
	var fs = perm.getElementsByTagName('fieldset');
	var opacity = 5;

	if (status)
	{
		opacity = 10;	
	}
	
	for (var i = 0; i < fs.length; i++ )
	{
		if (fs[i].className != 'quick')
		{
			set_opacity(fs[i], opacity);
		}
	}

	if (typeof(except_id) != 'undefined')
	{
		set_opacity(document.getElementById('perm' + except_id), 10);
	}

	//reset checkboxes too
	marklist('set-permissions', 'inherit', !status);
}


/**
* Check whether we have a full radiobutton row of true
* index = offset for the row of inputs (0 == first row, 1 == second, 2 == third),
* rb = array of radiobuttons
*/
function get_radio_status(index, rb) 
{
	for (var i = index; i < rb.length; i = i + 3 )
	{
		if (rb[i].checked != true)
		{
			if (i > index)
			{
				//at least one is true, but not all (custom)
				return 2;
			}
			//first one is not true
			return 0;
		}
	}

	// all radiobuttons true
	return 1;
}

/**
* Set tab colours
* id = panel the tab needs to be set for, 
* init = initialising on open, 
* quick = If no calculation needed, this contains the colour
*/
function set_colours(id, init, quick)
{
	var table = document.getElementById('table' + id);
	var tab = document.getElementById('tab' + id);

	if (typeof(quick) != 'undefined') 
	{
		tab.className = 'permissions-preset-' + quick + ' activetab';
		return;
	}

	var rb = table.getElementsByTagName('input');
	var colour = 'custom';

	var status = get_radio_status(0, rb);

	if (status == 1)
	{
		colour = 'yes';
	}
	else if (status == 0) 
	{
		// We move on to No
		status = get_radio_status(1, rb);

		if (status == 1)
		{
			colour = 'no';
		}
		else if (status == 0) 
		{
			// We move on to Never
			status = get_radio_status(2, rb);

			if (status == 1)
			{
				colour = 'never';
			}
		}
	}

	if (init)
	{
		tab.className = 'permissions-preset-' + colour;
	}
	else
	{
		tab.className = 'permissions-preset-' + colour + ' activetab';
	}
}

/**
* Initialise advanced tab colours on first load
* block_id = block that is opened
*/
function init_colours(block_id)
{	
	var block = document.getElementById('advanced' + block_id);
	var panels = block.getElementsByTagName('div');
	var tab = document.getElementById('tab' + id);

	for (var i = 0; i < panels.length; i++)
	{
		if(panels[i].className == 'permissions-panel')
		{
			set_colours(panels[i].id.replace(/options/, ''), true);
		}
	}

	tab.className = tab.className + ' activetab';
}

/**
* Show/hide option panels
* value = suffix for ID to show
* adv = we are opening advanced permissions
* view = called from view permissions
*/
function swap_options(pmask, fmask, cat, adv, view)
{
	id = pmask + fmask + cat;
	active_option = active_pmask + active_fmask + active_cat;

	var	old_tab = document.getElementById('tab' + active_option);	
	var new_tab = document.getElementById('tab' + id);
	var adv_block = document.getElementById('advanced' + pmask + fmask);

	if (adv_block.style.display == 'block' && adv == true)
	{
		dE('advanced' + pmask + fmask, -1);
		reset_opacity(1);
		display_checkboxes(false);
		return;
	}

	// no need to set anything if we are clicking on the same tab again
	if (new_tab == old_tab && !adv)
	{
		return;
	}

	// init colours
	if (adv && (pmask + fmask) != (active_pmask + active_fmask))
	{
		init_colours(pmask + fmask);
		display_checkboxes(true);
		reset_opacity(1);
	} 
	else if (adv) 
	{
		//Checkbox might have been clicked, but we need full visibility
		display_checkboxes(true);
		reset_opacity(1);
	}

	// set active tab
	old_tab.className = old_tab.className.replace(/\ activetab/g, '');
	new_tab.className = new_tab.className + ' activetab';

	if (id == active_option && adv != true)
	{
		return;
	}

	dE('options' + active_option, -1);
	
	//hiding and showing the checkbox
	if (document.getElementById('checkbox' + active_pmask + active_fmask))
	{
		dE('checkbox' + pmask + fmask, -1);	
		
		if ((pmask + fmask) != (active_pmask + active_fmask))
		{
			document.getElementById('checkbox' + active_pmask + active_fmask).style.display = 'inline';
		}
	}

	if (!view)
	{
		dE('advanced' + active_pmask + active_fmask, -1);
	}

	if (!view)
	{
		dE('advanced' + pmask + fmask, 1);
	}
	dE('options' + id, 1);

	active_pmask = pmask;
	active_fmask = fmask;
	active_cat = cat;
}

/**
* Mark all radio buttons in one panel
* id = table ID container, s = status ['y'/'u'/'n']
*/
function mark_options(id, s)
{
	var t = document.getElementById(id);

	if (!t)
	{
		return;
	}

	var rb = t.getElementsByTagName('input');

	for (var r = 0; r < rb.length; r++)
	{
		if (rb[r].id.substr(rb[r].id.length-1) == s)
		{
			rb[r].checked = true;
		}
	}
}

function mark_one_option(id, field_name, s)
{
	var t = document.getElementById(id);

	if (!t)
	{
		return;
	}

	var rb = t.getElementsByTagName('input');

	for (var r = 0; r < rb.length; r++)
	{
		if (rb[r].id.substr(rb[r].id.length-field_name.length-3, field_name.length) == field_name && rb[r].id.substr(rb[r].id.length-1) == s)
		{
			rb[r].checked = true;
		}
	}
}

/**
* Reset role dropdown field to Select role... if an option gets changed
*/
function reset_role(id)
{
	var t = document.getElementById(id);

	if (!t)
	{
		return;
	}

	t.options[0].selected = true;
}

/**
* Load role and set options accordingly
*/
function set_role_settings(role_id, target_id)
{
	settings = role_options[role_id];

	if (!settings)
	{
		return;
	}

	// Mark all options to no (unset) first...
	mark_options(target_id, 'u');

	for (var r in settings)
	{
		mark_one_option(target_id, r, (settings[r] == 1) ? 'y' : 'n');
	}
}


/**
 * Control system for showing and hiding permissions. without losing the original aspect
 *
 * Note: After we giveup on supporting IE9, this function may be altered to be more efficient. 
*/

var showHidePermissions;
(function (){
	
	/**
	 * Function originally meant to restore the multiple colored system of the permissions table.
	 * This function changes the class of all the direct children of element having in account the prefix and the ignoreForDegrade.
	 * For all elements it checks, if it does contain the class ignoreForDegrade the previous prefix class is removed and replaced with
	 * a class formed by appending the prefix with the next element in numbering array.
	 * Supports any number of different CSS valid characters for the HTML attribute class, except the space.
	 * 
	 * Note: Does not work if numbering cannot be treated as an infinite array.
	 * 
	 * @param element Node The element that contains the children that will do the multiple class
	 * @param prefix String The string that prefixes all in the numbering. E.g. 'row'
	 * @param numbering Array An array of anything that is transformable to string (and follows the rules above). E.g. [3,4]
	 * @param ignoreForDegrade String If this substring is inside the class HTML attribute, the system skips it.
	 * @return function Returns the function that executes the job
	*/
	function restoreDegrade(element, prefix, numbering, ignoreForDegrade){
		var children = element.childNodes;
		return function (){
			for(var i = 0, childCounter = 0; i < children.length; i++){
				// if this is a textNode, it doesn't matter me.
				// if this one is hidden, it's no use counting with it
				if(children[i].nodeType == 3 || children[i].className.indexOf(ignoreForDegrade) != -1) continue;
				
				children[i].className = children[i].className.replace(new RegExp(prefix + "[^ \"']+",'g'), prefix + numbering[childCounter]);
				childCounter = (childCounter + 1) % numbering.length;
			}
		}
	}
	
	/**
	 * Replaces the hide CSS class with the CSS show class
	 *
	 * @param element Node The element to apply the changes  
	 * @param callback function A function to execute when the job is done
	*/
	function appear(element, callback){
		return function (){
				if(element.classList){
					element.classList.remove('hide');
					element.classList.add('show');
				}else{
					// IE does not know what classList is. While we support IE9, well keep this one.
					element.className = element.className.replace(/hide/g, 'show');
				}
				if(callback) callback();
			}
	}
	
	/**
	 * Replaces the show CSS class with the CSS hide class
	 *
	 * @param element Node The element to apply the changes  
	 * @param callback function A function to execute when the job is done
	*/
	function disappear(element, callback){
		return function (){
				if(element.classList){
					element.classList.add('hide');
					element.classList.remove('show');
				}else{
					// IE does not know what classList is. While we support IE9, well keep this one.
					element.className = element.className.replace(/show/g, 'hide');
				}
				if(callback) callback();
			}
	}

	/**
	* Controler to make options appear or dissapear as needed
	* 
	* @param listenPermission string The permission id to listen to.
	*								 E.g. GOOD: "setting[5][2][f_read]" BAD: "setting[5][2][f_read]_y"
	* @param applyTo string The id of the tag whose content should show or hide depending on the listen option
	* 						E.g. GOOD: "setting[5][2][f_read]" BAD: "setting[5][2][f_read]_y"
	* @param appearOnYes boolean When should the conent appear? When YES is pressed (true) or when NO/NEVER is pressed (false)?
	* @throws Object 'not enugh arguments' when no enugh arguments given 
	* @throws DOMExeption '%s is null' when the given arguments are invalid
	*/
	showHidePermissions = function (listenPermission, applyTo, numbering, appearOnYes){
		if(arguments.length < 2){
			throw {
					reason: 'not enugh arguments'
				};
		}
		var yesPermission;
		var noPermission;
		var neverPermission;
		yesPermission = document.getElementById(listenPermission + '_y');
		noPermission = document.getElementById(listenPermission + '_u');
		neverPermission = document.getElementById(listenPermission + '_n');
		applyToElement = document.getElementById(applyTo + '_n');
		
		var current = applyToElement;
		var currentName = current.parentNode.tagName.toLowerCase();
		while( currentName != 'tr' && currentName != 'body'){
			current = current.parentNode;
			currentName = current.parentNode.tagName.toLowerCase();
		}
		applyToElement = current.parentNode;
		
		//IE8 fix. (does not follow the standard)
		if(!yesPermission.addEventListener){
			var eventListener = function(event, callback){
				this.attachEvent('on'+event, callback);
			}
			yesPermission.addEventListener = eventListener;
			noPermission.addEventListener = eventListener;
			neverPermission.addEventListener = eventListener;
			applyToElement.addEventListener = eventListener;
			applyToElement.parentNode.addEventListener = eventListener;
		}
		var restorePresentation = restoreDegrade(applyToElement.parentNode, 'row', numbering, 'hide');
		var appearFunc = appear(applyToElement, restorePresentation);
		var disappearFunc = disappear(applyToElement, restorePresentation);
		
		yesPermission.addEventListener('click', appearOnYes ? appearFunc : disappearFunc, false);
		noPermission.addEventListener('click', appearOnYes ? disappearFunc : appearFunc, false);
		neverPermission.addEventListener('click', appearOnYes ? disappearFunc : appearFunc, false);
		
		// If it's supposed not to be there:
		if((appearOnYes && !yesPermission.checked) || (!appearOnYes && yesPermission.checked)){
			disappearFunc();
		}
	}
})();
