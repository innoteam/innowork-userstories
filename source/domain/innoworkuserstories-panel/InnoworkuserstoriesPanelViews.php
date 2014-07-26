<?php

use \Innomatic\Core\InnomaticContainer;
use \Innomatic\Wui\Widgets;
use \Shared\Wui;

require_once('innowork/bugs/InnoworkBug.php');
require_once('innowork/bugs/InnoworkBugField.php');
require_once('innowork/projects/InnoworkProject.php');

class InnoworkbugsPanelViews extends \Innomatic\Desktop\Panel\PanelViews
{
    public $pageTitle;
    public $toolbars;
    public $pageStatus;
    public $innoworkCore;
    public $xml;
    protected $localeCatalog;

    public function update($observable, $arg = '')
    {
        switch ($arg) {
            case 'status':
                $this->pageStatus = $this->_controller->getAction()->status;
                break;
        }
    }

    public function beginHelper()
    {
        $this->localeCatalog = new LocaleCatalog(
            'innowork-bugs::innoworkbugs_domain_main',
            \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getCurrentUser()->getLanguage()
        );

        $this->innoworkCore = InnoworkCore::instance('innoworkcore',
            \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getDataAccess(),
            \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getCurrentDomain()->getDataAccess()
        );

$this->pageTitle = $this->localeCatalog->getStr('bugs.title');
$this->toolbars['mail'] = array(
    'bugs' => array(
        'label' => $this->localeCatalog->getStr('bugs.toolbar'),
        'themeimage' => 'listbulletleft',
        'horiz' => 'true',
        'action' => \Innomatic\Wui\Dispatch\WuiEventsCall::buildEventsCallString('', array(array(
            'view',
            'default',
            array('done' => 'false'))))
       ),
    'donebugs' => array(
        'label' => $this->localeCatalog->getStr('donebugs.toolbar'),
        'themeimage' => 'drawer',
        'horiz' => 'true',
        'action' => \Innomatic\Wui\Dispatch\WuiEventsCall::buildEventsCallString('', array(array(
            'view',
            'default',
            array('done' => 'true'))))
       ),
    'newbug' => array(
        'label' => $this->localeCatalog->getStr('newbug.toolbar'),
        'themeimage' => 'mathadd',
        'horiz' => 'true',
        'action' => \Innomatic\Wui\Dispatch\WuiEventsCall::buildEventsCallString('', array(array(
            'view',
            'newbug',
            '')))
       )
   );
    }

    public function endHelper()
    {
        $this->_wuiContainer->addChild(new WuiInnomaticPage('page', array(
    'pagetitle' => $this->pageTitle,
    'icon' => 'folder',
    'toolbars' => array(
        new WuiInnomaticToolbar(
            'view',
            array(
                'toolbars' => $this->toolbars, 'toolbar' => 'true'
               )),
        new WuiInnomaticToolBar(
            'core',
            array(
                'toolbars' => $this->innoworkCore->getMainToolBar(), 'toolbar' => 'true'
               ))
           ),
    'maincontent' => new WuiXml(
        'page', array(
            'definition' => $this->xml
           )),
    'status' => $this->pageStatus
   )));
    }

    public function viewDefault($eventData)
    {
        $innowork_projects = new InnoworkProject(
                \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getDataAccess(),
                \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getCurrentDomain()->getDataAccess()
        );
        $search_results = $innowork_projects->Search(
                '',
                \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getCurrentUser()->getUserId()
        );

        $projects['0'] = $this->localeCatalog->getStr('allprojects.label');
        while (list($id, $fields) = each($search_results)) {
            $projects[$id] = $fields['name'];
        }

        $statuses = InnoworkBugField::getFields(InnoworkBugField::TYPE_STATUS);
        $statuses['0'] = $this->localeCatalog->getStr('allstatuses.label');

        $priorities = InnoworkBugField::getFields(InnoworkBugField::TYPE_PRIORITY);
        $priorities['0'] = $this->localeCatalog->getStr('allpriorities.label');

        $sources = InnoworkBugField::getFields(InnoworkBugField::TYPE_SOURCE);
        $sources['0'] = $this->localeCatalog->getStr('allsources.label');

        $resolutions = InnoworkBugField::getFields(InnoworkBugField::TYPE_RESOLUTION);
        $resolutions['0'] = $this->localeCatalog->getStr('allresolutions.label');

        $types = InnoworkBugField::getFields(InnoworkBugField::TYPE_SEVERITY);
        $types['0'] = $this->localeCatalog->getStr('alltypes.label');

        // Filtering

        if (isset($eventData['filter'])) {
            // Project

            $project_filter_sk = new WuiSessionKey(
                    'project_filter',
                    array(
                            'value' => $eventData['filter_projectid']
                    )
            );

            if ($eventData['filter_projectid'] != 0) $search_keys['projectid'] = $eventData['filter_projectid'];

            // Priority

            $priority_filter_sk = new WuiSessionKey(
                    'priority_filter',
                    array(
                            'value' => $eventData['filter_priorityid']
                    )
            );

            if ($eventData['filter_priorityid'] != 0) $search_keys['priorityid'] = $eventData['filter_priorityid'];

            // Status

            $status_filter_sk = new WuiSessionKey(
                    'status_filter',
                    array(
                            'value' => $eventData['filter_statusid']
                    )
            );

            if ($eventData['filter_statusid'] != 0) $search_keys['statusid'] = $eventData['filter_statusid'];

            // Source

            $source_filter_sk = new WuiSessionKey(
                    'source_filter',
                    array(
                            'value' => $eventData['filter_sourceid']
                    )
            );

            if ($eventData['filter_sourceid'] != 0) $search_keys['sourceid'] = $eventData['filter_sourceid'];

            // Type
            $type_filter_sk = new WuiSessionKey('type_filter', array('value' => $eventData['filter_severityid']));

            if ($eventData['filter_severityid'] != 0) {
                $search_keys['severityid'] = $eventData['filter_severityid'];
            }

            // Resolution

            $resolution_filter_sk = new WuiSessionKey(
                    'resolution_filter',
                    array(
                            'value' => $eventData['filter_resolutionid']
                    )
            );

            if ($eventData['filter_resolutionid'] != 0) $search_keys['resolutionid'] = $eventData['filter_resolutionid'];

            // Year

            if (isset($eventData['filter_year'])) $_filter_year = $eventData['filter_year'];

            $year_filter_sk = new WuiSessionKey(
                    'year_filter',
                    array(
                            'value' => isset($eventData['filter_year']) ? $eventData['filter_year'] : ''
                    )
            );

            // Month

            if (isset($eventData['filter_month'])) $_filter_month = $eventData['filter_month'];

            $month_filter_sk = new WuiSessionKey(
                    'month_filter',
                    array(
                            'value' => isset($eventData['filter_month']) ? $eventData['filter_month'] : ''
                    )
            );

            // Day

            if (isset($eventData['filter_day'])) $_filter_day = $eventData['filter_day'];

            $day_filter_sk = new WuiSessionKey(
                    'day_filter',
                    array(
                            'value' => isset($eventData['filter_day']) ? $eventData['filter_day'] : ''
                    )
            );

            // Opened by
            $openedby_filter_sk = new WuiSessionKey('openedby_filter', array('value' => isset($eventData['filter_openedby']) ? $eventData['filter_openedby'] : ''));
            if ($eventData['filter_openedby'] != 0) {
                $search_keys['openedby'] = $eventData['filter_openedby'];
            }

            // Assigned to
            $assignedto_filter_sk = new WuiSessionKey('assignedto_filter', array('value' => isset($eventData['filter_assignedto']) ? $eventData['filter_assignedto'] : ''));
            if ($eventData['filter_assignedto'] != 0) {
                $search_keys['assignedto'] = $eventData['filter_assignedto'];
            }
        } else {
            // Project

            $project_filter_sk = new WuiSessionKey('project_filter');
            if (
            strlen($project_filter_sk->mValue)
            and $project_filter_sk->mValue != 0
            ) $search_keys['projectid'] = $project_filter_sk->mValue;
            $eventData['filter_projectid'] = $project_filter_sk->mValue;

            // Priority

            $priority_filter_sk = new WuiSessionKey('priority_filter');
            if (
            strlen($priority_filter_sk->mValue)
            and $priority_filter_sk->mValue != 0
            ) $search_keys['priorityid'] = $priority_filter_sk->mValue;
            $eventData['filter_priorityid'] = $priority_filter_sk->mValue;

            // Status

            $status_filter_sk = new WuiSessionKey('status_filter');
            if (
            strlen($status_filter_sk->mValue)
            and $status_filter_sk->mValue != 0
            ) $search_keys['statusid'] = $status_filter_sk->mValue;
            $eventData['filter_statusid'] = $status_filter_sk->mValue;

            // Source

            $source_filter_sk = new WuiSessionKey('source_filter');
            if (
            strlen($source_filter_sk->mValue)
            and $source_filter_sk->mValue != 0
            ) $search_keys['sourceid'] = $source_filter_sk->mValue;
            $eventData['filter_sourceid'] = $source_filter_sk->mValue;

            // Type

            $type_filter_sk = new WuiSessionKey('type_filter');
            if (strlen($type_filter_sk->mValue) and $type_filter_sk->mValue != 0) {
                $search_keys['severityid'] = $type_filter_sk->mValue;
            }

            $eventData['filter_severityid'] = $type_filter_sk->mValue;

            // Resolution

            $resolution_filter_sk = new WuiSessionKey('resolution_filter');
            if (
            strlen($resolution_filter_sk->mValue)
            and $resolution_filter_sk->mValue != 0
            ) $search_keys['resolutionid'] = $resolution_filter_sk->mValue;
            $eventData['filter_resolutionid'] = $resolution_filter_sk->mValue;

            // Year

            $year_filter_sk = new WuiSessionKey('year_filter');
            if (strlen($year_filter_sk->mValue) and $year_filter_sk->mValue != 0) $_filter_year = $year_filter_sk->mValue;
            $eventData['filter_year'] = $year_filter_sk->mValue;

            // Month

            $month_filter_sk = new WuiSessionKey('month_filter');
            if (strlen($month_filter_sk->mValue) and $month_filter_sk->mValue != 0) $_filter_month = $month_filter_sk->mValue;
            $eventData['filter_month'] = $month_filter_sk->mValue;

            // Day

            $day_filter_sk = new WuiSessionKey('day_filter');
            if (strlen($day_filter_sk->mValue) and $day_filter_sk->mValue != 0) $_filter_day = $day_filter_sk->mValue;
            $eventData['filter_day'] = $day_filter_sk->mValue;

            // Opened by
            $openedby_filter_sk = new WuiSessionKey('openedby_filter');
            $eventData['filter_openedby'] = $openedby_filter_sk->mValue;

            // Assigned to
            $assignedto_filter_sk = new WuiSessionKey('assignedto_filter');
            $eventData['filter_assignedto'] = $assignedto_filter_sk->mValue;
        }

        if (
        isset($_filter_year)
        or
        isset($_filter_month)
        or
        isset($_filter_day)
        )
        {
            $search_keys['creationdate'] =
            ((isset($_filter_year) and strlen($_filter_year)) ? str_pad($_filter_year, 4, '0', STR_PAD_LEFT) : '%').'-'.
            ((isset($_filter_month) and strlen($_filter_month)) ? str_pad($_filter_month, 2, '0', STR_PAD_LEFT) : '%').'-'.
            ((isset($_filter_day) and strlen($_filter_day)) ? str_pad($_filter_day, 2, '0', STR_PAD_LEFT) : '%');
        }

        $users_query = \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getCurrentDomain()->getDataAccess()->execute(
                'SELECT id,fname,lname '.
                'FROM domain_users '.
                'WHERE username<>'.\Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getCurrentDomain()->getDataAccess()->formatText(User::getAdminUsername(\Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getCurrentDomain()->getDomainId())).' '.
                'ORDER BY lname,fname');

        $users = array();
        $users[''] = $this->localeCatalog->getStr('filter_allusers.label');

        while (!$users_query->eof) {
            $users[$users_query->getFields('id')] = $users_query->getFields('lname').' '.$users_query->getFields('fname');
            $users_query->moveNext();
        }

        if (!isset($search_keys) or !count($search_keys)) $search_keys = '';

        // Sorting

        $tab_sess = new WuiSessionKey('innoworkbugstab');

        if (!isset($eventData['done'])) $eventData['done'] = $tab_sess->mValue;
        if (!strlen($eventData['done'])) $eventData['done'] = 'false';

        $tab_sess = new WuiSessionKey(
                'innoworkbugstab',
                array(
                        'value' => $eventData['done']
                )
        );

        $country = new \Innomatic\Locale\LocaleCountry(
                \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getCurrentUser()->getCountry()
        );

        $summaries = $this->innoworkCore->getSummaries();

        $table = new WuiTable('bugs_done_'.$eventData['done'], array(
                'sessionobjectusername' => $eventData['done'] == 'true' ? 'done' : 'undone'
        ));
        $sort_by = 0;
        if (strlen($table->mSortDirection)) $sort_order = $table->mSortDirection;
        else $sort_order = 'down';

        if (isset($eventData['sortby'])) {
            if ($table->mSortBy == $eventData['sortby']) {
                $sort_order = $sort_order == 'down' ? 'up' : 'down';
            } else {
                $sort_order = 'down';
            }

            $sort_by = $eventData['sortby'];
        } else {
            if (strlen($table->mSortBy)) $sort_by = $table->mSortBy;
        }

        $bugs = new InnoworkBug(
                \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getDataAccess(),
                \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getCurrentDomain()->getDataAccess()
        );

        switch ($sort_by) {
            case '1':
                $bugs->mSearchOrderBy = 'id'.($sort_order == 'up' ? ' DESC' : '');
                break;
            case '2':
                $bugs->mSearchOrderBy = 'projectid'.($sort_order == 'up' ? ' DESC' : '');
                break;
            case '3':
                $bugs->mSearchOrderBy = 'title'.($sort_order == 'up' ? ' DESC' : '');
                break;
            case '4':
                $bugs->mSearchOrderBy = 'openedby'.($sort_order == 'up' ? ' DESC' : '');
                break;
            case '5':
                $bugs->mSearchOrderBy = 'assignedto'.($sort_order == 'up' ? ' DESC' : '');
                break;
            case '6':
                $bugs->mSearchOrderBy = 'priorityid'.($sort_order == 'up' ? ' DESC' : '');
                break;
            case '7':
                $bugs->mSearchOrderBy = 'statusid'.($sort_order == 'up' ? ' DESC' : '');
                break;
            case '8':
                $bugs->mSearchOrderBy = 'sourceid'.($sort_order == 'up' ? ' DESC' : '');
                break;
        }

        if (
        isset($eventData['done'])
        and $eventData['done'] == 'true'
                )
        {
            $done_check = \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getCurrentDomain()->getDataAccess()->fmttrue;
            $done_icon = 'misc3';
            $done_action = 'false';
            $done_label = 'setundone.button';
        } else {
            $done_check = \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getCurrentDomain()->getDataAccess()->fmtfalse;
            $done_icon = 'drawer';
            $done_action = 'true';
            $done_label = 'setdone.button';
        }

        $search_keys['done'] = $done_check;

        $bugs_search = $bugs->Search(
                $search_keys,
                \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getCurrentUser()->getUserId(),
                false,
                false,
                0,
                0
        );

        $num_bugs = count($bugs_search);

        $headers[0]['label'] = $this->localeCatalog->getStr('bug.header');
        $headers[0]['link'] = \Innomatic\Wui\Dispatch\WuiEventsCall::buildEventsCallString('',
                array(array(
                        'view',
                        'default',
                        array('sortby' => '1')
                )));
        $headers[1]['label'] = $this->localeCatalog->getStr('project.header');
        $headers[1]['link'] = \Innomatic\Wui\Dispatch\WuiEventsCall::buildEventsCallString('',
                array(array(
                        'view',
                        'default',
                        array('sortby' => '2')
                )));
        $headers[2]['label'] = $this->localeCatalog->getStr('title.header');
        $headers[2]['link'] = \Innomatic\Wui\Dispatch\WuiEventsCall::buildEventsCallString('',
                array(array(
                        'view',
                        'default',
                        array('sortby' => '3')
                )));
        $headers[3]['label'] = $this->localeCatalog->getStr('openedby.header');
        $headers[3]['link'] = \Innomatic\Wui\Dispatch\WuiEventsCall::buildEventsCallString('', array(array('view', 'default', array('sortby' => '4'))));

        $headers[4]['label'] = $this->localeCatalog->getStr('assignedto.header');
        $headers[4]['link'] = \Innomatic\Wui\Dispatch\WuiEventsCall::buildEventsCallString('', array(array('view', 'default', array('sortby' => '5'))));

        $headers[5]['label'] = $this->localeCatalog->getStr('type.header');
        $headers[5]['link'] = \Innomatic\Wui\Dispatch\WuiEventsCall::buildEventsCallString('', array(array('view', 'default', array('sortby' => '6'))));

        $headers[6]['label'] = $this->localeCatalog->getStr('priority.header');
        $headers[6]['link'] = \Innomatic\Wui\Dispatch\WuiEventsCall::buildEventsCallString('',
                array(array(
                        'view',
                        'default',
                        array('sortby' => '7')
                )));
        $headers[7]['label'] = $this->localeCatalog->getStr('status.header');
        $headers[7]['link'] = \Innomatic\Wui\Dispatch\WuiEventsCall::buildEventsCallString('',
                array(array(
                        'view',
                        'default',
                        array('sortby' => '8')
                )));
        $headers[8]['label'] = $this->localeCatalog->getStr('source.header');
        $headers[8]['link'] = \Innomatic\Wui\Dispatch\WuiEventsCall::buildEventsCallString('',
                array(array(
                        'view',
                        'default',
                        array('sortby' => '9')
                )));

        $this->xml =
        '
<vertgroup>
  <children>

    <label><name>filter</name>
      <args>
        <bold>true</bold>
        <label>'.$this->localeCatalog->getStr('filter.label').'</label>
      </args>
    </label>

    <form><name>filter</name>
      <args>
            <action>'.WuiXml::cdata(\Innomatic\Wui\Dispatch\WuiEventsCall::buildEventsCallString('', array(
                        array(
                                'view',
                                'default',
                                array(
                                        'filter' => 'true'
                                )
                        )
                ))).'</action>
      </args>
      <children>

        <grid>
          <children>

    <label row="0" col="0">
      <args>
        <label>'.$this->localeCatalog->getStr('filter_date.label').'</label>
      </args>
    </label>
    <horizgroup row="0" col="1">
      <children>

    <string><name>filter_day</name>
      <args>
        <disp>view</disp>
        <size>2</size>
        <value>'.(isset($eventData['filter_day']) ? $eventData['filter_day'] : '').'</value>
      </args>
    </string>

    <string row="0" col="1"><name>filter_month</name>
      <args>
        <disp>view</disp>
        <size>2</size>
        <value>'.(isset($eventData['filter_month']) ? $eventData['filter_month'] : '').'</value>
      </args>
    </string>

    <string row="0" col="1"><name>filter_year</name>
      <args>
        <disp>view</disp>
        <size>4</size>
        <value>'.(isset($eventData['filter_year']) ? $eventData['filter_year'] : '').'</value>
      </args>
    </string>

      </children>
    </horizgroup>

        <button row="0" col="4"><name>filter</name>
          <args>
            <themeimage>zoom</themeimage>
            <horiz>true</horiz>
            <frame>false</frame>
            <mainaction>true</mainaction>
            <formsubmit>filter</formsubmit>
            <label>'.WuiXml::cdata($this->localeCatalog->getStr('filter.button')).'</label>
            <action>'.WuiXml::cdata(\Innomatic\Wui\Dispatch\WuiEventsCall::buildEventsCallString('', array(
                        array(
                                'view',
                                'default',
                                array(
                                        'filter' => 'true'
                                )
                        )
                ))).'</action>
          </args>
        </button>

        <button row="1" col="4"><name>erasefilter</name>
          <args>
            <themeimage>buttoncancel</themeimage>
            <horiz>true</horiz>
            <frame>false</frame>
            <formsubmit>filter</formsubmit>
            <label>'.WuiXml::cdata($this->localeCatalog->getStr('erase_filter.button')).'</label>
            <action>'.WuiXml::cdata(\Innomatic\Wui\Dispatch\WuiEventsCall::buildEventsCallString('', array(
                        array('view', 'default', array()),
                        array('action', 'erasefilter', array())
                ))).'</action>
          </args>
        </button>

    <label row="1" col="0"><name>project</name>
      <args>
        <label>'.$this->localeCatalog->getStr('filter_project.label').'</label>
      </args>
    </label>
    <combobox row="1" col="1"><name>filter_projectid</name>
      <args>
        <disp>view</disp>
        <elements type="array">'.WuiXml::encode($projects).'</elements>
        <default>'.(isset($eventData['filter_projectid']) ? $eventData['filter_projectid'] : '').'</default>
      </args>
    </combobox>

        <label row="2" col="0">
          <args>
            <label>'.$this->localeCatalog->getStr('openedby.label').'</label>
          </args>
        </label>

        <combobox row="2" col="1"><name>filter_openedby</name>
          <args>
            <disp>view</disp>
            <elements type="array">'.WuiXml::encode($users).'</elements>
            <default>'.$eventData['filter_openedby'].'</default>
          </args>
        </combobox>

        <label row="3" col="0">
          <args>
            <label>'.$this->localeCatalog->getStr('assignedto.label').'</label>
          </args>
        </label>

        <combobox row="3" col="1"><name>filter_assignedto</name>
          <args>
            <disp>view</disp>
            <elements type="array">'.WuiXml::encode($users).'</elements>
            <default>'.$eventData['filter_assignedto'].'</default>
          </args>
        </combobox>

    <label row="0" col="2">
      <args>
        <label>'.$this->localeCatalog->getStr('filter_type.label').'</label>
      </args>
    </label>
    <combobox row="0" col="3"><name>filter_severityid</name>
      <args>
        <disp>view</disp>
        <elements type="array">'.WuiXml::encode($types).'</elements>
        <default>'.(isset($eventData['filter_severityid']) ? $eventData['filter_severityid'] : '').'</default>
      </args>
    </combobox>

    <label row="1" col="2">
      <args>
        <label>'.$this->localeCatalog->getStr('filter_priority.label').'</label>
      </args>
    </label>
    <combobox row="1" col="3"><name>filter_priorityid</name>
      <args>
        <disp>view</disp>
        <elements type="array">'.WuiXml::encode($priorities).'</elements>
        <default>'.(isset($eventData['filter_priorityid']) ? $eventData['filter_priorityid'] : '').'</default>
      </args>
    </combobox>

    <label row="2" col="2">
      <args>
        <label>'.$this->localeCatalog->getStr('filter_status.label').'</label>
      </args>
    </label>
    <combobox row="2" col="3"><name>filter_statusid</name>
      <args>
        <disp>view</disp>
        <elements type="array">'.WuiXml::encode($statuses).'</elements>
        <default>'.(isset($eventData['filter_statusid']) ? $eventData['filter_statusid'] : '').'</default>
      </args>
    </combobox>

    <label row="3" col="2">
      <args>
        <label>'.$this->localeCatalog->getStr('filter_source.label').'</label>
      </args>
    </label>
    <combobox row="3" col="3"><name>filter_sourceid</name>
      <args>
        <disp>view</disp>
        <elements type="array">'.WuiXml::encode($sources).'</elements>
        <default>'.(isset($eventData['filter_sourceid']) ? $eventData['filter_sourceid'] : '').'</default>
      </args>
    </combobox>

    <label row="4" col="2">
      <args>
        <label>'.$this->localeCatalog->getStr('filter_resolution.label').'</label>
      </args>
    </label>
    <combobox row="4" col="3"><name>filter_resolutionid</name>
      <args>
        <disp>view</disp>
        <elements type="array">'.WuiXml::encode($resolutions).'</elements>
        <default>'.(isset($eventData['filter_resolutionid']) ? $eventData['filter_resolutionid'] : '').'</default>
      </args>
    </combobox>

          </children>
        </grid>

      </children>
    </form>

    <horizbar/>

    <label><name>title</name>
      <args>
        <bold>true</bold>
        <label>'.($this->localeCatalog->getStr(
                    (isset($eventData['done'])
                and $eventData['done'] == 'true') ? 'donebugs.label' : 'bugs.label')).'</label>
      </args>
    </label>

    <table><name>bugs_done_'.$eventData['done'].'</name>
      <args>
        <headers type="array">'.WuiXml::encode($headers).'</headers>
        <rowsperpage>15</rowsperpage>
        <pagesactionfunction>\\bugs_list_action_builder</pagesactionfunction>
        <pagenumber>'.(isset($eventData['pagenumber']) ? $eventData['pagenumber'] : '').'</pagenumber>
        <sessionobjectusername>'.($eventData['done'] == 'true' ? 'done' : 'undone').'</sessionobjectusername>
        <sortby>'.$sort_by.'</sortby>
        <sortdirection>'.$sort_order.'</sortdirection>
        <rows>'.$num_bugs.'</rows>
      </args>
      <children>';

        $row = 0;

        $statuses = InnoworkBugField::getFields(InnoworkBugField::TYPE_STATUS);
        $statuses['0'] = $this->localeCatalog->getStr('nostatus.label');

        $priorities = InnoworkBugField::getFields(InnoworkBugField::TYPE_PRIORITY);
        $priorities['0'] = $this->localeCatalog->getStr('nopriority.label');

        $sources = InnoworkBugField::getFields(InnoworkBugField::TYPE_SOURCE);
        $sources['0'] = $this->localeCatalog->getStr('nosource.label');

        $resolutions = InnoworkBugField::getFields(InnoworkBugField::TYPE_RESOLUTION);
        $resolutions['0'] = $this->localeCatalog->getStr('noresolution.label');

        $types = InnoworkBugField::getFields(InnoworkBugField::TYPE_SEVERITY);
        $types['0'] = $this->localeCatalog->getStr('notype.label');

        $page = 1;

        if (isset($eventData['pagenumber'])) {
            $page = $eventData['pagenumber'];
        } else {
            require_once('shared/wui/WuiTable.php');

            $table = new WuiTable(
                    'bugs_done_'.$eventData['done'],
                    array(
                            'sessionobjectusername' => $eventData['done'] == 'true' ? 'done' : 'undone'
                    )
            );

            $page = $table->mPageNumber;
        }

        if ($page > ceil($num_bugs / 15)) $page = ceil($num_bugs /15);

        $from = ($page * 15) - 15;
        $to = $from + 15 - 1;

        foreach ($bugs_search as $bug) {
            if ($row >= $from and $row <= $to) {
                if ($bug['done'] == $done_check) {
                    switch ($bug['_acl']['type']) {
                        case InnoworkAcl::TYPE_PRIVATE:
                            $image = 'personal';
                            break;

                        case InnoworkAcl::TYPE_PUBLIC:
                        case InnoworkAcl::TYPE_ACL:
                            $image = 'kuser';
                            break;
                    }

                    $tmp_project = new InnoworkProject(
                            \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getDataAccess(),
                            \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getCurrentDomain()->getDataAccess(),
                            $bug['projectid']
                    );

                    $tmp_project_data = $tmp_project->getItem();

                    $users[''] = $this->localeCatalog->getStr('noone.label');
                    $users[0] = $this->localeCatalog->getStr('noone.label');

                    $this->xml .=
                    '<horizgroup row="'.$row.'" col="0">
  <args>
  </args>
  <children>
    <link>
      <args>
        <label>'.WuiXml::cdata($bug['id'].
                    ' - '.
                    $country->FormatShortArrayDate(
                            \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getCurrentDomain()->getDataAccess()->getDateArrayFromTimestamp(
                                    $bug['creationdate'])
                    )
            ).'</label>
        <compact>true</compact>
        <link>'.WuiXml::cdata(
                    \Innomatic\Wui\Dispatch\WuiEventsCall::buildEventsCallString(
                            '',
                            array(
                                    array(
                                            'view',
                                            'showbug',
                                            array(
                                                    'id' => $bug['id']
                                            )
                                    )
                            )
                    )
            ).'</link>
        </args>
    </link>
  </children>
</horizgroup>
<link row="'.$row.'" col="1"><name>project</name>
  <args>
    <link>'.WuiXml::cdata(\Innomatic\Wui\Dispatch\WuiEventsCall::buildEventsCallString(
                $summaries['project']['domainpanel'],
                array(
                        array(
                                $summaries['project']['showdispatcher'],
                                $summaries['project']['showevent'],
                                array('id' => $bug['projectid'])
                        )
                )
        )).'</link>
    <label>'.WuiXml::cdata($tmp_project_data['name']).'</label>
    <compact>true</compact>
    <nowrap>false</nowrap>
  </args>
</link>
<label row="'.$row.'" col="2">
  <args>
    <label>'.WuiXml::cdata($bug['title']).'</label>
    <nowrap>false</nowrap>
  </args>
</label>
<label row="'.$row.'" col="3">
  <args>
    <label>'.WuiXml::cdata($users[$bug['openedby']]).'</label>
    <nowrap>false</nowrap>
  </args>
</label>
<label row="'.$row.'" col="4">
  <args>
    <label>'.WuiXml::cdata($users[$bug['assignedto']]).'</label>
    <nowrap>false</nowrap>
  </args>
</label>
<label row="'.$row.'" col="5">
  <args>
    <label>'.WuiXml::cdata($types[$bug['severityid']]).'</label>
    <nowrap>false</nowrap>
  </args>
</label>
<label row="'.$row.'" col="6">
  <args>
    <label>'.WuiXml::cdata($priorities[$bug['priorityid']]).'</label>
    <nowrap>false</nowrap>
  </args>
</label>
<label row="'.$row.'" col="7">
  <args>
    <label>'.WuiXml::cdata($statuses[$bug['statusid']]).'</label>
    <nowrap>false</nowrap>
  </args>
</label>
<label row="'.$row.'" col="8">
  <args>
    <label>'.WuiXml::cdata($sources[$bug['sourceid']]).'</label>
    <nowrap>false</nowrap>
  </args>
</label>
<innomatictoolbar row="'.$row.'" col="9"><name>tools</name>
  <args>
    <frame>false</frame>
    <toolbars type="array">'.WuiXml::encode(array(
                'view' => array(
                        'show' => array(
                                'label' => $this->localeCatalog->getStr('showbug.button'),
                                'themeimage' => 'zoom',
                                'horiz' => 'true',
                                'action' => \Innomatic\Wui\Dispatch\WuiEventsCall::buildEventsCallString('', array(array(
                                        'view',
                                        'showbug',
                                        array('id' => $bug['id']))))
                        ),
                        'done' => array(
                                'label' => $this->localeCatalog->getStr($done_label),
                                'themeimage' => $done_icon,
                                'horiz' => 'true',
                                'action' => \Innomatic\Wui\Dispatch\WuiEventsCall::buildEventsCallString('', array(
                                        array(
                                                'view',
                                                'default',
                                                ''
                                        ),
                                        array(
                                                'action',
                                                'editbug',
                                                array('id' => $bug['id'], 'done' => $done_action))))
                        ),
                        'trash' => array(
                                'label' => $this->localeCatalog->getStr('trashbug.button'),
                                'themeimage' => 'trash',
                                'horiz' => 'true',
                                'action' => \Innomatic\Wui\Dispatch\WuiEventsCall::buildEventsCallString('', array(
                                        array(
                                                'view',
                                                'default',
                                                ''
                                        ),
                                        array(
                                                'action',
                                                'trashbug',
                                                array('id' => $bug['id']))))
                        )))).'</toolbars>
  </args>
</innomatictoolbar>';

                }
            }
            $row++;
        }

        $this->xml .=
        '      </children>
    </table>

  </children>
</vertgroup>';
    }

    public function viewNewbug(
            $eventData
    )
    {
        // Companies

        require_once('innowork/projects/InnoworkProject.php');
        $innowork_projects = new InnoworkProject(
                \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getDataAccess(),
                \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getCurrentDomain()->getDataAccess()
        );
        $search_results = $innowork_projects->search(
                array('done' => \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getDataAccess()->fmtfalse),
                \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getCurrentUser()->getUserId()
        );
        $projects[0] = $this->localeCatalog->getStr('noproject.label');
        while (list($id, $fields) = each($search_results)) {
            $projects[$id] = $fields['name'];
        }

        $headers[0]['label'] = $this->localeCatalog->getStr('newbug.header');

        $this->xml =
        '
<vertgroup>
  <children>

    <table>
      <args>
        <headers type="array">'.WuiXml::encode($headers).'</headers>
      </args>
      <children>

        <form row="0" col="0"><name>newbug</name>
          <args>
                <action>'.WuiXml::cdata(
                            \Innomatic\Wui\Dispatch\WuiEventsCall::buildEventsCallString(
                                    '',
                                    array(
                                            array(
                                                    'view',
                                                    'showbug'
                                            ),
                                            array(
                                                    'action',
                                                    'newbug'
                                            )
                                    )
                            )
                    ).'</action>
          </args>
          <children>
            <grid>
              <children>

                <label row="0" col="0">
                  <args>
                    <label>'.$this->localeCatalog->getStr('project.label').'</label>
                  </args>
                </label>

    			<string row="0" col="1"><name>projectid</name>
              <args>
            		<id>projectid</id>
            		<autocomplete>true</autocomplete>
            		<autocompleteminlength>2</autocompleteminlength>
            		<autocompletesearchurl>'.WuiXml::cdata(WuiEventsCall::buildEventsCallString(
                    '',
                    array(array('view', 'searchproject'))
                )).'</autocompletesearchurl>
                <disp>action</disp>
                <size>30</size>
              </args>
            		</string>


              </children>
            </grid>
          </children>
        </form>

        <horizgroup row="1" col="0">
          <children>

            <button>
              <args>
                <themeimage>buttonok</themeimage>
                <label>'.$this->localeCatalog->getStr('new_bug.button').'</label>
                <formsubmit>newbug</formsubmit>
                <frame>false</frame>
                <horiz>true</horiz>
                <action>'.WuiXml::cdata(
                            \Innomatic\Wui\Dispatch\WuiEventsCall::buildEventsCallString(
                                    '',
                                    array(
                                            array(
                                                    'view',
                                                    'showbug'
                                            ),
                                            array(
                                                    'action',
                                                    'newbug'
                                            )
                                    )
                            )
                    ).'</action>
              </args>
            </button>

          </children>
        </horizgroup>

      </children>
    </table>

  </children>
</vertgroup>';
    }

    public function viewShowbug(
            $eventData
    )
    {
        $locale_country = new \Innomatic\Locale\LocaleCountry(
            \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getCurrentDomain()->getCountry()
        );

        if (isset($GLOBALS['innowork-bugs']['newbugid'])) {
            $eventData['id'] = $GLOBALS['innowork-bugs']['newbugid'];
            $newBug = true;
        } else {
            $newBug = false;
        }

        $innowork_bug = new InnoworkBug(
            \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getDataAccess(),
            \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getCurrentDomain()->getDataAccess(),
            $eventData['id']
        );

        $bug_data = $innowork_bug->getItem(\Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getCurrentUser()->getUserId());

        // Projects list

        $innowork_projects = new InnoworkProject(
            \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getDataAccess(),
            \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getCurrentDomain()->getDataAccess()
        );
        $search_results = $innowork_projects->search(
            '',
            \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getCurrentUser()->getUserId()
        );

        $projects['0'] = $this->localeCatalog->getStr('noproject.label');

        while (list($id, $fields) = each($search_results)) {
            $projects[$id] = $fields['name'];
        }

        // "Assigned to" user
        if ($bug_data['assignedto'] != '') {
            $assignedto_user = $bug_data['assignedto'];
        } else {
            $assignedto_user = \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getCurrentUser()->getUserId();
        }

        // "Opened by" user
        if ($bug_data['openedby'] != '') {
            $openedby_user = $bug_data['openedby'];
        } else {
            $openedby_user = \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getCurrentUser()->getUserId();
        }

        $users_query = \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getCurrentDomain()->getDataAccess()->execute(
                'SELECT id,fname,lname '.
                'FROM domain_users '.
                'WHERE username<>'.\Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getCurrentDomain()->getDataAccess()->formatText(User::getAdminUsername(\Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getCurrentDomain()->getDomainId())).' '.
                'ORDER BY lname,fname');

        $users = array();
        $users[0] = $this->localeCatalog->getStr('noone.label');

        while (!$users_query->eof) {
            $users[$users_query->getFields('id')] = $users_query->getFields('lname').' '.$users_query->getFields('fname');
            $users_query->moveNext();
        }

        $statuses = InnoworkBugField::getFields(InnoworkBugField::TYPE_STATUS);
        if (($newTicket == false and $bug_data['statusid'] == 0) or !count($statuses)) {
            $statuses['0'] = $this->localeCatalog->getStr('nostatus.label');
        }

        $priorities = InnoworkBugField::getFields(InnoworkBugField::TYPE_PRIORITY);
        if (($newTicket == false and $bug_data['priorityid'] == 0) or !count($priorities)) {
            $priorities['0'] = $this->localeCatalog->getStr('nopriority.label');
        }

        $sources = InnoworkBugField::getFields(InnoworkBugField::TYPE_SOURCE);
        if (($newTicket == false and $bug_data['sourceid'] == 0) or !count($sources)) {
            $sources['0'] = $this->localeCatalog->getStr('nosource.label');
        }

        $resolutions = InnoworkBugField::getFields(InnoworkBugField::TYPE_RESOLUTION);
        if (($newTicket == false and $bug_data['resolutionid'] == 0) or !count($resolutions)) {
            $resolutions['0'] = $this->localeCatalog->getStr('noresolution.label');
        }

        $types = InnoworkBugField::getFields(InnoworkBugField::TYPE_SEVERITY);
        if (($newTicket == false and $bug_data['severityid'] == 0) or !count($types)) {
            $types['0'] = $this->localeCatalog->getStr('notype.label');
        }

        if ($bug_data['done'] == \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getCurrentDomain()->getDataAccess()->fmttrue) {
            $done_icon = 'misc3';
            $done_action = 'false';
            $done_label = 'setundone.button';
        } else {
            $done_icon = 'drawer';
            $done_action = 'true';
            $done_label = 'archive_bug.button';
        }

        $headers[0]['label'] = sprintf($this->localeCatalog->getStr('showbug.header'), $bug_data['id']).(strlen($bug_data['title']) ? ' - '.$bug_data['title'] : '');

        $this->xml =
        '
<horizgroup>
  <children>

    <table><name>bug</name>
      <args>
        <headers type="array">'.WuiXml::encode($headers).'</headers>
      </args>
      <children>

        <form row="0" col="0"><name>bug</name>
          <args>
                <action>'.WuiXml::cdata(
                            \Innomatic\Wui\Dispatch\WuiEventsCall::buildEventsCallString(
                                    '',
                                    array(
                                            array(
                                                    'view',
                                                    'showbug',
                                                    array(
                                                            'id' => $eventData['id']
                                                    )
                                            ),
                                            array(
                                                    'action',
                                                    'editbug',
                                                    array(
                                                            'id' => $eventData['id']
                                                    )
                                            )
                                    )
                            )
                    ).'</action>
          </args>
          <children>

            <vertgroup>
              <children>

                <horizgroup>
                  <args>
                    <align>middle</align>
                    <width>0%</width>
                  </args>
                  <children>

                    <label>
                      <args>
                        <label>'.$this->localeCatalog->getStr('project.label').'</label>
                      </args>
                    </label>

                    <combobox><name>projectid</name>
                      <args>
                        <disp>action</disp>
                        <elements type="array">'.WuiXml::encode($projects).'</elements>
                        <default>'.$bug_data['projectid'].'</default>
                      </args>
                    </combobox>

                  </children>
                </horizgroup>

                <horizgroup><args><width>0%</width></args><children>

            <label><name>openedby</name>
              <args>
                <label>'.WuiXml::cdata($this->localeCatalog->getStr('openedby.label')).'</label>
              </args>
            </label>
            <combobox><name>openedby</name>
              <args>
                <disp>action</disp>
                <elements type="array">'.WuiXml::encode($users).'</elements>
                <default>'.$openedby_user.'</default>
              </args>
            </combobox>

            <label><name>assignedto</name>
              <args>
                <label>'.WuiXml::cdata($this->localeCatalog->getStr('assignedto.label')).'</label>
              </args>
            </label>
            <combobox><name>assignedto</name>
              <args>
                <disp>action</disp>
                <elements type="array">'.WuiXml::encode($users).'</elements>
                <default>'.$assignedto_user.'</default>
              </args>
            </combobox>

                </children></horizgroup>

                <horizbar/>

                <grid>
                  <children>

                    <label row="0" col="0" halign="right">
                      <args>
                        <label>'.$this->localeCatalog->getStr('type.label').'</label>
                      </args>
                    </label>

                    <combobox row="0" col="1"><name>severityid</name>
                      <args>
                        <disp>action</disp>
                        <elements type="array">'.WuiXml::encode($types).'</elements>
                        <default>'.$bug_data['severityid'].'</default>
                      </args>
                    </combobox>

                    <label row="0" col="2" halign="right">
                      <args>
                        <label>'.$this->localeCatalog->getStr('status.label').'</label>
                      </args>
                    </label>

                    <combobox row="0" col="3"><name>statusid</name>
                      <args>
                        <disp>action</disp>
                        <elements type="array">'.WuiXml::encode($statuses).'</elements>
                        <default>'.$bug_data['statusid'].'</default>
                      </args>
                    </combobox>

                    <label row="0" col="4" halign="right">
                      <args>
                        <label>'.$this->localeCatalog->getStr('priority.label').'</label>
                      </args>
                    </label>

                    <combobox row="0" col="5"><name>priorityid</name>
                      <args>
                        <disp>action</disp>
                        <elements type="array">'.WuiXml::encode($priorities).'</elements>
                        <default>'.$bug_data['priorityid'].'</default>
                      </args>
                    </combobox>

                    <label row="1" col="0" halign="right">
                      <args>
                        <label>'.$this->localeCatalog->getStr('source.label').'</label>
                      </args>
                    </label>

                    <combobox row="1" col="1"><name>sourceid</name>
                      <args>
                        <disp>action</disp>
                        <elements type="array">'.WuiXml::encode($sources).'</elements>
                        <default>'.$bug_data['sourceid'].'</default>
                      </args>
                    </combobox>

                    <label row="1" col="2" halign="right">
                      <args>
                        <label>'.$this->localeCatalog->getStr('resolution.label').'</label>
                      </args>
                    </label>

                    <combobox row="1" col="3"><name>resolutionid</name>
                      <args>
                        <disp>action</disp>
                        <elements type="array">'.WuiXml::encode($resolutions).'</elements>
                        <default>'.$bug_data['resolutionid'].'</default>
                      </args>
                    </combobox>

                  </children>
                </grid>

                <horizbar/>

                <horizgroup><args><width>0%</width></args>
                  <children>

                    <label>
                      <args>
                        <label>'.$this->localeCatalog->getStr('title.label').'</label>
                      </args>
                    </label>

                    <string><name>title</name>
                      <args>
                        <disp>action</disp>
                        <size>80</size>
                        <value>'.WuiXml::cdata($bug_data['title']).'</value>
                      </args>
                    </string>

                  </children>
                </horizgroup>

                <label>
                  <args>
                    <label>'.$this->localeCatalog->getStr('description.label').'</label>
                  </args>
                </label>

                <text><name>description</name>
                  <args>
                    <disp>action</disp>
                    <rows>6</rows>
                    <cols>100</cols>
                    <value>'.WuiXml::cdata($bug_data['description']).'</value>
                  </args>
                </text>

                <label>
                  <args>
                    <label>'.$this->localeCatalog->getStr('steps.label').'</label>
                  </args>
                </label>

                <text><name>steps</name>
                  <args>
                    <disp>action</disp>
                    <rows>6</rows>
                    <cols>100</cols>
                    <value>'.WuiXml::cdata($bug_data['steps']).'</value>
                  </args>
                </text>


                <label>
                  <args>
                    <label>'.$this->localeCatalog->getStr('solution.label').'</label>
                  </args>
                </label>

                <text><name>solution</name>
                  <args>
                    <disp>action</disp>
                    <rows>6</rows>
                    <cols>100</cols>
                    <value>'.WuiXml::cdata($bug_data['solution']).'</value>
                  </args>
                </text>

              </children>
            </vertgroup>

          </children>
        </form>

        <horizgroup row="1" col="0">
          <args><width>0%</width></args>
          <children>
            <button>
              <args>
                <themeimage>buttonok</themeimage>
                <label>'.WuiXml::cdata($this->localeCatalog->getStr('update_bug.button')).'</label>
                <formsubmit>bug</formsubmit>
                <frame>false</frame>
                <mainaction>true</mainaction>
                <horiz>true</horiz>
                <action>'.WuiXml::cdata(
                            \Innomatic\Wui\Dispatch\WuiEventsCall::buildEventsCallString(
                                    '',
                                    array(
                                            array(
                                                    'view',
                                                    'showbug',
                                                    array(
                                                            'id' => $eventData['id']
                                                    )
                                            ),
                                            array(
                                                    'action',
                                                    'editbug',
                                                    array(
                                                            'id' => $eventData['id']
                                                    )
                                            )
                                    )
                            )
                    ).'</action>
              </args>
            </button>

            <button>
              <args>
                <themeimage>attach</themeimage>
                <label>'.$this->localeCatalog->getStr('add_message.button').'</label>
                <frame>false</frame>
                <horiz>true</horiz>
                <target>messages</target>
                <action>'.WuiXml::cdata(
                            \Innomatic\Wui\Dispatch\WuiEventsCall::buildEventsCallString(
                                    '',
                                    array(
                                            array(
                                                    'view',
                                                    'addmessage',
                                                    array(
                                                            'bugid' => $eventData['id']
                                                    )
                                            )
                                    )
                            )
                    ).'</action>
              </args>
            </button>

        <button><name>setdone</name>
          <args>
            <themeimage>'.$done_icon.'</themeimage>
            <horiz>true</horiz>
            <frame>false</frame>
            <action>'.WuiXml::cdata(\Innomatic\Wui\Dispatch\WuiEventsCall::buildEventsCallString('', array(
                        array(
                                'view',
                                'default',
                                ''
                        ),
                        array(
                                'action',
                                'editbug',
                                array(
                                        'id' => $eventData['id'],
                                        'done' => $done_action
                                ))
                ))).'</action>
            <label>'.$this->localeCatalog->getStr($done_label).'</label>
            <formsubmit>bug</formsubmit>
          </args>
        </button>

            <button>
              <args>
                <themeimage>trash</themeimage>
                <label>'.$this->localeCatalog->getStr('trash_bug.button').'</label>
                <frame>false</frame>
                <horiz>true</horiz>
                <dangeraction>true</dangeraction>
                <action>'.WuiXml::cdata(
                            \Innomatic\Wui\Dispatch\WuiEventsCall::buildEventsCallString(
                                    '',
                                    array(
                                            array(
                                                    'view',
                                                    'default'
                                            ),
                                            array(
                                                    'action',
                                                    'trashbug',
                                                    array(
                                                            'id' => $eventData['id']
                                                    )
                                            )
                                    )
                            )
                    ).'</action>
              </args>
            </button>

          </children>
        </horizgroup>

        <iframe row="2" col="0"><name>messages</name>
          <args>
            <width>450</width>
            <height>200</height>
            <source>'.WuiXml::cdata(
                        \Innomatic\Wui\Dispatch\WuiEventsCall::buildEventsCallString(
                                '',
                                array(
                                        array(
                                                'view',
                                                'bugmessages',
                                                array(
                                                        'bugid' => $eventData['id']
                                                )
                                        )
                                )
                        )
                ).'</source>
            <scrolling>auto</scrolling>
          </args>
        </iframe>

      </children>
    </table>

  <innoworkitemacl><name>itemacl</name>
    <args>
      <itemtype>bug</itemtype>
      <itemid>'.$eventData['id'].'</itemid>
      <itemownerid>'.$bug_data['ownerid'].'</itemownerid>
      <defaultaction>'.WuiXml::cdata(\Innomatic\Wui\Dispatch\WuiEventsCall::buildEventsCallString('', array(
            array('view', 'showbug', array('id' => $eventData['id']))))).'</defaultaction>
    </args>
  </innoworkitemacl>

  </children>
</horizgroup>';
    }

    public function viewBugmessages(
            $eventData
    )
    {
        $innowork_bug = new InnoworkBug(
                \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getDataAccess(),
                \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getCurrentDomain()->getDataAccess(),
                $eventData['bugid']
        );

        $messages = $innowork_bug->getMessages();

        $headers[0]['label'] = $this->localeCatalog->getStr('date.header');
        $headers[1]['label'] = $this->localeCatalog->getStr('message.header');

        $this->xml =
        '
<page>
  <args>
    <border>false</border>
  </args>
  <children>
<table><name>bugmessages</name>
  <args>
    <headers type="array">'.WuiXml::encode($headers).'</headers>
  </args>
  <children>';

        $row = 0;

        $country = new \Innomatic\Locale\LocaleCountry(
                \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getCurrentUser()->getCountry()
        );

        foreach ($messages as $message) {
            $this->xml .=
            '<vertgroup row="'.$row.'" col="0" halign="" valign="top">
  <args>
  </args>
  <children>
    <label>
      <args>
        <label>'.WuiXml::cdata(
                    $country->FormatShortArrayDate($message['creationdate'])
            ).'</label>
        <compact>true</compact>
      </args>
    </label>
    <label>
      <args>
        <label>'.WuiXml::cdata(
                    $country->FormatArrayTime($message['creationdate'])
            ).'</label>
        <compact>true</compact>
      </args>
    </label>
    <label>
      <args>
        <label>'.WuiXml::cdata('('.$message['username'].')').'</label>
        <compact>true</compact>
      </args>
    </label>
  </children>
</vertgroup>
<vertgroup row="'.$row.'" col="1" halign="" valign="top">
  <children>
<label>
  <args>
    <label>'.WuiXml::cdata(nl2br($message['content'])).'</label>
    <nowrap>false</nowrap>
  </args>
</label>

  <button>
    <args>
      <horiz>true</horiz>
      <frame>false</frame>
      <themeimage>buttoncancel</themeimage>
      <themeimagetype>mini</themeimagetype>
      <label>'.$this->localeCatalog->getStr('remove_message.button').'</label>
      <needconfirm>true</needconfirm>
      <confirmmessage>'.$this->localeCatalog->getStr('remove_message.confirm').'</confirmmessage>
      <action>'.WuiXml::cdata(
                  \Innomatic\Wui\Dispatch\WuiEventsCall::buildEventsCallString(
                          '',
                          array(
                                  array(
                                          'view',
                                          'bugmessages',
                                          array(
                                                  'bugid' => $eventData['bugid']
                                          )
                                  ),
                                  array(
                                          'action',
                                          'removemessage',
                                          array(
                                                  'bugid' => $eventData['id'],
                                                  'messageid' => $message['id']
                                          )
                                  )
                          )
                  )
          ).'</action>
    </args>
  </button>

  </children>
</vertgroup>';
            $row++;
        }

        $this->xml .=
        '  </children>
</table>
  </children>
</page>';

        $wui = new WuiXml('', array('definition' => $this->xml));
        $wui->Build(new WuiDispatcher('wui'));
        echo $wui->render();

        \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->halt();
    }

    public function viewAddmessage($eventData)
    {
        $innowork_bug = new InnoworkBug(
            \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getDataAccess(),
            \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getCurrentDomain()->getDataAccess(),
            $eventData['bugid']
        );

        $headers[0]['label'] = $this->localeCatalog->getStr('message.header');

        $this->xml =
        '
<page>
  <args>
    <border>false</border>
  </args>
  <children>
<table><name>message</name>
  <args>
    <headers type="array">'.WuiXml::encode($headers).'</headers>
  </args>
  <children>
    <form row="0" col="0"><name>message</name>
      <args>
                <action>'.WuiXml::cdata(
                            \Innomatic\Wui\Dispatch\WuiEventsCall::buildEventsCallString(
                                    '',
                                    array(
                                            array(
                                                    'view',
                                                    'bugmessages',
                                                    array(
                                                            'bugid' => $eventData['bugid']
                                                    )
                                            ),
                                            array(
                                                    'action',
                                                    'newmessage',
                                                    array(
                                                            'bugid' => $eventData['bugid']
                                                    )
                                            )
                                    )
                            )
                    ).'</action>
      </args>
      <children>

        <text><name>content</name>
          <args>
            <disp>action</disp>
            <rows>5</rows>
            <cols>55</cols>
          </args>
        </text>

      </children>
    </form>

        <horizgroup row="1" col="0">
          <children>

            <button>
              <args>
                <themeimage>buttonok</themeimage>
                <label>'.$this->localeCatalog->getStr('add_message.button').'</label>
                <formsubmit>message</formsubmit>
                <frame>false</frame>
                <horiz>true</horiz>
                <action>'.WuiXml::cdata(
                            \Innomatic\Wui\Dispatch\WuiEventsCall::buildEventsCallString(
                                    '',
                                    array(
                                            array(
                                                    'view',
                                                    'bugmessages',
                                                    array(
                                                            'bugid' => $eventData['bugid']
                                                    )
                                            ),
                                            array(
                                                    'action',
                                                    'newmessage',
                                                    array(
                                                            'bugid' => $eventData['bugid']
                                                    )
                                            )
                                    )
                            )
                    ).'</action>
              </args>
            </button>

          </children>
        </horizgroup>

  </children>
</table>
  </children>
</page>';

        $wui = new WuiXml('', array('definition' => $this->xml));
        $wui->Build(new WuiDispatcher('wui'));
        echo $wui->render();

        \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->halt();
    }

    public function viewSearchproject($eventData)
    {
        $domain_da = InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getCurrentDomain()->getDataAccess();

        $query = $domain_da->execute('SELECT id, name FROM innowork_projects WHERE name LIKE "%'.$_GET['term'].'%" AND done <> '.$domain_da->formatText($domain_da->fmttrue));
        $k = 0;

        while (!$query->eof) {
            $content[$k]['id'] = $query->getFields('id');
            $content[$k++]['value'] = $query->getFields('name');
            $query->moveNext();
        }
        echo json_encode($content);
        InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->halt();
    }
}

function bugs_list_action_builder($pageNumber)
{
	return \Innomatic\Wui\Dispatch\WuiEventsCall::buildEventsCallString('', array(array(
			'view',
			'default',
			array('pagenumber' => $pageNumber)
	)));
}
