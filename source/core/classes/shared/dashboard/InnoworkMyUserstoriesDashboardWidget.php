<?php

namespace Shared\Dashboard;

use \Innomatic\Core\InnomaticContainer;
use \Shared\Wui;
use \Innomatic\Wui\Dispatch;

class InnoworkMyBugsDashboardWidget extends \Innomatic\Desktop\Dashboard\DashboardWidget
{
    public function getWidgetXml()
    {
        $locale_catalog = new \Innomatic\Locale\LocaleCatalog(
            'innowork-bugs::innoworkbugs_dashboard',
            InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getCurrentUser()->getLanguage()
        );

    	$locale_country = new \Innomatic\Locale\LocaleCountry(
			InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getCurrentUser()->getCountry()
        );

    	require_once('innowork/bugs/InnoworkBug.php');

		$bugs = new InnoworkBug(
			\Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getDataAccess(),
			\Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getCurrentDomain()->getDataAccess()
		);

		$bugs->mSearchOrderBy = 'id DESC';

		$search_result = $bugs->search(
			array('done' => \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getCurrentDomain()->getDataAccess()->fmtfalse, 'assignedto' => \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getCurrentUser()->getUserId()),
			\Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getCurrentUser()->getUserId()
		);

        $xml =
        '<vertgroup>
           <children>';

        $search_result_count = count($search_result);

        switch ($search_result_count) {
        	case 0:
        		$bugs_number_label = $locale_catalog->getStr('no_bugs.label');
        		break;

        	case 1:
        		$bugs_number_label = sprintf($locale_catalog->getStr('bug_number.label'), count($search_result));
        		break;

        	default:
        		$bugs_number_label = sprintf($locale_catalog->getStr('bugs_number.label'), count($search_result));
        }

        $xml .= '<label>
               <args>
        		 <label>'.WuiXml::cdata($bugs_number_label).'</label>
        	   </args>
        	 </label>';

        if ($search_result_count > 0) {
        	$xml .= '<label>
               <args>
        		 <label>'.WuiXml::cdata($locale_catalog->getStr('last_opened_bugs.label')).'</label>
        	   </args>
        	 </label>

        	<grid><children>';

        	$row = 0;
        	foreach ($search_result as $bug) {
        		$xml .= '<link row="'.$row.'" col="0" halign="left" valign="top">
               <args>
        		 <label>'.WuiXml::cdata($bug['id']).'</label>
        <compact>true</compact>
        <nowrap>false</nowrap>
        <link>'.WuiXml::cdata(\Innomatic\Wui\Dispatch\WuiEventsCall::buildEventsCallString('innoworkbugs', array(array('view', 'showbug', array('id' => $bug['id']))))).'</link>
        	   </args>
        	 </link>
        	<link row="'.$row.'" col="1" halign="left" valign="top">
               <args>
        		 <label>'.WuiXml::cdata($bug['title']).'</label>
        <compact>true</compact>
        <nowrap>false</nowrap>
        <link>'.WuiXml::cdata(\Innomatic\Wui\Dispatch\WuiEventsCall::buildEventsCallString('innoworkbugs', array(array('view', 'showbug', array('id' => $bug['id']))))).'</link>
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
      <label>'.$locale_catalog->getStr('new_bug.button').'</label>
      <action>'.WuiXml::cdata(\Innomatic\Wui\Dispatch\WuiEventsCall::buildEventsCallString('innoworkbugs', array(array('view', 'newbug', array())))).'</action>
    </args>
  </button>';

        if (count($search_result) > 0) {
        	$xml .= '  <button>
    <args>
      <horiz>true</horiz>
      <frame>false</frame>
      <themeimage>zoom</themeimage>
      <label>'.$locale_catalog->getStr('show_all_my_bugs.button').'</label>
      <action>'.WuiXml::cdata(\Innomatic\Wui\Dispatch\WuiEventsCall::buildEventsCallString('innoworkbugs', array(array('view', 'default', array('filter' => 'true', 'filter_assignedto' => \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getCurrentUser()->getUserId()))))).'</action>
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
