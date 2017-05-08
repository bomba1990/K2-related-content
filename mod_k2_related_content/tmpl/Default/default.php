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
JHTML::_('behavior.tooltip');

$db = & JFactory::getDBO();		
$version = new JVersion;
$joomla = $version->getShortVersion();
$document =& JFactory::getDocument();
$document->addStyleSheet(JURI::root() . 'modules/mod_k2_related_content/assets/css/K2RelatedContent.css');
?>
<style>
<?php
if(substr($joomla,0,3) == '1.5'){
?>
div.tool-tip{
<?php }else{ ?>  
.tip{
<?php } ?>
  background-color: #000;
  opacity : 0.75;
  -moz-opacity : 0.75;
  -ms-filter: "alpha(opacity=75)"; /* IE 8 */
  filter : alpha(opacity=75); /* IE < 8 */ 
  color:#fff;
  padding:7px;
  -webkit-border-radius: 5px;
  -moz-border-radius: 5px;
  border-radius: 5px;
  box-shadow:#555 2px 2px 5px;
  max-width:300px;
}
</style> 

<div id="k2ModuleRelated" class="k2ItemsRelatedList<?php echo $params->get('pOrientation').$params->get('moduleclass_sfx'); ?>">          
  <?php if(count($items)): ?>
  <ul>
    <?php foreach ($items as $key=>$item):	?>
    <li class="<?php echo ($key%2) ? "odd" : "even"; if(count($items)==$key+1) echo ' lastItem'; ?>">
    
        <?php if($params->get('pTitle', 1)): ?>
          <h3><a class="k2ItemTitle" href="<?php echo $item->link; ?>">
          <?php 
            if($params->get('pTooltip')){ 
              if(($params->get('pTooltipLimit')) && (strlen($item->fulltext) > $params->get('pTooltipLimit'))){
                $toolText = substr($item->fulltext, 0, $params->get('pTooltipLimit')); 
              }else{
                $toolText = $item->fulltext; 
              } 
              echo JHTML::tooltip(strip_tags($toolText), $item->title, 
  	            '', $item->title);
              ?>
              
            <?php
            }else{
              echo $item->title; 
            }         
          ?>
          </a></h3>
        <?php endif; ?>
        
      <?php if($params->get('pItemImage') && $item->image != '' ): ?>
      <div class="k2ItemImage<?php echo $params->get('pItemImagePosition'); ?>"> 
        <?php if($params->get('pItemImageCaption') && $item->imgcaption !=''): ?>
        <div class="k2ItemImgCaption">
          <h4>
          <?php echo $item->imgcaption; ?>
          </h4>
        </div>
        <?php endif; ?> 
        <a href="<?php echo $item->link; ?>" class="k2itemImageLink" title="<?php echo K2HelperUtilities::cleanHtml($item->title); ?>">
          <img class="k2ItemImage" src="<?php echo $item->image; ?>" <?php echo $item->imgsize; ?> alt="<?php echo K2HelperUtilities::cleanHtml($item->title); ?>" />
        </a>
      </div>
      <?php endif; ?> 
      
      <?php if($params->get('pItemCategory')): ?>
        <div class="k2ItemsCategoryLink">
          <?php echo JText::_('K2_IN') ; ?> <a href="<?php echo $item->categoryLink; ?>"><?php echo $item->categoryname; ?></a>
        </div>
      <?php endif; ?> 
      
      <?php if($params->get('pItemDateCreated')): ?>
        <span class="k2ItemsCreated"><?php echo JHTML::_('date', $item->created, JText::_('DATE_FORMAT_LC1')); ?></span>
      <?php endif; ?>
      
      <?php if($params->get('pIntro')): ?>
      <div class="k2ItemsRelIntro">
        <?php if(($params->get('pIntroLimit')) && (strlen($item->introtext) > $params->get('pIntroLimit'))){ ?>
      	 <?php echo strip_tags(substr($item->introtext, 0, $params->get('pIntroLimit')))." ..."; ?>
        <?php }else{ ?>
          <?php echo $item->introtext; ?>
        <?php } ?> 
      </div>
      <?php endif; ?> 
      
      <?php if($params->get('pFullText')): ?>
      <div class="k2ItemsRelFulltext">
        <?php if(($params->get('pFullTextLimit')) && (strlen($item->fulltext) > $params->get('pFullTextLimit'))){ ?>
      	 <?php echo strip_tags(substr($item->fulltext, 0, $params->get('pFullTextLimit')))." ..."; ?>
        <?php }else{ ?>
          <?php echo $item->fulltext; ?>
        <?php } ?> 
      </div>
      <?php endif; ?> 
      
      <?php if($params->get('pExtrafields')): ?>
      <div class="k2ItemsRelExtrafields">
        <ul>
  			<?php foreach ($item->extra_fields as $key=>$extraField): ?>
  			<?php if($extraField->value): ?>
    			<li class="<?php echo ($key%2) ? "odd" : "even"; ?> type<?php echo ucfirst($extraField->type); ?> group<?php echo $extraField->group; ?>">
    				<span class="itemExtraFieldsLabel"><?php echo $extraField->name; ?>:</span>
    				<span class="itemExtraFieldsValue"><?php echo $extraField->value; ?></span>
    			</li>
  			<?php endif; ?>
  			<?php endforeach; ?>
  			</ul> 
      </div>
      <?php endif; ?>     
      
      <?php if($pReadmore = $params->get('pReadmore')): ?>  
      <div class="k2RelReadmore">
        <a  href="<?php echo $item->link; ?>"><?php echo JText::_('K2_READMORE'); ?></a> 
      </div>
      <?php endif; ?>   
             
    </li>
    <?php endforeach; ?>
  </ul>
  <?php endif; ?>
  
</div>