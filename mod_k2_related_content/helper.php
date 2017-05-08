<?php
/*------------------------------------------------------------------------
# mod_k2_related_content - K2 Related Content
# ------------------------------------------------------------------------
# Effitech
# Copyright (C) 2012 effitech.fr All Rights Reserved.
# http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
# Website: http://www.effitech.fr
-------------------------------------------------------------------------*/
defined( '_JEXEC' ) or die( 'Restricted access' );

require_once(JPATH_SITE.DS.'components'.DS.'com_k2'.DS.'helpers'.DS.'route.php');
require_once(JPATH_SITE.DS.'components'.DS.'com_k2'.DS.'helpers'.DS.'utilities.php');

class modK2RelatedContentHelper {

    function getRelatedContent(&$params){

        $pTitle = $params->get('pTitle', 1);
        $pItemsNumber = $params->get('pItemsNumber', 0);
        $pIntro = $params->get('pIntro', 1);
        $pIntroLimit = $params->get('pIntroLimit', 0);
        $pFullText = $params->get('pFullText', 1);
        $pTooltip = $params->get('pTooltip', 0);
        $pRelatedBy = $params->get('pRelatedBy', 0);
        $category_id = $params->get('category_id', NULL);
        $pTags = $params->get('pTags', '');
        $pItemImage = $params->get('pItemImage', 0);
        $pItemImgSize = $params->get('pItemImgSize', 'Small');
        $pItemImageCaption = $params->get('pItemImageCaption', 0);
        $pItemImageCustomSize = $params->get('pItemImageCustomSize', 0);
        $pItemImageWidth = $params->get('pItemImageWidth', 0);
        $pItemImageHeight = $params->get('pItemImageHeight', 0);
        $pItemImagePosition = $params->get('pItemImagePosition', 0);
        $pItemCategory = $params->get('pItemCategory', 0);
        $pItemDateCreated = $params->get('pItemDateCreated', 0);
        $pItemSort = $params->get('pItemSort', 0);
        $pItemSortDirection = $params->get('pItemSortDirection', 0);
        $pExtrafields = $params->get('pExtrafields', 0);


        $componentParams = &JComponentHelper::getParams('com_k2');
        $Id = JRequest::getInt('id');
        $view = JRequest::getVar('view');

        if($view == 'item' || $view == 'article'){
            $db = &JFactory::getDBO();
            $queryItem = "SELECT catid, created_by FROM #__k2_items WHERE id = ".$Id;
            $db->setQuery( $queryItem );
            $rowItem = $db->loadObjectList();

            $join = "";
            $where = "";
            switch ($pRelatedBy) {
                case 0 :
                    $where = " AND i.catid = ".$rowItem[0]->catid;
                    break;
                case 1 :
                    $join = "\n INNER JOIN #__k2_tags_xref tr ON tr.itemID = i.id"
                        ."\n AND tr.tagID IN (SELECT tagID FROM #__k2_tags_xref WHERE itemID = '".$Id."')";
                    break;
                case 2 :
                    $join = "\n INNER JOIN #__k2_tags_xref tr ON tr.itemID = i.id";
                    $where = " AND i.catid = ".$rowItem[0]->catid." AND tr.tagID IN (SELECT tagID FROM #__k2_tags_xref WHERE itemID = '".$Id."')";
                    break;
                case 3 :
                    $join = "\n LEFT JOIN #__k2_tags_xref tr ON tr.itemID = i.id";
                    $where = " AND (i.catid = ".$rowItem[0]->catid." OR tr.tagID IN (SELECT tagID FROM #__k2_tags_xref WHERE itemID = '".$Id."'))";
                    break;
                case 4 :
                    $where = " AND i.created_by = ".$rowItem[0]->created_by;
                    break;
                default:
                    break;
            }


            if($category_id != ''){
                JArrayHelper::toInteger($category_id);
                $where .= " AND i.catid IN(".implode(',', $category_id).")";
            }

            if($pTags != ''){
                if ($pRelatedBy == 0){
                    $join .= "\n INNER JOIN #__k2_tags_xref tr ON tr.itemID = i.id";
                }
                $tagsquery = modK2RelatedContentHelper::splitTagToQuery($pTags);
                $join .= " INNER JOIN #__k2_tags AS ta ON ta.id = tr.tagID AND ta.name IN(".$tagsquery.")";
            }


            $fields = "";
            if ($pItemDateCreated || $pItemSort == 1){ $fields .= " , i.created"; }
            if ($pItemCategory){ $fields .= " , c.name AS categoryname"; }
            if ($pItemImageCaption){ $fields .= " , i.image_caption AS imgcaption "; }
            if ($pExtrafields){ $fields .= ", i.extra_fields "; }

            $query = "SELECT i.id, i.catid, i.alias, i.modified, i.publish_down, c.alias AS categoryalias".$fields;

            if ($pTitle){
                $query .= ", i.title";
            }
            if ($pIntro){
                $query .= ", i.introtext";
            }
            //if ($pFullText){
            $query .= ", i.fulltext";
            //}

            $order = "";
            switch($pItemSort){
                case 1:
                    $order = "\n ORDER BY i.created ";
                    break;
                case 2:
                    $order = "\n ORDER BY i.modified";
                    break;
                case 3:
                    $order = "\n ORDER BY i.id";
                    break;
                case 4:
                    $fields .= " , i.hits";
                    $order = "\n ORDER BY i.hits";
                    break;
                default:
                    break;
            }
            if ($pItemSort){
                $order .= $pItemSortDirection? " DESC " : " ASC";
            }

            $query .= "\n FROM #__k2_items AS i"
                ."\n INNER JOIN #__k2_categories c ON c.id = i.catid"
                .$join
                ."\n WHERE i.published = 1 AND i.trash = 0 AND c.published = 1"
                ."\n AND (i.publish_down > CURDATE() OR i.publish_down = '0000-00-00 00:00:00')"
                ."\n AND i.id <> ".$Id." ".$where
                ."\n GROUP BY i.id"
                .$order;

            if ($pItemsNumber){
                $query .= "\n LIMIT 0, ".$pItemsNumber;
            }

            $db->setQuery( $query );
            $items = $db->loadObjectList();

            if (count($items)) {
                foreach ($items as $item) {
                    $item->link = urldecode(JRoute::_(K2HelperRoute::getItemRoute($item->id.':'.urlencode($item->alias), $item->catid.':'.urlencode($item->categoryalias))));

                    //Images
                    if ($pItemImage) {

                        $date =& JFactory::getDate($item->modified);
                        $timestamp = '?t='.$date->toUnix();

                        if (JFile::exists(JPATH_SITE.DS.'media'.DS.'k2'.DS.'items'.DS.'cache'.DS.md5("Image".$item->id).'_XS.jpg')){
                            $item->imageXSmall = JURI::base(true).'/media/k2/items/cache/'.md5("Image".$item->id).'_XS.jpg';
                            if($componentParams->get('imageTimestamp')){
                                $item->imageXSmall.=$timestamp;
                            }
                        }

                        if (JFile::exists(JPATH_SITE.DS.'media'.DS.'k2'.DS.'items'.DS.'cache'.DS.md5("Image".$item->id).'_S.jpg')){
                            $item->imageSmall = JURI::base(true).'/media/k2/items/cache/'.md5("Image".$item->id).'_S.jpg';
                            if($componentParams->get('imageTimestamp')){
                                $item->imageSmall.=$timestamp;
                            }
                        }

                        if (JFile::exists(JPATH_SITE.DS.'media'.DS.'k2'.DS.'items'.DS.'cache'.DS.md5("Image".$item->id).'_M.jpg')){
                            $item->imageMedium = JURI::base(true).'/media/k2/items/cache/'.md5("Image".$item->id).'_M.jpg';
                            if($componentParams->get('imageTimestamp')){
                                $item->imageMedium.=$timestamp;
                            }
                        }

                        if (JFile::exists(JPATH_SITE.DS.'media'.DS.'k2'.DS.'items'.DS.'cache'.DS.md5("Image".$item->id).'_L.jpg')){
                            $item->imageLarge = JURI::base(true).'/media/k2/items/cache/'.md5("Image".$item->id).'_L.jpg';
                            if($componentParams->get('imageTimestamp')){
                                $item->imageLarge.=$timestamp;
                            }
                        }

                        if (JFile::exists(JPATH_SITE.DS.'media'.DS.'k2'.DS.'items'.DS.'cache'.DS.md5("Image".$item->id).'_XL.jpg')){
                            $item->imageXLarge = JURI::base(true).'/media/k2/items/cache/'.md5("Image".$item->id).'_XL.jpg';
                            if($componentParams->get('imageTimestamp')){
                                $item->imageXLarge.=$timestamp;
                            }
                        }

                        if (JFile::exists(JPATH_SITE.DS.'media'.DS.'k2'.DS.'items'.DS.'cache'.DS.md5("Image".$item->id).'_Generic.jpg')){
                            $item->imageGeneric = JURI::base(true).'/media/k2/items/cache/'.md5("Image".$item->id).'_Generic.jpg';
                            if($componentParams->get('imageTimestamp')){
                                $item->imageGeneric.=$timestamp;
                            }
                        }

                        $image = 'image'.$pItemImgSize;
                        if (isset($item->$image)){
                            $item->image = $item->$image;
                        }

                        $item->imgsize = "";
                        if($pItemImageCustomSize != 0){
                            switch($pItemImageCustomSize){
                                case 1:
                                    $item->imgsize = " style=\"width:".$pItemImageWidth."px;\" ";
                                    break;
                                case 2:
                                    $item->imgsize = " style=\"height:".$pItemImageHeight."px;\" ";
                                    break;
                                case 3:
                                    $item->imgsize = " style=\"width:".$pItemImageWidth."px;height:".$pItemImageHeight."px;\" ";
                                    break;
                            }
                        }

                        switch($pItemImagePosition){
                            case "left":
                                $item->textpos = "right";
                                break;
                            case "right":
                                $item->textpos = "left";
                                break;
                            case "top":
                                $item->textpos = "bottom";
                                break;
                            case "bottom":
                                $item->textpos = "top";
                                break;
                        }

                        if ($params->get('pItemCategory'))
                            $item->categoryLink = urldecode(JRoute::_(K2HelperRoute::getCategoryRoute($item->catid.':'.urlencode($item->categoryalias))));
                    }

                    $item->extra_fields = K2ModelItem::getItemExtraFields($item->extra_fields);

                    $rows[] = $item;
                }
            }

            return $rows;
        }
    }

    function splitTagToQuery($tags){
        $tab = explode(',', $tags);
        $tagQuery = '';
        for($i = 0; $i < sizeof($tab); $i++){
            $tagQuery.= "'".$tab[$i]."',";
        }
        return substr($tagQuery, 0, strlen($tagQuery)-1);
    }
}
?>
