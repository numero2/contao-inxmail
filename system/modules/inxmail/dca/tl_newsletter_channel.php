<?php if (!defined('TL_ROOT')) die('You can not access this file directly!');

/**
 * Contao Open Source CMS
 * Copyright (C) 2005-2011 Leo Feyer
 *
 * Formerly known as TYPOlight Open Source CMS.
 *
 * This program is free software: you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License as published by the Free Software Foundation, either
 * version 3 of the License, or (at your option) any later version.
 * 
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU
 * Lesser General Public License for more details.
 * 
 * You should have received a copy of the GNU Lesser General Public
 * License along with this program. If not, please visit the Free
 * Software Foundation website at <http://www.gnu.org/licenses/>.
 *
 * PHP version 5
 * @copyright  numero2 - Agentur für Internetdienstleistungen <www.numero2.de>
 * @author     Benny Born <benny.born@numero2.de>
 * @package    Inxmail
 * @license    LGPL
 * @filesource
 */
 

$GLOBALS['TL_DCA']['tl_newsletter_channel']['palettes']['default'] = str_replace(
	'{smtp_legend:hide}'
,	'{inxmail_legend},inxServer,inxListName,inxUser,inxPass;{smtp_legend:hide}'
,	$GLOBALS['TL_DCA']['tl_newsletter_channel']['palettes']['default']
);

$GLOBALS['TL_DCA']['tl_newsletter_channel']['fields']['inxServer'] = array(
	'label'				=> &$GLOBALS['TL_LANG']['tl_newsletter_channel']['inxServer']
,	'inputType'			=> 'text'
,	'eval'				=> array('mandatory'=>false, 'maxlength'=>255, 'tl_class'=>'w50')
);

$GLOBALS['TL_DCA']['tl_newsletter_channel']['fields']['inxListName'] = array(
	'label'				=> &$GLOBALS['TL_LANG']['tl_newsletter_channel']['inxListName']
,	'inputType'			=> 'select'
,	'options_callback'  => array('tl_inxmail_nl', 'getLists')
,	'eval'				=> array('mandatory'=>false, 'maxlength'=>255, 'tl_class'=>'w50')
);

$GLOBALS['TL_DCA']['tl_newsletter_channel']['fields']['inxUser'] = array(
	'label'				=> &$GLOBALS['TL_LANG']['tl_newsletter_channel']['inxUser']
,	'inputType'			=> 'text'
,	'eval'				=> array('mandatory'=>false, 'maxlength'=>255, 'tl_class'=>'w50')
);

$GLOBALS['TL_DCA']['tl_newsletter_channel']['fields']['inxPass'] = array(
	'label'				=> &$GLOBALS['TL_LANG']['tl_newsletter_channel']['inxPass']
,	'inputType'			=> 'text'
,	'eval'				=> array('mandatory'=>false, 'maxlength'=>255, 'submitOnChange'=>true, 'tl_class'=>'w50')
);


class tl_inxmail_nl extends Backend {
	
	
	public function getLists() {
	
		$objForm = NULL;
		$objForm = $this->Database->prepare("SELECT inxServer, inxUser, inxPass FROM tl_newsletter_channel WHERE id=?")->limit(1)->execute( $this->Input->get('id') );
	
		$aLists = array();
		
		if( !$objForm->inxServer || !$objForm->inxUser || !$objForm->inxPass ) {
		
			$aLists[0] = $GLOBALS['TL_LANG']['tl_newsletter_channel']['inxSetLoginFirst'];
		
		} else {
		
			try {
				$aLists = ModuleInxmail::getAvailableLists( $objForm->inxServer, $objForm->inxUser, $objForm->inxPass );
			} catch( Exception $e ) {
				$aLists[0] = $GLOBALS['TL_LANG']['tl_newsletter_channel']['inxLoginWrong'];
				$this->log('API Exception: '.$e->getMessage(), 'tl_newsletter_channel getLists()', TL_ERROR);
			}
		}
		
		return $aLists;
	}

}

?>