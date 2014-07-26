<?php

use \Innomatic\Core\InnomaticContainer;
use \Innomatic\Wui\Widgets;
use \Innomatic\Wui\Dispatch;
use \Innomatic\Locale\LocaleCatalog;
use \Innomatic\Domain\User;
use \Shared\Wui;

class InnoworkbugsprefsPanelActions extends \Innomatic\Desktop\Panel\PanelActions
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
            'innowork-bugs::innoworkbugs_domain_prefs',
            \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getCurrentUser()->getLanguage()
       );
    }

    public function endHelper()
    {
    }

    public function executeAddfield($eventData)
    {
        $field = new InnoworkBugField(
            \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getCurrentDomain()->getDataAccess(),
            $eventData['fieldtype']
        );
    
        if ($field->NewValue($eventData['value'])) {
            $this->status = $this->localeCatalog->getStr('field_added.status');
        } else {
            $this->status = $this->localeCatalog->getStr('field_not_added.status');
        }
        
        $this->setChanged();
        $this->notifyObservers('status');
    }

    public function executeEditfield($eventData)
    {
        $field = new InnoworkBugField(
            \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getCurrentDomain()->getDataAccess(),
            '',
            $eventData['id']
        );
    
        if ($field->editValue($eventData['value'], $eventData['fieldtype'])) {
            $this->status = $this->localeCatalog->getStr('field_updated.status');
        } else {
            $this->status = $this->localeCatalog->getStr('field_not_updated.status');
        }
        
        $this->setChanged();
        $this->notifyObservers('status');
    }
    
    public function executeRemovefield($eventData)
    {
        $field = new InnoworkBugField(
            \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getCurrentDomain()->getDataAccess(),
            $eventData['fieldtype'],
            $eventData['id']
        );
    
        if ($field->RemoveValue($eventData['value'])) {
            $this->status = $this->localeCatalog->getStr('field_removed.status');
        } else {
            $this->status = $this->localeCatalog->getStr('field_not_removed.status');
        }
        
        $this->setChanged();
        $this->notifyObservers('status');
    }
}
