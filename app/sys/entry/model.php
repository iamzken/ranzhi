<?php
/**
 * The model file of entry module of RanZhi.
 *
 * @copyright   Copyright 2009-2015 青岛易软天创网络科技有限公司(QingDao Nature Easy Soft Network Technology Co,LTD, www.cnezsoft.com)
 * @license     ZPL (http://zpl.pub/page/zplv12.html)
 * @author      Yidong Wang <yidong@cnezsoft.com>
 * @package     entry 
 * @version     $Id: model.php 3952 2016-07-13 05:13:17Z liugang $
 * @link        http://www.ranzhico.com
 */
class entryModel extends model
{
    /**
     * Get all entries. 
     * 
     * @param  string $type custom|system
     * @param  int    $category
     * @access public
     * @return array
     */
    public function getEntries($type = 'custom', $category = 0)
    {
        $entries = $this->dao->select('*')->from(TABLE_ENTRY)
            ->where(1)
            ->beginIF(!empty($category))->andWhere('category')->eq($category)->fi()
            ->orderBy('`order, id`')->fetchAll();

        /* Remove entry if no rights and fix logo path. */
        $newEntries = array();
        $this->app->loadLang('install');
        foreach($entries as $entry)
        {
            if($entry->buildin == 1)
            {
                $entry->name = $this->lang->install->buildinEntry->{$entry->code}['name'];
                $entry->abbr = $this->lang->install->buildinEntry->{$entry->code}['abbr'];
            }
            if($entry->logo != '' && substr($entry->logo, 0, 1) != '/') $entry->logo = $this->config->webRoot . $entry->logo;
            if(commonModel::hasAppPriv($entry->code)) $newEntries[] = $entry; 
        }
        $entries = $newEntries;

        if($type == 'mobile')
        {
            $this->app->loadLang('index', 'sys');

            $dashboardEntry = new stdclass();
            $dashboardEntry->id       = 'dashboard';
            $dashboardEntry->buildin  = true;
            $dashboardEntry->code     = 'dashboard';
            $dashboardEntry->name     = $this->lang->index->dashboard;
            $dashboardEntry->icon     = 'icon-home';
            $dashboardEntry->url      = helper::createLink('sys.index');
            $dashboardEntry->order    = 0;
            $dashboardEntry->category = 0;
            $entries[] = $dashboardEntry;

            if($this->app->user->admin == 'super' || commonModel::hasAppPriv('superadmin'))
            {
                $adminEntry = new stdclass();
                $adminEntry->id       = 'superadmin';
                $adminEntry->buildin  = true;
                $adminEntry->code     = 'superadmin';
                $adminEntry->name     = $this->lang->index->superAdmin;
                $adminEntry->icon     = 'icon-cog';
                $adminEntry->url      = helper::createLink('admin');
                $adminEntry->order    = 999999;
                $adminEntry->category = 0;
                $entries[] = $adminEntry;
            }

            usort($entries, 'commonModel::sortEntryByOrder');
            $newEntries = array();
            foreach($entries as $entry)
            {
                switch ($entry->code)
                {
                    case 'crm':
                        unset($entry->logo);
                        $entry->icon = 'icon-phone';
                        break;
                    case 'oa':
                        unset($entry->logo);
                        $entry->icon = 'icon-check';
                        break;
                    case 'cash':
                        unset($entry->logo);
                        $entry->icon = 'icon-dollar';
                        break;
                    case 'team':
                        unset($entry->logo);
                        $entry->icon = 'icon-group';
                        break;
                }
                if(empty($entry->url)) $entry->url = helper::createLink('entry', 'visit', "entryID=$entry->id");
                $newEntries[$entry->id] = $entry;
            }
            return $newEntries;
        }
        if($type != 'custom') return $entries;

        /* Add custom settings. */
        $customApp = isset($this->config->personal->common->customApp) ? json_decode($this->config->personal->common->customApp->value) : new stdclass();
        foreach($entries as $entry)
        {
            if(isset($customApp->{$entry->id}))
            {
                if(isset($customApp->{$entry->id}->order))   $entry->order   = $customApp->{$entry->id}->order;
                if(isset($customApp->{$entry->id}->visible)) $entry->visible = $customApp->{$entry->id}->visible;
            }
        }
        usort($entries, 'commonModel::sortEntryByOrder');

        return $entries;
    }

    /**
     * Get entry by id.
     * 
     * @param  int    $entryID
     * @access public
     * @return object 
     */
    public function getById($entryID)
    {
        return $this->dao->select('*')->from(TABLE_ENTRY)->where('id')->eq($entryID)->fetch();
    }

    /**
     * Get entry by code.
     * 
     * @param  string $code 
     * @access public
     * @return object 
     */
    public function getByCode($code)
    {
        return $this->dao->select('*')->from(TABLE_ENTRY)->where('code')->eq($code)->fetch(); 
    }

    /**
     * Create entry. 
     * 
     * @access public
     * @return void
     */
    public function create()
    {
        $maxOrder = $this->dao->select('`order`')->from(TABLE_ENTRY)->orderBy('order_desc')->limit(1)->fetch('order');

        $entry = fixer::input('post')
            ->setDefault('ip', '*')
            ->setDefault('visible', 0)
            ->setDefault('buildin', 0)
            ->setDefault('integration', 0)
            ->setDefault('order', $maxOrder + 10)
            ->setDefault('open', 'iframe')
            ->setDefault('control', 'none')
            ->setDefault('size', 'max')
            ->setDefault('width', '700')
            ->setDefault('height', '538')
            ->setDefault('position', 'default')
            ->setDefault('zentao', 0)
            ->setIF($this->post->allip, 'ip', '*')
            ->setIF($this->post->zentao, 'open', 'iframe')
            ->setIF($this->post->zentao, 'integration', 1)
            ->setIF($this->post->zentao, 'control', 'full')
            ->remove('allip,adminAccount,adminPassword')
            ->stripTags('login,logout,block', $this->config->allowedTags->admin)
            ->get();

        if($this->post->chanzhi) 
        {
            $entry->logout = $entry->login . "?m=ranzhi&f=logout";
            $entry->block  = $entry->login . "?m=ranzhi&f=block";
            $entry->login .= "?m=ranzhi&f=login";
        }

        if($entry->size == 'custom') $entry->size = helper::jsonEncode(array('width' => (int)$entry->width, 'height' => (int)$entry->height));

        $this->dao->insert(TABLE_ENTRY)
            ->data($entry, $skip = 'width,height,files,chanzhi,groups')
            ->autoCheck()
            ->batchCheck($this->config->entry->require->create, 'notempty')
            ->check('code', 'unique')
            ->check('code', 'code')
            ->check('code', 'notInt')
            ->exec();

        if(dao::isError()) return false;

        $entryID = $this->dao->lastInsertID();

        /* Insert app privilage. */
        $groups = $this->post->groups;
        if($groups != false && !empty($groups))
        {
            $priv = new stdclass();
            $priv->module = 'apppriv';
            $priv->method = $this->post->code;
            foreach($this->post->groups as $group)
            {
                $priv->group = $group;
                $this->dao->replace(TABLE_GROUPPRIV)->data($priv)->exec();
            }
        }

        return $entryID;
    }

    /**
     * Update entry.
     * 
     * @param  int    $code 
     * @access public
     * @return void
     */
    public function update($code)
    {
        $entry = fixer::input('post')->stripTags('login', $this->config->allowedTags->admin)->get();
        if(!isset($entry->visible)) $entry->visible = 0;

        $this->dao->update(TABLE_ENTRY)->data($entry)
            ->autoCheck()
            ->batchCheck($this->config->entry->require->edit, 'notempty')
            ->where('code')->eq($code)
            ->exec();
        return !dao::isError();
    }

    /**
     * Set style for entry.
     * 
     * @param  string    $code 
     * @access public
     * @return int
     */
    public function setStyle($code)
    {
        $oldEntry = $this->getByCode($code);

        $entry = fixer::input('post')->get();

        if($entry->size == 'custom') $entry->size = helper::jsonEncode(array('width' => (int)$entry->width, 'height' => (int)$entry->height));
        unset($entry->logo);

        $this->dao->update(TABLE_ENTRY)->data($entry, $skip = 'width,height,files')
            ->autoCheck()
            ->where('code')->eq($code)
            ->exec();

        return $oldEntry->id;
    }

    /**
     * Integration entry.
     * 
     * @param  string    $code 
     * @access public
     * @return void
     */
    public function integration($code)
    {
        $entry = fixer::input('post')
            ->setDefault('ip', '*')
            ->setDefault('integration', 0)
            ->setIF($this->post->allip, 'ip', '*')
            ->stripTags('logout,block', $this->config->allowedTags->admin)
            ->get();

        $this->dao->update(TABLE_ENTRY)->data($entry)->autoCheck()->where('code')->eq($code)->exec();
        return !dao::isError();
    }

    /**
     * Delete entry. 
     * 
     * @param  string $code 
     * @access public
     * @return void
     */
    public function delete($code, $table = null)
    { 
        $entry = $this->getByCode($code);

        $this->deleteLogo($entry->id);
        $this->dao->delete()->from(TABLE_ENTRY)->where('code')->eq($code)->exec();

        return !dao::isError();
    }

    /**
     * Get key of entry. 
     * 
     * @param  string $entry 
     * @access public
     * @return object 
     */
    public function getAppKey($entry)
    {
        return $this->config->entry->$entry->key;
    }
    /**
     * Create a key.
     * 
     * @access public
     * @return string 
     */
    public function createKey()
    {
        return md5(rand());
    }

    /**
     * Get all departments.
     * 
     * @access public
     * @return object 
     */
    public function getAllDepts()
    {
        return $this->dao->select('*')->from(TABLE_DEPT)->fetchAll();
    }

    /**
     * Get all users. 
     * 
     * @access public
     * @return object 
     */
    public function getAllUsers()
    {
        return $this->dao->select('*')->from(TABLE_USER)
            ->where('deleted')->eq(0)
            ->fetchAll();
    }

    /**
     * Update entry logo. 
     * 
     * @param  int    $entryID 
     * @access public
     * @return void
     */
    public function updateLogo($entryID)
    {
        /* if no files then return. */
        if(empty($_FILES)) return true;

        /* Delete logo img. */
        $this->deleteLogo($entryID);

        /* Save logo img. */
        $fileTitle = $this->file->saveUpload('entryLogo', $entryID);
        if(!dao::isError())
        {
            $file = $this->file->getByID(key($fileTitle));

            $logoPath = $this->file->webPath . $file->pathname;
            $this->dao->update(TABLE_ENTRY)->set('logo')->eq($logoPath)->where('id')->eq($entryID)->exec();
        }
    }

    /**
     * Delete entry logo.
     * 
     * @param  int    $entryID 
     * @access public
     * @return void
     */
    public function deleteLogo($entryID)
    {
        $files = $this->loadModel('file')->getByObject('entryLogo', $entryID);

        foreach($files as $file) $this->file->delete($file->id);
    }

    /**
     * Get blocks by API.
     * 
     * @param  object    $entry 
     * @access public
     * @return array
     */
    public function getBlocksByAPI($entry)
    {
        if(empty($entry)) return array();
        $parseUrl   = parse_url($entry->block);
        $blockQuery = "mode=getblocklist&hash={$entry->key}&lang=" . $this->app->getClientLang();
        $parseUrl['query'] = empty($parseUrl['query']) ? $blockQuery : $parseUrl['query'] . '&' . $blockQuery;

        $link = '';
        if(!isset($parseUrl['scheme'])) 
        {
            $link  = commonModel::getSysURL() . $parseUrl['path'];
            $link .= '?' . $parseUrl['query'];
        }
        else
        {
            $link .= $parseUrl['scheme'] . '://' . $parseUrl['host'];
            if(isset($parseUrl['port'])) $link .= ':' . $parseUrl['port']; 
            if(isset($parseUrl['path'])) $link .= $parseUrl['path']; 
            $link .= '?' . $parseUrl['query'];
        }

        $blocks = commonModel::http($link);
        return json_decode($blocks, true);
    }

    /**
     * Get block params.
     * 
     * @param  object $entry 
     * @param  int    $blockID 
     * @access public
     * @return json
     */
    public function getBlockParams($entry, $blockID)
    {
        if(empty($entry)) return array();
        $parseUrl  = parse_url($entry->block);
        $formQuery = "mode=getblockform&blockid=$blockID&hash={$entry->key}&lang=" . $this->app->getClientLang();
        $parseUrl['query'] = empty($parseUrl['query']) ? $formQuery : $parseUrl['query'] . '&' . $formQuery;

        $link = '';
        if(!isset($parseUrl['scheme'])) 
        {
            $link  = commonModel::getSysURL() . $parseUrl['path'];
            $link .= '?' . $parseUrl['query'];
        }
        else
        {
            $link .= $parseUrl['scheme'] . '://' . $parseUrl['host'];
            if(isset($parseUrl['port'])) $link .= ':' . $parseUrl['port']; 
            if(isset($parseUrl['path'])) $link .= $parseUrl['path']; 
            $link .= '?' . $parseUrl['query'];
        }
        $params = commonModel::http($link);

        return json_decode($params, true);
    }

    /**
     * Get entries of json.
     * 
     * @access public
     * @return void
     */
    public function getJSONEntries()
    {
        $entries    = $this->getEntries();
        $allEntries = array();

        foreach($entries as $entry)
        {
            $sso     = helper::createLink('entry', 'visit', "entryID=$entry->id");
            $logo    = !empty($entry->logo) ? $entry->logo : '';
            $size    = !empty($entry->size) ? ($entry->size != 'max' ? $entry->size : "'$entry->size'") : "'max'";
            $menu    = $entry->visible ? 'all' : 'list';
            $display = $entry->buildin ? 'fixed' : 'sizeable';
            
            /* add web root if logo not start with /  */
            if($logo != '' && substr($logo, 0, 1) != '/') $logo = $this->config->webRoot . $logo;
            
            if(!isset($entry->control))  $entry->control = '';
            if(!isset($entry->position)) $entry->position = '';
            unset($tmpEntry);
            $tmpEntry['id']       = $entry->id;
            $tmpEntry['name']     = $entry->name;
            $tmpEntry['code']     = $entry->code;
            $tmpEntry['url']      = $sso;
            $tmpEntry['open']     = $entry->open;
            $tmpEntry['desc']     = $entry->name;
            $tmpEntry['size']     = $size;
            $tmpEntry['icon']     = $logo;
            $tmpEntry['control']  = $entry->control;
            $tmpEntry['position'] = $entry->position;
            $tmpEntry['menu']     = $menu;
            $tmpEntry['display']  = $display;
            $tmpEntry['abbr']     = $entry->abbr;
            $tmpEntry['order']    = $entry->order;
            $tmpEntry['sys']      = $entry->buildin;
            $tmpEntry['category'] = $entry->category;
            $allEntries[] = $tmpEntry;
        }
        return json_encode($allEntries);
    }

    /**
     * Get categories of json. 
     * 
     * @access public
     * @return string 
     */
    public function getJSONCategories()
    {
        $entries = $this->getEntries();
        $categories = array();
        $this->loadModel('tree');
        foreach($entries as $entry)
        {
            if($entry->category)
            {
                unset($tmpCategory);
                $tmpCategory['id']   = $entry->category;
                $tmpCategory['name'] = $this->dao->select('name')->from(TABLE_CATEGORY)->where('id')->eq($entry->category)->fetch('name');

                $categories[] = $tmpCategory;
            }
        }
        return helper::jsonEncode($categories);
    }
}
