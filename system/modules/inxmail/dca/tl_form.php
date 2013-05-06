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

 
$GLOBALS['TL_DCA']['tl_form']['palettes']['default'] = str_replace(
	'{expert_legend:hide}'
,	'{inxmail_legend},inxServer,inxMailField,inxUser,inxPass,inxListName,inxAdditionalFields;{expert_legend:hide}'
,	$GLOBALS['TL_DCA']['tl_form']['palettes']['default']
);

$GLOBALS['TL_DCA']['tl_form']['fields']['inxServer'] = array(
	'label'				=> &$GLOBALS['TL_LANG']['tl_form']['inxServer']
,	'inputType'			=> 'text'
,	'eval'				=> array('mandatory'=>false, 'maxlength'=>255, 'tl_class'=>'w50')
);

$GLOBALS['TL_DCA']['tl_form']['fields']['inxMailField'] = array(
	'label'				=> &$GLOBALS['TL_LANG']['tl_form']['inxMailField']
,	'inputType'			=> 'select'
,	'options_callback'  => array('tl_inxmail', 'getFormFields')
,	'eval'				=> array('mandatory'=>false, 'maxlength'=>255, 'tl_class'=>'w50')
);

$GLOBALS['TL_DCA']['tl_form']['fields']['inxUser'] = array(
	'label'				=> &$GLOBALS['TL_LANG']['tl_form']['inxUser']
,	'inputType'			=> 'text'
,	'eval'				=> array('mandatory'=>false, 'maxlength'=>255, 'tl_class'=>'w50')
);

$GLOBALS['TL_DCA']['tl_form']['fields']['inxPass'] = array(
	'label'				=> &$GLOBALS['TL_LANG']['tl_form']['inxPass']
,	'inputType'			=> 'text'
,	'eval'				=> array('mandatory'=>false, 'maxlength'=>255, 'submitOnChange'=>true, 'tl_class'=>'w50')
);

$GLOBALS['TL_DCA']['tl_form']['fields']['inxListName'] = array(
	'label'				=> &$GLOBALS['TL_LANG']['tl_form']['inxListName']
,	'inputType'			=> 'select'
,	'options_callback'  => array('tl_inxmail', 'getLists')
,	'eval'				=> array('mandatory'=>false, 'maxlength'=>255, 'tl_class'=>'clr')
);

$GLOBALS['TL_DCA']['tl_form']['fields']['inxAdditionalFields'] = array(
	'label'				=> &$GLOBALS['TL_LANG']['tl_form']['inxAdditionalFields']
,	'inputType'			=> 'multiColumnWizard'
,	'eval'      		=> array (
		'style' 		=> 'width:100%; clear:both;'
	,	'columnFields' 	=> array (
			'inxName' => array(
				'label' 			=> &$GLOBALS['TL_LANG']['tl_form']['inxAdditionalFieldsInxName']
			,	'inputType'     	=> 'select'
			,	'options_callback'  => array('tl_inxmail', 'getInxmailFields')
            ,	'mandatory' 		=> false
			,	'eval' 				=> array('style' => 'width:320px', 'includeBlankOption'=>true)
            )
		,	'formName' => array(
				'label' 			=> &$GLOBALS['TL_LANG']['tl_form']['inxAdditionalFieldsFormName']
			,	'inputType'     	=> 'select'
			,	'options_callback'  => array('tl_inxmail', 'getFormFields')
            ,	'mandatory' 		=> false
			,	'eval' 				=> array('style' => 'width:250px', 'includeBlankOption'=>true)
            )
		)
   )
);

class tl_inxmail extends Backend {


	public function getFormFields() {

		$objFields = NULL;
		$objFields = $this->Database->prepare("SELECT id, name FROM tl_form_field WHERE pid=? AND invisible!=1")->execute( $this->Input->get('id') );
		
		$aFields = array();
		
		while( $objFields->next() ) {
		
			if( !$objFields->name )
				continue;
		
			$aFields[ $objFields->name ] = $objFields->name;
		}
	
		return $aFields;
	}
	
	public function getInxmailFields() {
	
		$objForm = NULL;
		$objForm = $this->Database->prepare("SELECT inxServer, inxUser, inxPass, inxListName FROM tl_form WHERE id=?")->limit(1)->execute( $this->Input->get('id') );
	
		$aLists = array();
		
		if( !$objForm->inxServer || !$objForm->inxUser || !$objForm->inxPass ) {
		
			$aLists[0] = $GLOBALS['TL_LANG']['tl_form']['inxSetLoginFirst'];
		
		} else {
		
			if( !$objForm->inxListName ) {
			
				$aLists[0] = $GLOBALS['TL_LANG']['tl_form']['inxSelectListFirst'];
			
			} else {
			
				try {
					$aLists = ModuleInxmail::getAvailableFields( $objForm->inxServer, $objForm->inxUser, $objForm->inxPass, $objForm->inxListName );
				} catch( Exception $e ) {
					$this->log('API Exception: '.$e->getMessage(), 'tl_form getInxmailFields()', TL_ERROR);
				}
			}
		}
		
		return $aLists;
	}
	
	
	public function getLists() {
	
		$objForm = NULL;
		$objForm = $this->Database->prepare("SELECT inxServer, inxUser, inxPass FROM tl_form WHERE id=?")->limit(1)->execute( $this->Input->get('id') );
	
		$aLists = array();
		
		if( !$objForm->inxServer || !$objForm->inxUser || !$objForm->inxPass ) {
		
			$aLists[0] = $GLOBALS['TL_LANG']['tl_form']['inxSetLoginFirst'];
		
		} else {
		
			try {
				$aLists = ModuleInxmail::getAvailableLists( $objForm->inxServer, $objForm->inxUser, $objForm->inxPass );
			} catch( Exception $e ) {
				$aLists[0] = $GLOBALS['TL_LANG']['tl_form']['inxLoginWrong'];
				$this->log('API Exception: '.$e->getMessage(), 'tl_form getLists()', TL_ERROR);
			}
		}
		
		return $aLists;
	}

}

?>