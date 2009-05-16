



	/*	Developed by Niels Fröhling, http://frohling.biz
	 *	Code/licensing: exclusive permition to be used in Typo3 -> GPL
	 */

	/* IO --------------------------------------------------------------- */
	function xmlarea_doc(tarea, context) {
		var xmlDoc, parser;
		var value = tarea.value;
		var prefix = '', doctype = '';
		var information = '';

		var strip = new RegExp('(<\\?xml.*\\?>)([\\s\\S]*?)$', 'i');
		if ((strip = value.match(strip))) {
			prefix = strip[1];
			value = strip[2];
		}

		strip = new RegExp('(<!DOCTYPE[\\s\\S]*?>)([\\s\\S]*?)$', 'i');
		if ((strip = value.match(strip))) {
			doctype = strip[1];
			value = strip[2];
		}

		var fragment = (prefix ? prefix : '<?xml version="1.0"?>') + '<capsed type="array">' + value + '</capsed>';
		var info = new RegExp('^<\\?xml ?(.*)\\?>$', 'i');
		if ((info = prefix.match(info))) {
			tarea.struct.infos.firstChild.nodeValue = info[1];
		}

		// Internet Explorer
		try {
			xmlDoc = new ActiveXObject("Microsoft.XMLDOM");
			xmlDoc.async = "false";
			xmlDoc.loadXML(fragment);
		}
		// Firefox, Mozilla, Opera, etc.
		catch(e) { try {
			parser = new DOMParser();
			xmlDoc = parser.parseFromString(fragment, "text/xml");
		}
		catch(e) {
			xmlDoc = null;
		}}

		tarea.struct.prefix = prefix + doctype;
		tarea.struct.xml = xmlDoc;
		tarea.struct.context = context;
	}

	function xmlarea_string(tarea) {
		var xmlString, serializer;
		var tree = tarea.struct.xml.documentElement;

		// Firefox, Mozilla, Opera, etc.
		try {
			serializer = new XMLSerializer();
			xmlString = serializer.serializeToString(tree);
		}
		// Internet Explorer
		catch(e) { try {
			xmlString = tree.xml;
		}
		catch(e) {
			xmlString = null;
		}}

		var strip = new RegExp('^<capsed type="array">([\\s\\S]*?)<\/capsed>$', 'i');
		if ((strip = xmlString.match(strip)))
			tarea.value = tarea.struct.prefix + strip[1];
	}

	function xmlarea_whitespace(elm) {
		var strip = false;

		for (var e = 0; e < elm.childNodes.length; e++) {
			var row = elm.childNodes[e];
			if (row.nodeType == 1)
				strip = true;
		}

		if (strip) {
			for (var e = 0; e < elm.childNodes.length; e++) {
				var row = elm.childNodes[e];
				if ((row.nodeType != 1) && (!row.nodeValue.match(/^[\\s\\S]*$/)))
					elm.removeChild(row);
			}
		}
	}

	function xmlarea_context(elm) {
		var ctx = [];

		while (elm && !elm.struct)
			elm = elm.parentNode;

		elm = elm.struct;

		while (elm) {
			ctx.push(elm.name ? elm.name.childNodes[2].nodeValue : elm.context);
			elm = elm.parent;
		}

		return ctx.without(null).reverse().join('.');
	}

	/* GUI -------------------------------------------------------------- */
	var presets = {
		'.meta' : 'array',
		'.meta.langDisable' : 'boolean',
		'.meta.langChildren' : 'boolean',

		'.tx_templavoila.description' : 'area',
		'.tx_templavoila.inheritance' : 'select[a=0,b=1,c=2]',
		'.tx_templavoila.eType' : 'select[input,none,TypoScriptObject,ce,image,rte,imagefixed,link,...]',
		'.tx_templavoila.proc' : 'array',
		'.tx_templavoila.proc.HSC' : 'boolean',
		'.tx_templavoila.oldStyleColumnNumber' : 'integer',
		'.tx_templavoila.sample_data' : 'array',
		'.tx_templavoila.sample_data.numIndex' : 'area',
		'.tx_templavoila.TypoScript' : 'area',
		'.tx_templavoila.TypoScriptObject' : 'text',

		'.TCEforms.exclude'					: ['boolean'						, 'If set, all backend users are prevented from editing the field unless they are members of a backend usergroup with this field added as an "Allowed Excludfield" (or "admin" user).'],
		'.TCEforms.label'					: ['text'						, 'The name of the field as it is shown in the interface.'],
		'.TCEforms.displayCond'					: ['select[FIELD|EXT|HIDE_L10N_SIBLINGS]:'		, 'Contains a condition rules for whether to display the field or not.'],
		'.TCEforms.displayCond:FIELD'				: [':field:select[REQ|>|<|>=|<=|=|!=|IN|!IN|-|!-]:text', ''],
		'.TCEforms.displayCond:EXT'				: [':text:constant[LOADED]:boolean'			, ''],
		'.TCEforms.displayCond:HIDE_L10N_SIBLINGS'		: [':boolean[except_admin]'				, ''],
		'.TCEforms.defaultExtras'				: ['text'						, 'This string will be the default string of extra options for a field regardless of types configuration.'],

		'.TCEforms.config.type'					: ['select[input|text|check|radio|select|group|...]'	, ''],

		'.TCEforms.config[type=input].size'			: ['integer,positive'					, 'Abstract value for the width of the <input> field. To set the input field to the full width of the form area, use the value 48. Default is 30.'],
		'.TCEforms.config[type=input].max'			: ['integer,positive'					, 'Value for the "maxlength" attribute of the <input> field.'],
		'.TCEforms.config[type=input].eval'			: ['select[required|trim|date|datetime|time|timesec|' +
									   'year|int|upper|lower|alpha|num|alphanum|alphanum_x|' +
									   'nospace|md5|is_in|password|double2|unique|' +
									   'uniqueInPid],multiple,ordered'			, 'Configuration of field evaluation. Some of these evaluation keywords will trigger a JavaScript pre-evaluation in the form. Other evaluations will be performed in the backend. The eval-functions will be executed in the list-order.'],
		'.TCEforms.config[type=input].is_in'			: ['text'						, ''],
		'.TCEforms.config[type=input].checkbox'			: [''							, 'If defined (even empty), a checkbox is placed before the input field.'],
		'.TCEforms.config[type=input].range'			: ['array'						, 'An array which defines an integer range within which the value must be.'],
		'.TCEforms.config[type=input].range.lower'		: ['integer'						, 'Defines the lower integer value.'],
		'.TCEforms.config[type=input].range.upper'		: ['integer'						, 'Defines the upper integer value.'],

		'.TCEforms.config[type=text].cols'			: ['integer,positive'					, 'Abstract value for the width of the <textarea> field. To set the textarea to the full width of the form area, use the value 48. Default is 30.'],
		'.TCEforms.config[type=text].rows'			: ['integer,positive'					, 'The number of rows in the textarea. May be corrected for harmonisation between browsers. Will also automatically be increased if the content in the field is found to be of a certain length, thus the field will automatically fit the content.'],
		'.TCEforms.config[type=text].wrap'			: ['select[off|virtual]'				, 'Determines the wrapping of the textarea field.'],

		'.TCEforms.config[type=check].cols'			: ['integer,positive'					, 'How many columns the checkbox array are shown in.'],
		'.TCEforms.config[type=check].showIfRTE'		: ['boolean'						, 'If set, this field will show only if the RTE editor is enabled (which includes correct browserversion and user-rights altogether).'],
		'.TCEforms.config[type=check].items'			: ['array'						, ''],
		'.TCEforms.config[type=check].itemsProcFunc'		: ['function'						, 'PHP function which is called to fill / manipulate the array with elements.'],

		'.TCEforms.config[type=radio].items'			: ['array'						, ''],
		'.TCEforms.config[type=radio].itemsProcFunc'		: ['function'						, 'PHP function which is called to fill / manipulate the array with elements.'],

		'.TCEforms.config[type=select].size'			: ['integer,positive'					, 'Height of the selectorbox in TCEforms.'],
		'.TCEforms.config[type=select].autoSizeMax'		: ['integer,positive'					, 'If set, then the height of multiple-item selector boxes (maxitem > 1) will automatically be adjusted to the number of selected elements.'],
		'.TCEforms.config[type=select].selectedListStyle'	: ['css'						, 'If set, this will override the default style of the selector box with selected items.'],
		'.TCEforms.config[type=select].itemListStyle'		: ['css'						, 'If set, this will override the default style of the selector box with available items to select.'],
		'.TCEforms.config[type=select].renderMode'		: ['css'						, 'If set, this will override the default style of the selector box with available items to select.'],
		'.TCEforms.config[type=select].selicon_cols'		: ['integer,positive'					, 'The number of rows in which to position the iconimages for the selectorbox.'],
		'.TCEforms.config[type=select].suppress_icons'		: ['select[IF_VALUE_FALSE|ONLY_SELECTED|1]'		, 'Lets you disable display of icons.'],
		'.TCEforms.config[type=select].iconsInOptionTags'	: ['boolean'						, 'If set, icons will appear in the <option> tags of the selector box.'],
		'.TCEforms.config[type=select].items'			: ['array'						, ''],

		'.TCEforms.config[type=select].special'			: ['select[tables|pagetypes|exclude|modListGroup|' +
									   'modListUser|explicitValues|languages|custom]'	, 'This configures the selector box to fetch content from some predefined internal source.'],

		'.TCEforms.config[type=select].fileFolder'		: ['path'						, 'Specifying a folder from where files are added to the item array.'],
		'.TCEforms.config[type=select].fileFolder_extList'	: ['set'						, 'List of extensions to select. If blank, all files are selected.'],
		'.TCEforms.config[type=select].fileFolder_recursions'	: ['integer,positive'					, 'Depth of directory recursions.'],

		'.TCEforms.config[type=select].foreign_table'				: ['text'				, 'The item-array will be filled with records from the table defined here.'],
		'.TCEforms.config[type=select].foreign_table_where'			: ['text'				, 'The items from "foreign_table" are selected with this WHERE-clause.'],
		'.TCEforms.config[type=select].foreign_table_prefix'			: ['text'				, 'Label prefix to the title of the records from the foreign-table (LS).'],
		'.TCEforms.config[type=select].foreign_table_loadIcons'			: ['boolean'				, 'If set, then the icons for the records of the foreign table are loaded and presented in the form.'],
		'.TCEforms.config[type=select].neg_foreign_table'			: ['text'				, 'The item-array will be filled with records from the table defined here.'],
		'.TCEforms.config[type=select].neg_foreign_table_where'			: ['text'				, 'The items from "foreign_table" are selected with this WHERE-clause.'],
		'.TCEforms.config[type=select].neg_foreign_table_prefix'		: ['text'				, 'Label prefix to the title of the records from the foreign-table (LS).'],
		'.TCEforms.config[type=select].neg_foreign_table_loadIcons'		: ['boolean'				, 'If set, then the icons for the records of the foreign table are loaded and presented in the form.'],
		'.TCEforms.config[type=select].neg_foreign_table_imposeValueField'	: [''					, ''],
		'.TCEforms.config[type=select].dontRemapTablesOnCopy'	: ['text'						, 'Set it to the exact same value as "foreign_table" if you dont want values to be remapped on copy.'],
		'.TCEforms.config[type=select].rootLevel'		: ['boolean'						, 'If set, the "foreign_table_where" will be ignored and a "pid=0" will be added to the query to select only records from root level of the page tree.'],
		'.TCEforms.config[type=select].MM'			: ['text'						, 'Means that the relation to the records of "foreign_table" / "new_foreign_table" is done with a M-M relation with a third "join" table.'],

		'.TCEforms.config.default'				: ['text'						, 'Default value.'],
		'.TCEforms.config.items.numIndex'			: ['array',						, ''],
		'.TCEforms.config.items.numIndex.numIndex'		: ['text',						, ''],
		'.TCEforms.config.items.numIndex.numIndex[0]'		: ['text',						, 'First value is the item label (LS).'],
		'.TCEforms.config.items.numIndex.numIndex[1]'		: ['text',						, 'Second value is the value of the item.'],
		'.TCEforms.config.items.numIndex.numIndex[2]'		: ['text',						, 'Third value is an optional icon.'],
		'.TCEforms.config.items.numIndex.numIndex[3]'		: ['text',						, 'Forth value is an optional description text.'],
		'.TCEforms.config.items.numIndex.numIndex[4]'		: ['select[EXPL_ALLOW|EXPL_DENY]',			, 'Fifth value is “authMode” / “individual”'],

		'.TCEforms.config.internal_type' : 'select[db,file,...]',
		'.TCEforms.config.allowed' : 'select[pages,tt_content,gif,png,jpg,jpeg,...],multiple',
		'.TCEforms.config.max_size' : 'integer,positive',
		'.TCEforms.config.uploadfolder' : 'path',
		'.TCEforms.config.maxitems' : 'integer,positive',
		'.TCEforms.config.minitems' : 'integer,positive',
		'.TCEforms.config.multiple' : 'boolean',
		'.TCEforms.config.selectedListStyle' : 'text,css',
		'.TCEforms.config.itemListStyle' : 'text,css',
		'.TCEforms.config.show_thumbs' : 'boolean',
		'.TCEforms.config.softref' : 'text,commalist',
		'.TCEforms.config.wizards' : 'array',
	};

	function xmlarea_guielement_search(elm, ctx) {
		/* quick hit */
		if (presets[ctx])
			return presets[ctx];

		/* search progressively, stripping off prefixes */
		var rline = ctx.split('.').reverse();

		/* search with conditions */
		while (rline.length) {

			for (var c in presets) {
				var cline = c.split('.').reverse();
				var ccond = [], cc = 0;

				/* determine conditional search */
				for (var r = 0; r < cline.length; r++) {
					/* split off choice-collection */
					if (cline[r].indexOf('[') != -1) {
						ccond[r] = cline[r].substring(cline[r].indexOf('[') + 1, cline[r].lastIndexOf(']')).split('=');
						cline[r] = cline[r].substring(0, cline[r].indexOf('['));

						cc++;
					}
				}

				var rstrg = '.' + rline.reverse().join('.');
				var cstrg =  '' + cline.reverse().join('.');

				/* match, maybe conditional */
				if (rstrg == cstrg) {
					if (!cc)
						return presets[c];

					while (elm && !elm.struct)
						elm = elm.parentNode;

					/* determine conditional match (in XML-space) */
					for (var e = elm.struct.node, r = 0; r < ccond.length; r++) {
						if (ccond[r] && ccond[r].length) {
							var nde = e;
							var chd = null;

							if (nde)
							for (var n = 0; n < nde.childNodes.length; n++) {
								if (nde.childNodes[n].tagName == ccond[r][0]) {
									nde = nde.childNodes[n];
									break;
								}
							}

							if (nde)
							for (var n = 0; n < nde.childNodes.length; n++) {
								if (nde.childNodes[n].nodeValue == ccond[r][1]) {
									nde = nde.childNodes[n];
									break;
								}
							}

							if (nde)
								return presets[c];
						}

						e = e.parentNode;
					}
				}

				/* this was inplace, repair */
				rline.reverse();
				cline.reverse();
			}

			rline.pop();
		}

		return null;
	}

	function xmlarea_guielement_create(elm, ctx, gui) {
		if (!gui) {
			return null;
		}

		if (typeof gui[0] == 'string') {
			/* split config/opt */
			gui[0] = gui[0].split(',');

			/* split off choice-collection */
			if (gui[0][0] && (gui[0][0].indexOf('[') != -1)) {
				gui[0][0] = gui[0][0].substring(gui[0][0].indexOf('[') + 1, gui[0][0].lastIndexOf(']')).split('|');
			}
			/* split off parameter-collection */
			if (gui[0][1] && (gui[0][1].indexOf('|') != -1)) {
				gui[0][1] = gui[0][1].split('|');
			}
		}

		var check = typeof gui[0][0];

		/* create the element */
		if (check == 'object') {
			var inp, opt;

			inp = document.createElement('select');
			inp.value = elm.nodeValue;
			inp.title = gui[1];
		//	inp.multiple = gui[0][1];
		//	inp.size = gui[0][1] ? 10 : 1;

			for (var o = 0; o < gui[0][0].length; o++) {
				opt = document.createElement('option');
				opt.value = gui[0][0][o];

				if (opt.value == elm.nodeValue)
					opt.selected = 'selected';

				opt.appendChild(document.createTextNode(gui[0][0][o]));
				inp.appendChild(opt);
			}

			return inp;
		}
		else if (typeof gui[0][0] == 'string') {
			switch (gui[0][0]) {
				case 'array':
					break;
				case 'function':
					break;
				case 'set':
					break;
				case 'boolean':
					break;
				case 'integer':
					break;
				case 'text':
					break;
				case 'css':
					break;
			}
		}

		return null;
	}

	function xmlarea_guielement(elm) {
		var ctx = xmlarea_context(elm);
		var gui = xmlarea_guielement_search(elm, ctx);
		var inp = xmlarea_guielement_create(elm, ctx, gui);

		if (!inp) {
			inp = document.createElement('input');
			inp.type = 'text';
			inp.value = elm.nodeValue;
		}

		return inp;
	}

	/* DnD -------------------------------------------------------------- */
	var xmlarea_sortable_currentItem;
	var xmlarea_sortable_sortableParameters = {
		tree:true,
		ghosting:false,
		format:/(.*)/,
		handle:"nodename",
		dropOnEmpty:true,
		constraint:false,
		containment:[],
		scroll:window,
		onChange:xmlarea_sortable_change,
		onUpdate:xmlarea_sortable_update
	};

	function xmlarea_sortable_update(ft) {
		el = xmlarea_sortable_currentItem;
		subClass(el.parentNode.parentNode, 'candidate');

		if (!el.done) {
			/* update XML-information after drop */
			xmlarea_hierarchy_reindex(el.struct.parent.childs, el.struct.parent.childs.is_of_type == 'index');
			xmlarea_hierarchy_reindex(el.parentNode, el.parentNode.is_of_type == 'index');

			/* assign new parent */
			el.struct.parent = el.parentNode.struct;

			/* restructure XML */
			xmlarea_hierarchy_update(el);
		}

		el.done = true;
	}

	function xmlarea_sortable_reindex(father, is_index) {
		var row, adj, prp, val, arr, hir, knb;

		if (father.childNodes.length && is_index) {
			father.is_of_type = (is_index ? 'index' : father.is_of_type);

			for (var c = 0; c < father.childNodes.length; c++) {
				row = father.childNodes[c];
				adj = row.firstChild;
				prp = adj.firstChild.nextSibling;

				row.has_index = (is_index ? c : null);
				prp.firstChild.nodeValue = (is_index ? c + '. ' : '');
			}
		}
	}

	function xmlarea_sortable_change(el) {
		xmlarea_sortable_currentItem = el;
		el.done = false;

		// el.struct.edt.getElementsByTagName('ul');
		var ul, uls = [
			el.struct.path,			// parent.childs,
			el.parentNode			//.struct.childs
		];

		/* measure path */
		el.struct.path = el.parentNode;

		/* first, and we didn't leave the urrent yet */
		if (uls[0] == (ul = uls[1])) {
			addClass(ul.lastChild, 'last-child');

			xmlarea_sortable_reindex(ul, ul.is_of_type == 'index');
		}
		/* process both */
		else {
			for (var u = 0; u < uls.length; u++) {
				ul = uls[u];
				nd = ul.struct;

				if (ul == el.parentNode)
					addClass(ul.parentNode, 'candidate');
				else
					subClass(ul.parentNode, 'candidate');

				if (ul.lastChild) {
					addClass(ul.lastChild, 'last-child');

					if (ul == el.parentNode)
						addClass(nd.element, 'opened');
				}
				else
					subClass(nd.element, 'opened');

				/* assign new numbers if we reorder (no ol possible) */
				xmlarea_sortable_reindex(ul, ul.is_of_type == 'index');
			}

			/* if we move the only node out of a list, we make the list appear as empty */
			if (!el.struct.parent.childs.childNodes.length)
				addClass(el.struct.parent.element, 'empty');
		}

		if (el.previousSibling)
			subClass(el.previousSibling, 'last-child');
		if (el.nextSibling)
			subClass(el, 'last-child');
	}

	function xmlarea_sortable_init(hier, pool) {
		xmlarea_sortable_sortableParameters.containment = [hier,pool];

		Sortable.create(hier, xmlarea_sortable_sortableParameters);
		Sortable.create(pool, xmlarea_sortable_sortableParameters);
	}

	function xmlarea_sortable_extend(hier, pool) {
		xmlarea_sortable_sortableParameters.containment = [hier,pool];

		Sortable.create(hier, xmlarea_sortable_sortableParameters);
		Sortable.create(pool, xmlarea_sortable_sortableParameters);
	}

	/* App-Events -------------------------------------------------------------- */
	function xmlarea_hierarchy_edit(field, pos) {
		var elm = field.childNodes[pos];
		var row, inp;

		if (elm) {
			inp = xmlarea_guielement(elm);
			inp.className = 'inline';
			inp.onblur = function() {
				elm = document.createTextNode(this.value);

				this.parentNode.insertBefore(elm, this);
				this.parentNode.removeChild(this);

				xmlarea_hierarchy_redo(elm.parentNode.parentNode.parentNode);
			}

			field.insertBefore(inp, elm);
			field.removeChild(elm);

			inp.focus();
		}
	}

	function xmlarea_hierarchy_reindex(father, is_index) {
		var row, adj, prp, val, arr, hir, knb;

		if (father.childNodes.length) {
			father.is_of_type = (is_index ? 'index' : father.is_of_type);

			for (var c = 0; c < father.childNodes.length; c++) {
				row = father.childNodes[c];
				adj = row.firstChild;
				prp = adj.firstChild.nextSibling;

				row.has_index = (is_index ? c : null);
				prp.firstChild.nodeValue = (is_index ? c + '. ' : '');

				if (!row.struct.node)
					continue;

				// XML node
				if (is_index)
					row.struct.node.setAttribute('index', c);
				else
					row.struct.node.removeAttribute('index');
			}

			subClass(father.struct.element, 'empty');
		}
		else
			addClass(father.struct.element, 'empty');

		if (!father.struct.node)
			return;

		xmlarea_whitespace(father.struct.node);

		// XML node
		if (father.childNodes.length)
			father.struct.node.setAttribute('type', 'array');
		else
			father.struct.node.removeAttribute('type');
	}

	function xmlarea_hierarchy_redo(row) {
		var nde = row.struct.node;
		if (!row.struct.node)
			return;

		var tag = row.struct.name.childNodes[2].nodeValue;
		var val = row.struct.value.childNodes[0].nodeValue;

		// XML nodes
		if (!nde.firstChild)
			nde.appendChild(row.struct.xml.createTextNode(''));
		if (nde.firstChild.nodeValue != val)
			nde.firstChild.nodeValue = val;
		if (nde.tagName != tag) {
			var cln = row.struct.xml.createElement(tag);

			while (nde.childNodes.length) {
				var sel = nde.firstChild;
				nde.removeChild(sel)
				cln.appendChild(sel);
			}

			for (var a = 0; a < nde.attributes.length; a++) {
				var ats = nde.attributes.item(a);
				cln.setAttribute(ats.nodeName, ats.nodeValue);
			}

			nde.parentNode.insertBefore(cln, nde);
			nde.parentNode.removeChild(nde);

			row.struct.node = cln;
		}

		xmlarea_string(row.struct.root.tarea);
	}

	function xmlarea_hierarchy_remove(row) {
		var nde = row.struct.node;

		// XML nodes
		nde.parentNode.removeChild(nde);

		row.struct.node = null;
		row.struct.xml = null;

		xmlarea_string(row.struct.root.tarea);
	}

	function xmlarea_hierarchy_reset(tarea) {
		var crt = tarea.struct.clipboard;
		var hir = tarea.struct.hierarchy;

		if (!crt.lastChild) {
			xmlarea_hierarchy_rowdummy(tarea, crt.struct, crt);
			xmlarea_sortable_extend(hir.id, crt.id);
		}
	}

	function xmlarea_hierarchy_clone(row) {
		/* the element hasn't been dropped into a xml-aware tree */
		if (!(row.struct.xml = row.struct.parent.xml))
			return;

		row.struct.node = xmlarea_hierarchy_excursive(row);

		// XML nodes
		var newx = (row.struct.parent.node);
		var posi = (row.nextSibling ? row.nextSibling.struct.node : null);

		newx.insertBefore(row.struct.node, posi);

		if (row.struct.parent.value)
			row.struct.parent.value.childNodes[0].nodeValue = '...';

		xmlarea_whitespace(newx);
		xmlarea_string(row.struct.root.tarea);

		xmlarea_hierarchy_reset(row.struct.root.tarea);
	}

	function xmlarea_hierarchy_update(row) {
		/* the element wasn't connected to a xml-aware tree */
		if (!row.struct.node)
			return xmlarea_hierarchy_clone(row);
		/* the element has been dropped outof a xml-aware tree */
		if (!row.struct.parent.node)
			return xmlarea_hierarchy_remove(row);

		// XML nodes
		var oldx = row.struct.node.parentNode;
		var newx = (row.struct.parent.node);
		var posi = (row.nextSibling ? row.nextSibling.struct.node : null);

		oldx.removeChild(row.struct.node);
		newx.insertBefore(row.struct.node, posi);

		if (row.struct.parent.value)
			row.struct.parent.value.childNodes[0].nodeValue = '...';

		xmlarea_whitespace(newx);
		xmlarea_string(row.struct.root.tarea);
	}

	/* App-Core -------------------------------------------------------------- */
	function xmlarea_hierarchy_excursive(rot) {
		var chd, adj, prp, val, arr, hir, knb;

		hir = rot.struct.childs;
		prp = rot.struct.name.childNodes[2].nodeValue;
		val = rot.struct.value.childNodes[0].nodeValue;

		chd = rot.struct.xml.createElement(prp);

		/* transfer structure-informations */
		if (rot.is_of_type)
			chd.setAttribute('type', rot.is_of_type);
		if (hir.childNodes.length)
			chd.setAttribute('type', 'array');
		if (hir.is_of_type == 'index')
			chd.setAttribute('type', 'index');
		if (rot.parentNode.is_of_type == 'index')
			chd.setAttribute('index', rot.has_index);

	//	knb = '';
	//	for (var i = 0; i < rot.struct.indention; i++)
	//		knb += '\t';

		if (hir.childNodes.length) {
			for (var r = 0; r < hir.childNodes.length; r++) {
				var row = hir.childNodes[r];
				if (row.nodeType == 1) {
					row.struct.xml = rot.struct.xml;
					row.struct.node = xmlarea_hierarchy_excursive(row);

	//				chd.appendChild(rot.struct.xml.createTextNode('\n' + knb + '\t'));
					chd.appendChild(row.struct.node);
				}
			}

	//		chd.appendChild(rot.struct.xml.createTextNode('\n' + knb));
		}
		else {
			chd.appendChild(rot.struct.xml.createTextNode(val));
		}

		return chd;
	}

	function xmlarea_hierarchy_rowdummy(tarea, up, father) {
		var ctt = tarea.parentNode;
		var row, adj, prp, val, arr, hir, knb;

		father.className = 'DS-tree';
		father.struct = up;
		{
			row = document.createElement('li');
			adj = document.createElement('div');
			prp = document.createElement('div');
			val = document.createElement('div');
			arr = document.createElement('div');
			hir = document.createElement('ul');

			row.id = 'id' + Math.floor(Math.random() * 0xFFFFFFFF);
			row.className = 'node empty last-child';
			row.struct = {
				edt : tarea.struct.editor,
				root : tarea.struct,
				xml : null,

				parent : up,
				indention : up.indention,
				node : null,
				element : row,

				name : prp,
				value : val,
				type : arr,
				childs : hir,

				path : father
			};

			prp.className = 'nodename';
			prp.appendChild(document.createTextNode(''));
			prp.appendChild(document.createTextNode('<'));
			prp.appendChild(document.createTextNode('new'));
			prp.appendChild(document.createTextNode('>'));
			prp.ondblclick = function() {
				xmlarea_hierarchy_edit(this, 2);
			};

			val.className = 'nodevalue';
			val.appendChild(document.createTextNode('...'));
			val.ondblclick = function() {
				xmlarea_hierarchy_edit(this, 0);
			};

			/* do WE have childs? do WE index? */
			arr.className = 'nodearray';
			arr.appendChild(document.createElement('input'));
			arr.lastChild.type = 'checkbox';
			arr.lastChild.onclick = function() {
				xmlarea_hierarchy_reindex(this.nextSibling, this.checked);
				xmlarea_string(this.parentNode.parentNode.parentNode.struct.root.tarea);
			};

			/* do WE have childs? */
			knb = document.createElement('div');
			knb.className = 'knob';
			knb.onclick = function() {
				var cn = this.parentNode.parentNode.className;
				if (cn.hasWord('closed'))
					cn = cn.replaceWord('closed', 'opened');
				else if (cn.hasWord('opened'))
					cn = cn.replaceWord('opened', 'closed');
				this.parentNode.parentNode.className = cn;
			};

			hir.className = 'DS-tree';
			hir.struct = row.struct;

			arr.appendChild(hir);
			adj.appendChild(knb);
			adj.appendChild(prp);
			adj.appendChild(val);
			adj.appendChild(arr);
			row.appendChild(adj);

			father.appendChild(row);
		}
	}

	function xmlarea_hierarchy_recursive(tarea, up, father, chl) {
		var ctt = tarea.parentNode;
		var tre = tarea.struct.hierarchy;
		var row, adj, prp, val, arr, hir, knb, res;

		father.className = 'DS-tree';
		father.struct = up;
		if (chl.childNodes.length) {
			for (var c = 0; c < chl.childNodes.length; c++) {
				var chd = chl.childNodes[c];
				if (chd.nodeType == 1) {
					row = document.createElement('li');
					adj = document.createElement('div');
					prp = document.createElement('div');
					val = document.createElement('div');
					arr = document.createElement('div');
					hir = document.createElement('ul');

					/* create element structure */
					row.id = 'id' + Math.floor(Math.random() * 0xFFFFFFFF);
					row.className = 'node';
					row.struct = {
						edt : tarea.struct.editor,
						root : tarea.struct,
						xml : tarea.struct.xml,

						parent : up,
						indention : up.indention,
						node : chd,
						element : row,

						name : prp,
						value : val,
						type : arr,
						childs : hir,

						path : father
					};

					/* transfer XML-informations */
					row.is_of_type = chd.getAttribute('type');
					row.has_index = chd.getAttribute('index');

					/* analyse children */
					if (chd.childNodes.length > 0) {
						for (var t = 0, h = 0, i = false, x = ''; t < chd.childNodes.length; t++) {
							if (chd.childNodes[t].nodeType != 1)
								x += chd.childNodes[t].nodeValue;
							else {
								h++;
								if (chd.childNodes[t].hasAttribute('index'))
									i = true;
							}
						}

						if (h)
							row.className += ' closed';
						if (i)
							hir.is_of_type = 'index';

						val.appendChild(document.createTextNode(x));
					}

					prp.className = 'nodename';
				//	prp.style.width = (l - 22) + 'px';
					prp.appendChild(document.createTextNode((father.is_of_type == 'index' ? row.has_index + '. ' : '')));
					prp.appendChild(document.createTextNode('<'));
					prp.appendChild(document.createTextNode(chd.tagName));
					prp.appendChild(document.createTextNode('>'));
					prp.ondblclick = function() {
						xmlarea_hierarchy_edit(this, 2);
					};

					val.className = 'nodevalue';
				//	val.style.width = (r - 22) + 'px';
					val.ondblclick = function() {
						xmlarea_hierarchy_edit(this, 0);
					};

					/* do WE have childs? do WE index? */
					arr.className = 'nodearray';
					arr.appendChild(document.createElement('input'));
					arr.lastChild.type = 'checkbox';
					arr.lastChild.checked = (hir.is_of_type == 'index' ? 'checked' : '');
					arr.lastChild.onclick = function() {
						xmlarea_hierarchy_reindex(this.nextSibling, this.checked);
						xmlarea_string(this.parentNode.parentNode.parentNode.struct.root.tarea);
					};

					/* do WE have childs? */
					knb = document.createElement('div');
					knb.className = 'knob';
					knb.onclick = function() {
						var cn = this.parentNode.parentNode.className;
						if (cn.hasWord('closed'))
							cn = cn.replaceWord('closed', 'opened');
						else if (cn.hasWord('opened'))
							cn = cn.replaceWord('opened', 'closed');
						this.parentNode.parentNode.className = cn;

					};

					xmlarea_hierarchy_recursive(tarea, row.struct, hir, chd);

					arr.appendChild(hir);
					adj.appendChild(knb);
					adj.appendChild(prp);
					adj.appendChild(val);
					adj.appendChild(arr);
					row.appendChild(adj);

					father.appendChild(row);
				}
			}

			/* non-empty tree */
			if (father.lastChild)
				father.lastChild.className += ' last-child';
		}

		/* empty tree */
		if (!father.lastChild && up.element)
			up.element.className += ' empty';
	}

	function xmlarea_hierarchy(tarea) {
		var hir = tarea.struct.hierarchy;
		var crt = tarea.struct.clipboard;
		var chl = tarea.struct.xml.documentElement;
		var row, act, prp, atr;

		// remove all initial children
		while (crt.childNodes.length) crt.removeChild(crt.lastChild);
		while (hir.childNodes.length) hir.removeChild(hir.lastChild);

		crt.struct = {
			xml : null,
			node : null,
			element : crt,
			childs : crt,
			indention : -1,
			context : null
		};

		hir.struct = {
			xml : tarea.struct.xml,
			node : tarea.struct.xml.documentElement,
			element : hir,
			childs : hir,
			indention : -1,
			context : tarea.struct.context
		};

		xmlarea_hierarchy_rowdummy(tarea, crt.struct, crt);
		xmlarea_hierarchy_recursive(tarea, hir.struct, hir, chl);

		xmlarea_sortable_init(hir.id, crt.id);
	}

	function xmlarea_dnd_reconstruct(tarea, context) {
		var ctt = tarea.parentNode;
		var hir = document.createElement('ul');
		var crt = document.createElement('ul');

		while (ctt.lastChild)
			ctt.removeChild(ctt.lastChild);

		ctt.className = 'xmlarea';
		crt.className = 'DS-tree';
		hir.className = 'DS-tree';
		ctt.id = 'xmlarea_' + Math.random();
		crt.id = ctt.id + '_pool';
		hir.id = ctt.id + '_tree';
		ctt.appendChild(document.createTextNode('Clipboard:'));
		ctt.appendChild(document.createElement('br'));
		ctt.appendChild(crt); crt.style.marginBottom = '1em';
		ctt.appendChild(document.createTextNode('XML-Tree: '));
		ctt.appendChild(document.createElement('br'));
		ctt.appendChild(document.createElement('em')).appendChild(document.createTextNode(''));
		ctt.appendChild(hir); hir.style.marginBottom = '1em';
		ctt.appendChild(document.createTextNode('XML-Code:'));
		ctt.appendChild(document.createElement('br'));
		ctt.appendChild(tarea); tarea.onchange = function() { xmlarea_dnd_reconstruct(this, context); };

		tarea.struct = {
			editor : ctt,
			infos : ctt.childNodes[5],
			clipboard : crt,
			hierarchy : hir,
			tarea : tarea
		};

		xmlarea_doc(tarea, context);
		xmlarea_hierarchy(tarea);
		xmlarea_string(tarea);
	}

	function xmlarea_dnd_construct(tarea, context) {
		if (!tarea || (tarea.tagName.toLowerCase() != 'textarea'))
			return;

		var par = tarea.parentNode;
		var ctt = document.createElement('div');

		par.insertBefore(ctt, tarea);
		par.removeChild(tarea);
		ctt.appendChild(tarea);

		xmlarea_dnd_reconstruct(tarea, context);
	}

	/* prototype ------------------------------------------------------------- */
	var xmlarea_started = false;

	function addClass(elm, word) {
		if (elm) elm.className = elm.className.addWord(word);
	}
	function subClass(elm, word) {
		if (elm) elm.className = elm.className.subWord(word);
	}
	function hasClass(elm, word) {
		if (elm) return elm.className.hasWord(word);
	}
	function replaceClass(elm, word, subst) {
		if (elm) elm.className = elm.className.replaceWord(word, subst);
	}

	function xmlarea_init() {
		if (xmlarea_started)
			return;
		xmlarea_started = true;

		/* prototype */
		Object.extend(String.prototype, {
			addWord: function(word) {
				var res = this.split(' ');

				{
					res.push(word);
					res = res.uniq();
				}

				return res.join(' ');
			},
			subWord: function(word) {
				var res = this.split(' ');

				{
					res = res.without(word);
				}

				return res.join(' ');
			},
			hasWord: function(word) {
				var res = this.split(' ');

				return res.indexOf(word) != -1;
			},
			replaceWord: function(word, subst) {
				var res = this.split(' ');

				if (res.indexOf(word) != -1) {
					res = res.without(word);
					res.push(subst);
					res = res.uniq();
				}

				return res.join(' ');
			}
		});

		Event.observe(window, 'load', function(){
			if ($("typo3-docbody")) {
				xmlarea_sortable_sortableParameters.scroll = $("typo3-docbody");
				xmlarea_sortable_sortableParameters.scrollid = "typo3-docbody";
			}

			var xmlareas = getElementsByClassName('xml', 'textarea');
			var tarea;

			/* construct the visual structure editor display */
			if (xmlareas) {
				while ((tarea = xmlareas.pop())) {
					xmlarea_dnd_construct(tarea, tarea.getAttribute('rel'));
				//	xmlarea_tree_construct(tarea);
				}
			}
		});
	}
