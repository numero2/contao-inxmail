<?php

/**
 * Inxmail Bundle for Contao Open Source CMS
 *
 * @author    Benny Born <benny.born@numero2.de>
 * @author    Michael Bösherz <michael.boesherz@numero2.de>
 * @license   LGPL-3.0-or-later
 * @copyright Copyright (c) 2024, numero2 - Agentur für digitales Marketing GbR
 */


namespace numero2\InxmailBundle\EventListener\Hook;

use Contao\CoreBundle\DependencyInjection\Attribute\AsHook;
use Contao\CoreBundle\InsertTag\InsertTagParser;
use Contao\Date;
use Contao\Form;
use Contao\FormFieldModel;
use Doctrine\DBAL\Connection;
use Exception;
use numero2\InxmailBundle\API\InxmailApi;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\RequestStack;


class FormListener {


    /**
     * @var Symfony\Component\HttpFoundation\RequestStack
     */
    private RequestStack $requestStack;

    /**
     * @var Doctrine\DBAL\Connection
     */
    private Connection $connection;

    /**
     * @var Contao\CoreBundle\InsertTag\InsertTagParser
     */
    private InsertTagParser $insertTagParser;

    /**
     * @var numero2\InxmailBundle\API\InxmailApi
     */
    private InxmailApi $inxmailApi;

    /**
     * @var Psr\Log\LoggerInterface
     */
    protected LoggerInterface $logger;


    public function __construct( RequestStack $requestStack, Connection $connection, InsertTagParser $insertTagParser, InxmailApi $inxmailApi, LoggerInterface $logger ) {

        $this->requestStack = $requestStack;
        $this->connection = $connection;
        $this->insertTagParser = $insertTagParser;
        $this->inxmailApi = $inxmailApi;
        $this->logger = $logger;
    }


    /**
     * Process form data and create an contact at inxmail
     *
     * @param array $submittedData
     * @param array $formData
     * @param array $files
     * @param array $labels
     * @param Contao\Form $form
     */
    #[AsHook('processFormData')]
    public function createEventSubscriptionAtInxmail( array $submittedData, array $formData, ?array $files, array $labels, Form $form ): void {

        if( empty($form->sendToInxmail)
            || empty($form->inxmail_customer) || empty($form->inxmail_client_id) || empty($form->inxmail_secret)
            || empty($form->inxmail_list_id)
            ) {
            return;
        }

        // match fields to inxmail
        $inxmailData = [];
        $inxmailSettings = [];

        $t = FormFieldModel::getTable();

        $formFields = $this->connection->executeQuery(
            "SELECT * FROM $t AS field WHERE pid=:pid ORDER BY sorting ASC"
        ,   ['pid'=>$form->id]
        )->fetchAllAssociative();

        if( $formFields ) {
            foreach( $formFields as $field ) {
                if( empty($submittedData[$field['name']]) ) {
                    continue;
                }
                if( empty($field['inxmail_field_name']) ) {
                    continue;
                }

                $value = $submittedData[$field['name']];

                // convert date times to tstamp
                if( !empty($field['rgxp']) ) {
                    $date = null;

                    try {
                        if( $field['rgxp'] === 'date' ) {
                            $date = new Date($value, Date::getNumericDateFormat());
                        } else if( $field['rgxp'] === 'time' ) {
                            $date = new Date($value, Date::getNumericTimeFormat());
                        } else if( $field['rgxp'] === 'datim' ) {
                            $date = new Date($value, Date::getNumericDatimFormat());
                        }
                    } catch( Exception $e ) {
                        $date = null;
                    }

                    if( $date ) {
                        $value = $date->timestamp;
                    }
                }

                $inxmailData[$field['inxmail_field_name']] = $value;
            }
        }

        $inxmailSettings['listId'] = $form->inxmail_list_id;
        $inxmailSettings['suppliedRemoteAddress'] = $this->requestStack->getMasterRequest()->getClientIp();

        if( !empty($form->inxmail_src) ) {
            $inxmailSettings['source'] = $this->insertTagParser->replaceInline($form->inxmail_src);
        }

        $this->inxmailApi->setApiCredentials($form->inxmail_customer, $form->inxmail_client_id, $form->inxmail_secret);

        $result = $this->inxmailApi->createEventSubscription($inxmailData, $inxmailSettings);

        if( !etmpy($result) ) {
            $this->logger->info('Subscribed recipient at Inxmail.');
        } else {
            $this->logger->info('Subscribed recipient at Inxmail maybe failed. Please check for other errors!');
        }
    }
}
