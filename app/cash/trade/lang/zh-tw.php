<?php
/**
 * The trade module zh-tw file of RanZhi.
 *
 * @copyright   Copyright 2009-2015 青島易軟天創網絡科技有限公司(QingDao Nature Easy Soft Network Technology Co,LTD, www.cnezsoft.com)
 * @license     ZPL (http://zpl.pub/page/zplv12.html)
 * @author      Xiying Guan <guanxiying@xirangit.com>
 * @package     trade
 * @version     $Id$
 * @link        http://www.ranzhico.com
 */
if(!isset($lang->trade)) $lang->trade = new stdclass();
$lang->trade->common      = '記賬';
$lang->trade->id          = '編號';
$lang->trade->depositor   = '賬號';
$lang->trade->type        = '交易';
$lang->trade->currency    = '貨幣';
$lang->trade->trader      = '商戶';
$lang->trade->customer    = '客戶';
$lang->trade->money       = '金額';
$lang->trade->desc        = '說明';
$lang->trade->product     = '產品';
$lang->trade->order       = '訂單';
$lang->trade->contract    = '合同';
$lang->trade->category    = '科目';
$lang->trade->date        = '時間';
$lang->trade->handlers    = '經手人';
$lang->trade->dept        = '部門';
$lang->trade->receipt     = '收款賬戶';
$lang->trade->payment     = '付款賬戶';
$lang->trade->fee         = '手續費';
$lang->trade->transferIn  = '轉入金額';
$lang->trade->transferOut = '轉出金額';
$lang->trade->schema      = '模板';
$lang->trade->importFile  = '導入檔案';
$lang->trade->encode      = '編碼';
$lang->trade->createdBy   = '由誰創建';
$lang->trade->createdDate = '創建時間';
$lang->trade->editedBy    = '由誰編輯';
$lang->trade->editedDate  = '編輯時間';
$lang->trade->month       = '月份';
$lang->trade->uploadFile  = '上傳附件';
$lang->trade->productLine = '產品綫';
$lang->trade->area        = '客戶區域';
$lang->trade->industry    = '客戶行業';
$lang->trade->level       = '客戶級別';
$lang->trade->size        = '客戶規模';

$lang->trade->create       = '記賬';
$lang->trade->in           = '收入';
$lang->trade->out          = '支出';
$lang->trade->invest       = '投資';
$lang->trade->redeem       = '贖回';
$lang->trade->createIn     = '記收入';
$lang->trade->createOut    = '記支出';
$lang->trade->transfer     = '轉賬';
$lang->trade->edit         = '編輯賬目';
$lang->trade->detail       = '明細';
$lang->trade->view         = '詳情';
$lang->trade->browse       = '賬目列表';
$lang->trade->delete       = '刪除記錄';
$lang->trade->batchCreate  = '批量記賬';
$lang->trade->batchEdit    = '批量編輯';
$lang->trade->newTrader    = '新建';
$lang->trade->import       = '導入';
$lang->trade->export       = '導出';
$lang->trade->showImport   = '導入確認';
$lang->trade->fullYear     = '全年';
$lang->trade->quarter      = '季度';
$lang->trade->export2Excel = '導出Excel';
$lang->trade->compare      = '年度對比表';

$lang->trade->report = new stdclass();
$lang->trade->report->common      = '報表'; 
$lang->trade->report->annual      = '年度收支表'; 
$lang->trade->report->compare     = '年度對比表';
$lang->trade->report->create      = '生成報表';
$lang->trade->report->selectYears = '選擇年份';
$lang->trade->report->undefined   = '未定義';
$lang->trade->report->compareTip  = '必須選擇兩個年份進行比較';

$lang->trade->report->typeList['annual']  = '年度收支表'; 
$lang->trade->report->typeList['compare'] = '年度對比表'; 

$lang->trade->typeList['in']          = '收入';
$lang->trade->typeList['out']         = '支出';
$lang->trade->typeList['transferout'] = '轉出';
$lang->trade->typeList['transferin']  = '轉入';
$lang->trade->typeList['invest']      = '投資';
$lang->trade->typeList['redeem']      = '贖回';

$lang->trade->quarters = new stdclass();
$lang->trade->quarters->Q4 = '10,11,12';
$lang->trade->quarters->Q3 = '07,08,09';
$lang->trade->quarters->Q2 = '04,05,06';
$lang->trade->quarters->Q1 = '01,02,03';

$lang->trade->quarterList['Q1'] = '第一季度';
$lang->trade->quarterList['Q2'] = '第二季度';
$lang->trade->quarterList['Q3'] = '第三季度';
$lang->trade->quarterList['Q4'] = '第四季度';

$lang->trade->monthList['last']  = '上年結轉';
$lang->trade->monthList['01']    = '一月';
$lang->trade->monthList['02']    = '二月';
$lang->trade->monthList['03']    = '三月';
$lang->trade->monthList['04']    = '四月';
$lang->trade->monthList['05']    = '五月';
$lang->trade->monthList['06']    = '六月';
$lang->trade->monthList['07']    = '七月';
$lang->trade->monthList['08']    = '八月';
$lang->trade->monthList['09']    = '九月';
$lang->trade->monthList['10']    = '十月';
$lang->trade->monthList['11']    = '十一月';
$lang->trade->monthList['12']    = '十二月';
$lang->trade->monthList['total'] = '總計';

$lang->trade->categoryList['transferin']  = '轉入';
$lang->trade->categoryList['transferout'] = '轉出';
$lang->trade->categoryList['invest']      = '投資';
$lang->trade->categoryList['redeem']      = '贖回';

$lang->trade->expenseCategoryList['fee']  = '手續費';
$lang->trade->expenseCategoryList['loss'] = '理財虧損';

$lang->trade->incomeCategoryList['profit'] = '理財盈利';

$lang->trade->categoryList = $lang->trade->categoryList + $lang->trade->expenseCategoryList + $lang->trade->incomeCategoryList;

$lang->trade->investCategoryList['profit'] = '盈利';
$lang->trade->investCategoryList['loss']   = '虧損';

$lang->trade->transferCategoryList['transferin']  = '轉入';
$lang->trade->transferCategoryList['transferout'] = '轉出';

$lang->trade->objectTypeList['customer'] = '客戶支出';
$lang->trade->objectTypeList['order']    = '訂單支出';
$lang->trade->objectTypeList['contract'] = '合同支出';

$lang->trade->investTypeList['invest'] = '投資';
$lang->trade->investTypeList['redeem'] = '贖回';

$lang->trade->encodeList['gbk']  = 'GBK';
$lang->trade->encodeList['utf8'] = 'UTF-8';

$lang->trade->notEqual = '付款賬號不能與收款賬號相同。';
$lang->trade->feeDesc  = '%s %s 轉入 %s';
$lang->trade->fileNode = '檔案格式為csv';

$lang->trade->importedFields = array();
$lang->trade->importedFields['category'] = '項目';
$lang->trade->importedFields['type']     = '交易類型';
$lang->trade->importedFields['trader']   = '商戶';
$lang->trade->importedFields['in']       = '收入';
$lang->trade->importedFields['out']      = '支出';
$lang->trade->importedFields['date']     = '時間';
$lang->trade->importedFields['category'] = '科目';
$lang->trade->importedFields['dept']     = '部門';
$lang->trade->importedFields['desc']     = '備註';
$lang->trade->importedFields['fee']      = '手續費';
$lang->trade->importedFields['product']  = '產品';

$lang->trade->totalIn       = '%s收入%s；';
$lang->trade->totalOut      = '%s支出%s；';
$lang->trade->totalAmount   = '%s收入%s，支出%s，%s；';
$lang->trade->totalInvest   = '%s投資%s，贖回%s，未贖回%s，%s；';
$lang->trade->profit        = '盈';
$lang->trade->loss          = '虧';
$lang->trade->balance       = '收支平衡';
$lang->trade->total         = '總計';

$lang->trade->noTraderMatch = '沒有匹配到相應的商戶，點擊新建';
$lang->trade->unique        = '今天已經有相同金額的賬目';
$lang->trade->ignore        = '忽略';

$lang->trade->chartList['category']    = '按科目統計';
$lang->trade->chartList['dept']        = '按部門統計';
$lang->trade->chartList['area']        = '按客戶區域統計';
$lang->trade->chartList['industry']    = '按客戶行業統計';
$lang->trade->chartList['level']       = '按客戶級別統計';
$lang->trade->chartList['size']        = '按客戶規模統計';
$lang->trade->chartList['productLine'] = '按產品綫統計';

$lang->trade->excel = new stdclass();
$lang->trade->excel->title = new stdclass();
$lang->trade->excel->title->depositor = '賬號盈虧';

$lang->trade->excel->help = new stdclass();
$lang->trade->excel->help->depositor       = '本報表不區分幣種，僅統計交易類型為收入和支出的帳目。';
