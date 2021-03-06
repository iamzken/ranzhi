<?php
/**
 * The html template file of step1 method of install module of RanZhi.
 *
 * @copyright   Copyright 2009-2015 青岛易软天创网络科技有限公司(QingDao Nature Easy Soft Network Technology Co,LTD, www.cnezsoft.com)
 * @license     ZPL (http://zpl.pub/page/zplv12.html)
 * @author      Chunsheng Wang <chunsheng@cnezsoft.com>
 * @package     install 
 * @version     $Id: step1.html.php 3149 2015-11-11 08:23:01Z daitingting $
 * @link        http://www.ranzhico.com
 */
?>
<?php include '../../common/view/header.lite.html.php';?>
<?php
$wholeResult = strpos($phpResult . $pdoResult . $pdoMySQLResult . $tmpRootResult . $dataRootResult . $sessionRootResult, 'fail') !== false ? 'fail' : 'ok';
js::set('wholeResult', $wholeResult);
?>
<div class="container">
  <div class='modal-dialog'>
    <div class='modal-content'>
      <div class='modal-header'><strong><?php echo $lang->install->checking;?></strong></div>
      <div class='modal-body'>
        <table class='table table-bordered table-form'>
          <tr>
            <th class='w-p20'><?php echo $lang->install->checkItem;?></th>
            <th class='w-p20 text-left'><?php echo $lang->install->current?></th>
            <th class='w-100px text-left'><?php echo $lang->install->result?></th>
            <th class='text-center'><?php echo $lang->install->action?></th>
          </tr>
          <tr>
            <th><?php echo $lang->install->phpVersion;?></th>
            <td><?php echo $phpVersion;?></td>
            <td class='<?php echo $phpResult;?>'><?php echo $lang->install->$phpResult;?></td>
            <td class='f-12px'><?php if($phpResult == 'fail') echo $lang->install->phpFail;?></td>
          </tr>
          <tr>
            <th><?php echo $lang->install->pdo;?></th>
            <td><?php $pdoResult == 'ok' ? printf($lang->install->loaded) : printf($lang->install->unloaded);?></td>
            <td class='<?php echo $pdoResult;?>'><?php echo $lang->install->$pdoResult;?></td>
            <td class='f-12px'><?php if($pdoResult == 'fail') echo $lang->install->pdoFail;?></td>
          </tr>
          <tr>
            <th><?php echo $lang->install->pdoMySQL;?></th>
            <td><?php $pdoMySQLResult == 'ok' ? printf($lang->install->loaded) : printf($lang->install->unloaded);?></td>
            <td class='<?php echo $pdoMySQLResult;?>'><?php echo $lang->install->$pdoMySQLResult;?></td>
            <td class='f-12px'><?php if($pdoMySQLResult == 'fail') echo $lang->install->pdoMySQLFail;?></td>
          </tr>
          <tr>
            <th><?php echo $lang->install->tmpRoot;?></th>
            <td>
              <?php
              $tmpRootInfo['exists']   ? print($lang->install->exists)   : print($lang->install->notExists);
              $tmpRootInfo['writable'] ? print($lang->install->writable) : print($lang->install->notWritable);
              ?>
            </td>
            <td class='<?php echo $tmpRootResult;?>'><?php echo $lang->install->$tmpRootResult;?></td>
            <td class='f-12px'>
              <?php 
              if(!$tmpRootInfo['exists'])   printf($lang->install->mkdir, $tmpRootInfo['path'], $tmpRootInfo['path']);
              if(!$tmpRootInfo['writable']) printf($lang->install->chmod, $tmpRootInfo['path'], $tmpRootInfo['path']);
              ?>
            </td>
          </tr>
          <tr>
            <th><?php echo $lang->install->dataRoot;?></th>
            <td>
              <?php
              $dataRootInfo['exists']   ? print($lang->install->exists)   : print($lang->install->notExists);
              $dataRootInfo['writable'] ? print($lang->install->writable) : print($lang->install->notWritable);
              ?>
            </td>
            <td class='<?php echo $dataRootResult;?>'><?php echo $lang->install->$dataRootResult;?></td>
            <td class='f-12px'>
              <?php 
              if(!$dataRootInfo['exists'])   printf($lang->install->mkdir, $dataRootInfo['path'], $dataRootInfo['path']);
              if(!$dataRootInfo['writable']) printf($lang->install->chmod, $dataRootInfo['path'], $dataRootInfo['path']);
              ?>
            </td>
          </tr>
          <tr>
            <th><?php echo $lang->install->sessionRoot;?></th>
            <td><?php $sessionRootResult == 'ok' ? print($lang->install->writable) : print($lang->install->notWritable);?></td>
            <td class='<?php echo $sessionRootResult;?>'><?php echo $lang->install->$sessionRootResult;?></td>
            <td class='f-12px'><?php if($sessionRootResult == 'fail') printf($lang->install->sessionChmod, $sessionRoot, $sessionRoot);?></td>
          </tr>
        </table>
        <?php if($pdoResult == 'fail' or $pdoMySQLResult == 'fail'):?>
        <div class='alert'><?php echo "<p class='small text-left'>" . '<strong>' . $lang->install->phpINI . '</strong><br />' . nl2br($this->install->getIniInfo()) . '</p>';?></div>
        <?php endif;?>
      </div>
      <div class='modal-footer'>
      <?php
      if($wholeResult == 'ok')   echo html::a(inLink('step2'), $lang->install->next,   "class='btn btn-primary'");
      if($wholeResult == 'fail') echo html::a(inLink('step1'), $lang->install->reload, "class='btn btn-primary'");
      ?>
      </div>
    </div>
  </div>
</div>
<?php include './footer.html.php';?>
