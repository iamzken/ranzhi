<?php
/**
 * The side view of common module of RanZhi.
 *
 * @copyright   Copyright 2009-2015 青岛易软天创网络科技有限公司(QingDao Nature Easy Soft Network Technology Co,LTD, www.cnezsoft.com)
 * @license     ZPL (http://zpl.pub/page/zplv12.html)
 * @author      Chunsheng Wang <chunsheng@cnezsoft.com>
 * @package     common 
 * @version     $Id: side.html.php 3138 2015-11-09 07:32:18Z chujilu $
 * @link        http://www.ranzhico.com
 */
$topCategories = $this->loadModel('tree')->getChildren(0);
?>
<div class='col-md-3'>
  <div class='list-group'> 
    <strong class='list-group-item list-group-title'><?php echo $lang->categoryMenu;?></strong>
    <?php
    foreach($topCategories as $topCategory){
        $browseLink = $this->createLink('article', 'browse', "categoryID={$topCategory->id}", "category={$topCategory->alias}");
        if($category->name==$topCategory->name)
        {
            echo html::a($browseLink, "<i class='icon-folder-open-alt '></i>" . $topCategory->name, "id='category{$topCategory->id}' class='list-group-item active'");
        }
        else
        {
            echo html::a($browseLink, "<i class='icon-folder-close-alt '></i>" . $topCategory->name, "id='category{$topCategory->id}' class='list-group-item'");
        }
    }
    ?>
  </div>
  <div id='contact' class="panel panel-default">
    <div class="panel-heading">
      <h4><?php echo $lang->company->contactUs;?></h4>
    </div>
    <div class="panel-body">
      <?php foreach($contact as $item => $value):?>
      <dl>
        <dt><?php echo $this->lang->company->$item . $lang->colon;?></dt>
        <dd><?php echo $value;?></dd>
        <div class='c-both'></div>
      </dl>
      <?php endforeach;?>      
    </div>
  </div>
</div>
