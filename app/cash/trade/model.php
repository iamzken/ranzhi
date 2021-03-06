<?php
/**
 * The model file of trade module of RanZhi.
 *
 * @copyright   Copyright 2009-2015 青岛易软天创网络科技有限公司(QingDao Nature Easy Soft Network Technology Co,LTD, www.cnezsoft.com)
 * @license     ZPL (http://zpl.pub/page/zplv12.html)
 * @author      Xiying Guan <guanxiying@xirangit.com>
 * @package     contact
 * @version     $Id$
 * @link        http://www.ranzhico.com
 */
class tradeModel extends model
{
    /**
     * Get trade by id.
     * 
     * @param  int    $id 
     * @access public
     * @return object
     */
    public function getByID($id)
    {
        $trade = $this->dao->select('*')->from(TABLE_TRADE)->where('id')->eq($id)->fetch();
        if($trade) $trade->files = $this->loadModel('file')->getByObject('trade', $id);
        return $trade;
    }

    /** 
     * Get trade list.
     * 
     * @param  string  $orderBy 
     * @param  object  $pager 
     * @access public
     * @return array
     */
    public function getList($mode, $date, $orderBy, $pager = null)
    {
        if($this->session->tradeQuery === false) $this->session->set('tradeQuery', ' 1 = 1');
        $tradeQuery = $this->loadModel('search', 'sys')->replaceDynamic($this->session->tradeQuery);

        if(strpos($orderBy, 'id') === false) $orderBy .= ', id_desc';

        /* Do not get trades which user has no privilege to browse their categories. */
        $denyCategories = array();
        $outCategories = $this->dao->select('*')->from(TABLE_CATEGORY)->where('type')->eq('out')->fetchAll('id');
        foreach($outCategories as $id => $outCategory)
        {
            if(!$this->loadModel('tree')->hasRight($id)) $denyCategories[] = $id; 
        }

        $rights = $this->app->user->rights;
        $expensePriv = ($this->app->user->admin == 'super' or isset($rights['tradebrowse']['out'])) ? true : false; 

        $startDate = '';
        $endDate   = '';

        if(strlen($date) == 4)
        {
            $startDate = $date . '-01-01';
            $endDate   = ($date + 1) . '-01-01';
        }

        if(strlen($date) == 6 and strpos($date, 'Q') === false)
        {
            if(substr($date, 4, 1) == 0) $nextMonth = '0' . (substr($date, 5, 1) + 1);
            if(substr($date, 4, 1) == 1) $nextMonth = substr($date, 4, 2) + 1;
            $startDate = substr($date, 0, 4) . '-' . substr($date, 4, 2) . '-01';
            $endDate   = substr($date, 0, 4) . '-' . $nextMonth . '-01';
            if(substr($date, 4, 2) == 12) $endDate = (substr($date, 0, 4) + 1) . '-01-01';
        }

        if(strlen($date) == 6 and strpos($date, 'Q') !== false)
        {
            $startMonth = (substr($date, 5, 1) - 1) * 3 + 1;
            $endMonth   = substr($date, 5, 1) * 3 + 1;
            $startDate  = substr($date, 0, 4) . '-' . $startMonth . '-01';
            $endDate    = substr($date, 5, 1) == 4 ? (substr($date, 0, 4) + 1) . '-01-01' : substr($date, 0, 4) . '-' . $endMonth . '-01';
        }

        $trades = $this->dao->select('*')->from(TABLE_TRADE)
            ->where('parent')->eq('')
            ->beginIF($startDate and $endDate)->andWhere('date')->ge($startDate)->andWhere('date')->lt($endDate)->fi()
            ->beginIF($mode == 'in')->andWhere('type')->eq('in')->fi()
            ->beginIF($mode == 'out')->andWhere('type')->eq('out')->fi()
            ->beginIF($mode == 'transfer')->andWhere('type', true)->like('transfer%')->orWhere('category')->eq('fee')->markRight(1)->fi()
            ->beginIF($mode == 'invest')->andWhere('type', true)->in('invest,redeem')->orWhere('category')->in('profit,loss')->markRight(1)->fi()
            ->beginIF($mode == 'bysearch')->andWhere($tradeQuery)->fi()
            ->beginIF(!empty($denyCategories))->andWhere('category')->notin($denyCategories)
            ->beginIF(!$expensePriv)->andWhere('type')->ne('out')->fi()
            ->orderBy($orderBy)
            ->page($pager)
            ->fetchAll('id');

        $this->session->set('tradeQueryCondition', $this->dao->get());

        return $trades;
    }

    /**
     * Get date pairs of trade.
     * 
     * @access public
     * @return array
     */
    public function getDatePairs($type = 'all')
    {
        return $this->dao->select('id, date')->from(TABLE_TRADE)
            ->where('1=1')
            ->beginIf($type != 'all')->andWhere('type')->in($type)->fi()
            ->orderBy('date_desc')
            ->fetchPairs();
    }

    /**
     * Get monthly chart data.
     * 
     * @param  string    $type 
     * @param  string    $currentYear 
     * @param  string    $currentMonth 00-12
     * @param  string    $groupBy 
     * @param  string    $currency 
     * @access public
     * @return array
     */
    public function getChartData($type, $currentYear, $currentMonth, $groupBy, $currency)
    {
        list($module, $groupBy, $field) = explode('|', $this->config->trade->groupBy[$groupBy]);

        /* Get this year data if currentMonth == '00'. */
        $startDate = $currentMonth == '00' ? $currentYear . '-01-01' : $currentYear . '-' . $currentMonth . '-01';
        $endDate   = $currentMonth == '00' ? date('Y-m-d', strtotime('+12 months', strtotime($startDate))) : date('Y-m-d', strtotime('+1 months', strtotime($startDate)));

        if($groupBy == 'category')
        {
            if($type == 'in')  $list = $this->lang->trade->incomeCategoryList + $this->loadModel('tree')->getOptionMenu('in', 0, true);
            if($type == 'out') $list = $this->lang->trade->expenseCategoryList + $this->loadModel('tree')->getOptionMenu('out', 0, true);
            $list = array('' => '') + $list;
        }

        if($groupBy == 'dept') $list = $this->loadModel('tree')->getOptionMenu('dept', 0, true);
        if($groupBy == 'area') $list = $this->loadModel('tree')->getOptionMenu('area', 0, true);
        if($groupBy == 'line')
        {
            $this->app->loadLang('product', 'crm');
            $list = $this->lang->product->lineList;
        }

        if($module == 'trade')
        {
            $datas = $this->dao->select("$groupBy as name, sum(money) as value")->from(TABLE_TRADE)
                ->where('type')->eq($type)
                ->beginIf($currency != '')->andWhere('currency')->eq($currency)->fi()
                ->beginIf($startDate != '' and $endDate != '')->andWhere('date')->ge($startDate)->andWhere('date')->lt($endDate)->fi()
                ->beginIf($groupBy == 'category')->andWhere('category')->in(array_keys($list))
                ->groupBy($groupBy)
                ->orderBy('value_desc')
                ->fetchAll('name');
        }
        else
        {
            $t2 = $this->config->report->moduleList[$module];
            $datas = $this->dao->select("ifnull(t2.{$groupBy}, 'null') as name, sum(money) as value")->from(TABLE_TRADE)->alias('t1')
                ->leftJoin($t2)->alias('t2')->on("t1.$field = t2.id")
                ->where('t1.type')->eq($type)
                ->beginIf($currency != '')->andWhere('currency')->eq($currency)->fi()
                ->beginIf($startDate != '' and $endDate != '')->andWhere('date')->ge($startDate)->andWhere('date')->lt($endDate)->fi()
                ->groupBy("t2.{$groupBy}")
                ->orderBy('value_desc')
                ->fetchAll("name");
        }

        if(empty($datas)) return array();

        if($groupBy == 'area')
        {
            $areaParents = $this->dao->select('id')->from(TABLE_CATEGORY)->where('parent')->eq(0)->andWhere('type')->eq('area')->fetchPairs();
            $areas       = $this->dao->select('id,path')->from(TABLE_CATEGORY)->where('type')->eq('area')->fetchPairs();
            $areaList    = array();
            foreach($areaParents as $areaParent)
            {
                foreach($areas as $id => $path)
                {
                    if(strpos($path, ',' . $areaParent . ',') !== false) $areaList[$areaParent][] = $id;
                }
            }

            $areaDatas = array();
            foreach($areaList as $parent => $areaChildren)
            {
                foreach($areaChildren as $areaChild)
                {
                    foreach($datas as $name => $data)
                    {
                        if($name == $areaChild)
                        {
                            if(empty($list[$name]))
                            {
                                $areaDatas['unset']->name  = $this->lang->trade->report->undefined;
                                $areaDatas['unset']->value = isset($areaDatas['unset']) ? $areaDatas['unset']->value + $data->value : $data->value;
                                $areaDatas['unset'] = $data;
                                unset($datas[$name]);
                            }
                            else
                            {
                                if(!isset($areaDatas[$parent])) $areaDatas[$parent] = new stdclass();
                                $areaDatas[$parent]->name  = $list[$parent];
                                $areaDatas[$parent]->value = isset($areaDatas[$parent]->value) ? $areaDatas[$parent]->value + $data->value : $data->value;
                            }
                        }
                    }
                }
            }
            $datas = $areaDatas;
        }
        else
        {
            foreach($datas as $name => $data)
            {
                if(empty($list[$name]))
                {
                    $data->name  = $this->lang->trade->report->undefined;
                    $data->value = isset($datas['unset']) ? $datas['unset']->value + $data->value : $data->value;
                    $datas['unset'] = $data;
                    unset($datas[$name]);
                }
                else
                {
                    $data->name = $list[$name];
                }
            }
        }

        return $datas;
    }

    /** 
     * Get trade list by trade's id list.
     * 
     * @param  array    $idList 
     * @access public
     * @return void
     */
    public function getByIdList($idList)
    {
        return $this->dao->select('*')->from(TABLE_TRADE)->where('id')->in($idList)->fetchAll('id');
    }

    /**
     * Get trades by year.
     * 
     * @param  string    $year 
     * @param  string    $currency 
     * @access public
     * @return void
     */
    public function getByYear($year, $currency)
    {
        return $this->dao->select('*, substr(date, 6, 2) as month')->from(TABLE_TRADE)
            ->where('date')->like("$year%")
            ->andWhere('currency')->eq($currency)
            ->orderBy('date_desc')
            ->fetchGroup('month');
    }

    /** 
     * Get details of a trade.
     * 
     * @param  int    $tradeID 
     * @access public
     * @return array
     */
    public function getDetail($tradeID)
    {
        return $this->dao->select('*')->from(TABLE_TRADE)->where('parent')->eq($tradeID)->fetchAll();
    }

    /**
     * Create a trade.
     * 
     * @param  string    $type   in|out
     * @access public
     * @return void
     */
    public function create($type)
    {
        $now = helper::now();
        if($type == 'in') $_POST['objectType'] = array('contract');

        $trade = fixer::input('post')
            ->add('type', $type)
            ->add('createdBy', $this->app->user->account)
            ->add('createdDate', $now)
            ->add('handlers', trim(join(',', $this->post->handlers), ','))
            ->setDefault('contract', 0)
            ->setIf($this->post->trader == '', 'trader', 0)
            ->setIf($this->post->createTrader, 'trader', 0)
            ->setIf($this->post->customer, 'trader', $this->post->customer)
            ->setIf($type == 'in', 'order', 0)
            ->setIf(!$this->post->objectType or !in_array('order', $this->post->objectType), 'order', 0)
            ->setIf(!$this->post->objectType or !in_array('contract', $this->post->objectType), 'contract', 0)
            ->remove('objectType,customer')
            ->striptags('desc')
            ->get();

        $depositor = $this->loadModel('depositor')->getByID($trade->depositor);
        if(!empty($depositor)) $trade->currency = $depositor->currency;

        $this->dao->insert(TABLE_TRADE)
            ->data($trade, $skip = 'createTrader,traderName,files,labels')
            ->autoCheck()
            ->batchCheck($this->config->trade->require->create, 'notempty')
            ->exec();

        $tradeID = $this->dao->lastInsertID();
        if(!dao::isError()) $this->loadModel('file')->saveUpload('trade', $tradeID);

        if($this->post->createTrader and $type == 'out')
        {
            $trader = new stdclass();
            $trader->relation    = 'provider';
            $trader->name        = $this->post->traderName;
            $trader->createdBy   = $this->app->user->account;
            $trader->createdDate = helper::now();
            $trader->public      = 1;

            $this->dao->insert(TABLE_CUSTOMER)->data($trader)->check('name', 'notempty')->exec();
            $trader = $this->dao->lastInsertID();
            $this->loadModel('action')->create('customer', $trader, 'Created');

            $this->dao->update(TABLE_TRADE)->set('trader')->eq($trader)->where('id')->eq($tradeID)->exec();
        }

        return $tradeID;
    }

    /**
     * Batch create.
     * 
     * @access public
     * @return array
     */
    public function batchCreate()
    {
        $now    = helper::now();
        $trades = array();

        $depositorList = $this->loadModel('depositor')->getList();

        $this->loadModel('action');
        /* Get data. */
        foreach($this->post->type as $key => $type)
        {
            if(empty($type)) break;
            if(!$this->post->money[$key]) continue;
            $trade = new stdclass();
            $trade->type           = $type;
            $trade->depositor      = $this->post->depositor[$key];
            $trade->money          = $this->post->money[$key];
            $trade->category       = $this->post->category[$key];
            $trade->dept           = $this->post->dept[$key];
            $trade->trader         = $this->post->trader[$key] ? $this->post->trader[$key] : 0;
            $trade->createTrader   = isset($this->post->createTrader[$key]) ? $this->post->createTrader[$key] : false;
            $trade->createCustomer = false;
            $trade->traderName     = isset($this->post->traderName[$key]) ? $this->post->traderName[$key] : '';
            $trade->handlers       = !empty($this->post->handlers[$key]) ? join(',', $this->post->handlers[$key]) : '';
            $trade->product        = $this->post->product[$key];
            $trade->date           = $this->post->date[$key];
            $trade->desc           = strip_tags(nl2br($this->post->desc[$key]), $this->config->allowedTags->admin);
            $trade->currency       = isset($depositorList[$trade->depositor]) ? $depositorList[$trade->depositor]->currency : '';
            $trade->createdBy      = $this->app->user->account;
            $trade->createdDate    = $now;

            if($trade->createTrader)
            {
                $this->dao->insert(TABLE_CUSTOMER)->data(array('relation' => 'provider', 'name' => $trade->traderName, 'public' => 1))->exec();
                $trade->trader = $this->dao->lastInsertID();
                $this->action->create('customer', $trade->trader, 'Created');
            }

            $trades[$key] = $trade;
        }

        if(empty($trades)) return array('result' => 'fail');

        $errors = $this->batchCheck($trades);
        if(!empty($errors)) return array('result' => 'fail', 'message' => $errors);

        foreach($trades as $trade)
        {
            $tradeID = $this->dao->insert(TABLE_TRADE)->data($trade, $skip = 'createTrader,traderName,createCustomer')->autoCheck()->exec();
            if(!dao::isError()) $this->action->create('trade', $tradeID, 'Created');
        }

        if(!dao::isError()) return array('result' => 'success', 'message' => $this->lang->saveSuccess, 'locate' => inlink('browse'));
        return array('result' => 'fail', 'message' => dao::getError());
    }

    /**
     * Batch update trades.
     * 
     * @access public
     * @return void
     */
    public function batchUpdate()
    {
        $trades = array();

        $depositorList = $this->loadModel('depositor')->getList();

        /* Get data. */
        if($this->post->type === false) return array('result' => 'fail');

        foreach($this->post->type as $key => $type)
        {
            if(empty($type)) break;
            $trade = new stdclass();
            $trade->depositor      = $this->post->depositor[$key];
            $trade->money          = $this->post->money[$key];
            $trade->type           = $type;
            $trade->dept           = $this->post->dept[$key];
            $trade->trader         = $this->post->trader[$key] ? $this->post->trader[$key] : 0;
            $trade->createTrader   = false;
            $trade->createCustomer = false;
            $trade->traderName     = $this->post->traderName[$key];
            $trade->handlers       = !empty($this->post->handlers[$key]) ? join(',', $this->post->handlers[$key]) : '';
            $trade->product        = $this->post->product[$key];
            $trade->date           = $this->post->date[$key];
            $trade->desc           = strip_tags(nl2br($this->post->desc[$key]));
            $trade->currency       = $depositorList[$trade->depositor]->currency;
            if(isset($this->post->category[$key])) $trade->category = $this->post->category[$key];

            $trades[$key] = $trade;
        }

        if(empty($trades)) return array('result' => 'fail');

        $errors = $this->batchCheck($trades);
        if(!empty($errors)) return array('result' => 'fail', 'message' => $errors);

        $tradeIDList = array();
        foreach($trades as $tradeID => $trade)
        {
            $this->dao->update(TABLE_TRADE)->data($trade, $skip = 'createTrader,traderName,createCustomer')->where('id')->eq($tradeID)->autoCheck()->exec();
        }
        if(!dao::isError()) return array('result' => 'success', 'message' => $this->lang->saveSuccess, 'locate' => inlink('browse'));
        return array('result' => 'fail', 'message' => dao::getError());
    }

    /**
     * Batch check trades.
     * 
     * @param  array    $trades 
     * @access public
     * @return void
     */
    public function batchCheck($trades)
    {
        $this->app->loadClass('filter', true);

        $errors = array();
        foreach($trades as $key => $trade)
        {
            $item = $this->lang->trade->money; 
            if(empty($trade->money) or !validater::checkFloat($trade->money)) $errors["money" . $key] = sprintf($this->lang->error->notempty, $item) . sprintf($this->lang->error->float, $item);

            $item = $this->lang->trade->handlers;
            if(empty($trade->handlers)) $errors['handlers'. $key] = sprintf($this->lang->error->notempty, $item);

            $item = $this->lang->trade->date;
            if(empty($trade->date) or !validater::checkDate($trade->date)) $errors['date' . $key] = sprintf($this->lang->error->date, $item) . sprintf($this->lang->error->notempty, $item);

        }

        return $errors;
    }

    /**
     * Update a trade.
     * 
     * @param  int    $tradeID 
     * @access public
     * @return string|bool
     */
    public function update($tradeID)
    {
        $oldTrade = $this->getByID($tradeID);

        if($oldTrade->type == 'in') $_POST['objectType'] = array('contract');

        $trade = fixer::input('post')
            ->add('type', $oldTrade->type)
            ->add('editedBy', $this->app->user->account)
            ->add('editedDate', helper::now())
            ->add('handlers', trim(join(',', $this->post->handlers), ','))
            ->setDefault('contract', 0)
            ->setIf($this->post->trader == '', 'trader', 0)
            ->setIf($this->post->createTrader, 'trader', 0)
            ->setIf($this->post->customer, 'trader', $this->post->customer)
            ->setIf($oldTrade->type == 'in', 'order', 0)
            ->setIf(!$this->post->objectType or !in_array('order', $this->post->objectType), 'order', 0)
            ->setIf(!$this->post->objectType or !in_array('contract', $this->post->objectType), 'contract', 0)
            ->remove('objectType,customer')
            ->striptags('desc')
            ->get();

        $this->dao->update(TABLE_TRADE)
            ->data($trade, $skip = 'createTrader,traderName,files,labels')
            ->autoCheck()
            ->batchCheck($this->config->trade->require->edit, 'notempty')
            ->where('id')->eq($tradeID)->exec();

        if($this->post->createTrader and $trade->type == 'out')
        {
            $trader = new stdclass();
            $trader->relation = 'provider';
            $trader->name     = $this->post->traderName;
            $trader->public   = 1;

            $this->dao->insert(TABLE_CUSTOMER)->data($trader)->check('name', 'notempty')->exec();
            $traderID = $this->dao->lastInsertID();

            $this->loadModel('action')->create('customer', $traderID, 'Created');

            $this->dao->update(TABLE_TRADE)->set('trader')->eq($traderID)->where('id')->eq($tradeID)->exec();
        }

        if(!dao::isError())
        {
            $this->loadModel('file')->saveUpload('trade', $tradeID);
            return commonModel::createChanges($oldTrade, $trade);
        }

        return false;
    }

    /**
     * Save imported trades. 
     * 
     * @param  int    $depositorID 
     * @access public
     * @return void
     */
    public function saveImport($depositorID)
    {
        $now       = helper::now();
        $trades    = array();
        $depositor = $this->loadModel('depositor')->getByID($depositorID);

        $this->loadModel('action');

        $newCustomer = array();
        $newTrader   = array();
        $category    = '';
        $dept        = '';

        /* Get data. */
        foreach($this->post->type as $key => $type)
        {
            if(empty($type)) break;
            if(!$this->post->money[$key]) continue;
            if(isset($this->post->ignoreUnique[$key]) and $this->post->ignoreUnique[$key]) continue;

            $category = $this->post->category[$key] == 'ditto' ? $category : $this->post->category[$key];
            $dept     = $this->post->dept[$key]     == 'ditto' ? $dept : $this->post->dept[$key];

            $trade = new stdclass();
            $trade->type           = $type;
            $trade->depositor      = $depositorID;
            $trade->money          = $this->post->money[$key];
            $trade->category       = $category;
            $trade->dept           = $dept;
            $trade->trader         = $this->post->trader[$key];
            $trade->createTrader   = isset($this->post->createTrader[$key])   ? $this->post->createTrader[$key] : '';
            $trade->traderName     = isset($this->post->traderName[$key])     ? $this->post->traderName[$key] : '';
            $trade->createCustomer = isset($this->post->createCustomer[$key]) ? $this->post->createCustomer[$key] : '';
            $trade->customerName   = isset($this->post->customerName[$key])   ? $this->post->customerName[$key] : '';
            $trade->handlers       = !empty($this->post->handlers[$key]) ? join(',', $this->post->handlers[$key]) : '';
            $trade->product        = isset($this->post->product[$key])        ? $this->post->product[$key] : 0;
            $trade->date           = $this->post->date[$key];
            $trade->desc           = strip_tags(nl2br($this->post->desc[$key]));
            $trade->currency       = $depositor->currency;
            $trade->createdBy      = $this->app->user->account;
            $trade->createdDate    = $now;

            if($trade->createTrader)
            {
                if(isset($newTrader[$trade->traderName]))
                {
                    $trade->trader = $newTrader[$trade->traderName];
                }
                else
                {
                    $data = new stdclass();
                    $data->relation    = 'provider';
                    $data->name        = $trade->traderName;
                    $data->level       = 0;
                    $data->public      = 1;
                    $data->createdBy   = $this->app->user->account;
                    $data->createdDate = $now;

                    $this->dao->insert(TABLE_CUSTOMER)->data($data)->exec();
                    $trade->trader = $this->dao->lastInsertID();
                    $this->action->create('customer', $trade->trader, 'Created');

                    $newTrader[$data->name] = $trade->trader;
                }
            }

            if($trade->createCustomer)
            {
                if(isset($newCustomer[$trade->customerName]))
                {
                    $trade->trader = $newCustomer[$trade->customerName];
                }
                else
                {
                    $customer = new stdclass();
                    $customer->relation    = 'client';
                    $customer->name        = $trade->customerName;
                    $customer->level       = 0;
                    $customer->status      = 'payed';
                    $customer->intension   = $trade->desc;
                    $customer->createdBy   = $this->app->user->account;
                    $customer->assignedTo  = $this->app->user->account;
                    $customer->createdDate = $now;

                    $this->dao->insert(TABLE_CUSTOMER)->data($customer)->exec();
                    $trade->trader = $this->dao->lastInsertID();
                    $this->action->create('customer', $trade->trader, 'Created');

                    $newCustomer[$customer->name] = $trade->trader;
                }
            }

            if(empty($trade->trader)) $trade->trader = 0; 

            $trades[$key] = $trade;
        }

        if(empty($trades)) return array('result' => 'fail');

        $errors = $this->batchCheck($trades);
        if(!empty($errors)) return array('result' => 'fail', 'message' => $errors);

        $tradeIDList = array();
        foreach($trades as $trade)
        {
            $this->dao->insert(TABLE_TRADE)->data($trade, $skip = 'createTrader,traderName,createCustomer,customerName')->autoCheck()->exec();
        }

        if(!dao::isError()) return array('result' => 'success', 'message' => $this->lang->saveSuccess, 'locate' => inlink('browse'));
        return array('result' => 'fail', 'message' => dao::getError());
    }

    /**
     * Transfer.
     * 
     * @access public
     * @return int|bool
     */
    public function transfer()
    {
        if($this->post->receipt == $this->post->payment) return array('result' => 'fail', 'message' => $this->lang->trade->notEqual);

        $receiptDepositor = $this->loadModel('depositor')->getByID($this->post->receipt);
        $paymentDepositor = $this->loadModel('depositor')->getByID($this->post->payment);

        $diffCurrency = $receiptDepositor->currency != $paymentDepositor->currency;

        $now = helper::now();

        $payment = fixer::input('post')
            ->add('type', 'transferout')
            ->add('category', 'transferout')
            ->add('depositor', $this->post->payment)
            ->add('currency', $paymentDepositor->currency)
            ->add('handlers', trim(join(',', $this->post->handlers), ','))
            ->add('createdBy', $this->app->user->account)
            ->add('createdDate', $now)
            ->add('editedDate', $now)
            ->setIF($diffCurrency, 'money', $this->post->transferOut)
            ->get();

        $receipt = $payment;
        $fee     = $payment;

        $this->dao->insert(TABLE_TRADE)
            ->data($payment, $skip = 'receipt, payment, fee, transferIn, transferOut')
            ->autoCheck()
            ->check('handlers', 'notempty')
            ->batchCheckIF($diffCurrency, 'transferOut,transferIn', 'notempty')
            ->batchCheckIF($diffCurrency, 'transferOut,transferIn', 'float')
            ->checkIF(!$diffCurrency, 'money', 'notempty')
            ->exec();

        if(dao::isError()) return array('result' => 'fail', 'message' => dao::getError());

        $paymentID = $this->dao->lastInsertID();
        $this->loadModel('action')->create('trade', $paymentID, 'Created');

        $receipt->type      = 'transferin';
        $receipt->category  = 'transferin';
        $receipt->depositor = $this->post->receipt;
        $receipt->currency  = $receiptDepositor->currency;
        if($diffCurrency) $receipt->money = $this->post->transferIn;

        $this->dao->insert(TABLE_TRADE)
            ->data($receipt, $skip = 'receipt, payment, fee, transferIn, transferOut')
            ->autoCheck()
            ->check('handlers', 'notempty')
            ->batchCheckIF($diffCurrency, 'transferOut, transferIn', 'notempty')
            ->checkIF(!$diffCurrency, 'money', 'notempty')
            ->exec();

        if(dao::isError()) return array('result' => 'fail', 'message' => dao::getError());

        $receiptID = $this->dao->lastInsertID();
        $this->loadModel('action')->create('trade', $receiptID, 'Created');

        if($this->post->fee)
        {
            $fee->type      = 'out';
            $fee->category  = 'fee';
            $fee->depositor = $this->post->payment;
            $fee->money     = $this->post->fee;
            $fee->desc      = sprintf($this->lang->trade->feeDesc, $fee->date, $paymentDepositor->abbr, $receiptDepositor->abbr);
            if($diffCurrency) $fee->desc = sprintf($this->lang->trade->feeDesc, $fee->date, $paymentDepositor->abbr, $receiptDepositor->abbr);

            $this->dao->insert(TABLE_TRADE)->data($fee, $skip = 'receipt, payment, fee, transferIn, transferOut')->exec();
            if(dao::isError()) return array('result' => 'fail', 'message' => dao::getError());

            $feeID = $this->dao->lastInsertID();
            $this->loadModel('action')->create('trade', $feeID, 'Created');
        }

        return array('result' => 'success', 'message' => $this->lang->saveSuccess, 'locate' => inlink('browse', 'mode=transfer'));
    }

    /**
     * Invest.
     * 
     * @access public
     * @return int|bool
     */
    public function invest()
    {
        $depositor = $this->loadModel('depositor')->getByID($this->post->depositor);
        $now = helper::now();

        $trade = fixer::input('post')
            ->add('category', $this->post->type)
            ->add('currency', !empty($depositor) ? $depositor->currency : '')
            ->add('handlers', trim(join(',', $this->post->handlers), ','))
            ->add('createdBy', $this->app->user->account)
            ->add('createdDate', $now)
            ->add('editedDate', $now)
            ->setIf($this->post->createTrader or !$this->post->trader, 'trader', 0)
            ->get();

        $this->dao->insert(TABLE_TRADE)
            ->data($trade, $skip = 'investCategory,investMoney,createTrader,traderName')
            ->autoCheck()
            ->batchCheck($this->config->trade->require->invest, 'notempty')
            ->exec();

        if(dao::isError()) return false;

        $tradeID = $this->dao->lastInsertID();
        $this->loadModel('action')->create('trade', $tradeID, 'Created');

        if($this->post->createTrader and $this->post->type == 'invest')
        {
            $trader = new stdclass();
            $trader->relation    = 'provider';
            $trader->name        = $this->post->traderName;
            $trader->createdBy   = $this->app->user->account;
            $trader->createdDate = helper::now();
            $trader->public      = 1;

            $this->dao->insert(TABLE_CUSTOMER)->data($trader)->check('name', 'notempty')->exec();
            if(dao::isError()) return false;

            $traderID = $this->dao->lastInsertID();
            $this->loadModel('action')->create('customer', $traderID, 'Created');

            $this->dao->update(TABLE_TRADE)->set('trader')->eq($traderID)->where('id')->eq($tradeID)->exec();
        }

        if($this->post->type == 'redeem' and $this->post->investMoney)
        {
            $invest = fixer::input('post') 
                ->setIF($this->post->investCategory == 'profit', 'type', 'in')
                ->setIF($this->post->investCategory == 'loss', 'type', 'out')
                ->add('category', $this->post->investCategory)
                ->add('money', $this->post->investMoney)
                ->add('currency', !empty($depositor) ? $depositor->currency : '')
                ->add('handlers', trim(join(',', $this->post->handlers), ','))
                ->setIf($this->post->createTrader or !$this->post->trader, 'trader', 0)
                ->add('createdBy', $this->app->user->account)
                ->add('createdDate', $now)
                ->add('editedDate', $now)
                ->get();

            $this->dao->insert(TABLE_TRADE)
                ->data($invest, $skip = 'investCategory,investMoney,createTrader,traderName')
                ->autoCheck()
                ->batchCheck($this->config->trade->require->invest, 'notempty')
                ->exec();

            if(dao::isError()) return false;

            $revesetID = $this->dao->lastInsertID();
            $this->loadModel('action')->create('trade', $revesetID, 'Created');
        }

        return !dao::isError();
    }

    /**
     * Save details of a trade. 
     * 
     * @param  int    $tradeID 
     * @access public
     * @return void
     */
    public function saveDetail($tradeID)
    {
        $trade = $this->getByID($tradeID);
        $trade->parent = $tradeID;

        $now = helper::now();
        $trade->createdDate = $now;
        $trade->createdBy   = $this->app->user->account;
        $trade->editedDate  = $now;
        $trade->editedBy    = $this->app->user->account;
        $trade->category    = 0;
        $trade->handlers    = '';

        $this->dao->delete()->from(TABLE_TRADE)->where('parent')->eq($tradeID)->exec();

        foreach($this->post->money as $key => $money)
        {
            if($money === '') continue;

            $trade->money = $money;
            $trade->desc  = $this->post->desc[$key];
            if(isset($this->post->category[$key])) $trade->category = join(',', $this->post->category[$key]);
            if(isset($this->post->handlers[$key])) $trade->handlers = join(',', $this->post->handlers[$key]);

            $this->dao->insert(TABLE_TRADE)->data($trade, 'id')->exec();
        }

        return !dao::isError();
    }

    /**
     * Delete a trade.
     * 
     * @param  int      $tradeID 
     * @access public
     * @return void
     */
    public function delete($tradeID, $null = null)
    {
        $this->dao->delete()->from(TABLE_TRADE)->where('id')->eq($tradeID)->exec();
        return !dao::isError();
    }

    /**
     *  Count money.
     * 
     * @param  array   $trades 
     * @access public
     * @return array
     */
    public function countMoney($trades, $mode)
    {
        $totalMoney  = array();
        $currencyList = $this->loadModel('common', 'sys')->getCurrencyList();

        foreach($currencyList as $key => $currency)
        {
            $totalMoney[$key]['in']  = 0;
            $totalMoney[$key]['out'] = 0;
            if($mode == 'invest')
            {
                $totalMoney[$key]['invest'] = 0;
                $totalMoney[$key]['redeem'] = 0;
            }

            foreach($trades as $trade)
            {
                if($trade->currency != $key) continue;
                if($trade->type == 'in' or $trade->type == 'out') $totalMoney[$key][$trade->type] += $trade->money;
                if($mode == 'invest' and ($trade->type == 'invest' or $trade->type == 'redeem')) $totalMoney[$key][$trade->type] += $trade->money;
            }
        }

        foreach($totalMoney as $currency => $money)
        {
            if($mode != 'invest' and $money['in'] == 0 and $money['out'] == 0) continue;
            if($mode == 'invest')
            {
                if($money['invest'] == 0 and $money['redeem'] == 0 and $money['in'] == 0 and $money['out'] == 0) continue;

                $tidyMoneyInvest   = "<span title='" . $money['invest'] . "'>" . commonModel::tidyMoney($money['invest']) . '</span>';
                $tidyMoneyRedeem   = "<span title='" . $money['redeem'] . "'>" . commonModel::tidyMoney($money['redeem']) . '</span>';
                $tidyUnRedeemMoney = "<span title='" . ($money['invest'] - $money['redeem']) . "'>" .  commonModel::tidyMoney($money['invest'] - $money['redeem']) . '</span>';
            }

            $tidyMoneyIn  = "<span title='" . $money['in'] . "'>" . commonModel::tidyMoney($money['in']) . '</span>';
            $tidyMoneyOut = "<span title='" . $money['out'] . "'>" . commonModel::tidyMoney($money['out']) . '</span>';
            
            if($mode == 'in')  printf($this->lang->trade->totalIn, $currencyList[$currency], $tidyMoneyIn);
            if($mode == 'out') printf($this->lang->trade->totalOut, $currencyList[$currency], $tidyMoneyOut);

            if($mode == 'all' or $mode == 'bysearch' or $mode == 'invest') 
            {
                $profitsMoney = $money['in'] - $money['out'];
                if($profitsMoney > 0)  $profits = "<span title='$profitsMoney'>" . $this->lang->trade->profit . commonModel::tidyMoney($profitsMoney) . '</span>';
                if($profitsMoney < 0)  $profits = "<span title='" . -$profitsMoney . "'>" . $this->lang->trade->loss . commonModel::tidyMoney(-$profitsMoney) . '</span>';
                if($profitsMoney == 0) $profits = $this->lang->trade->balance;

                if($mode == 'invest') printf($this->lang->trade->totalInvest, $currencyList[$currency], $tidyMoneyInvest, $tidyMoneyRedeem, $tidyUnRedeemMoney, $profits);
                if($mode != 'invest') printf($this->lang->trade->totalAmount, $currencyList[$currency], $tidyMoneyIn, $tidyMoneyOut, $profits);
            }
        }
    }

    /**
     * Check privilege for expense.
     *
     * @access public
     * @return void
     */
    public function checkExpensePriv()
    {
        if($this->app->user->admin == 'super') return true;

        $rights = $this->app->user->rights;
        if(!isset($rights['tradebrowse']['out']))
        {
            $locate = helper::createLink('cash.index');
            $errorLink = helper::createLink('sys.error', 'index', "type=accessLimited&locate={$locate}");
            die(js::locate($errorLink));
        }
    }

    /**
     * Get data to export. 
     * 
     * @param  string $mode 
     * @access public
     * @return object 
     */
    public function getExportData($mode = '')
    {
        $trades     = $this->getList($mode = 'all', $date = date('Y'), $orderBy = 'date');
        $depositors = $this->loadModel('depositor', 'cash')->getPairs();
        $lastDates  = $this->dao->select('depositor, max(date)')
            ->from(TABLE_BALANCE)
            ->where('date')->lt(date('Y-01-01'))
            ->groupBy('depositor')
            ->fetchPairs();
        $balances = array();
        foreach($lastDates as $depositor => $date)
        {
            $balances[$depositor] = $this->dao->select('money')
                ->from(TABLE_BALANCE)
                ->where('depositor')->eq($depositor)
                ->andWhere('date')->eq($date)
                ->fetch('money');
        }

        $numberFields = $this->config->trade->excel->numberFields;
        $customWidth  = $this->config->trade->excel->customWidth;

        $titles = array();
        $titles['In']      = $this->lang->trade->in;
        $titles['Out']     = $this->lang->trade->out;
        $titles['Profit']  = $this->lang->trade->profit . $this->lang->trade->loss;
        $titles['Balance'] = $this->lang->depositor->balance;

        $depositors += array('undefined' => $this->lang->trade->report->undefined, 'total' => $this->lang->trade->total);

        $fields = array();
        $rows   = array();

        $fields['month'] = '';
        foreach($depositors as $key => $depositor)
        {
            foreach($titles as $titleKey => $title)
            {
                $fields[$key . $titleKey] = $depositor . $title;

                $numberFields[] = $key . $titleKey; 
                $customWidth[$key . $titleKey] = 20;
            }
        }

        /* Initial rows. */
        foreach($this->lang->trade->monthList as $monthKey => $month)
        {
            foreach($fields as $fieldsKey => $field)
            {
                $rows[$monthKey][$fieldsKey] = 0;
            }

            $rows[$monthKey]['month'] = $month;
        }

        $undefined = false;
        /* Add last year balance. */
        foreach($balances as $depositor => $money)
        {
            if(!isset($depositors[$depositor]))
            {
                $depositor = 'undefined';
                $undefined = true;
            }

            /* Add money to balance of last year. */
            $rows['last']["{$depositor}Balance"] += $money;
            $rows['last']['totalBalance']        += $money;

            /* Add money to balance of every month in this year. */
            for($i = 1; $i <= (int)date('m'); $i++)
            {
                $month = $i < 10 ? '0' . $i : $i;
                $rows[$month]["{$depositor}Balance"] += $money;
                $rows[$month]['totalBalance']        += $money;
            }

            /* Add money to total balance. */
            $rows['total']["{$depositor}Balance"] += $money;
            $rows['total']['totalBalance']        += $money;
        }

        foreach($trades as $trade)
        {
            if($trade->type != 'in' && $trade->type != 'out') continue;

            if(isset($depositors[$trade->depositor]))
            {
                $depositor = $trade->depositor;
            }
            else
            {
                $depositor = 'undefined';
                $undefined = true;
            }

            $month = date('m', strtotime($trade->date));
            $type  = ucfirst($trade->type);
            $money = $trade->type == 'in' ? $trade->money : -$trade->money;

            /* Add money to profit and balance of this month. */
            $rows[$month]["{$depositor}{$type}"] += $trade->money;
            $rows[$month]["{$depositor}Profit"]  += $money;
            $rows[$month]["{$depositor}Balance"] += $money;
            $rows[$month]["total{$type}"]        += $trade->money;
            $rows[$month]["totalProfit"]         += $money;
            $rows[$month]["totalBalance"]        += $money;

            /* Add money to profit and balance of every month that after this month. */
            for($i = (int)$month + 1; $i <= (int)date('m'); $i++)
            {
                $m = $i < 10 ? '0' . $i : $i;
                $rows[$m]["{$depositor}Balance"] += $money;
                $rows[$m]['totalBalance']        += $money;
            }

            /* Add money to total profit and balance. */
            $rows['total']["{$depositor}{$type}"] += $trade->money;
            $rows['total']["{$depositor}Profit"]  += $money;
            $rows['total']["{$depositor}Balance"] += $money;
            $rows['total']["total{$type}"]        += $trade->money;
            $rows['total']["totalProfit"]         += $money;
            $rows['total']["totalBalance"]        += $money;
        }

        /* Remove undefined columns. */
        if(!$undefined) 
        {
            foreach($titles as $key => $title) unset($fields["undefined{$key}"]);
        }
        
        $data = new stdclass();
        $data->fields       = $fields;
        $data->numberFields = $numberFields;
        $data->kind         = 'depositor';
        $data->rows         = $rows;
        $data->title        = $this->lang->trade->excel->title->depositor;
        $data->customWidth  = $customWidth;
        $data->help         = $this->lang->trade->excel->help->depositor;

        return $data;
    }
}
