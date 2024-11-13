<?php

/**
 * Inxmail Bundle for Contao Open Source CMS
 *
 * @author    Benny Born <benny.born@numero2.de>
 * @author    Michael Bösherz <michael.boesherz@numero2.de>
 * @license   LGPL-3.0-or-later
 * @copyright Copyright (c) 2024, numero2 - Agentur für digitales Marketing GbR
 */


use Contao\CoreBundle\DataContainer\PaletteManipulator;


/**
 * Add palettes to tl_form
 */
PaletteManipulator::create()
    ->addLegend('inxmail_legend', 'store_legend', PaletteManipulator::POSITION_BEFORE)
    ->addField(['sendToInxmail'], 'inxmail_legend', PaletteManipulator::POSITION_APPEND)
    ->applyToPalette('default', 'tl_form')
;

$GLOBALS['TL_DCA']['tl_form']['palettes']['__selector__'][] = 'sendToInxmail';

$GLOBALS['TL_DCA']['tl_form']['subpalettes']['sendToInxmail'] = 'inxmail_customer,inxmail_client_id,inxmail_secret,inxmail_list_id,inxmail_src';


/**
 * Add fields to tl_form
 */
$GLOBALS['TL_DCA']['tl_form']['fields']['sendToInxmail'] = [
    'exclude'           => true
,   'inputType'         => 'checkbox'
,   'filter'            => true
,   'eval'              => ['submitOnChange'=>true, 'tl_class'=>'clr']
,   'sql'               => ['type'=>'boolean', 'default'=>false]
];

$GLOBALS['TL_DCA']['tl_form']['fields']['inxmail_customer'] = [
    'exclude'           => true
,   'inputType'         => 'text'
,   'eval'              => ['mandatory'=>true, 'tl_class'=>'w50']
,   'sql'               => "varchar(64) NOT NULL default ''"
];

$GLOBALS['TL_DCA']['tl_form']['fields']['inxmail_client_id'] = [
    'exclude'           => true
,   'inputType'         => 'text'
,   'eval'              => ['mandatory'=>true, 'tl_class'=>'w50 clr']
,   'sql'               => "varchar(64) NOT NULL default ''"
];

$GLOBALS['TL_DCA']['tl_form']['fields']['inxmail_secret'] = [
    'exclude'           => true
,   'inputType'         => 'text'
,   'eval'              => ['mandatory'=>true, 'tl_class'=>'w50']
,   'sql'               => "varchar(128) NOT NULL default ''"
];

$GLOBALS['TL_DCA']['tl_form']['fields']['inxmail_list_id'] = [
    'exclude'           => true
,   'inputType'         => 'text'
,   'eval'              => ['mandatory'=>true, 'tl_class'=>'w50']
,   'sql'               => "varchar(64) NOT NULL default ''"
];

$GLOBALS['TL_DCA']['tl_form']['fields']['inxmail_src'] = [
    'exclude'           => true
,   'inputType'         => 'text'
,   'eval'              => ['tl_class'=>'w50']
,   'sql'               => "varchar(128) NOT NULL default ''"
];
