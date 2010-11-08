<?php
# TYPO3 CVS ID: $Id: ext_localconf.php 5928 2007-07-12 11:20:33Z kasper $
if (!defined ('TYPO3_MODE')) 	die ('Access denied.');

// unserializing the configuration so we can use it here:
$_EXTCONF = unserialize($_EXTCONF);

// Adding the two plugins TypoScript:
t3lib_extMgm::addPItoST43($_EXTKEY, 'pi1/class.tx_templavoila_pi1.php', '_pi1', 'CType', 1);
if (!$_EXTCONF['enable.']['renderFCEHeader']) {
	t3lib_extMgm::addTypoScript($_EXTKEY, 'setup', 'tt_content.templavoila_pi1.10 >', 43);
}

// Use templavoila's wizard instead the default create new page wizard
t3lib_extMgm::addPageTSConfig('
	mod.web_list.newPageWiz.overrideWithExtension = templavoila
	mod.web_list.newPageWiz.overrideWithExtension.url = mod.php?M=tx_templavoila_wizards&wiz=page&id=###ID###&pid=###PID###
	mod.web_list.newContentWiz.overrideWithExtension = templavoila
	mod.web_list.newContentWiz.overrideWithExtension.url = mod.php?M=tx_templavoila_wizards&wiz=content&id=###ID###&sys_language_uid=###LANGUID###

	# set default templates-dir (fileadmin/templates)
	mod.web_txtemplavoilaM2.templatePath = templates
	
	# we are now in control
	TSFE.frontendEditingController = templavoila
');

$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['newPageWiz']['templavoila'] = 'mod.php?M=tx_templavoila_wizards&wiz=page';
$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['newContentWiz']['templavoila'] = 'mod.php?M=tx_templavoila_wizards&wiz=content';

// Use templavoila instead of the default page module
t3lib_extMgm::addUserTSConfig('
	options.overridePageModule = web_txtemplavoilaM1

	# free, bound
#	mod.web_txtemplavoilaM1.translationParadigm

	# left, toprows, toptabs
	mod.web_txtemplavoilaM1.sideBarEnable = 1
	mod.web_txtemplavoilaM1.sideBarPosition = toptabs

	mod.web_txtemplavoilaM1.disableAdvancedControls = 0
	mod.web_txtemplavoilaM1.disableHideIcon = 0
	mod.web_txtemplavoilaM1.disableDeleteIcon = 0
	mod.web_txtemplavoilaM1.enableDeleteIconForLocalElements = 1
	mod.web_txtemplavoilaM1.disableContainerElementLocalizationWarning = 0
	mod.web_txtemplavoilaM1.disableContainerElementLocalizationWarning_warningOnly = 0
');

// Adding Page Template Selector Fields to root line:
$GLOBALS['TYPO3_CONF_VARS']['FE']['addRootLineFields'] .= ',tx_templavoila_ds,tx_templavoila_to,tx_templavoila_next_ds,tx_templavoila_next_to';

// Register our classes at a the hooks:
$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_tcemain.php']['processDatamapClass']['templavoila'] = 'EXT:templavoila/classes/class.tx_templavoila_tcemain.php:tx_templavoila_tcemain';
$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_tcemain.php']['processCmdmapClass']['templavoila'] = 'EXT:templavoila/classes/class.tx_templavoila_tcemain.php:tx_templavoila_tcemain';
$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_tcemain.php']['moveRecordClass']['templavoila'] = 'EXT:templavoila/classes/class.tx_templavoila_tcemain.php:tx_templavoila_tcemain';
$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_userauthgroup.php']['recordEditAccessInternals']['templavoila'] = 'EXT:templavoila/classes/class.tx_templavoila_access.php:&tx_templavoila_access->recordEditAccessInternals';
$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_tsfebeuserauth.php']['frontendEditingController']['templavoila'] = 'EXT:templavoila/classes/class.tx_templavoila_frontendedit.php:tx_templavoila_frontendedit';

$GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['lowlevel']['cleanerModules']['tx_templavoila_unusedce'] = array('EXT:templavoila/classes/class.tx_templavoila_unusedce.php:tx_templavoila_unusedce');
$GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['l10nmgr']['indexFilter']['tx_templavoila_usedce'] = array('EXT:templavoila/classes/class.tx_templavoila_usedce.php:tx_templavoila_usedce');

// version-switch
if (is_callable(array('t3lib_div', 'int_from_ver')) && t3lib_div::int_from_ver(TYPO3_version) >= 4003000) {
	// configuration for new content element wizard
	t3lib_extMgm::addPageTSConfig('
	templavoila.wizards.newContentElement.wizardItems {
		common.header = LLL:EXT:cms/layout/locallang_db_new_content_el.xml:common
		common.elements {
			head {
				icon = gfx/c_wiz/header.gif
				title = LLL:EXT:cms/layout/locallang_db_new_content_el.xml:common_headerOnly_title
				description = LLL:EXT:cms/layout/locallang_db_new_content_el.xml:common_headerOnly_description
				tt_content_defValues {
					CType = header
				}
			}
			text {
				icon = gfx/c_wiz/regular_text.gif
				title = LLL:EXT:cms/layout/locallang_db_new_content_el.xml:common_regularText_title
				description = LLL:EXT:cms/layout/locallang_db_new_content_el.xml:common_regularText_description
				tt_content_defValues {
					CType = text
				}
			}
			textpic {
				icon = gfx/c_wiz/text_image_right.gif
				title = LLL:EXT:cms/layout/locallang_db_new_content_el.xml:common_textImage_title
				description = LLL:EXT:cms/layout/locallang_db_new_content_el.xml:common_textImage_description
				tt_content_defValues {
					CType = textpic
					imageorient = 17
				}
			}
			image {
				icon = gfx/c_wiz/images_only.gif
				title = LLL:EXT:cms/layout/locallang_db_new_content_el.xml:common_imagesOnly_title
				description = LLL:EXT:cms/layout/locallang_db_new_content_el.xml:common_imagesOnly_description
				tt_content_defValues {
					CType = image
					imagecols = 2
				}
			}
			bullets {
				icon = gfx/c_wiz/bullet_list.gif
				title = LLL:EXT:cms/layout/locallang_db_new_content_el.xml:common_bulletList_title
				description = LLL:EXT:cms/layout/locallang_db_new_content_el.xml:common_bulletList_description
				tt_content_defValues {
					CType = bullets
				}
			}
			table {
				icon = gfx/c_wiz/table.gif
				title = LLL:EXT:cms/layout/locallang_db_new_content_el.xml:common_table_title
				description = LLL:EXT:cms/layout/locallang_db_new_content_el.xml:common_table_description
				tt_content_defValues {
					CType = table
				}
			}

		}
		common.show = head,text,textpic,image,bullets,table

		special.header = LLL:EXT:cms/layout/locallang_db_new_content_el.xml:special
		special.elements {
			uploads {
				icon = gfx/c_wiz/filelinks.gif
				title = LLL:EXT:cms/layout/locallang_db_new_content_el.xml:special_filelinks_title
				description = LLL:EXT:cms/layout/locallang_db_new_content_el.xml:special_filelinks_description
				tt_content_defValues {
					CType = uploads
				}
			}
			multimedia {
				icon = gfx/c_wiz/multimedia.gif
				title = LLL:EXT:cms/layout/locallang_db_new_content_el.xml:special_multimedia_title
				description = LLL:EXT:cms/layout/locallang_db_new_content_el.xml:special_multimedia_description
				tt_content_defValues {
					CType = multimedia
				}
			}
			menu {
				icon = gfx/c_wiz/sitemap2.gif
				title = LLL:EXT:cms/layout/locallang_db_new_content_el.xml:special_sitemap_title
				description = LLL:EXT:cms/layout/locallang_db_new_content_el.xml:special_sitemap_description
				tt_content_defValues {
					CType = menu
					menu_type = 2
				}
			}
			html {
				icon = gfx/c_wiz/html.gif
				title = LLL:EXT:cms/layout/locallang_db_new_content_el.xml:special_plainHTML_title
				description = LLL:EXT:cms/layout/locallang_db_new_content_el.xml:special_plainHTML_description
				tt_content_defValues {
					CType = html
				}
			}
			div {
			 	icon = gfx/c_wiz/div.gif
				title = LLL:EXT:cms/layout/locallang_db_new_content_el.xml:special_divider_title
				description = LLL:EXT:cms/layout/locallang_db_new_content_el.xml:special_divider_description
				tt_content_defValues {
					CType = div
				}
			}

		}
		special.show = uploads,multimedia,menu,html,div

		forms.header = LLL:EXT:cms/layout/locallang_db_new_content_el.xml:forms
		forms.elements {
			mailform {
				icon = gfx/c_wiz/mailform.gif
				title = LLL:EXT:cms/layout/locallang_db_new_content_el.xml:forms_mail_title
				description = LLL:EXT:cms/layout/locallang_db_new_content_el.xml:forms_mail_description
				tt_content_defValues {
					CType = mailform
					bodytext (
	# Example content:
	Name: | *name = input,40 | Enter your name here
	Email: | *email=input,40 |
	Address: | address=textarea,40,5 |
	Contact me: | tv=check | 1

	|formtype_mail = submit | Send form!
	|html_enabled=hidden | 1
	|subject=hidden| This is the subject
					)
				}
			}
			search {
				icon = gfx/c_wiz/searchform.gif
				title = LLL:EXT:cms/layout/locallang_db_new_content_el.xml:forms_search_title
				description = LLL:EXT:cms/layout/locallang_db_new_content_el.xml:forms_search_description
				tt_content_defValues {
					CType = search
				}
			}
			login {
				icon = gfx/c_wiz/login_form.gif
				title = LLL:EXT:cms/layout/locallang_db_new_content_el.xml:forms_login_title
				description = LLL:EXT:cms/layout/locallang_db_new_content_el.xml:forms_login_description
				tt_content_defValues {
					CType = login
				}
			}

		}
		forms.show = mailform,search,login

		fce.header = LLL:EXT:templavoila/wizards/locallang_content.xml:fce
		fce.elements  {

		}
		fce.show = *

		plugins.header = LLL:EXT:cms/layout/locallang_db_new_content_el.xml:plugins
		plugins.elements {
			general {
				icon = gfx/c_wiz/user_defined.gif
				title = LLL:EXT:cms/layout/locallang_db_new_content_el.xml:plugins_general_title
				description = LLL:EXT:cms/layout/locallang_db_new_content_el.xml:plugins_general_description
				tt_content_defValues.CType = list
			}
		}
		plugins.show = *
	}

	# set to tabs for tab rendering
	templavoila.wizards.newContentElement.renderMode =

	');

	if (t3lib_div::compat_version('4.3')) {
		t3lib_extMgm::addPageTSConfig('
	templavoila.wizards.newContentElement.wizardItems.special.elements.media {
		icon = gfx/c_wiz/multimedia.gif
		title = LLL:EXT:cms/layout/locallang_db_new_content_el.xml:special_media_title
		description = LLL:EXT:cms/layout/locallang_db_new_content_el.xml:special_media_description
		tt_content_defValues {
			CType = media
		}
	}

	templavoila.wizards.newContentElement.wizardItems.special.show = uploads,media,menu,html,div
	');
	}
}
?>