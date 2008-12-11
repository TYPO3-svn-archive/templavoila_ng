/***************************************************************
*  Copyright notice
*
*  (c) 2008 Niels Fröhling <niels@frohling.biz>
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
 * class to handle the sticky information toggle
 *
 * $Id$
 */

function getBaseScript(args) {
	var mod = window.location.href.match(/mod.php\?M\=([^\&\=]+)\&?/);
	if (mod[1])
		return 'mod.php?M=' + mod[1] + '&' + args;
	else
		return 'index.php?' + args;
}

var StickyToggle = Class.create({

	/**
	 * registers for resize event listener and executes on DOM ready
	 */
	initialize: function() {
		Event.observe(window, 'load', function(){
			var stickyBlock = $('sticky-block');
			var stickyClass = stickyBlock.parentNode;
			var stickyShift = $('typo3-docbody');

			if(stickyClass.className.match(/expulsed/)) {
				var now = (stickyBlock.getHeight() - 8);
				var off = (10);

				stickyShift.style.top = (now + 6 + 51) + 'px';
				stickyShift.style.paddingTop = (off) + 'px';
			}
			else if(stickyClass.className.match(/impulsed/))
				;
			else
				stickyClass.className += ' impulsed';

			Event.observe('sticky-toggle', 'click', this.toggleSticky);
		}.bindAsEventListener(this));
	},

	/**
	 * toggles the visibility of the menu and places it under the toolbar icon
	 */
	toggleSticky: function(event) {
		var stickyBlock = $('sticky-block');
		var stickyClass = stickyBlock.parentNode;
		var stickyShift = $('typo3-docbody');

		if(!stickyClass.className.match(/expulsed/)) {
		/*	Effect.Scale(stickyBlock, {
				scaleX : false,
				scaleY : true,
				scaleContent : false,
				scaleFromCenter : false,
				originalHeight : stickyClass.getHeight(),
				duration: 2.5});*/
			var duration = 1.0;
			var reach = 0.0;
			var scale = setInterval(function() {
				var now = ((stickyBlock.getHeight() - 8) * reach) / duration;
				var off = (10 * reach) / duration;
				stickyClass.style.height = (now + 8) + 'px';
				stickyShift.style.top = (now + 6 + 51) + 'px';
				stickyShift.style.paddingTop = (off) + 'px';
				if (reach == 0.0) {
				}
				reach += 0.2;
				if (reach > duration) {
					clearInterval(scale);
					stickyClass.style.height = '';

					stickyClass.className =
					stickyClass.className.replace(/impulsed/, 'expulsed');

					new Ajax.Request(getBaseScript('ajaxStick=1&SET[stick]=1'), {
						'method': 'head'
					});
				}
			}, 100);
		} else {
			var duration = 1.0;
			var reach = 1.0;
			var scale = setInterval(function() {
				var now = ((stickyBlock.getHeight()) * reach) / duration;
				var off = (10 * reach) / duration;
				stickyClass.style.height = (now + 8) + 'px';
				stickyShift.style.top = (now + 6 + 51) + 'px';
				stickyShift.style.paddingTop = (off) + 'px';
				if (reach == duration) {
					stickyClass.className =
					stickyClass.className.replace(/expulsed/, 'impulsed');
				}
				reach -= 0.2;
				if (reach < 0.0) {
					clearInterval(scale);
					stickyClass.style.height = '';

					stickyShift.style.top = '';
					stickyShift.style.paddingTop = '';

					new Ajax.Request(getBaseScript('ajaxStick=1&SET[stick]=0'), {
						'method': 'head'
					});
				}
			}, 100);
		}

	//	stickyShift.style.top = (stickyBlock.getHeight() - 11 + 51) + 'px';

		if (event) {
			Event.stop(event);
		}
	},
});

var TYPO3BackendTVStickyToggle = new StickyToggle();
