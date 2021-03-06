<?php
/**
 * The report view file of trade module of RanZhi.
 *
 * @copyright   Copyright 2009-2015 青岛易软天创网络科技有限公司(QingDao Nature Easy Soft Network Technology Co,LTD, www.cnezsoft.com)
 * @license     ZPL (http://zpl.pub/page/zplv12.html)
 * @author      Tingting Dai <daitingting@xirangit.com>
 * @package     trade
 * @version     $Id$
 * @link        http://www.ranzhico.com
 */
?>
<?php include $app->getModuleRoot() . 'common/view/header.html.php';?>
<?php include '../../../sys/common/view/chart.html.php';?>
<?php js::set('mode', 'report');?>
<div class='panel panel-sm'>
  <div class='panel-heading'>
    <div class='date dropdown'>
      <?php $currentMonthTip = $currentMonth == '00' ? '' : $currentMonth . $lang->month;?>
      <button type='button' class='btn btn-sm btn-default dropdown-toggle' data-toggle='dropdown'><?php echo $currentYear . $lang->year . $currentMonthTip;?> <span class="caret"></span></button>
      <ul class='dropdown-menu'>
        <?php foreach($tradeYears as $tradeYear):?>
        <li class='dropdown-submenu'>
          <?php echo html::a(helper::createLink('trade', 'report', "date=$tradeYear&currency=$currentCurrency"), $tradeYear);?>
          <ul class='dropdown-menu'>
            <?php foreach($tradeMonths[$tradeYear] as $tradeMonth):?>
            <li><?php echo html::a(helper::createLink('trade', 'report', "date=$tradeYear$tradeMonth&currency=$currentCurrency"), $tradeMonth . $lang->month);?></li>
            <?php endforeach;?>
            <li><?php echo html::a(helper::createLink('trade', 'report', "date={$tradeYear}00&currency=$currentCurrency"), $lang->trade->fullYear);?></li>
          </ul>
        </li>
        <?php endforeach;?>
      </ul>
    </div>
    <div class='currency dropdown'>
      <button type='button' class='btn btn-sm btn-default dropdown-toggle' data-toggle='dropdown'><?php echo $currencyList[$currentCurrency];?> <span class="caret"></span></button>
      <ul class='dropdown-menu'>
        <?php foreach($currencyList as $key => $currency):?>
        <li><?php echo html::a(helper::createLink('trade', 'report', "date=$currentYear$currentMonth&currency=$key"), $currency);?></li>
        <?php endforeach;?>
      </ul>
    </div>
  </div>
  <div class='panel-body'>
    <?php if($currentMonth == '00'):?>
    <table class='table active-disabled'>
      <tr>
        <td colspan='3'>
          <div class='chart-wrapper text-center'>
            <h5><?php echo $currentYear . $lang->trade->report->annual . '(' . $currencyList[$currentCurrency] . ')';?></h5>
            <div class='chart-canvas'><canvas height='260' width='800' id='myBarChart'></canvas></div>
          </div>
        </td>
        <td class='w-400px'>
          <div class='table-wrapper'>
            <table id='barChart' class='table table-condensed table-hover table-striped table-bordered table-chart' data-chart='bar' data-target='#myBarChart' data-animation='false'>
              <thead>
                <tr class='text-center'>
                  <th><?php echo $lang->trade->month;?></th>
                  <th class='chart-label-in'><i class='chart-color-dot-in icon-circle'></i> <?php echo $lang->trade->in;?></th>
                  <th class='chart-label-out'><i class='chart-color-dot-out icon-circle'></i> <?php echo $lang->trade->out;?></th>
                  <th class='chart-label-profit'><?php echo $lang->trade->profit . '/' . $lang->trade->loss;?></th>
                </tr>
              </thead>
              <tbody>
              <?php foreach($annualChartDatas as $month => $annualChartData):?>
              <tr class='text-center'>
                <td class='chart-label'><?php echo $month == 'all' ? $lang->trade->total : $month;?></td>
                <td class='chart-value-in'><?php echo $annualChartData['in'];?></td>
                <td class='chart-value-out'><?php echo $annualChartData['out'];?></td>
                <td class='chart-value-profit'><?php echo $annualChartData['profit'];?></td>
              </tr>
              <?php endforeach;?>
              </tbody>
            </table>
          </div>
        </td>
      </tr>
    </table>
    <?php endif;?>
    <?php foreach($monthlyChartDatas as $groupBy => $chartDatas):?>
    <table class='table active-disabled'>
      <tr class='text-top'>
        <?php foreach($chartDatas as $type => $datas):?>
        <td class='w-p25'>
          <div class='chart-wrapper text-center'>
            <?php $dateTip = $currentMonth == '00' ? $currentYear . $lang->year : $currentMonth . $lang->month;?>
            <h5><?php echo $dateTip . $lang->trade->$type . $lang->trade->chartList[$groupBy];?></h5>
            <div class='chart-canvas'><canvas id="<?php echo 'chart-' . $type . '-' . $groupBy;?>" width='400' height='200' data-responsive='true'></canvas></div>
          </div>
        </td>
        <td class='w-p25'>
          <div style="overflow:auto; max-height:260px" class='table-wrapper'>
            <table class='table table-condensed table-hover table-striped table-bordered table-chart' data-chart='pie' data-target="<?php echo '#chart-' . $type . '-' . $groupBy;?>" data-animation='false'>
              <thead>
                <tr class='text-center'>
                  <th colspan='2'><?php echo $lang->trade->$groupBy;?></th>
                  <th><?php echo $lang->trade->money;?></th>
                  <th class='w-50px'><?php echo $lang->report->percent;?></th>
                </tr>
              </thead>
              <tbody>
              <?php foreach($datas as $data):?>
              <tr class='text-center'>
                <td class='chart-color w-20px'><i class='chart-color-dot icon-circle'></i></td>
                <td class='chart-label text-left'><?php echo $data->name;?></td>
                <td class='chart-value'><?php echo $data->value;?></td>
                <td><?php echo ($data->percent * 100) . '%';?></td>
              </tr>
              <?php endforeach;?>
              </tbody>
            </table>
          </div>
        </td>
        <?php endforeach;?>
      </tr>
    </table>
    <?php endforeach;?>
  </div>
</div>
<?php include $app->getModuleRoot() . 'common/view/footer.html.php';?>
