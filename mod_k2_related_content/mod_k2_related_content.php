<?php
/*------------------------------------------------------------------------
# mod_k2_related_content - K2 Related Content
# ------------------------------------------------------------------------
# Effitech
# Copyright (C) 2012 effitech.fr All Rights Reserved.
# http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
# Website: http://www.effitech.fr
-------------------------------------------------------------------------*/
// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' ); 
defined('_JEXEC') or die('Restricted access');
require_once (dirname(__FILE__).DS.'helper.php');

//retrieve module parameter
$moduleclass_sfx = $params->get('moduleclass_sfx','');
$getTemplate = $params->get('getTemplate','Default');
$pTitle = $params->get('pTitle', 0);
$pIntro = $params->get('pIntro', 0);
$pIntroLimit = $params->get('pIntroLimit', 0);
$pFullText = $params->get('pFullText', 0);
$pFullTextLimit = $params->get('pFullTextLimit', 0);
$pTooltip = $params->get('pTooltip', 0);


$items = modK2RelatedContentHelper::getRelatedContent($params);
require(JModuleHelper::getLayoutPath('mod_k2_related_content', $getTemplate.DS.'default'));
?>
