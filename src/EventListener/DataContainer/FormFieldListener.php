<?php

/**
 * Inxmail Bundle for Contao Open Source CMS
 *
 * @author    Benny Born <benny.born@numero2.de>
 * @author    Michael Bösherz <michael.boesherz@numero2.de>
 * @license   LGPL-3.0-or-later
 * @copyright Copyright (c) 2024, numero2 - Agentur für digitales Marketing GbR
 */


namespace numero2\InxmailBundle\EventListener\DataContainer;

use Contao\CoreBundle\DataContainer\PaletteManipulator;
use Contao\CoreBundle\DependencyInjection\Attribute\AsCallback;
use Contao\CoreBundle\Routing\ScopeMatcher;
use Contao\DataContainer;
use Contao\FormFieldModel;
use Contao\FormModel;
use Doctrine\DBAL\Connection;
use numero2\InxmailBundle\API\InxmailApi;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Contracts\Translation\TranslatorInterface;


class FormFieldListener {


    /**
     * @var Symfony\Component\HttpFoundation\RequestStack
     */
    private RequestStack $requestStack;

    /**
     * @var Contao\CoreBundle\Routing\ScopeMatcher
     */
    private ScopeMatcher $scopeMatcher;

    /**
     * @var Doctrine\DBAL\Connection
     */
    private Connection $connection;

    /**
     * @var numero2\InxmailBundle\API\InxmailApi
     */
    private InxmailApi $inxmailApi;

    /**
     * @var Symfony\Contracts\Translation\TranslatorInterface
     */
    private TranslatorInterface $translator;


    public function __construct( RequestStack $requestStack, ScopeMatcher $scopeMatcher, Connection $connection, InxmailApi $inxmailApi, TranslatorInterface $translator ) {

        $this->requestStack = $requestStack;
        $this->scopeMatcher = $scopeMatcher;
        $this->connection = $connection;
        $this->inxmailApi = $inxmailApi;
        $this->translator = $translator;
    }


    /**
     * Adjust the palette based on sendToInxmail flag in form
     *
     * @param Contao\DataContainer $dc
     */
    #[AsCallback(table: 'tl_form_field', target: 'config.onload')]
    public function adjustPalette( DataContainer $dc ): void {

        $request = $this->requestStack->getCurrentRequest();
        if( !$request || !$this->scopeMatcher->isBackendRequest($request) ) {
            return;
        }

        $act = $this->requestStack->getCurrentRequest()->query->get('act');

        $isEditInxmailForm = false;

        if( $act === 'edit' ) {

            $tForm = FormModel::getTable();
            $tField = FormFieldModel::getTable();

            $form = $this->connection->executeQuery(
                "SELECT form.*
                FROM $tForm AS form
                JOIN $tField AS field ON (field.pid=form.id)
                WHERE field.id=:id LIMIT 1"
            ,   ['id'=>$dc->id]
            )->fetchAssociative();

            if( $form && !empty($form['sendToInxmail']) ) {
                $isEditInxmailForm = true;
            }

        } else if( $act === 'editAll' ) {

            $isEditInxmailForm = true;
        }

        if( $isEditInxmailForm ) {

            $supportedType = $GLOBALS['TL_DCA']['tl_form_field']['fields']['inxmail_field_name']['eval']['supportedType'] ?? [];

            foreach( $GLOBALS['TL_DCA']['tl_form_field']['palettes'] as $type => $palette ) {

                if( !in_array($type, $supportedType) ) {
                    continue;
                }

                PaletteManipulator::create()
                    ->addField(['inxmail_field_name'], 'type_legend', PaletteManipulator::POSITION_APPEND)
                    ->applyToPalette($type, 'tl_form_field')
                ;
            }
        }
    }


    /**
     * Get all configured attrbiutes at Inxmail based on settings in the form
     *
     * @param Contao\DataContainer $dc
     *
     * @return array
     */
    #[AsCallback(table: 'tl_form_field', target: 'fields.inxmail_field_name.options')]
    public function getFieldsFromInxmail( DataContainer $dc ): array {

        $stdKey = $this->translator->trans('INXMAIL.group_label.standard_contact_field', [], 'contao_default');
        $customKey = $this->translator->trans('INXMAIL.group_label.custom_contact_field', [], 'contao_default');


        $fields = [
            $stdKey => InxmailApi::STANDARD_CONTACT_FIELDS
        ,   $customKey => []
        ];

        $tForm = FormModel::getTable();
        $tField = FormFieldModel::getTable();

        $form = $this->connection->executeQuery(
            "SELECT form.*
            FROM $tForm AS form
            JOIN $tField AS field ON (field.pid=form.id)
            WHERE field.id=:id LIMIT 1"
        ,   ['id'=>$dc->id]
        )->fetchAssociative();

        if( $form && !empty($form['sendToInxmail'])
            && !empty($form['inxmail_customer']) && !empty($form['inxmail_client_id']) && !empty($form['inxmail_secret'])
            ) {

            $this->inxmailApi->setApiCredentials($form['inxmail_customer'], $form['inxmail_client_id'], $form['inxmail_secret']);
            $attributes = $this->inxmailApi->getRecipientAttributes();

            foreach( $attributes as $attribute ) {

                $name = $attribute['name'];
                $fields[$customKey][$name] = $name;

                if( !empty($attribute['maxLength']) ) {
                    $fields[$customKey][$name] .= ' '. $this->translator->trans('INXMAIL.attribute_label.max_length', [$attribute['maxLength']], 'contao_default');
                }
            }
        }

        return $fields;
    }
}
