<?

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
 

require_once TL_ROOT.'/system/modules/inxmail/inxmail_api/Api/Session.php';
require_once TL_ROOT.'/system/modules/inxmail/inxmail_api/Api/Order.php';
require_once TL_ROOT.'/system/modules/inxmail/inxmail_api/Api/LoginException.php';
require_once TL_ROOT.'/system/modules/inxmail/inxmail_api/Api/BusinessObject.php';
require_once TL_ROOT.'/system/modules/inxmail/inxmail_api/Api/Property/Property.php';
require_once TL_ROOT.'/system/modules/inxmail/inxmail_api/Apiimpl/Constants.php';
require_once TL_ROOT.'/system/modules/inxmail/inxmail_api/Apiimpl/SessionContext.php';
require_once TL_ROOT.'/system/modules/inxmail/inxmail_api/Apiimpl/PropertyConstants.php';
require_once TL_ROOT.'/system/modules/inxmail/inxmail_api/Apiimpl/RemoteObject.php';
require_once TL_ROOT.'/system/modules/inxmail/inxmail_api/Api/BOResultSet.php';
require_once TL_ROOT.'/system/modules/inxmail/inxmail_api/Apiimpl/Core/AbstractBOResultSet.php';
require_once TL_ROOT.'/system/modules/inxmail/inxmail_api/Apiimpl/Property/PropertyImpl.php';
require_once TL_ROOT.'/system/modules/inxmail/inxmail_api/Apiimpl/Property/PropertyContext.php';
require_once TL_ROOT.'/system/modules/inxmail/inxmail_api/Apiimpl/Property/PropertyResultSet.php';
require_once TL_ROOT.'/system/modules/inxmail/inxmail_api/Api/BOManager.php';
require_once TL_ROOT.'/system/modules/inxmail/inxmail_api/Api/List/ListContextManager.php';
require_once TL_ROOT.'/system/modules/inxmail/inxmail_api/Apiimpl/Core/BOResultSetDelegate.php';
require_once TL_ROOT.'/system/modules/inxmail/inxmail_api/Apiimpl/Util/IndexedBuffer.php';
require_once TL_ROOT.'/system/modules/inxmail/inxmail_api/Apiimpl/Core/DelegateBOResultSet.php';
require_once TL_ROOT.'/system/modules/inxmail/inxmail_api/Api/List/ListContext.php';
require_once TL_ROOT.'/system/modules/inxmail/inxmail_api/Api/List/StandardListContext.php';
require_once TL_ROOT.'/system/modules/inxmail/inxmail_api/Api/List/SystemListContext.php';
require_once TL_ROOT.'/system/modules/inxmail/inxmail_api/Apiimpl/List/ListImpl.php';
require_once TL_ROOT.'/system/modules/inxmail/inxmail_api/Apiimpl/List/SystemListImpl.php';
require_once TL_ROOT.'/system/modules/inxmail/inxmail_api/Apiimpl/List/StandardListImpl.php';
require_once TL_ROOT.'/system/modules/inxmail/inxmail_api/Apiimpl/List/ListManagerImpl.php';
require_once TL_ROOT.'/system/modules/inxmail/inxmail_api/Apiimpl/RemoteRef.php';
require_once TL_ROOT.'/system/modules/inxmail/inxmail_api/Apiimpl/RemoteRefImpl.php';
require_once TL_ROOT.'/system/modules/inxmail/inxmail_api/Apiimpl/Recipient/Constants.php';
require_once TL_ROOT.'/system/modules/inxmail/inxmail_api/Api/Recipient/RecipientContext.php';
require_once TL_ROOT.'/system/modules/inxmail/inxmail_api/Api/Recipient/RecipientMetaData.php';
require_once TL_ROOT.'/system/modules/inxmail/inxmail_api/Api/Recipient/Attribute.php';
require_once TL_ROOT.'/system/modules/inxmail/inxmail_api/Api/Recipient/RecipientRowSet.php';
require_once TL_ROOT.'/system/modules/inxmail/inxmail_api/Apiimpl/Util/IndexException.php';
require_once TL_ROOT.'/system/modules/inxmail/inxmail_api/Apiimpl/Util/Utils.php';
require_once TL_ROOT.'/system/modules/inxmail/inxmail_api/Apiimpl/TConvert.php';
require_once TL_ROOT.'/system/modules/inxmail/inxmail_api/Api/Recipient/DuplicateKeyException.php';
require_once TL_ROOT.'/system/modules/inxmail/inxmail_api/Apiimpl/Recipient/RecipientRowSetImpl.php';
require_once TL_ROOT.'/system/modules/inxmail/inxmail_api/Apiimpl/Recipient/ContextAttribute.php';
require_once TL_ROOT.'/system/modules/inxmail/inxmail_api/Apiimpl/Recipient/ContextAttribute/Id.php';
require_once TL_ROOT.'/system/modules/inxmail/inxmail_api/Apiimpl/Recipient/ContextAttribute/String.php';
require_once TL_ROOT.'/system/modules/inxmail/inxmail_api/Apiimpl/Recipient/ContextAttribute/Datetime.php';
require_once TL_ROOT.'/system/modules/inxmail/inxmail_api/Apiimpl/Recipient/ContextAttribute/Integer.php';
require_once TL_ROOT.'/system/modules/inxmail/inxmail_api/Apiimpl/Recipient/ContextAttribute/LastModification.php';
require_once TL_ROOT.'/system/modules/inxmail/inxmail_api/Apiimpl/Recipient/ContextAttribute/Hardbounce.php';
require_once TL_ROOT.'/system/modules/inxmail/inxmail_api/Apiimpl/Recipient/RecipientContextImpl/AttributeIterator.php';
require_once TL_ROOT.'/system/modules/inxmail/inxmail_api/Apiimpl/Recipient/RecipientContextImpl.php';
require_once TL_ROOT.'/system/modules/inxmail/inxmail_api/Api/Recipient/AttributeManager.php';
require_once TL_ROOT.'/system/modules/inxmail/inxmail_api/Apiimpl/Recipient/AttributeManagerImpl.php';
require_once TL_ROOT.'/system/modules/inxmail/inxmail_api/Apiimpl/AbstractSession.php';
require_once TL_ROOT.'/system/modules/inxmail/inxmail_api/Api/RemoteException.php';
require_once TL_ROOT.'/system/modules/inxmail/inxmail_api/Apiimpl/SoapClient.php';
require_once TL_ROOT.'/system/modules/inxmail/inxmail_api/Apiimpl/SoapSession.php';
require_once TL_ROOT.'/system/modules/inxmail/inxmail_api/Api/Subscription/SubscriptionManager.php';
require_once TL_ROOT.'/system/modules/inxmail/inxmail_api/Apiimpl/Core/SubscriptionManagerImpl.php';
require_once TL_ROOT.'/system/modules/inxmail/inxmail_api/Api/APIException.php';


class ModuleInxmail extends Frontend {


	/**
	 * Handles data submitted by forms
	 */	
	public function processFormData( $arrPost=NULL, $arrForm=NULL, $arrFiles=NULL ) {

		if( empty($arrForm['inxServer']) || empty($arrForm['inxUser']) || empty($arrForm['inxPass']) || empty($arrForm['inxMailField']) || empty($arrForm['inxListName']) )
			return false;

		$additionalFields = array();
		
		// get additional fields
		if( !empty($arrForm['inxAdditionalFields']) ) {
		
			$additionalFields = array();
			$inxAdditionalFields = unserialize($arrForm['inxAdditionalFields']);

			foreach( $inxAdditionalFields as $i => $fields ) {
			
				$additionalFields[ $fields['inxName'] ] = $arrPost[ $fields['formName'] ];
			}
		}
		
		return $this->addRecipient( $arrForm['inxServer'], $arrForm['inxUser'], $arrForm['inxPass'], $arrForm['inxListName'], $arrPost[ $arrForm['inxMailField'] ], $additionalFields );
	}
	
	
	/**
	 * Handles newsletter subscriptions
	 */
	public function processNewsletterSubscription( $sEmail=NULL, $arrRecipients=NULL, $arrChannels=NULL ) {

		foreach( $arrChannels as $channelId ) {
		
			$objChannel = NULL;
			$objChannel = $this->Database->prepare("SELECT inxServer, inxUser, inxPass, inxListName FROM tl_newsletter_channel WHERE id=?")->limit(1)->execute( $channelId );
		
			if( !$objChannel->inxServer || !$objChannel->inxUser || !$objChannel->inxPass || !$objChannel->inxListName )
				continue;

			$this->addRecipient( $objChannel->inxServer, $objChannel->inxUser, $objChannel->inxPass, $objChannel->inxListName, $sEmail );
		}
		
		return;
	}
	
	
	/**
	 * Returns a list of all available mailing lists on the given account
	 */
	public static function getAvailableLists( $server, $user, $pass ) {
	
		$aLists = array();
	
		$session = Inx_Api_Session::createRemoteSession( $server, $user, $pass );
		$listContextManager = $session->getListContextManager();
		$oBOResultSet = $listContextManager->selectAll();

		for( $i=0; $i < $oBOResultSet->size(); ++$i ) {
			$l = $oBOResultSet->get($i);
			$aLists[ $l->getId() ] = $l->getName();
		}

		$oBOResultSet->close();

		return $aLists;
	}
	
	
	/**
	 * Returns a list of all fields available in the given mailing list
	 */
	public static function getAvailableFields( $sServer, $sUser, $sPass, $iMailListID ) {
	
		$aFields = array();
	
		// create session
		$session = NULL;
		$session = Inx_Api_Session::createRemoteSession( $sServer, $sUser, $sPass  );

		// get mailing lists
		$listContextManager = $session->getListContextManager();
		$oBOResultSet = $listContextManager->selectAll();

		$selectedList = NULL;
		
		for( $i=0; $i < $oBOResultSet->size(); ++$i ) {
			$l = $oBOResultSet->get($i);
			
			if( $l->getId() == $iMailListID )
				$selectedList = $l;
		}
		
		if( $selectedList ) {
		
			// get list attributes
			$rm = $session->createRecipientContext();
			$rmd = $rm->getMetaData();
			$rrs = $rm->select( null, null, null, $rmd->getEmailAttribute(), Inx_Api_Order::ASC );
			$ait = $rmd->getAttributeIterator();
			
			while( $ait->valid() ) {
			
				$name = $ait->current()->getName();
				
				if( !empty($name) && $name != 'email' ) {
					$aFields[$name] = $name;
				}

				$ait->next();
			}
		}

		return $aFields;
	}	


	/**
	 * Adds a single recipient on Inxmail list
	 */
	public function addRecipient( $sServer, $sUser, $sPass, $iMailListID, $sEmail, $aAdditionalFields ) {

		// create session
		$session = NULL;
		$session = Inx_Api_Session::createRemoteSession( $sServer, $sUser, $sPass  );

		// get mailing lists
		$listContextManager = $session->getListContextManager();
		$oBOResultSet = $listContextManager->selectAll();

		$selectedList = NULL;

		// find correct list
		for( $i=0; $i < $oBOResultSet->size(); ++$i ) {
			$l = $oBOResultSet->get($i);

			if( $l->getId() == $iMailListID )
				$selectedList = $l;
		}

		if( $selectedList ) {
		
			$oSubscriptionManager = $session->getSubscriptionManager();
			
			$result = $oSubscriptionManager->processSubscription(
				"Contao Inxmail Module"
			,	$_SERVER['REMOTE_ADDR']
			,	$selectedList
			,	$sEmail
			,	$aAdditionalFields
			);
			
			$session->close();
			
			if( $result == Inx_Api_Subscription_SubscriptionManager::PROCESS_ACTIVATION_SUCCESSFULLY ) {
			
				$this->log('Added recipient "'.$sEmail. '" to list "'.$selectedList->getName().'"', 'ModuleInxmail addRecipient()', TL_LOG);
				return true;
			
			} else {
			
				$this->log('Error when trying to add recipient "'.$sEmail. '" to list "'.$selectedList->getName(), 'ModuleInxmail addRecipient()', TL_ERROR);
				return false;
			}
		}
	}
}

?>