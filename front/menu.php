<?php

/*
   ------------------------------------------------------------------------
   Plugin Monitoring for GLPI
   Copyright (C) 2011-2014 by the Plugin Monitoring for GLPI Development Team.

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
   @copyright Copyright (c) 2011-2014 Plugin Monitoring for GLPI team
   @license   AGPL License 3.0 or (at your option) any later version
              http://www.gnu.org/licenses/agpl-3.0-standalone.html
   @link      https://forge.indepnet.net/projects/monitoring/
   @since     2011

   ------------------------------------------------------------------------
 */

include ("../../../inc/includes.php");

Session::checkCentralAccess();

Html::header(__('Monitoring - dashboard', 'monitoring'), $_SERVER["PHP_SELF"], "plugins",
             "monitoring", "menu");

$pmMessage = new PluginMonitoringMessage();
$pmMessage->getMessages();

$toDisplayArea=0;

// Restart Shinken buttons :
// - on main Monitoring plugin page
// - one button per each declared Shinken tag
// - one button to restart all Shinken instances
if (Session::haveRight("plugin_monitoring_restartshinken", CREATE)) {
   echo '<div style="position: absolute; float: left; width: 30%; max-width: 300px; background: #ccc; border: outset 2px white; padding: 0.5%;">';

   $pmTag = new PluginMonitoringTag();
   $a_tagsBrut = $pmTag->find();

   $a_tags = array();
   foreach ($a_tagsBrut as $data) {
      if (!isset($a_tags[$data['ip'].':'.$data['port']])) {
         $a_tags[$data['ip'].':'.$data['port']] = $data;
      }
   }

   if (count($a_tags) > 0) {
      $shinken_commands = [
            'reload'    => [
                  'command' => 'reload',
                  'title' => __('Reconfigure Shinken from Glpi database', 'monitoring'),
                  'button' => __('Reconfigure Shinken', 'monitoring'),
            ],
            'restart'   => [
                  'command' => 'restart',
                  'title' => __('Restart all Shinken daemons', 'monitoring'),
                  'button' => __('Restart Shinken', 'monitoring'),
            ],
      ];

      foreach ($shinken_commands as $command) {
         echo '<b>'.$command['title'].'</b>';
         echo '<br/>';
         echo '<table style="table-layout: fixed">';

         echo '<tr>';
         echo '<td >';
         echo "<a style='display: block; padding: 2px; text-align: center; background: #bbb; border: outset 2px white; font-size: 9px;' href='".$CFG_GLPI['root_doc']."/plugins/monitoring/front/restartshinken.form.php?action=".$command['command']."&tag=0'>".$command['button']."<br/>".__('All instances', 'monitoring')."</a>";
         echo '</td><td style="width: 5px;"></td>';
         echo '</tr>';

         if (count($a_tags) > 1) {
            $i=1;
            echo '<tr>';
            foreach ($a_tags as $taginfo=>$data) {
               echo '<td>';
               echo "<a style='display: block; padding: 2px; text-align: center; background: #bbb; border: outset 2px white; font-size: 9px;' href='".$CFG_GLPI['root_doc']."/plugins/monitoring/front/restartshinken.form.php?action=".$command['command']."&tag=". $data['id'] ."'>".$command['button']." \n(". $taginfo .") "."</a>";
               echo '</td>';
               echo '<td style="width: 5px;"></td>';
               if ($i > 2) {
                  $i = 1;
                  echo '</tr><tr>';
               } else {
                  $i++;
               }
            }
            echo '</tr>';
         }
         echo '</table>';
         echo '</br>';
      }
   } else {
      echo '<div style="z-index: 1;position:absolute;width: 100%; margin: 0 auto;">';
      echo __('No Shinken instance configured', 'monitoring');
      echo "</div>";
   }
   echo '</div>';
}

if (Session::haveRight("plugin_monitoring_dashboard", READ)
        && !Session::haveRight("config", READ)) {
   Html::redirect($CFG_GLPI['root_doc']."/plugins/monitoring/front/dashboard.php");
}

if (Session::haveRight("plugin_monitoring_dashboard", READ)
      && (
         Session::haveRight("plugin_monitoring_restartshinken", CREATE)
         || Session::haveRight("plugin_monitoring_systemstatus", PluginMonitoringSystem::DASHBOARD)
         || Session::haveRight("plugin_monitoring_hoststatus", PluginMonitoringHost::DASHBOARD)
         || Session::haveRight("plugin_monitoring_servicescatalog", PluginMonitoringServicescatalog::DASHBOARD)
         || Session::haveRight("plugin_monitoring_componentscatalog", PluginMonitoringComponentscatalog::DASHBOARD)
         || Session::haveRight("plugin_monitoring_service", PluginMonitoringService::DASHBOARD)
         || Session::haveRight("plugin_monitoring_displayview", PluginMonitoringDisplayview::DASHBOARD))) {
   $toDisplayArea++;

   echo "<table class='tab_cadre' width='950'>";
   echo "<tr class='tab_bg_1'>";
   echo "<th height='80'>";
   echo "<a href='".$CFG_GLPI['root_doc']."/plugins/monitoring/front/dashboard.php'>".__('Dashboard', 'monitoring')."</a>";
   echo "</th>";
   echo "</tr>";
   echo "</table>";

   echo "<br/>";
}
if (Session::haveRight("plugin_monitoring_displayview", READ)
        || Session::haveRight("plugin_monitoring_slider", READ)) {

   echo "<table class='tab_cadre' width='950'>";
   echo "<tr class='tab_bg_1'>";
   if (Session::haveRight("plugin_monitoring_displayview", READ)) {
      $toDisplayArea++;
      echo "<th align='center' height='40' width='34%'>";
      echo "<a href='".$CFG_GLPI['root_doc']."/plugins/monitoring/front/displayview.php'>".__('Views', 'monitoring')."</a>";
      echo "</th>";
   }
   if (Session::haveRight("plugin_monitoring_slider", READ)) {
      echo "<th align='center' height='40' width='34%'>";
      echo "<a href='".$CFG_GLPI['root_doc']."/plugins/monitoring/front/slider.php'>".__('Carrousel / slider', 'monitoring')."</a>";
      echo "</th>";
   }
   echo "</tr>";
   echo "</table>";
   echo "<br/>";
}


if (Session::haveRight("plugin_monitoring_servicescatalog", READ)
      || Session::haveRight("plugin_monitoring_weathermap", READ)
      || Session::haveRight("plugin_monitoring_displayview", READ)) {
   echo "<table class='tab_cadre' width='950'>";
   echo "<tr class='tab_bg_1'>";
   if (Session::haveRight("plugin_monitoring_servicescatalog", READ)) {
      $toDisplayArea++;
      echo "<th align='center' height='30' width='33%'>";
      echo "<a href='".$CFG_GLPI['root_doc']."/plugins/monitoring/front/servicescatalog.php'>".__('Services catalogs', 'monitoring')."</a>";
      echo "</th>";
   }

   if (Session::haveRight("plugin_monitoring_weathermap", READ)) {
      $toDisplayArea++;
      echo "<th align='center' height='30' width='33%'>";
      echo "<a href='".$CFG_GLPI['root_doc']."/plugins/monitoring/front/weathermap.php'>".__('Weathermaps', 'monitoring')."</a>";
      echo "</th>";
   }
   echo "</tr>";

   echo "<tr class='tab_bg_1'>";
   if (Session::haveRight("plugin_monitoring_displayview", READ)) {
      $toDisplayArea++;
      echo "<th align='center' height='30' width='34%'>";
      echo "<a href='".$CFG_GLPI['root_doc']."/plugins/monitoring/front/customitem_gauge.php'>".PluginMonitoringCustomitem_Gauge::getTypeName()."</a>";
      echo "</th>";

      echo "<th align='center' height='30' width='34%'>";
      echo "<a href='".$CFG_GLPI['root_doc']."/plugins/monitoring/front/customitem_counter.php'>".PluginMonitoringCustomitem_Counter::getTypeName()."</a>";
      echo "</th>";
   }
   echo "</tr>";
   echo "</table>";
   echo "<br/>";
}

if (Session::haveRight("plugin_monitoring_componentscatalog", READ)) {
   $toDisplayArea++;
   echo "<table class='tab_cadre' width='950'>";
   echo "<th height='30'>";
   echo "<a href='".$CFG_GLPI['root_doc']."/plugins/monitoring/front/componentscatalog.php'>".__('Components catalog', 'monitoring')."</a>";
   echo "</th>";
   echo "</tr>";
   echo "</table>";

   echo "<br/>";
}

if (Session::haveRight("config", READ)) {

   $toDisplayArea++;
   echo "<table class='tab_cadre' width='950'>";
   echo "<tr class='tab_bg_1'>";
   echo "<th colspan='5' height='30' width='55%'>";
   echo "<a href='".$CFG_GLPI['root_doc']."/plugins/monitoring/front/component.php'>".__('Components', 'monitoring')."</a>";
   echo "</th>";

   echo "<th rowspan='2' width='11%'>";
   echo "<a href='".$CFG_GLPI['root_doc']."/plugins/monitoring/front/contacttemplate.php'>".__('Contact templates', 'monitoring')."</a>";
   echo "</th>";

   echo "<th rowspan='2' width='11%'>";
   echo "<a href='".$CFG_GLPI['root_doc']."/plugins/monitoring/front/notificationcommand.php'>".__('Notification commands', 'monitoring')."</a>";
   echo "</th>";

   echo "<th rowspan='2' width='11%'>";
   echo "<a href='".$CFG_GLPI['root_doc']."/plugins/monitoring/front/realm.php'>".__('Reamls', 'monitoring')."</a>";
   echo "</th>";

   echo "<th rowspan='2'>";
   echo "<a href='".$CFG_GLPI['root_doc']."/plugins/monitoring/front/tag.php'>".__('Tag', 'monitoring')."</a>";
   echo "</th>";
   echo "</tr>";


   echo "<tr class='tab_bg_1'>";
   echo "<th width='11%' height='25'>";
   echo "<a href='".$CFG_GLPI['root_doc']."/plugins/monitoring/front/command.php'>".__('Commands', 'monitoring')."</a>";
   echo "</th>";

   echo "<th width='11%'>";
   echo "<a href='".$CFG_GLPI['root_doc']."/plugins/monitoring/front/check.php'>".__('Check definition', 'monitoring')."</a>";
   echo "</th>";

   echo "<th width='11%'>";
   if (Session::haveRight('calendar', READ)) {
      echo "<a href='".$CFG_GLPI['root_doc']."/front/calendar.php'>".__('Calendar')."</a>";
   }
   echo "</th>";

   echo "<th width='11%'>";
   echo "<a href='".$CFG_GLPI['root_doc']."/plugins/monitoring/front/eventhandler.php'>".__('Event handler', 'monitoring')."</a>";
   echo "</th>";

   echo "<th width='11%'>";
   echo "<a href='".$CFG_GLPI['root_doc']."/plugins/monitoring/front/perfdata.php'>".__('Graph templates', 'monitoring')."</a>";
   echo "</th>";
   echo "</tr>";

   echo "</table>";
}

if ($toDisplayArea <= 0) {
   echo "<table class='tab_cadre' width='950'>";
   echo "<tr class='tab_bg_1'>";
   echo "<th height='80'>";
   echo __('Sorry, your profile does not allow any views in the Monitoring', 'monitoring');
   echo "</th>";
   echo "</tr>";
   echo "</table>";
}

Html::footer();

?>