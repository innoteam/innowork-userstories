<?php

namespace Shared\Dashboard;

use \Innomatic\Core\InnomaticContainer;
use \Shared\Wui;
use \Innomatic\Wui\Dispatch;

class InnoworkMyUserstoriesDashboardWidget extends \Innomatic\Desktop\Dashboard\DashboardWidget
{
    public function getWidgetXml()
    {
        $locale_catalog = new \Innomatic\Locale\LocaleCatalog(
            'innowork-userstories::dashboard',
            InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getCurrentUser()->getLanguage()
        );

    	$locale_country = new \Innomatic\Locale\LocaleCountry(
			InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getCurrentUser()->getCountry()
        );

    	require_once('innowork/userstories/InnoworkUserStory.php');

		$userstories = new InnoworkUserStory(
			\Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getDataAccess(),
			\Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getCurrentDomain()->getDataAccess()
		);

		$userstories->mSearchOrderBy = 'id DESC';

		$search_result = $userstories->search(
			array('done' => \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getCurrentDomain()->getDataAccess()->fmtfalse, 'assignedto' => \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getCurrentUser()->getUserId()),
			\Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getCurrentUser()->getUserId()
		);

        $xml =
        '<vertgroup>
           <children>';

        $search_result_count = count($search_result);

        switch ($search_result_count) {
        	case 0:
        		$userstories_number_label = $locale_catalog->getStr('no_userstories.label');
        		break;

        	case 1:
        		$userstories_number_label = sprintf($locale_catalog->getStr('userstory_number.label'), count($search_result));
        		break;

        	default:
        		$userstories_number_label = sprintf($locale_catalog->getStr('userstories_number.label'), count($search_result));
        }

        $xml .= '<label>
               <args>
        		 <label>'.WuiXml::cdata($userstories_number_label).'</label>
        	   </args>
        	 </label>';

        if ($search_result_count > 0) {
        	$xml .= '<label>
               <args>
        		 <label>'.WuiXml::cdata($locale_catalog->getStr('last_opened_userstories.label')).'</label>
        	   </args>
        	 </label>

        	<grid><children>';

        	$row = 0;
        	foreach ($search_result as $userstory) {
        		$xml .= '<link row="'.$row.'" col="0" halign="left" valign="top">
               <args>
        		 <label>'.WuiXml::cdata($userstory['id']).'</label>
        <compact>true</compact>
        <nowrap>false</nowrap>
        <link>'.WuiXml::cdata(\Innomatic\Wui\Dispatch\WuiEventsCall::buildEventsCallString('innoworkuserstories', array(array('view', 'showuserstory', array('id' => $userstory['id']))))).'</link>
        	   </args>
        	 </link>
        	<link row="'.$row.'" col="1" halign="left" valign="top">
               <args>
        		 <label>'.WuiXml::cdata($userstory['title']).'</label>
        <compact>true</compact>
        <nowrap>false</nowrap>
        <link>'.WuiXml::cdata(\Innomatic\Wui\Dispatch\WuiEventsCall::buildEventsCallString('innoworkuserstories', array(array('view', 'showuserstory', array('id' => $userstory['id']))))).'</link>
        	   </args>
        	 </link>';
        		if (++$row == 5) {
        			break;
        		}
        	}

        	$xml .= '</children></grid>';
        }

        $xml .= '<horizbar/>';

        $xml .= '<horizgroup><args><width>0%</width></args><children>';

        $xml .= '
  <button>
    <args>
      <horiz>true</horiz>
      <frame>false</frame>
      <themeimage>mathadd</themeimage>
      <mainaction>true</mainaction>
      <label>'.$locale_catalog->getStr('new_userstory.button').'</label>
      <action>'.WuiXml::cdata(\Innomatic\Wui\Dispatch\WuiEventsCall::buildEventsCallString('innoworkuserstories', array(array('view', 'newuserstory', array())))).'</action>
    </args>
  </button>';

        if (count($search_result) > 0) {
        	$xml .= '  <button>
    <args>
      <horiz>true</horiz>
      <frame>false</frame>
      <themeimage>zoom</themeimage>
      <label>'.$locale_catalog->getStr('show_all_my_userstories.button').'</label>
      <action>'.WuiXml::cdata(\Innomatic\Wui\Dispatch\WuiEventsCall::buildEventsCallString('innoworkuserstories', array(array('view', 'default', array('filter' => 'true', 'filter_assignedto' => \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getCurrentUser()->getUserId()))))).'</action>
    </args>
  </button>';
        }

  $xml .= '</children></horizgroup>

           </children>
         </vertgroup>';

        return $xml;
    }

    public function getWidth()
    {
        return 1;
    }

    public function getHeight()
    {
        return 60;
    }
}
