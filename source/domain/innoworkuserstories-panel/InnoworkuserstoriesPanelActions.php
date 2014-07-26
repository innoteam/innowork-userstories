<?php

use \Innomatic\Core\InnomaticContainer;
use \Innomatic\Wui\Widgets;
use \Innomatic\Wui\Dispatch;
use \Innomatic\Locale\LocaleCatalog;
use \Innomatic\Domain\User;
use \Shared\Wui;

class InnoworkuserstoriesPanelActions extends \Innomatic\Desktop\Panel\PanelActions
{
    private $localeCatalog;
    public $status;

    public function __construct(\Innomatic\Desktop\Panel\PanelController $controller)
    {
        parent::__construct($controller);
    }

    public function beginHelper()
    {
        $this->localeCatalog = new LocaleCatalog(
            'innowork-userstories::domain_main',
            \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getCurrentUser()->getLanguage()
        );
    }

    public function endHelper()
    {
    }

    public function executeNewuserstory($eventData)
    {
    	require_once('innowork/userstories/InnoworkUserStory.php');
    	$userstory = new InnoworkUserStory(
    		\Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getDataAccess(),
    		\Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getCurrentDomain()->getDataAccess()
    	);

    	if (isset($eventData['projectid_id'])) {
    	    $eventData['projectid'] = $eventData['projectid_id'];
    	    unset($eventData['projectid_id']);
    	}

        $eventData['openedby'] = \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getCurrentUser()->getUserId();
        $eventData['assignedto'] = \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getCurrentUser()->getUserId();

    	if ($userstory->create($eventData)) {
    		$GLOBALS['innowork-userstories']['newuserstoryid'] = $userstory->mItemId;
    		$this->status = $this->localeCatalog->getStr('userstory_created.status');
    	} else {
    		$this->status = $this->localeCatalog->getStr('userstory_not_created.status');
    	}

    	$this->setChanged();
    	$this->notifyObservers('status');
    }

    public function executeEdituserstory($eventData)
    {
    	require_once('innowork/userstories/InnoworkUserStory.php');

    	$userstory = new InnoworkUserStory(
    		\Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getDataAccess(),
    		\Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getCurrentDomain()->getDataAccess(),
    		$eventData['id']
    	);

    	if ($userstory->Edit($eventData)) {
    		$this->status = $this->localeCatalog->getStr('userstory_updated.status');
    	} else {
    		$this->status = $this->localeCatalog->getStr('userstory_not_updated.status');
    	}

    	$this->setChanged();
    	$this->notifyObservers('status');
    }

    public function executeTrashuserstory($eventData)
    {
    	require_once('innowork/userstories/InnoworkUserStory.php');

    	$userstory = new InnoworkUserStory(
    		\Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getDataAccess(),
    		\Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getCurrentDomain()->getDataAccess(),
    		$eventData['id']
    	);

    	if ($userstory->trash(\Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getCurrentUser()->getUserId())) {
    		$this->status = $this->localeCatalog->getStr('userstory_trashed.status');
    	} else {
    		$this->status = $this->localeCatalog->getStr('userstory_not_trashed.status');
    	}

    	$this->setChanged();
    	$this->notifyObservers('status');
    }

    public function executeErasefilter($eventData) {
    	$filter_sk = new WuiSessionKey('project_filter', array('value' => ''));
    	$filter_sk = new WuiSessionKey('priority_filter', array('value' => ''));
    	$filter_sk = new WuiSessionKey('status_filter', array('value' => ''));
    	$filter_sk = new WuiSessionKey('source_filter', array('value' => ''));
    	$filter_sk = new WuiSessionKey('resolution_filter', array('value' => ''));
    	$filter_sk = new WuiSessionKey('type_filter', array('value' => ''));
    	$filter_sk = new WuiSessionKey('year_filter', array('value' => ''));
    	$filter_sk = new WuiSessionKey('month_filter', array('value' => ''));
    	$filter_sk = new WuiSessionKey('day_filter', array('value' => ''));
    	$filter_sk = new WuiSessionKey('openedby_filter', array('value' => ''));
    	$filter_sk = new WuiSessionKey('assignedto_filter', array('value' => ''));
    }
}
