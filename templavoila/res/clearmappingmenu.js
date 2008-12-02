/***************************************************************
*  Copyright notice
*
*  (c) 2007 Ingo Renner <ingo@typo3.org>
*  All rights reserved
*
*  This script is part of the TYPO3 project. The TYPO3 project is
*  free software; you can redistribute it and/or modify
*  it under the terms of the GNU General Public License as published by
*  the Free Software Foundation; either version 2 of the License, or
*  (at your option) any later version.
*
*  The GNU General Public License can be found at
*  http://www.gnu.org/copyleft/gpl.html.
*  A copy is found in the textfile GPL.txt and important notices to the license
*  from the author is found in LICENSE.txt distributed with these scripts.
*
*
*  This script is distributed in the hope that it will be useful,
*  but WITHOUT ANY WARRANTY; without even the implied warranty of
*  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*  GNU General Public License for more details.
*
*  This copyright notice MUST APPEAR in all copies of the script!
***************************************************************/

/**
 * class to handle the clear cache menu
 *
 * $Id$
 */
var ClearMappingMenu = Class.create({

	/**
	 * registers for resize event listener and executes on DOM ready
	 */
	initialize: function() {
		Event.observe(window, 'load', function(){
			var toolbarGroup = $$('#clear-mapping-actions-menu')[0];
			var toolbarItem  = $$('#clear-mapping-actions-menu > .toolbar-item')[0];

			Event.observe(toolbarItem, 'click', this.toggleMenu);
			Event.observe(toolbarItem, 'blur' , this.toggleMenu);

				// observe all clicks on clear cache actions in the menu
			$$('#clear-mapping-actions-menu li a').each(function(element) {
				Event.observe(element, 'click', this.clearMapping.bind(this));
			}.bindAsEventListener(this));
		}.bindAsEventListener(this));
	},

	/**
	 * toggles the visibility of the menu and places it under the toolbar icon
	 */
	toggleMenu: function(event) {
		var toolbarItem = $$('#clear-mapping-actions-menu > .toolbar-item'     )[0];
		var toolbarMenu = $$('#clear-mapping-actions-menu > .toolbar-item-menu')[0];

		if(!toolbarItem.hasClassName('toolbar-item-active') && !event.type.match(/blur/)) {
			toolbarItem.addClassName('toolbar-item-active');
			Effect.Appear(toolbarMenu, {duration: 0.2});
		//	TYPO3BackendToolbarManager.hideOthers(toolbarItem);
		} else {
			toolbarItem.removeClassName('toolbar-item-active');
			Effect.Fade(toolbarMenu, {duration: 0.1});
		}

		if (event) {
			Event.stop(event);
		}
	},

	/**
	 * calls the actual clear cache URL using an asynchronious HTTP request
	 *
	 * @param	Event	prototype event object
	 */
	clearMapping: function(event) {
		var url             = '';
		var clickedElement  = Event.element(event);

		if (clickedElement.tagName == 'IMG') {
			url =  clickedElement.up('a').href;
		} else {
			url =  clickedElement.href;
		}

		if (url) {
			window.location.href = url;
		}
		Event.stop(event);
	//	this.toggleMenu(event);
	}
});

var TYPO3BackendTVClearMappingMenu = new ClearMappingMenu();
