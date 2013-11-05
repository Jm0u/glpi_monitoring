<?php

/*
   ------------------------------------------------------------------------
   Plugin Monitoring for GLPI
   Copyright (C) 2011-2013 by the Plugin Monitoring for GLPI Development Team.

   https://forge.indepnet.net/projects/monitoring/
   ------------------------------------------------------------------------

   LICENSE

   This file is part of Plugin Monitoring project.

   Plugin Monitoring for GLPI is free software: you can redistribute it and/or modify
   it under the terms of the GNU Affero General Public License as published by
   the Free Software Foundation, either version 3 of the License, or
   (at your option) any later version.

   Plugin Monitoring for GLPI is distributed in the hope that it will be useful,
   but WITHOUT ANY WARRANTY; without even the implied warranty of
   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
   GNU Affero General Public License for more details.

   You should have received a copy of the GNU Affero General Public License
   along with Monitoring. If not, see <http://www.gnu.org/licenses/>.

   ------------------------------------------------------------------------

   @package   Plugin Monitoring for GLPI
   @author    David Durieux
   @co-author 
   @comment   
   @copyright Copyright (c) 2011-2013 Plugin Monitoring for GLPI team
   @license   AGPL License 3.0 or (at your option) any later version
              http://www.gnu.org/licenses/agpl-3.0-standalone.html
   @link      https://forge.indepnet.net/projects/monitoring/
   @since     2011
 
   ------------------------------------------------------------------------
 */

if (!defined('GLPI_ROOT')) {
   die("Sorry. You can't access directly to this file");
}

class PluginMonitoringComponentscatalog extends CommonDropdown {
   
   /**
   * Get name of this type
   *
   *@return text name of this type by language of the user connected
   *
   **/
   static function getTypeName($nb=0) {
      return __('Components catalog', 'monitoring');
   }



   static function canCreate() {
      return PluginMonitoringProfile::haveRight("componentscatalog", 'w');
   }


   
   static function canView() {
      return PluginMonitoringProfile::haveRight("componentscatalog", 'r');
   }

   
   
   function defineTabs($options=array()){

      $ong = array();
      $this->addStandardTab("PluginMonitoringComponentscatalog", $ong, $options);
      return $ong;
   }
   
   
   
   /**
    * Display tab
    *
    * @param CommonGLPI $item
    * @param integer $withtemplate
    *
    * @return varchar name of the tab(s) to display
    */
   function getTabNameForItem(CommonGLPI $item, $withtemplate=0) {

      if ($item->getID() > 0) {
         $ong = array();
         $ong[1] = __('Components', 'monitoring');
         $ong[2] = self::createTabEntry(__('Static hosts', 'monitoring'), self::countForStaticHosts($item));
         $ong[3] = _n('Rule', 'Rules', 2);
         $ong[4] = self::createTabEntry(__('Dynamic hosts', 'monitoring'), self::countForDynamicHosts($item));
         $ong[5] = __('Contacts', 'monitoring');
         $ong[6] = __('Availability', 'monitoring');
         $ong[7] = __('Simple report', "monitoring");
         $ong[8] = __('Synthese', "monitoring");
         //$ong[7] = __('Report');

         return $ong;
      }
      return '';
   }
   
   
   
   /**
    * @param $item PluginMonitoringComponentscatalog object
   **/
   static function countForStaticHosts(PluginMonitoringComponentscatalog $item) {

      $restrict = "`plugin_monitoring_componentscalalog_id` = '".$item->getField('id') ."'
         AND `is_static`='1'";

      return countElementsInTable('glpi_plugin_monitoring_componentscatalogs_hosts', $restrict);
   }

   
   
   /**
    * @param $item PluginMonitoringComponentscatalog object
   **/
   static function countForDynamicHosts(PluginMonitoringComponentscatalog $item) {

      $restrict = "`plugin_monitoring_componentscalalog_id` = '".$item->getField('id') ."'
         AND `is_static`='0'";

      return countElementsInTable('glpi_plugin_monitoring_componentscatalogs_hosts', $restrict);
   }
   
   
   
   /**
    * Display content of tab
    *
    * @param CommonGLPI $item
    * @param integer $tabnum
    * @param interger $withtemplate
    *
    * @return boolean true
    */
   static function displayTabContentForItem(CommonGLPI $item, $tabnum=1, $withtemplate=0) {
      
      if ($item->getID() > 0) {
         switch($tabnum) {

            case 1:
               $pmComponentscatalog_Component = new PluginMonitoringComponentscatalog_Component();
               $pmComponentscatalog_Component->showComponents($item->getID());         
               break;

            case 2 :
               $pmComponentscatalog_Host = new PluginMonitoringComponentscatalog_Host();
               $pmComponentscatalog_Host->showHosts($item->getID(), 1);
               break;

            case 3 :
               $pmComponentscatalog_rule = new PluginMonitoringComponentscatalog_rule();
               $pmComponentscatalog_rule->showRules($item->getID());
               break;

            case 4 :
               $pmComponentscatalog_Host = new PluginMonitoringComponentscatalog_Host();
               $pmComponentscatalog_Host->showHosts($item->getID(), 0);
               break;

            case 5 : 
               $pmContact_Item = new PluginMonitoringContact_Item();
               $pmContact_Item->showContacts("PluginMonitoringComponentscatalog", $item->getID());
               break;

            case 6:
               $pmUnavaibility = new PluginMonitoringUnavaibility();
               $pmUnavaibility->displayComponentscatalog($item->getID());
               break;
            
            case 7:
               $pmPluginMonitoringComponentscatalog = new PluginMonitoringComponentscatalog();
               $pmPluginMonitoringComponentscatalog->showSimpleReport($item->getID());
               break;

            case 8:
               $pmPluginMonitoringComponentscatalog = new PluginMonitoringComponentscatalog();
               $pmPluginMonitoringComponentscatalog->showSyntheseReport($item->getID());
               break;

            
            default :

         }
         
      }
      return true;
   }
   
   
   
   function getAdditionalFields() {
      return array(array('name'  => 'notification_interval',
                         'label' => __('Interval between 2 notifications (in minutes)', 'monitoring'),
                         'type'  => 'notificationinterval'));
   }
   
   
   
   function displaySpecificTypeField($ID, $field=array()) {
      
      
      switch ($field['type']) {
         case 'notificationinterval' :
            if ($ID > 0) {
//               $this->fields['notification_interval'];
            } else {
               $this->fields['notification_interval'] = 30;
            }
            Dropdown::showNumber('notification_interval', array(
                'value' => $this->fields['notification_interval'], 
                'min'   => 1, 
                'max'   => 1000)
            );
            break;
      }
   }
   
   
   
   function showChecks() {      

      echo "<table class='tab_cadre' width='100%'>";
      echo "<tr class='tab_bg_4' style='background: #cececc;'>";
      
      $a_componentscatalogs = $this->find();
      $i = 0;
      foreach ($a_componentscatalogs as $data) {
         echo "<td>";

         echo $this->showWidget($data['id']);
         $this->ajaxLoad($data['id']);
         
         echo "</td>";
         
         $i++;
         if ($i == '6') {
            echo "</tr>";
            echo "<tr class='tab_bg_4' style='background: #cececc;'>";
            $i = 0;
         }
      }      
      
      echo "</tr>";
      echo "</table>";      
   }
   
   
   
   static function replayRulesCatalog($item) {
      
      $datas = getAllDatasFromTable("glpi_plugin_monitoring_componentscatalogs_rules", 
              "`plugin_monitoring_componentscalalog_id`='".$item->getID()."'");
      $pmComponentscatalog_rule = new PluginMonitoringComponentscatalog_rule();
      foreach($datas as $data) {
         $pmComponentscatalog_rule->getFromDB($data['id']);
         PluginMonitoringComponentscatalog_rule::getItemsDynamicly($pmComponentscatalog_rule);
      }
   }
  
   
   
   static function removeCatalog($item) {
      global $DB;
      
      $pmComponentscatalog_Host = new PluginMonitoringComponentscatalog_Host();
      $pmComponentscatalog_rule = new PluginMonitoringComponentscatalog_rule(); 
      
      $query = "SELECT * FROM `glpi_plugin_monitoring_componentscatalogs_hosts`
         WHERE `plugin_monitoring_componentscalalog_id`='".$item->fields["id"]."'
            AND `is_static`='1'";
      $result = $DB->query($query);
      while ($data=$DB->fetch_array($result)) {
         $pmComponentscatalog_Host->delete($data);
      }
      
      $query = "SELECT * FROM `glpi_plugin_monitoring_componentscatalogs_rules`
         WHERE `plugin_monitoring_componentscalalog_id`='".$item->fields["id"]."'";
      $result = $DB->query($query);
      while ($data=$DB->fetch_array($result)) {
         $pmComponentscatalog_rule->delete($data);
      }
   }
   
   
   
   function showWidget($id) {
      return "<div id=\"updatecomponentscatalog".$id."\"></div>";
   }
   
   
   
   function showWidgetFrame($id) {
      global $DB, $CFG_GLPI;
      
      $this->getFromDB($id);
      $data = $this->fields;
      
      $ret = $this->getInfoOfCatalog($id);
      $nb_ressources = $ret[0];
      $stateg = $ret[1];
      
      $colorclass = 'ok';
      $count = 0;
            
      $link = '';
      if ($stateg['CRITICAL'] > 0) {
         $count = $stateg['CRITICAL'];
         $colorclass = 'crit';
         $link = $CFG_GLPI['root_doc'].
         "/plugins/monitoring/front/service.php?hidesearch=1&reset=reset&field[0]=3&searchtype[0]=equals&contains[0]=CRITICAL".
            "&link[1]=AND&field[1]=8&searchtype[1]=equals&contains[1]=".$id.
            "&link[2]=OR&field[2]=3&searchtype[2]=equals&contains[2]=DOWN".
            "&link[3]=AND&field[3]=8&searchtype[3]=equals&contains[3]=".$id.
            "&link[4]=OR&field[4]=3&searchtype[4]=equals&contains[4]=UNREACHABLE".
            "&link[5]=AND&field[5]=8&searchtype[5]=equals&contains[5]=".$id.
            "&itemtype=PluginMonitoringService&start=0&glpi_tab=3";
      } else if ($stateg['WARNING'] > 0) {
         $count = $stateg['WARNING'];
         $colorclass = 'warn';
         $link = $CFG_GLPI['root_doc'].
         "/plugins/monitoring/front/service.php?hidesearch=1&reset=reset&field[0]=3&searchtype[0]=equals&contains[0]=WARNING".
            "&link[1]=AND&field[1]=8&searchtype[1]=equals&contains[1]=".$id.
            "&link[2]=OR&field[2]=3&searchtype[2]=equals&contains[2]=UNKNOWN".
            "&link[3]=AND&field[3]=8&searchtype[3]=equals&contains[3]=".$id.
            "&link[4]=OR&field[4]=3&searchtype[4]=equals&contains[4]=RECOVERY".
            "&link[5]=AND&field[5]=8&searchtype[5]=equals&contains[5]=".$id.
            "&link[6]=OR&field[6]=3&searchtype[6]=equals&contains[6]=FLAPPING".
            "&link[7]=AND&field[7]=8&searchtype[7]=equals&contains[7]=".$id.
            "&itemtype=PluginMonitoringService&start=0&glpi_tab=3";
      } else if ($stateg['OK'] > 0) {
         $count = $stateg['OK'];
         $link = $CFG_GLPI['root_doc'].
         "/plugins/monitoring/front/service.php?hidesearch=1&reset=reset&field[0]=3&searchtype[0]=equals&contains[0]=OK".
            "&link[1]=AND&field[1]=8&searchtype[1]=equals&contains[1]=".$id.
            "&link[2]=OR&field[2]=3&searchtype[2]=equals&contains[2]=UP".
            "&itemtype=PluginMonitoringService&start=0&glpi_tab=3";
      }
      
      echo '<div class="ch-item">
         <div class="ch-info-'.$colorclass.'">
			<h1>'.ucfirst($data['name']);
         if ($data['comment'] != '') {
            echo ' '.$this->getComments();
         }
         echo '</h1>
			<p><a href="'.$link.'">'.$count.'</a></p>
         </div>
		</div>';
      return;
      /////////////////////// This is the end !! ///////////////////////
      
      echo '<table  class="tab_cadre_fixe" style="width:158px;">';
      echo '<tr class="tab_bg_1">';
      echo '<th colspan="2" style="font-size:18px;" height="60">';
      echo $data['name']."&nbsp;";
      echo '</th>';
      echo '</tr>';
         

      
      
      echo '<tr class="tab_bg_1">';
      echo '<td>';
      echo __('Resources', 'monitoring')."&nbsp;:";
      echo '</td>';
      echo '<th align="center" height="40" width="50%">';
      $link = $CFG_GLPI['root_doc'].
         "/plugins/monitoring/front/service.php?hidesearch=1&reset=reset&field[0]=8&searchtype[0]=equals&contains[0]=".$id.
            "&itemtype=PluginMonitoringService&start=0&glpi_tab=3";
      echo '<a href="'.$link.'">'.$nb_ressources.'</a>';
      echo '</th>';
      echo '</tr>';

      $background = '';
      $count = 0;
            
      $link = '';
      if ($stateg['CRITICAL'] > 0) {
         $count = $stateg['CRITICAL'];
         $background = 'background="'.$CFG_GLPI['root_doc'].'/plugins/monitoring/pics/bg_critical.png"';
         $link = $CFG_GLPI['root_doc'].
         "/plugins/monitoring/front/service.php?hidesearch=1&reset=reset&field[0]=3&searchtype[0]=equals&contains[0]=CRITICAL".
            "&link[1]=AND&field[1]=8&searchtype[1]=equals&contains[1]=".$id.
            "&link[2]=OR&field[2]=3&searchtype[2]=equals&contains[2]=DOWN".
            "&link[3]=AND&field[3]=8&searchtype[3]=equals&contains[3]=".$id.
            "&link[4]=OR&field[4]=3&searchtype[4]=equals&contains[4]=UNREACHABLE".
            "&link[5]=AND&field[5]=8&searchtype[5]=equals&contains[5]=".$id.
            "&itemtype=PluginMonitoringService&start=0&glpi_tab=3";
      } else if ($stateg['WARNING'] > 0) {
         $count = $stateg['WARNING'];
         $background = 'background="'.$CFG_GLPI['root_doc'].'/plugins/monitoring/pics/bg_warning.png"';
         $link = $CFG_GLPI['root_doc'].
         "/plugins/monitoring/front/service.php?hidesearch=1&reset=reset&field[0]=3&searchtype[0]=equals&contains[0]=WARNING".
            "&link[1]=AND&field[1]=8&searchtype[1]=equals&contains[1]=".$id.
            "&link[2]=OR&field[2]=3&searchtype[2]=equals&contains[2]=UNKNOWN".
            "&link[3]=AND&field[3]=8&searchtype[3]=equals&contains[3]=".$id.
            "&link[4]=OR&field[4]=3&searchtype[4]=equals&contains[4]=RECOVERY".
            "&link[5]=AND&field[5]=8&searchtype[5]=equals&contains[5]=".$id.
            "&link[6]=OR&field[6]=3&searchtype[6]=equals&contains[6]=FLAPPING".
            "&link[7]=AND&field[7]=8&searchtype[7]=equals&contains[7]=".$id.
            "&itemtype=PluginMonitoringService&start=0&glpi_tab=3";
      } else if ($stateg['OK'] > 0) {
         $count = $stateg['OK'];
         $background = 'background="'.$CFG_GLPI['root_doc'].'/plugins/monitoring/pics/bg_ok.png"';
         $link = $CFG_GLPI['root_doc'].
         "/plugins/monitoring/front/service.php?hidesearch=1&reset=reset&field[0]=3&searchtype[0]=equals&contains[0]=OK".
            "&link[1]=AND&field[1]=8&searchtype[1]=equals&contains[1]=".$id.
            "&link[2]=OR&field[2]=3&searchtype[2]=equals&contains[2]=UP".
            "&itemtype=PluginMonitoringService&start=0&glpi_tab=3";
      }
      echo  "<tr ".$background.">";
      echo  '<th style="background-color:transparent;" '.$background.'" colspan="2" height="100">';
      echo  '<a href="'.$link.'"><font style="font-size: 52px; color:black">'.$count.'</font></a>';         
      echo  '</th>';
      echo  '</tr>';
      
      echo  '</table>';
   }
   
   
   
   function ajaxLoad($id) {
      global $CFG_GLPI;
      
      echo "<script type=\"text/javascript\">

      var elcc".$id." = Ext.get(\"updatecomponentscatalog".$id."\");
      var mgrcc".$id." = elcc".$id.".getUpdateManager();
      mgrcc".$id.".loadScripts=true;
      mgrcc".$id.".showLoadIndicator=false;
      mgrcc".$id.".startAutoRefresh(50, \"".$CFG_GLPI["root_doc"]."/plugins/monitoring/ajax/updateWidgetComponentscatalog.php\", \"id=".$id."\", \"\", true);
      </script>";
   }
   
   
   
   function getInfoOfCatalog($componentscatalogs_id) {
      global $DB;
      
      $pmComponentscatalog_Host = new PluginMonitoringComponentscatalog_Host();
      $pmService = new PluginMonitoringService();
      
      $stateg = array();
      $stateg['OK'] = 0;
      $stateg['WARNING'] = 0;
      $stateg['CRITICAL'] = 0;
      $a_gstate = array();
      $nb_ressources = 0;
      $query = "SELECT * FROM `".$pmComponentscatalog_Host->getTable()."`
         WHERE `plugin_monitoring_componentscalalog_id`='".$componentscatalogs_id."'";
      $result = $DB->query($query);
      while ($dataComponentscatalog_Host=$DB->fetch_array($result)) {
         $queryService = "SELECT * FROM `".$pmService->getTable()."`
            WHERE `plugin_monitoring_componentscatalogs_hosts_id`='".$dataComponentscatalog_Host['id']."'
               AND `entities_id` IN (".$_SESSION['glpiactiveentities_string'].")";
         $resultService = $DB->query($queryService);
         while ($dataService=$DB->fetch_array($resultService)) {
            $nb_ressources++;
            if ($dataService['state_type'] != "HARD") {
               $a_gstate[$dataService['id']] = "OK";
            } else {
               $statecurrent = PluginMonitoringDisplay::getState($dataService['state'], 
                                                                 $dataService['state_type'],
                                                                 $dataService['event'],
                                                                 $dataService['is_acknowledged']);
               if ($statecurrent == 'green') {
                  $a_gstate[$dataService['id']] = "OK";
               } else if ($statecurrent == 'orange') {
                  $a_gstate[$dataService['id']] = "WARNING";
               } else if ($statecurrent == 'yellow') {
                  $a_gstate[$dataService['id']] = "WARNING";
               } else if ($statecurrent == 'red') {
                  $a_gstate[$dataService['id']] = "CRITICAL";
               }
            }
         }
      }
      foreach ($a_gstate as $value) {
         $stateg[$value]++;
      }
      return array($nb_ressources,
                   $stateg);
   }

   
   
   function getRessources($componentscatalogs_id, $state, $state_type='HARD') {
      global $DB;
      
      $a_services = array();
      
      $pmComponentscatalog_Host = new PluginMonitoringComponentscatalog_Host();
      
      $query = "SELECT * FROM `glpi_plugin_monitoring_services`         
         LEFT JOIN `".$pmComponentscatalog_Host->getTable()."`
            ON `plugin_monitoring_componentscatalogs_hosts_id`=
               `".$pmComponentscatalog_Host->getTable()."`.`id`
         WHERE `plugin_monitoring_componentscalalog_id`='".$componentscatalogs_id."'
            AND `state_type` LIKE '".$state_type."'
         ORDER BY `name`";
      $result = $DB->query($query);
      while ($data=$DB->fetch_array($result)) {
         if (PluginMonitoringDisplay::getState($data['state'], 
                                               $data['state_type'],
                                               '',
                                               $data['is_acknowledged']) == $state) {
            $a_services[] = $data;
         }
      }
      return $a_services;      
   }
   
   

   function showSimpleReport($componentscatalogs_id) {
      global $CFG_GLPI;

      $pmComponentscatalog_Component = new PluginMonitoringComponentscatalog_Component();
      $pmComponent = new PluginMonitoringComponent();
      $a_options = array();
      
      $this->getFromDB($componentscatalogs_id);
      
      echo "<form name='form' method='post' 
         action='".$CFG_GLPI['root_doc']."/plugins/monitoring/front/report_componentscatalog.form.php'>";
      
      echo "<table class='tab_cadre_fixe'>";
      echo '<tr class="tab_bg_1">';
      echo '<th colspan="5">';
      echo __('Report');
      echo "<input type='hidden' name='componentscatalogs_id' value='".$componentscatalogs_id."' />";
      $a_options['componentscatalogs_id'] = $componentscatalogs_id;
      echo '</th>';
      echo '</tr>';

      // ** simple report
      echo '<tr class="tab_bg_1">';
      echo '<tr class="tab_bg_1">';
      echo '<td>';
      echo '<input type="radio" name="reporttype" value="simplereport" checked />'; 
      echo '</td>';
      echo '<td colspan="4">';
      echo '<strong>'.__('Simple report', "monitoring").'</strong>';
      echo '</td>';
      echo '</tr>';
      
      echo '<tr class="tab_bg_1">';
      echo '<td>';
      echo '</td>';
      echo "<td>".__('Start date')." :</td>";
      echo "<td>";
      Html::showDateFormItem("date_start", date('Y-m-d H:i:s', date('U') - (24 * 3600 * 7)));
      $a_options['date_start'] = date('Y-m-d H:i:s', date('U') - (24 * 3600 * 7));
$a_options['date_start'] = '2013-01-01 01:01:01';
      echo "</td>";
      echo "<td>".__('End date')." :</td>";
      echo "<td>";
      Html::showDateFormItem("date_end", date('Y-m-d'));
      $a_options['date_end'] = date('Y-m-d');
      echo "</td>";
      echo "</tr>";
      echo "</table>";
      
      echo "<table class='tab_cadre_fixe'>";      
      $a_composants = $pmComponentscatalog_Component->find("`plugin_monitoring_componentscalalog_id`='".$componentscatalogs_id."'");
      foreach ($a_composants as $comp_data) {
         $pmComponent->getFromDB($comp_data['plugin_monitoring_components_id']);

         echo "<tr class='tab_bg_1'>";
         echo "<td width='10'>";
         echo "<input type='checkbox' name='components_id[]' value='".$pmComponent->getID()."' checked />";
         $a_options['components_id'][] = $pmComponent->getID();
         echo "</td>";
         echo "<td>";
         echo $pmComponent->getLink();
         echo "</td>";      
         echo "</tr>";
         
         echo "<tr class='tab_bg_1'>";
         echo "<td width='10'>";
         echo "</td>";
         echo "<td>";
         PluginMonitoringServicegraph::preferences($pmComponent->getID(), 1, 1);
         echo "</td>";
      
         echo "</tr>";
      }
      echo "<tr class='tab_bg_1'>";
      echo "<td colspan='2' align='center'>";
      echo "<input type='submit' class='submit' name='generate' value='".__('Generate the report', 'monitoring')."'/>";
      echo "</td>";
      echo "</tr>";
      echo "</table>";
      
      Html::closeForm();
      
      $this->generateReport($a_options, FALSE);
   }

   
   
   function showSyntheseReport($componentscatalogs_id) {
      global $CFG_GLPI;
      
      if (!isset($_SESSION['glpi_plugin_monitoring']['synthese'])) {
         $_SESSION['glpi_plugin_monitoring']['synthese'] = array();
      }
      if (!isset($_SESSION['glpi_plugin_monitoring']['synthese'][$componentscatalogs_id])) {
         $_SESSION['glpi_plugin_monitoring']['synthese'][$componentscatalogs_id] = array();
      }
      $sess = $_SESSION['glpi_plugin_monitoring']['synthese'][$componentscatalogs_id];
      $pmComponentscatalog_Component = new PluginMonitoringComponentscatalog_Component();
      $pmComponent = new PluginMonitoringComponent();
      $a_options = array();
      
      $this->getFromDB($componentscatalogs_id);
      
      echo "<form name='form' method='post' 
         action='".$CFG_GLPI['root_doc']."/plugins/monitoring/front/report_componentscatalog.form.php'>";
      
      echo "<table class='tab_cadre_fixe'>";
      echo '<tr class="tab_bg_1">';
      echo '<th colspan="5">';
      echo __('Report');
      echo "<input type='hidden' name='componentscatalogs_id' value='".$componentscatalogs_id."' />";
      echo "<input type='hidden' name='reporttype' value='synthese' />";
      $a_options['componentscatalogs_id'] = $componentscatalogs_id;
      echo '</th>';
      echo '</tr>';
      
      echo '<tr class="tab_bg_1">';
      echo '<td>';
      echo '</td>';
      echo '<td colspan="2">';
      $default_value = 12;
      if (isset($sess['synthesenumber'])) {
         $default_value = $sess['synthesenumber'];
      }
      Dropdown::showNumber("synthesenumber", array(
                'value' => $default_value, 
                'min'   => 2, 
                'max'   => 30)
      );
      $a_options['synthesenumber'] = $default_value;
      echo "&nbsp;";
      $a_time = array('week' => __('Week'),
                      'month' => __('Month'),
                      'year' => __('Year'));
      $default_value = 'week';
      if (isset($sess['synthesenumber'])) {
         $default_value = $sess['synthesenumber'];
      }
      Dropdown::showFromArray("syntheseperiod", $a_time, array('value' => $default_value));
      $a_options['syntheseperiod'] = $default_value;
      echo '</td>';
      echo "<td>".__('End date')." :</td>";
      echo "<td>";
      $default_value = date('Y-m-d');
      if (isset($sess['synthesedate_end'])) {
         $default_value = $sess['synthesedate_end'];
      }
      Html::showDateFormItem("synthesedate_end", $default_value);
      $a_options['synthesedate_end'] = $default_value;
      echo "</td>";
      echo '</tr>';
      
      echo "</table>";
            
      echo "<table class='tab_cadre_fixe'>";      
      $a_composants = $pmComponentscatalog_Component->find("`plugin_monitoring_componentscalalog_id`='".$componentscatalogs_id."'");
      foreach ($a_composants as $comp_data) {
         $pmComponent->getFromDB($comp_data['plugin_monitoring_components_id']);

         echo "<tr class='tab_bg_1'>";
         echo "<td width='10'>";
         //echo "<input type='checkbox' name='components_id[]' value='".$pmComponent->getID()."' checked />";
         echo "<input type='hidden' name='components_id[]' value='".$pmComponent->getID()."' />";
         $a_options['components_id'][] = $pmComponent->getID();
         echo "</td>";
         echo "<td>";
         echo $pmComponent->getLink();
         echo "</td>";      
         echo "</tr>";
         
         echo "<tr class='tab_bg_1'>";
         echo "<td width='10'>";
         echo "</td>";
         echo "<td>";
         
         PluginMonitoringServicegraph::loadPreferences($pmComponent->getID());
         
         $a_perfnames = PluginMonitoringServicegraph::getperfdataNames($pmComponent->fields['graph_template']);
         echo "<table class='tab_cadre_fixe'>";      
         echo "<tr class='tab_bg_3'>";
         echo "<td rowspan='".count($a_perfnames)."' width='90'>";
         echo __('Use for report', 'monitoring')."&nbsp;:";

         echo "</td>";
         $i = 0;
         $j = 0;
         if (!isset($_SESSION['glpi_plugin_monitoring']['perfname'][$pmComponent->getID()])) {
            foreach ($a_perfnames as $name) {
               $_SESSION['glpi_plugin_monitoring']['perfname'][$pmComponent->getID()][$name] = 'checked';
            }
         }
         
         foreach ($a_perfnames as $name) {
            if ($i > 0) {
               echo "<tr class='tab_bg_3'>";
            }
            echo "<td>";
            $checked = "checked";
            if (isset($sess['perfname'])
                 && isset($sess['perfname'][$pmComponent->getID()])) {
               
               if (isset($sess['perfname'][$pmComponent->getID()])) {
                  $checked = "";
               }
               if (isset($sess['perfname'][$pmComponent->getID()][$name])) {
                  $checked = "checked";
               }
            } else {
               if (isset($_SESSION['glpi_plugin_monitoring']['perfname'][$pmComponent->getID()])) {
                  $checked = "";
               }
               if (isset($_SESSION['glpi_plugin_monitoring']['perfname'][$pmComponent->getID()][$name])) {
                  $checked = $_SESSION['glpi_plugin_monitoring']['perfname'][$pmComponent->getID()][$name];
               }
            }
            echo "<input type='checkbox' name='perfname[".$pmComponent->getID()."][".$name."]' value='".$name."' ".$checked."/> ".$name;
            if ($checked == 'checked') {
               $a_options['perfname'][$pmComponent->getID()][] = $name;
            }
            echo "</td>";
            echo "<td>";
            echo __('Best is high value', 'monitoring').' :';
            echo "</td>";
            echo "<td>";
            $default_value = 1;
            if (isset($sess['perfname_val'])
                 && isset($sess['perfname_val'][$pmComponent->getID()])) {
               
               if (isset($sess['perfname_val'][$pmComponent->getID()][$name])) {
                  $default_value = $sess['perfname_val'][$pmComponent->getID()][$name];
               }
            }            
            Dropdown::showYesNo('perfname_val['.$pmComponent->getID().']['.$name.']', $default_value);
            if ($checked == 'checked') {
               $a_options['perfname_val'][$pmComponent->getID()][$name] = $default_value;
            }
            echo "</td>";
            echo "</tr>";
            $i++;
         }

         echo "</table>";
         
         echo "</td>";
      
         echo "</tr>";
      }
      echo "<tr class='tab_bg_1'>";
      echo "<td colspan='2' align='center'>";
      echo "<input type='submit' class='submit' name='update' value='".__('Save')."'/>";
      echo "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
         &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
         <input type='submit' class='submit' name='generatepdf' value='".__('Generate PDF', 'monitoring')."'/>";
      echo "</td>";
      echo "</tr>";
      echo "</table>";
            
      Html::closeForm();
      
      if (isset($_SESSION['plugin_monitoring_report'])) {
//         $a_options = $_SESSION['plugin_monitoring_report'];
      }      
      $this->generateSyntheseReport(
              $_SESSION['glpi_plugin_monitoring']['synthese'][$componentscatalogs_id], 
              FALSE);
   }
   
   
   
   function generateReport($array, $pdf=TRUE) {
      global $DB,$CFG_GLPI;
      
      $componentscatalogs_id = $array['componentscatalogs_id'];
      
      // define time for the report:
      // Week, week -1, week -2, month, month -1, month -2, year, year -1
      
      $pmUnavaibility = new PluginMonitoringUnavaibility();
      $pmComponent = new PluginMonitoringComponent();
      $pmServiceevent = new PluginMonitoringServiceevent();
      
      if ($pdf) {
         PluginMonitoringReport::beginCapture();
      }
      
      $this->getFromDB($componentscatalogs_id);
      echo '<h1>'.$this->getTypeName().' : '.$this->getName().'<br/>
         Mois de Novembre</h1>';
      
      echo '<br/>';
      
      foreach ($array['components_id'] as $components_id) {
         $pmComponent->getFromDB($components_id);

         $a_name = $array['perfname'];         
         
         echo "<table class='tab_cadre_fixe'>";
         echo '<tr class="tab_bg_1">';
         echo '<th colspan="'.(6 + (count($a_name) * 3)).'">';
         echo $pmComponent->getName();
         echo '</th>';
         echo '</tr>';
         
         echo '<tr class="tab_bg_1">';
         echo '<th rowspan="2">';
         echo __('Name');
         echo '</th>';
         echo '<th rowspan="2">';
         echo __('Entity');
         echo '</th>';
         echo '<th rowspan="2">';
         echo __('Itemtype');
         echo '</th>';
         echo '<th rowspan="2">';
         echo __('Trend', 'monitoring');
         echo '</th>';
         echo '<th colspan="2">';
         echo __('Avaibility', 'monitoring');
         echo '</th>';
         foreach ($a_name as $name) {
            echo '<th colspan="3">';
            echo str_replace('_', ' ', $name);
            echo '</th>';
         }
         echo '</tr>';
         
         echo '<tr class="tab_bg_1">';
         echo '<th>';
         echo __('%', 'monitoring');
         echo '</th>';
         echo '<th>';
         echo __('Time', 'monitoring');
         echo '</th>';
         foreach ($a_name as $name) {
            echo '<th>';
            echo __('Min', 'monitoring');
            echo '</th>';
            echo '<th>';
            echo __('Avg', 'monitoring');
            echo '</th>';
            echo '<th>';
            echo __('Max', 'monitoring');
            echo '</th>';
         }
         echo '</tr>';
         

         $query = "SELECT `glpi_plugin_monitoring_componentscatalogs_hosts`.*, 
               `glpi_plugin_monitoring_services`.`id` as sid FROM `glpi_plugin_monitoring_componentscatalogs_hosts`
            LEFT JOIN `glpi_plugin_monitoring_services`
               ON `glpi_plugin_monitoring_componentscatalogs_hosts`.`id`=`plugin_monitoring_componentscatalogs_hosts_id`
            WHERE `plugin_monitoring_componentscalalog_id`='".$componentscatalogs_id."'
               AND `plugin_monitoring_components_id`='".$components_id."'";
         $result = $DB->query($query);
         $rownb = true;
         while ($data=$DB->fetch_array($result)) {
            $itemtype = $data['itemtype'];
            $item = new $itemtype();
            $item->getFromDB($data['items_id']);
            
            $_SESSION['plugin_monitoring_checkinterval'] = PluginMonitoringComponent::getTimeBetween2Checks($pmComponent->fields['id']);

            $ret = array();
            if (count($a_name) > 0) {
               $queryevents = "SELECT * FROM `glpi_plugin_monitoring_serviceevents`
                  WHERE `plugin_monitoring_services_id`='".$data['sid']."'
                     AND `date` >= '".$array['date_start']."'
                     AND `date` <= '".$array['date_end']."'
                  ORDER BY `date`";
               $resultevents = $DB->query($queryevents);
               $ret = $pmServiceevent->getData($resultevents, $pmComponent->fields['graph_template']);
            }

            echo '<tr class="tab_bg_1'.(($rownb = !$rownb)?'_2':'').'">';
            echo '<td>';
            echo $item->getName();
            echo '</td>';
            echo '<td>';
            echo Dropdown::getDropdownName("glpi_entities", $item->fields['entities_id']);
            echo '</td>';
            echo '<td>';
            echo $item->getTypeName();
            echo '</td>';
            echo '<td>';
            $a_times = $pmUnavaibility->parseEvents($data['id'], '', $array['date_start'], $array['date_end']);
            // previous unavaibility
            $str_start = strtotime($array['date_start']);
            $str_end   = strtotime($array['date_end']);
            $a_times_previous = $pmUnavaibility->parseEvents($data['id'], '', 
                                 date('Y-m-d', $str_start - ($str_end - $str_start)), 
                                 $array['date_start']);
            $previous_percentage = round(((($a_times_previous[1] - $a_times_previous[0]) / $a_times_previous[1]) * 100), 3);
            $percentage = round(((($a_times[1] - $a_times[0]) / $a_times[1]) * 100), 3);
            if ($previous_percentage < $percentage) {
               echo '<img src="../pics/arrow-up-right.png" width="16" />';
            } else if ($previous_percentage == $percentage) {
               echo '<img src="../pics/arrow-right.png" width="16" />';
            } else if ($previous_percentage > $percentage) {
               echo '<img src="../pics/arrow-down-right.png" width="16" />';
            }
            echo '</td>';
            echo '<td>';
            echo $percentage."%";
            echo '</td>';
            echo '<td>';
            if ($a_times[0] == 0) {
               echo "-";
            } else {
               echo Html::timestampToString($a_times[0]);
            }
            echo '</td>';
            foreach ($a_name as $name) {
               echo '<td>';
               echo min($ret[0][$name]);
               echo '</td>';
               echo '<td>';
               echo round(array_sum($ret[0][$name]) / count($ret[0][$name]), 3);
               echo '</td>';
               echo '<td>';
               echo max($ret[0][$name]);
               echo '</td>';
            }
            echo '</tr>';
         }
         echo '</table>';
      }
      if ($pdf) {
         $content = PluginMonitoringReport::endCapture();
         PluginMonitoringReport::generatePDF($content);
      }
   }
   
   
   
   function generateSyntheseReport($array, $pdf=TRUE) {
      global $DB;

      $end_date = $array['synthesedate_end'];
      $end_date_timestamp = strtotime($end_date);
      $number   = $array['synthesenumber'];
      $period   = $array['syntheseperiod'];
      
      $componentscatalogs_id = $array['componentscatalogs_id'];
      
      $pmComponent    = new PluginMonitoringComponent();
      $pmUnavaibility = new PluginMonitoringUnavaibility();
      $pmServiceevent = new PluginMonitoringServiceevent();

      if ($pdf) {
         PluginMonitoringReport::beginCapture();
      }
      echo "<table class='tab_cadrehov'>";
      foreach ($array['components_id'] as $components_id) {
         $pmComponent->getFromDB($components_id);
         array_unshift($array['perfname'][$components_id], 'avaibility');
         array_unshift($array['perfname_val'][$components_id], 1);
         echo '<tr class="tab_bg_1" height="90">';
         echo '<th colspan="'.(3 + ($number * 2)).'">';
         echo $pmComponent->getName();
         echo '</th>';
         echo '</tr>';

         foreach ($array['perfname'][$components_id] as $num=>$groupname) {
            echo '<tr class="tab_bg_1">';
            echo '<th colspan="'.(3 + ($number * 2)).'">';
            if ($groupname == 'avaibility') {
               echo __('Avaibility', 'monitoring');
            } else {
               echo $groupname;
            }
            echo '</th>';
            echo '</tr>';

            echo '<tr class="tab_bg_1">';
            echo '<th rowspan="2">';
            echo __('Name');
            echo '</th>';
            echo '<th rowspan="2">';
            echo __('Entity');
            echo '</th>';
            echo '<th rowspan="2">';
            echo __('Itemtype');
            echo '</th>';
            $a_year = array();
            for ($i = $number; $i >= 1;$i--) {
               $year = date('Y', strtotime("-".$i." ".$period, $end_date_timestamp));
               if (!isset($a_year[$year])) {
                  $a_year[$year] = 2;
               } else {
                  $a_year[$year] += 2;
               }
            }
            foreach ($a_year as $year=>$colspan) {
               echo '<th colspan="'.$colspan.'">';
               echo $year;
               echo '</th>';           
            }
            echo '</tr>';
            
            echo '<tr class="tab_bg_1">';
            for ($i = $number; $i >= 1;$i--) {
               echo '<th colspan="2">';
               echo Html::convDate(date('m-d', strtotime("-".$i." ".$period, $end_date_timestamp)));
               echo "<br/>";
               echo Html::convDate(date('m-d', strtotime("-".($i-1)." ".$period, $end_date_timestamp)));
               echo '</th>';           
            }
            echo '</tr>';

            $query = "SELECT `glpi_plugin_monitoring_componentscatalogs_hosts`.*, 
                  `glpi_plugin_monitoring_services`.`id` as sid FROM `glpi_plugin_monitoring_componentscatalogs_hosts`
               LEFT JOIN `glpi_plugin_monitoring_services`
                  ON `glpi_plugin_monitoring_componentscatalogs_hosts`.`id`=`plugin_monitoring_componentscatalogs_hosts_id`
               WHERE `plugin_monitoring_componentscalalog_id`='".$componentscatalogs_id."'
                  AND `plugin_monitoring_components_id`='".$components_id."'";
            $result = $DB->query($query);
            $rownb = true;
            while ($data=$DB->fetch_array($result)) {
               $itemtype = $data['itemtype'];
               $item = new $itemtype();
               $item->getFromDB($data['items_id']);

               if ($groupname == 'avaibility') {
                  $a_times = $pmUnavaibility->parseEvents($data['id'], '', 
                                                          date('Y-m-d', strtotime("-".($number + 1)." ".$period, $end_date_timestamp)),
                                                          date('Y-m-d', strtotime("-".$number." ".$period, $end_date_timestamp)));
                  $previous_value = round(((($a_times[1] - $a_times[0]) / $a_times[1]) * 100), 3);
               } else {
                  $previous_value = 0;
               }
               echo '<tr class="tab_bg'.(($rownb = !$rownb)?'_4':'_1').'">';
               echo '<td>';
               echo $item->getLink();
               echo '</td>';
               echo '<td>';
               echo Dropdown::getDropdownName("glpi_entities", $item->fields['entities_id']);
               echo '</td>';
               echo '<td>';
               echo $item->getTypeName();
               echo '</td>';
               for ($i = $number; $i >= 1;$i--) {
                  $startdatet = date('Y-m-d', strtotime("-".$i." ".$period, $end_date_timestamp));
                  $enddatet   = date('Y-m-d', strtotime("-".($i-1)." ".$period, $end_date_timestamp));
                  if ($groupname == 'avaibility') {
                     $a_times = $pmUnavaibility->parseEvents($data['id'], '', $startdatet, $enddatet);
                     $value = round(((($a_times[1] - $a_times[0]) / $a_times[1]) * 100), 2);
                  } else {
                     $queryevents = "SELECT * FROM `glpi_plugin_monitoring_serviceevents`
                        WHERE `plugin_monitoring_services_id`='".$data['sid']."'
                           AND `date` >= '".$startdatet."'
                           AND `date` <= '".$enddatet."'
                        ORDER BY `date`";
                     $resultevents = $DB->query($queryevents);
                     $_SESSION['plugin_monitoring_checkinterval'] = PluginMonitoringComponent::getTimeBetween2Checks($pmComponent->fields['id']);
                     $ret = $pmServiceevent->getData($resultevents, $pmComponent->fields['graph_template']);
                     if (!isset($ret[0][$groupname])) {
                        $value = 0;
                     } else {
                        $value = round(array_sum($ret[0][$groupname]) / count($ret[0][$groupname]), 2);
                     }
                  }

                  $bgcolor = '';
                  if ($array['perfname_val'][$components_id][$num] == 1) {
                     if ($previous_value < $value) {
                        $bgcolor = 'style="background-color:#d1ffc3"';
                     } else if ($previous_value > $value) {
                        $bgcolor = 'style="background-color:#ffd1d3"';
                     }
                  } else {
                     if ($previous_value < $value) {
                        $bgcolor = 'style="background-color:#ffd1d3"';
                     } else if ($previous_value > $value) {
                        $bgcolor = 'style="background-color:#d1ffc3"';
                     }
                  }
                  
                  echo '<td '.$bgcolor.'>';
                  if ($groupname == 'avaibility') {
                     echo $value."%";
                  } else {
                     echo $value;
                  }
                  echo '</td>';
                  echo '<td '.$bgcolor.'>';
                  if ($array['perfname_val'][$components_id][$num] == 1) {
                     if ($previous_value < $value) {
                        echo '<img src="../pics/arrow-up-right.png" width="16" />';
                     } else if ($previous_value == $value) {
                        echo '<img src="../pics/arrow-right.png" width="16" />';
                     } else if ($previous_value > $value) {
                        echo '<img src="../pics/arrow-down-right.png" width="16" />';
                     }
                  } else {
                     if ($previous_value < $value) {
                        echo '<img src="../pics/arrow-up-right_inv.png" width="16" />';
                     } else if ($previous_value == $value) {
                        echo '<img src="../pics/arrow-right.png" width="16" />';
                     } else if ($previous_value > $value) {
                        echo '<img src="../pics/arrow-down-right_inv.png" width="16" />';
                     }
                  }
                  $previous_value = $value;
                  echo '</td>';
               }
               echo "</tr>";
               
            }
         }
         echo '<tr class="tab_bg_1" height="50">';
         echo '<td colspan="'.(3 + ($number * 2)).'">';
         echo '</td>';
         echo '</tr>';
      }
      echo "</table>";
      if ($pdf) {
         $content = PluginMonitoringReport::endCapture();
         PluginMonitoringReport::generatePDF($content, 'L');
      }
   }
}

?>