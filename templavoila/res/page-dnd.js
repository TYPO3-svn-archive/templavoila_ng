var sortable_clipboard;
var sortable_currentItem;
var sortable_baseLink;
var sortable_containers;

var sortable_parameters = {
	tag: "div",
	ghosting: false,
	format: /(.*)/,
	handle: "sortableHandle",
	dropOnEmpty: true,
	constraint: false,
	containment: sortable_containers,
	scroll: window,
	onChange: sortable_change,
	onUpdate: sortable_update
};

/* -------------------------------------------------------------------------- */
function sortable_exec(url) {
	new Ajax.Request(url);
}

/* -------------------------------------------------------------------------- */
function sortable_removeCallBack(obj) {
	var el = obj.element;

	while (el.lastChild)
		el.removeChild(el.lastChild);
}

function sortable_removeRecord(it, command) {
	while (it.className != 'sortableItem')
		it = it.parentNode;

	new Ajax.Request(command);
	new Effect.Fade(it,
		{ duration: 0.5,
		  afterFinish: sortable_removeCallBack });
}

/* -------------------------------------------------------------------------- */
function sortable_unhideRecordObscureCallBack(obj) {
	obj.element.removeClassName('tv-hidden');
}

function sortable_unhideRecord(it, command) {
	var tab = it.parentNode;
	while (tab.tagName.toLowerCase() != 'table')
		tab = tab.parentNode;

	it.setAttribute('rel', it.getAttribute('rel').replace('[hidden]=0', '[hidden]=1').replace('unhide', 'hide'));
	it.firstChild.src = it.firstChild.src.replace('unhide', 'hide');

	new Ajax.Request(command);
	new Effect.Fade(tab,
		{ duration: 0.5,
		  to: 1.0,
		  afterFinish: sortable_unhideRecordObscureCallBack });
}

/* -------------------------------------------------------------------------- */
function sortable_hideRecordObscureCallBack(obj) {
	obj.element.addClassName('tv-hidden');
}

function sortable_hideRecord(it, command) {
	if (sortable_removeHidden)
		return sortable_removeRecord(it, command);

	var tab = it.parentNode;
	while (tab.tagName.toLowerCase() != 'table')
		tab = tab.parentNode;

	it.setAttribute('rel', it.getAttribute('rel').replace('[hidden]=1', '[hidden]=0').replace('hide', 'unhide'));
	it.firstChild.src = it.firstChild.src.replace('hide', 'unhide');

	new Ajax.Request(command);
	new Effect.Fade(tab,
		{ duration: 0.5,
		  to: 0.5,
		  afterFinish: sortable_hideRecordObscureCallBack });
}

/* -------------------------------------------------------------------------- */
function sortable_unlinkRecordCallBack(obj) {
	var el = obj.element;
	var pn = el.parentNode;

	pn.removeChild(el);
	sortable_update(pn);

	if (el.innerHTML.match(/makeLocalRecord/))
		return;
	if (!(pn = document.getElementById(sortable_clipboard)))
		return;

	pn.appendChild(el);
	sortable_update(pn);

	new Effect.Appear(el,
		{ duration: 0.5 });
}

function sortable_unlinkRecord(id) {
	new Ajax.Request(sortable_baseLink + "&ajaxUnlinkRecord=" + escape(id));
	new Effect.Fade(id,
		{ duration: 0.5,
		  afterFinish: sortable_unlinkRecordCallBack });
}

function sortable_unlinkRecordsAll(el) {
	$(el).select('a').each( function(anchor) {
		if (anchor.href.match(/unlinkRecord/)) {

		}
	} );
}

/* -------------------------------------------------------------------------- */
function sortable_deleteRecordCallBack(obj) {
	var el = obj.element;
	var pn = el.parentNode;

	pn.removeChild(el);
	sortable_update(pn);
}

function sortable_deleteRecord(id) {
	new Ajax.Request(sortable_baseLink + "&ajaxDeleteRecord=" + escape(id));
	new Effect.Fade(id,
		{ duration: 0.5,
		  afterFinish: sortable_deleteRecordCallBack });
}

/* -------------------------------------------------------------------------- */
function sortable_updateItemButtons(el, newPos) {
	/* hidden elements still carry around their marker, but nothing else */
	var childs = el.childElements();
	if (!childs)
		return;

	el.id = newPos;
	newPos = escape(newPos);

	$(el).select('a').each( function(anchor) {
		var p, p1, href = anchor.href;

		if (!href || href.charAt(href.length - 1) == "#") {
			return;

		} else if ((p = href.split("unlinkRecord")).length == 2) {
			anchor.href = p[0] + "unlinkRecord('" + newPos + "');";
		} else if ((p = href.split("deleteRecord")).length == 2) {
			anchor.href = p[0] + "deleteRecord('" + newPos + "');";
		} else if ((p = href.split("&parentRecord=")).length == 2) {
			anchor.href = p[0] + "&parentRecord=" + newPos;

		} else if((p = href.split("CB[el][tt_content")).length == 2) { p1 = p[1].split("=");
			anchor.href = p[0] + "CB[el][tt_content" + p1[0] + "="  + newPos;
		} else if ((p = href.split("&destination=")).length == 2) {
			anchor.href = p[0] + "&destination=" + newPos;
		}
	} );
}

function sortable_updatePasteButtons(oldPos, newPos) {
	var i = 0; var p = new Array; var href = "";
	var buttons = document.getElementsByClassName("sortablePaste");
	if (buttons[i].firstChild && buttons[i].firstChild.href.indexOf("&source=" + escape(oldPos)) != -1) {
		for (i = 0; i < buttons.length; i++) {
			if (buttons[i].firstChild) {
				href = buttons[i].firstChild.href;
				if ((p = href.split("&source=" + escape(oldPos))).length == 2) {
					buttons[i].firstChild.href = p[0] + "&source=" + escape(newPos) + p[1];
				}
			}
		}
	}
}

function sortable_purify(el) {
	var node = el.firstChild;

	while (node != null) {
		if (node.className == "sortableItem") {
			var actPos = node.id;
			var newPos = node.getAttribute('rel');

			if (sortable_currentItem && (sortable_currentItem.id == actPos)) {
				new Ajax.Request(sortable_baseLink + "&ajaxPasteRecord=cut&source=" + actPos + "&destination=" + newPos);

				sortable_updatePasteButtons(actPos, newPos);
				sortable_currentItem = false;
			}

			sortable_updateItemButtons(node, newPos);
		}

		node = node.nextSibling;
	}
}

function sortable_update(el) {
	if (el.id == sortable_clipboard)
		return sortable_purify(el);

	var node = el.firstChild;
	var i = 1;

	while (node != null) {
		if (node.className == "sortableItem") {
			var actPos = node.id;
			var prvPos = el.id + (i - 1);
			var newPos = el.id +  i;

			if (sortable_currentItem && (sortable_currentItem.id == actPos)) {
				new Ajax.Request(sortable_baseLink + "&ajaxPasteRecord=cut&source=" + actPos + "&destination=" + prvPos);

				sortable_updatePasteButtons(actPos, newPos);
				sortable_currentItem = false;
			}

			sortable_updateItemButtons(node, newPos);
			i++;
		}

		node = node.nextSibling;
	}
}

function sortable_change(el) {
	sortable_currentItem = el;
}
