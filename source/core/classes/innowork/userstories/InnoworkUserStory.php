<?php

require_once('innowork/core/InnoworkItem.php');

class InnoworkUserStory extends InnoworkItem
{
    public $mTable = 'innowork_userstories';
    public $mNewDispatcher = 'view';
    public $mNewEvent = 'newuserstory';
    public $mNoTrash = false;
    public $mConvertible = true;
    public $mTypeTags = array('task', 'userstory');
    public $mParentType = 'project';
    public $mParentIdField = 'projectid';
    const ITEM_TYPE = 'userstory';

    public function __construct($rrootDb, $rdomainDA, $storyId = 0)
    {
        parent::__construct($rrootDb, $rdomainDA, InnoworkUserStory::ITEM_TYPE, $storyId);

        $this->mKeys['title'] = 'text';
        $this->mKeys['description'] = 'text';
        $this->mKeys['accepcriteria'] = 'text';
        $this->mKeys['projectid'] = 'table:innowork_projects:name:integer';
        $this->mKeys['statusid'] = 'table:innowork_userstories_fields_values:fieldvalue:integer';
        $this->mKeys['priorityid'] = 'table:innowork_userstories_fields_values:fieldvalue:integer';
        $this->mKeys['creationdate'] = 'timestamp';
        $this->mKeys['done'] = 'boolean';
        $this->mKeys['openedby'] = 'userid';
        $this->mKeys['assignedto'] = 'userid';
        $this->mKeys['iterationid'] = 'integer';

        $this->mSearchResultKeys[] = 'title';
        $this->mSearchResultKeys[] = 'projectid';
        $this->mSearchResultKeys[] = 'statusid';
        $this->mSearchResultKeys[] = 'priorityid';
        $this->mSearchResultKeys[] = 'creationdate';
        $this->mSearchResultKeys[] = 'done';
        $this->mSearchResultKeys[] = 'openedby';
        $this->mSearchResultKeys[] = 'assignedto';

        $this->mViewableSearchResultKeys[] = 'id';
        $this->mViewableSearchResultKeys[] = 'title';
        $this->mViewableSearchResultKeys[] = 'projectid';
        $this->mViewableSearchResultKeys[] = 'statusid';
        $this->mViewableSearchResultKeys[] = 'priorityid';
        $this->mViewableSearchResultKeys[] = 'creationdate';
        $this->mViewableSearchResultKeys[] = 'openedby';
        $this->mViewableSearchResultKeys[] = 'assignedto';

        $this->mSearchOrderBy = 'id DESC';
        $this->mShowDispatcher = 'view';
        $this->mShowEvent = 'showuserstory';

        $this->mGenericFields['projectid'] = 'projectid';
        $this->mGenericFields['title'] = 'title';
        $this->mGenericFields['content'] = 'description';
        $this->mGenericFields['binarycontent'] = '';
    }

    public function doCreate(
        $params,
        $userId
        )
    {
        $result = false;

            if ( $params['done'] == 'true' ) $params['done'] = $this->mrDomainDA->fmttrue;
            else $params['done'] = $this->mrDomainDA->fmtfalse;

            if (
                !isset($params['projectid'] )
                or !strlen( $params['projectid'] )
                ) $params['projectid'] = '0';

            if (
                !isset($params['statusid'] )
                or !strlen( $params['statusid'] )
                ) $params['statusid'] = '0';

            if (
                !isset($params['iterationid'] )
                or !strlen( $params['iterationid'] )
                ) $params['iterationid'] = '0';
            if (
                !isset($params['priorityid'] )
                or !strlen( $params['priorityid'] )
                ) $params['priorityid'] = '0';

            if (!isset($params['openedby']) or !strlen($params['openedby'])) {
            	$params['openedby'] = '0';
            }

            if (!isset($params['assignedto']) or !strlen($params['assignedto'])) {
            	$params['assignedto'] = '0';
            }

        if (count($params)) {
            $item_id = $this->mrDomainDA->getNextSequenceValue( $this->mTable.'_id_seq' );

            $params['trashed'] = $this->mrDomainDA->fmtfalse;
            $params['creationdate']['year'] = date( 'Y' );
            $params['creationdate']['mon'] = date( 'n' );
            $params['creationdate']['mday'] = date( 'd' );
            $params['creationdate']['hours'] = date( 'H' );
            $params['creationdate']['minutes'] = date( 'i' );
            $params['creationdate']['seconds'] = date( 's' );

            $timestamp = $this->mrDomainDA->getTimestampFromDateArray( $date );

            $key_pre = $value_pre = $keys = $values = '';

            while ( list( $key, $val ) = each( $params ) ) {
                $key_pre = ',';
                $value_pre = ',';

                switch ( $key ) {
                case 'title':
                case 'description':
                case 'accepcriteria':
                case 'done':
                case 'trashed':
                    $keys .= $key_pre.$key;
                    $values .= $value_pre.$this->mrDomainDA->formatText( $val );
                    break;

                case 'creationdate':
                    $val = $this->mrDomainDA->getTimestampFromDateArray( $val );
                    unset( $date_array );

                    $keys .= $key_pre.$key;
                    $values .= $value_pre.$this->mrDomainDA->formatText( $val );
                    break;

                case 'projectid':
                case 'statusid':
                case 'priorityid':
                case 'openedby':
                case 'assignedto':
                case 'iterationid':
                    if ( !strlen( $key ) ) $key = 0;
                    $keys .= $key_pre.$key;
                    $values .= $value_pre.$val;
                    break;

                default:
                    break;
                }
            }

            if ( strlen( $values ) ) {
                if ( $this->mrDomainDA->Execute( 'INSERT INTO '.$this->mTable.' '.
                                               '(id,ownerid'.$keys.') '.
                                               'VALUES ('.$item_id.','.
                                               $userId.
                                               $values.')' ) )
                {
                    $result = $item_id;
                }
            }
        }

        //$this->_mCreationAcl = InnoworkAcl::TYPE_PRIVATE;

        return $result;
    }

    public function doEdit(
        $params
        )
    {
        $result = false;

        if ( $this->mItemId ) {
            if ( count( $params ) ) {
                $start = 1;
                $update_str = '';

                if ( isset($params['done'] ) ) {
                    if ( $params['done'] == 'true' ) $params['done'] = $this->mrDomainDA->fmttrue;
                    else $params['done'] = $this->mrDomainDA->fmtfalse;
                }

                while ( list( $field, $value ) = each( $params ) ) {
                    if ( $field != 'id' ) {
                        switch ( $field ) {
                        case 'title':
                        case 'description':
                        case 'accepcriteria':
                        case 'done':
                        case 'trashed':
                            if ( !$start ) $update_str .= ',';
                            $update_str .= $field.'='.$this->mrDomainDA->formatText( $value );
                            $start = 0;
                            break;

                        case 'creationdate':
                            $value = $this->mrDomainDA->getTimestampFromDateArray( $value );
                            unset( $date_array );

                            if ( !$start ) $update_str .= ',';
                            $update_str .= $field.'='.$this->mrDomainDA->formatText( $value );
                            $start = 0;
                            break;

                        case 'projectid':
                        case 'statusid':
                        case 'priorityid':
                		case 'openedby':
                		case 'assignedto':
                        case 'iterationid':
                        	if ( !strlen( $value ) ) $value = 0;
                            if ( !$start ) $update_str .= ',';
                            $update_str .= $field.'='.$value;
                            $start = 0;
                            break;

                        default:
                            break;
                        }
                    }
                }

                $query = &$this->mrDomainDA->Execute(
                    'UPDATE '.$this->mTable.' '.
                    'SET '.$update_str.' '.
                    'WHERE id='.$this->mItemId );

                if ( $query ) $result = true;
            }
        }

        return $result;
    }

    public function doTrash()
    {
        return true;
    }

    public function doRemove(
        $userId
        )
    {
        $result = false;

        $result = $this->mrDomainDA->Execute(
            'DELETE FROM '.$this->mTable.' '.
            'WHERE id='.$this->mItemId
            );

        return $result;
    }

    public function doGetSummary()
    {
        $result = false;

        $userstories = new InnoworkUserStory(
            \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getDataAccess(),
            \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getCurrentDomain()->getDataAccess()
            );
        $userstories_search = $userstories->Search(
            array(
                'done' => \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getCurrentDomain()->getDataAccess()->fmtfalse
                ),
            \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getCurrentUser()->getUserId(),
            false,
            false,
            10,
            0
            );

        $result =
'<vertgroup>
  <children>';

        foreach ( $userstories_search as $story ) {
            $result .=
'<link>
  <args>
    <label type="encoded">'.urlencode( '- '.$story['id'] ).'</label>
    <link type="encoded">'.urlencode(
        WuiEventsCall::buildEventsCallString( 'innoworkuserstories', array(
                array(
                    'view',
                    'showuserstory',
                    array( 'id' => $story['id'] )
                )
            ) )
        ).'</link>
    <compact>true</compact>
  </args>
</link>';
        }

        $result .=
'  </children>
</vertgroup>';

        return $result;
    }
}
