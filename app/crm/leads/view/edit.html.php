<?php 
/**
 * The edit view of contact module of RanZhi.
 *
 * @copyright   Copyright 2009-2015 青岛易软天创网络科技有限公司(QingDao Nature Easy Soft Network Technology Co,LTD, www.cnezsoft.com)
 * @license     ZPL (http://zpl.pub/page/zplv12.html)
 * @author      Tingting Dai <daitingting@xirangit.com>
 * @package     contact 
 * @version     $Id$
 * @link        http://www.ranzhico.com
 */
?>
<?php if(helper::isAjaxRequest()):?>
<?php include '../../../sys/common/view/header.modal.html.php';?>
<?php include '../../common/view/datepicker.html.php';?>
<?php include '../../../sys/common/view/chosen.html.php';?>
<?php else:?>
<?php include '../../common/view/header.html.php';?>
<?php include '../../common/view/datepicker.html.php';?>
<?php include '../../../sys/common/view/chosen.html.php';?>
<ul id='menuTitle'>
  <li><?php commonModel::printLink('leads', 'browse', "mode={$mode}&status={$status}", $lang->contact->list);?></li>
  <li class='divider angle'></li>
  <li><?php commonModel::printLink('leads', 'view', "contactID={$contact->id}&mode={$mode}&status={$status}", $lang->contact->view);?></li>
  <li class='divider angle'></li>
  <li class='title'><?php echo $lang->contact->edit?></li>
</ul>
<?php endif;?>
<form method='post' id='contactForm' class='form-condensed' action="<?php echo helper::createLink('leads', 'edit', "contactID=$contact->id")?>">
  <div class="row-table">
    <div class='col-main'>
      <div class='panel'>
        <div class='panel-body'>
          <table class='table table-form'>
            <tr>
              <th class='w-50px'><?php echo $lang->contact->desc;?></th>
              <td colspan='2'><?php echo html::textarea('desc', $contact->desc, "rows='3' class='form-control'");?></td>
            </tr>
          </table>
        </div>
      </div>
      <?php echo $this->fetch('action', 'history', "objectType=contact&objectID={$contact->id}")?>
      <div class='page-actions'>
        <?php echo html::submitButton() . html::backButton();?>
      </div>
    </div>
    <div class='col-side'>
      <div class='panel'>
        <div class='panel-heading'><strong><?php echo $lang->contact->basicInfo;?></strong></div>
        <div class='panel-body'>
          <table class='table table-info table-form'>
            <tr class='required'>
              <th class='w-70px'><?php echo $lang->contact->realname;?></th>
              <td><?php echo html::input('realname', $contact->realname, "class='form-control'");?></td>
            </tr>
            <tr>
              <th><?php echo $lang->contact->company;?></th>
              <td><?php echo html::input('company', $contact->company, "class='form-control'");?></td>
            </tr>
            <tr>
              <th><?php echo $lang->contact->birthday;?></th>
              <td colspan='2'><?php echo html::input('birthday', $contact->birthday, "class='form-control form-date'");?></td>
            </tr>
            <tr>
              <th><?php echo $lang->contact->gender;?></th>
              <td colspan='2'><?php unset($lang->genderList->u); echo html::radio('gender', $lang->genderList, $contact->gender);?></td>
            </tr>
          </table>
        </div>
      </div>
      <div class='panel'>
        <div class='panel-heading'><strong><?php echo $lang->contact->contactInfo;?></strong></div>
        <div class='panel-body'>
          <table class='table table-info'>
            <?php foreach($config->contact->contactWayList as $item):?>
            <tr>
              <th class='w-70px'><?php echo $lang->contact->{$item};?></th>
              <td>
                <?php
                $itemValue = $contact->$item;
                if($item == 'site' and empty($contact->$item)) $itemValue = 'http://';
                if($item == 'weibo' and empty($contact->$item)) $itemValue = 'http://weibo.com/';
                $placeholder = $item == 'email' ? "placeholder='{$lang->contact->emailTip}'" : '';
                echo html::input($item, $itemValue, "class='form-control' $placeholder");
                ?>
              </td>
            </tr>
            <?php endforeach;?>
          </table>
        </div>
      </div>
    </div>
  </div>
</form>
<?php if(helper::isAjaxRequest()):?>
<?php include '../../../sys/common/view/footer.modal.html.php';?>
<?php else:?>
<?php include '../../common/view/footer.html.php';?>
<?php endif;?>
