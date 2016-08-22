<?php
/**
 * The model file of leave module of Ranzhi.
 *
 * @copyright   Copyright 2009-2015 青岛易软天创网络科技有限公司(QingDao Nature Easy Soft Network Technology Co,LTD, www.cnezsoft.com)
 * @license     ZPL (http://zpl.pub/page/zplv12.html)
 * @author      chujilu <chujilu@cnezsoft.com>
 * @package     leave
 * @version     $Id$
 * @link        http://www.ranzhico.com
 */
class leaveModel extends model
{
    /**
     * Get a leave by id. 
     * 
     * @param  int    $id 
     * @access public
     * @return object
     */
    public function getById($id)
    {
        return $this->dao->select('*')->from(TABLE_LEAVE)->where('id')->eq($id)->fetch();
    }

    /**
     * Get leave list. 
     * 
     * @param  string $type 
     * @param  string $year 
     * @param  string $month 
     * @param  string $account 
     * @param  string $dept 
     * @param  string $status 
     * @access public
     * @return array
     */
    public function getList($type = 'personal', $year = '', $month = '', $account = '', $dept = '', $status = '', $orderBy = 'id_desc')
    {
        $leaveList = $this->dao->select('t1.*, t2.realname, t2.dept')->from(TABLE_LEAVE)->alias('t1')->leftJoin(TABLE_USER)->alias('t2')->on("t1.createdBy=t2.account")
            ->where('1=1')
            ->beginIf($year != '')->andWhere('t1.year')->eq($year)->fi()
            ->beginIf($month != '')->andWhere('t1.begin')->like("%-$month-%")->fi()
            ->beginIf($account != '')->andWhere('t1.createdBy')->eq($account)->fi()
            ->beginIf($dept != '')->andWhere('t2.dept')->in($dept)->fi()
            ->beginIf($status != '')->andWhere('t1.status')->eq($status)->fi()
            ->beginIf($type != 'personal')->andWhere('t1.status')->ne('draft')->fi()
            ->orderBy("t2.dept,t1.{$orderBy}")
            ->fetchAll();
        $this->session->set('leaveQueryCondition', $this->dao->get());

        return $leaveList;
    }

    /**
     * Get leave by date and account.
     * 
     * @param  string    $date 
     * @param  string    $account 
     * @access public
     * @return object
     */
    public function getByDate($date, $account)
    {
        return $this->dao->select('*')->from(TABLE_LEAVE)->where('begin')->le($date)->andWhere('end')->ge($date)->andWhere('createdBy')->eq($account)->fetch();
    }

    /**
     * Get all month of leave's begin.
     * 
     * @access public
     * @return array
     */
    public function getAllMonth()
    {
        $monthList = array();
        $dateList  = $this->dao->select('begin')->from(TABLE_LEAVE)->groupBy('begin')->orderBy('begin_asc')->fetchAll('begin');
        foreach($dateList as $date)
        {
            $year  = substr($date->begin, 0, 4);
            $month = substr($date->begin, 5, 2);
            if(!isset($monthList[$year][$month])) $monthList[$year][$month] = $month;
        }
        return $monthList;
    }

    /**
     * Get list by date.
     * 
     * @param  string    $date 
     * @param  string    $account 
     * @access public
     * @return array
     */
    public function getListByDate($date, $account)
    {
        $begin = strtolower($date['begin']);
        $end   = strtolower($date['end']);

        return $this->dao->select('*')->from(TABLE_LEAVE)
            ->where('status')->eq('pass')
            ->andWhere('createdBy')->eq($account)
            ->andWhere('begin')->ge($begin)
            ->andWhere('end')->le($end)
            ->fetchAll();
    }

    /**
     * Check leave.
     * 
     * @param  object $currentLeave
     * @param  string $account 
     * @param  int    $id
     * @access public
     * @return bool 
     */
    public function checkLeave($currentLeave = null, $account = '', $id = 0)
    {
        $beginTime  = date('Y-m-d H:i:s', strtotime($currentLeave->begin . ' ' . $currentLeave->start));
        $endTime    = date('Y-m-d H:i:s', strtotime($currentLeave->end   . ' ' . $currentLeave->finish));
        $leaveList  = $this->getList($type = '', $year = '', $month = '', $account, $dept = '', $status = '', $orderBy = 'begin, start');
        $existLeave = array();
        foreach($leaveList as $leave)
        {
            if($leave->id == $id) continue;

            $begin = $leave->begin . ' ' . $leave->start;
            $end   = $leave->end   . ' ' . $leave->finish;
            if(($beginTime > $begin && $beginTime < $end) 
                || ($endTime > $begin && $endTime < $end) 
                || ($beginTime <= $begin && $endTime >= $end))
            {
                $existLeave[] = substr($begin, 0, 16) . ' ~ ' . substr($end, 0, 16);
            }
        }
        return $existLeave;
    }

    /**
     * Create a leave.
     * 
     * @access public
     * @return bool
     */
    public function create()
    {
        $leave = fixer::input('post')
            ->add('status', 'wait')
            ->add('createdBy', $this->app->user->account)
            ->add('createdDate', helper::now())
            ->get();
        if(isset($leave->begin) and $leave->begin != '') $leave->year = substr($leave->begin, 0, 4);

        $existLeave = $this->checkLeave($leave, $this->app->user->account);
        if(!empty($existLeave)) return array('result' => 'fail', 'message' => sprintf($this->lang->leave->unique, implode(', ', $existLeave))); 
        
        $overtime = clone $leave;
        $overtime->start  = '00:00:00';
        $overtime->finish = '23:59:59';
        $existOvertime = $this->loadModel('overtime')->checkOvertime($overtime, $this->app->user->account);
        if(!empty($existOvertime)) return array('result' => 'fail', 'message' => sprintf($this->lang->overtime->unique, implode(', ', $existOvertime))); 

        $trip = $overtime;
        $existTrip = $this->loadModel('trip')->checkTrip($trip, $this->app->user->account); 
        if(!empty($existTrip)) return array('result' => 'fail', 'message' => sprintf($this->lang->trip->unique, implode(', ', $existTrip))); 

        $this->app->loadConfig('attend');
        $this->dao->insert(TABLE_LEAVE)
            ->data($leave)
            ->autoCheck()
            ->batchCheck($this->config->leave->require->create, 'notempty')
            ->check('end', 'ge', $leave->begin)
            ->check('start', 'ge', $this->config->attend->signInLimit)
            ->check('start', 'le', $this->config->attend->signOutLimit)
            ->check('finish', 'ge', $this->config->attend->signInLimit)
            ->check('finish', 'le', $this->config->attend->signOutLimit)
            ->exec();

        return $this->dao->lastInsertID();
    }

    /**
     * update leave.
     * 
     * @param  int    $id 
     * @access public
     * @return bool
     */
    public function update($id)
    {
        $oldLeave = $this->getByID($id);

        $leave = fixer::input('post')
            ->remove('status')
            ->remove('createdBy')
            ->remove('createdDate')
            ->get();

        if(isset($leave->begin) and $leave->begin != '') $leave->year = substr($leave->begin, 0, 4);

        $existLeave = $this->checkLeave($leave, $this->app->user->account, $id);
        if(!empty($existLeave)) return array('result' => 'fail', 'message' => sprintf($this->lang->leave->unique, implode(', ', $existLeave))); 

        $overtime = clone $leave;
        $overtime->start  = '00:00:00';
        $overtime->finish = '23:59:59';
        $existOvertime = $this->loadModel('overtime')->checkOvertime($overtime, $this->app->user->account);
        if(!empty($existOvertime)) return array('result' => 'fail', 'message' => sprintf($this->lang->overtime->unique, implode(', ', $existOvertime))); 
        
        $trip = $overtime;
        $existTrip = $this->loadModel('trip')->checkTrip($trip, $this->app->user->account); 
        if(!empty($existTrip)) return array('result' => 'fail', 'message' => sprintf($this->lang->trip->unique, implode(', ', $existTrip))); 

        $this->app->loadConfig('attend');
        $this->dao->update(TABLE_LEAVE)
            ->data($leave)
            ->autoCheck()
            ->batchCheck($this->config->leave->require->edit, 'notempty')
            ->check('end', 'ge', $leave->begin)
            ->check('start', 'ge', $this->config->attend->signInLimit)
            ->check('start', 'le', $this->config->attend->signOutLimit)
            ->check('finish', 'ge', $this->config->attend->signInLimit)
            ->check('finish', 'le', $this->config->attend->signOutLimit)
            ->where('id')->eq($id)
            ->exec();

        return !dao::isError();
    }

    /**
     * review 
     * 
     * @param  int    $id 
     * @param  string $status 
     * @access public
     * @return bool
     */
    public function review($id, $status)
    {
        if(!isset($this->lang->leave->statusList[$status])) return false;

        $leave = $this->getByID($id);

        $this->dao->update(TABLE_LEAVE)
            ->set('status')->eq($status)
            ->set('reviewedBy')->eq($this->app->user->account)
            ->set('reviewedDate')->eq(helper::now())
            ->where('id')->eq($id)
            ->exec();

        if(!dao::isError() and $status == 'pass')
        {
            $dates = range(strtotime($leave->begin), strtotime($leave->end), 60*60*24);
            $this->loadModel('attend')->batchUpdate($dates, $leave->createdBy, 'leave', '', $leave);
        }

        return !dao::isError();
    }

    /**
     * check date is in leave. 
     * 
     * @param  string $date 
     * @param  string $account 
     * @access public
     * @return bool
     */
    public function isLeave($date, $account)
    {
        static $leaveList = array();
        if(!isset($leaveList[$account])) $leaveList[$account] = $this->getList($type = 'company', $year = '', $month = '', $account, $dept = '', 'pass');

        foreach($leaveList[$account] as $leave)
        {
            if(strtotime($date) >= strtotime($leave->begin) and strtotime($date) <= strtotime($leave->end)) return true;
        }

        return false;
    }

    /**
     * delete leave.
     * 
     * @param  int    $id 
     * @access public
     * @return bool
     */
    public function delete($id, $null = null)
    {
        $oldLeave = $this->getByID($id);

        $this->dao->delete()->from(TABLE_LEAVE)->where('id')->eq($id)->exec();

        if(!dao::isError())
        {
            $oldDates = range(strtotime($oldLeave->begin), strtotime($oldLeave->end), 60*60*24);
            $this->loadModel('attend')->batchUpdate($oldDates, $oldLeave->createdBy, '');
        }

        return !dao::isError();
    }
}
