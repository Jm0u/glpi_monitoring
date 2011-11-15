<?php

/*
   ----------------------------------------------------------------------
   Monitoring plugin for GLPI
   Copyright (C) 2010-2011 by the GLPI plugin monitoring Team.

   https://forge.indepnet.net/projects/monitoring/
   ----------------------------------------------------------------------

   LICENSE

   This file is part of Monitoring plugin for GLPI.

   Monitoring plugin for GLPI is free software: you can redistribute it and/or modify
   it under the terms of the GNU General Public License as published by
   the Free Software Foundation, either version 2 of the License, or
   any later version.

   Monitoring plugin for GLPI is distributed in the hope that it will be useful,
   but WITHOUT ANY WARRANTY; without even the implied warranty of
   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
   GNU General Public License for more details.

   You should have received a copy of the GNU General Public License
   along with Monitoring plugin for GLPI.  If not, see <http://www.gnu.org/licenses/>.

   ------------------------------------------------------------------------
   Original Author of file: David DURIEUX
   Co-authors of file:
   Purpose of file:
   ----------------------------------------------------------------------
 */

if (!defined('GLPI_ROOT')) {
   die("Sorry. You can't access directly to this file");
}

class PluginMonitoringShinken extends CommonDBTM {
   

   function generateConfig() {
      global $DB,$CFG_GLPI,$LANG;

      


      return true;
   }


   function constructFile($name, $array) {
      $config = '';
      $config .= "define ".$name."{\n";
      foreach ($array as $key => $value) {
         $c = 35;
         $c = $c - strlen($key);
         $config .= "       ".$key;
         for ($t=0; $t < $c; $t++) {
            $config .= " ";
         }
         $config .= $value."\n";
      }
      $config .= "}\n";
      $config .= "\n\n";
      return $config;
   }


   function generateCommandsCfg($file=0) {
      
      $pluginMonitoringCommand = new PluginMonitoringCommand();
      $pluginMonitoringNotificationcommand = new PluginMonitoringNotificationcommand();

      $a_commands = array();
      $i=0;

      $a_list = $pluginMonitoringCommand->find();
      $a_listnotif = $pluginMonitoringNotificationcommand->find();
      $a_list = array_merge($a_list, $a_listnotif);
      foreach ($a_list as $data) {
         if ($data['command_name'] != "bp_rule") {
            $a_commands[$i]['name'] = $data['name'];
            $a_commands[$i]['command_name'] = $data['command_name'];
            $a_commands[$i]['command_line'] = $data['command_line'];
            $i++;
         }
      }

      if ($file == "1") {
         $config = "# Generated by plugin monitoring for GLPI\n# on ".date("Y-m-d H:i:s")."\n\n";
         foreach ($a_commands as $data) {
            $config .= "# ".$data['name']."\n";
            unset($data['name']);
            $config .= $this->constructFile("command", $data);
         }
         return array('commands.cfg', $config);         
      } else {
         return $a_commands;
      }
   }


   
   function generateHostsCfg($file=0) {
      global $DB;

      $pMonitoringService           = new PluginMonitoringService();
      $pluginMonitoringContact      = new PluginMonitoringContact();
      $pluginMonitoringHost_Contact = new PluginMonitoringHost_Contact();
      $pluginMonitoringCommand      = new PluginMonitoringCommand();
      $pluginMonitoringCheck        = new PluginMonitoringCheck();
      $pmComponent           = new PluginMonitoringComponent();
      $calendar      = new Calendar();
      $user          = new User();
      $networkPort   = new NetworkPort();

      $a_hosts = array();
      $i=0;
      
      $command_ping = current($pluginMonitoringCommand->find("`command_name`='check_host_alive'", "", 1));
      $a_component = current($pmComponent->find("`plugin_monitoring_commands_id`='".$command_ping['id']."'", "", 1));

      $query = "SELECT * FROM `glpi_plugin_monitoring_componentscatalogs_hosts`
         GROUP BY `itemtype`, `items_id`";
      $result = $DB->query($query);
      while ($data=$DB->fetch_array($result)) {
         
         $classname = $data['itemtype'];
         $class = new $classname;
         if ($class->getFromDB($data['items_id'])) {
            $a_hosts[$i]['host_name'] = $classname."-".$data['id']."-".preg_replace("/[^A-Za-z0-9]/","",$class->fields['name']);
            $a_hosts[$i]['alias'] = $a_hosts[$i]['host_name'];
               $ip = $class->fields['name'];
               if ($data['itemtype'] == 'NetworkEquipment') {
                  if ($class->fields['ip'] != '') {
                     $ip = $class->fields['ip'];
                  }
               } else {
                  $a_listnetwork = $networkPort->find("`itemtype`='".$data['itemtype']."'
                     AND `items_id`='".$data['items_id']."'", "`id`");
                  foreach ($a_listnetwork as $datanetwork) {
                     if ($datanetwork['ip'] != '' 
                             AND $datanetwork['ip'] != '127.0.0.1'
                             AND $ip != '') {
                        $ip = $datanetwork['ip'];
                        break;
                     }
                  }
               }
            $a_hosts[$i]['address'] = $ip;
            $a_hosts[$i]['parents'] = "";

            $a_fields = array();

            $a_fields = $a_component;

               $pluginMonitoringCommand->getFromDB($a_fields['plugin_monitoring_commands_id']);
            $a_hosts[$i]['check_command'] = $pluginMonitoringCommand->fields['command_name'];
               $pluginMonitoringCheck->getFromDB($a_fields['plugin_monitoring_checks_id']);
            $a_hosts[$i]['check_interval'] = $pluginMonitoringCheck->fields['check_interval'];
            $a_hosts[$i]['retry_interval'] = $pluginMonitoringCheck->fields['retry_interval'];
            $a_hosts[$i]['max_check_attempts'] = $pluginMonitoringCheck->fields['max_check_attempts'];
            if ($calendar->getFromDB($a_fields['calendars_id'])) {
               $a_hosts[$i]['check_period'] = $calendar->fields['name'];
            } else {
               $a_hosts[$i]['check_period'] = "24x7";
            }

            $a_hosts[$i]['contacts'] = '';
            $a_hosts[$i]['process_perf_data'] = '1';
            $a_hosts[$i]['notification_interval'] = '30';
            $a_hosts[$i]['notification_period'] = '24x7';
            $a_hosts[$i]['notification_options'] = 'd,u,r';
            $i++;
         }
      }
      

      if ($file == "1") {
         $config = "# Generated by plugin monitoring for GLPI\n# on ".date("Y-m-d H:i:s")."\n\n";

         foreach ($a_hosts as $data) {
            $config .= $this->constructFile("host", $data);
         }
         return array('hosts.cfg', $config);

      } else {
         return $a_hosts;
      }
   }

   
   
   function generateServicesCfg($file=0) {
      global $DB;
      
//      $pluginMonitoringContact      = new PluginMonitoringContact();
//      $pluginMonitoringHost_Contact = new PluginMonitoringHost_Contact();
      $pMonitoringCommand      = new PluginMonitoringCommand();
      $pMonitoringCheck        = new PluginMonitoringCheck();
//      $pluginMonitoringServicescatalog = new PluginMonitoringServicescatalog();
//      $pluginMonitoringBusinessrule = new PluginMonitoringBusinessrule();
      $calendar      = new Calendar();
//      $user          = new User();
      $hostnamebp = '';
      
      $a_services = array();
      $i=0;
      
      $query = "SELECT * FROM `glpi_plugin_monitoring_components`";
      $result = $DB->query($query);
      while ($data=$DB->fetch_array($result)) {
         // Select services to get host associated
         $a_hostname = array();
         $queryh = "SELECT `glpi_plugin_monitoring_componentscatalogs_hosts`.* FROM `glpi_plugin_monitoring_services`
            LEFT JOIN `glpi_plugin_monitoring_componentscatalogs_hosts` 
               ON `plugin_monitoring_componentscatalogs_hosts_id`=`glpi_plugin_monitoring_componentscatalogs_hosts`.`id`
            WHERE `plugin_monitoring_components_id`='".$data['id']."'";
         $resulth = $DB->query($queryh);
         while ($datah=$DB->fetch_array($resulth)) {
            $itemtype = $datah['itemtype'];
            $item = new $itemtype();
            $item->getFromDB($datah['items_id']);
            $a_hostname[] = $itemtype."-".$datah['items_id']."-".preg_replace("/[^A-Za-z0-9]/","",$item->fields['name']);
         }         
         $a_services[$i]['host_name'] = implode(",", array_unique($a_hostname));
         $hostnamebp = $a_services[$i]['host_name']; // For business rules
         
         $a_services[$i]['service_description'] = preg_replace("/[^A-Za-z0-9]/","",$data['name'])."-".$data['id'];
         $a_fields = array();
         $pMonitoringCommand->getFromDB($data['plugin_monitoring_commands_id']);
         // Manage arguments
         $array = array();
         preg_match_all("/\\$(ARG\d+)\\$/", $pMonitoringCommand->fields['command_line'], $array);
         $a_arguments = importArrayFromDB($data['arguments']);
         $args = '';
         foreach ($array[0] as $arg) {
            if ($arg != '$PLUGINSDIR$'
                    AND $arg != '$HOSTADDRESS$'
                    AND $arg != '$MYSQLUSER$'
                    AND $arg != '$MYSQLPASSWORD$') {
               $arg = str_replace('$', '', $arg);
               if (!isset($a_arguments[$arg])) {
                  $args .= '!';
               } else {
                  if (strstr($a_arguments[$arg], "[")) {
                     $a_arguments[$arg] = pluginMonitoringService::convertArgument($data['id'], $a_arguments[$arg]);
                  }
                  $args .= '!'.$a_arguments[$arg];
                  if ($a_arguments[$arg] == ''
                          AND $data['alias_command'] != '') {
                     $args .= $data['alias_command'];
                  }
               }
            }
         }
         // End manage arguments
         $a_services[$i]['check_command'] = $pMonitoringCommand->fields['command_name'].$args;
            $pMonitoringCheck->getFromDB($data['plugin_monitoring_checks_id']);
         $a_services[$i]['check_interval'] = $pMonitoringCheck->fields['check_interval'];
         $a_services[$i]['retry_interval'] = $pMonitoringCheck->fields['retry_interval'];
         $a_services[$i]['max_check_attempts'] = $pMonitoringCheck->fields['max_check_attempts'];
         if ($calendar->getFromDB($data['calendars_id'])) {
            $a_services[$i]['check_period'] = $calendar->fields['name'];            
         }
            $a_contacts = array();
//            $a_list_contact = $pluginMonitoringHost_Contact->find("`plugin_monitoring_hosts_id`='".$data['id']."'");
//            foreach ($a_list_contact as $data_contact) {
//               $pluginMonitoringContact->getFromDB($data_contact['plugin_monitoring_contacts_id']);
//               $user->getFromDB($pluginMonitoringContact->fields['users_id']);
//               $a_contacts[] = $user->fields['name'];
//            }
         $a_services[$i]['contacts'] = implode(',', $a_contacts);

         $a_services[$i]['notification_interval'] = '30';
         $a_services[$i]['notification_period'] = '24x7';
         $a_services[$i]['notification_options'] = 'w,c,r';
         $a_services[$i]['active_checks_enabled'] = '1';
         $a_services[$i]['process_perf_data'] = '1';
         $a_services[$i]['active_checks_enabled'] = '1';
         $a_services[$i]['passive_checks_enabled'] = '1';
         $a_services[$i]['parallelize_check'] = '1';
         $a_services[$i]['obsess_over_service'] = '1';
         $a_services[$i]['check_freshness'] = '1';
         $a_services[$i]['freshness_threshold'] = '1';
         $a_services[$i]['notifications_enabled'] = '1';
         $a_services[$i]['event_handler_enabled'] = '0';
         $a_services[$i]['event_handler'] = 'super_event_kill_everyone!DIE';
         $a_services[$i]['flap_detection_enabled'] = '1';
         $a_services[$i]['failure_prediction_enabled'] = '1';
         $a_services[$i]['retain_status_information'] = '1';
         $a_services[$i]['retain_nonstatus_information'] = '1';
         $a_services[$i]['is_volatile'] = '0';
         $a_services[$i]['_httpstink'] = 'NO';

         $i++;
      }

//      // Business rules....
//      $pluginMonitoringServiceH = new PluginMonitoringService();
//      $a_listBA = $pluginMonitoringServicescatalog->find();
//      foreach ($a_listBA as $dataBA) {
//
//         $a_services[$i]['contacts'] = 'ddurieux';
//         $pMonitoringCheck->getFromDB($pMonitoringServicedef->fields['plugin_monitoring_checks_id']);
//         $a_services[$i]['check_interval'] = $pMonitoringCheck->fields['check_interval'];
//         $a_services[$i]['retry_interval'] = $pMonitoringCheck->fields['retry_interval'];
//         $a_services[$i]['max_check_attempts'] = $pMonitoringCheck->fields['max_check_attempts'];
//         if ($calendar->getFromDB($pMonitoringServicedef->fields['calendars_id'])) {
//            $a_services[$i]['check_period'] = $calendar->fields['name'];            
//         }
//         $a_services[$i]['host_name'] = $hostnamebp;
//         $a_services[$i]['service_description'] = preg_replace("/[^A-Za-z0-9]/","",$dataBA['name'])."-".$dataBA['id']."-businessrules";
//         $command = "bp_rule!";
//         $pMonitoringBusinessrulegroup = new PluginMonitoringBusinessrulegroup();
//         $a_grouplist = $pMonitoringBusinessrulegroup->find("`plugin_monitoring_servicescatalogs_id`='".$dataBA['id']."'");
//         $a_group = array();
//         foreach ($a_grouplist as $gdata) {
//            $a_listBR = $pluginMonitoringBusinessrule->find(
//                    "`plugin_monitoring_businessrulegroups_id`='".$gdata['id']."'");
//            foreach ($a_listBR as $dataBR) {
//               $pluginMonitoringService->getFromDB($dataBR['plugin_monitoring_services_id']);
//
//               $pluginMonitoringServiceH->getFromDB($pluginMonitoringService->fields['plugin_monitoring_services_id']);
//               $itemtype = $pluginMonitoringServiceH->fields['itemtype'];
//               $item = new $itemtype();
//               if ($item->getFromDB($pluginMonitoringServiceH->fields['items_id'])) {           
//                  $hostname = $itemtype."-".$pluginMonitoringServiceH->fields['id']."-".preg_replace("/[^A-Za-z0-9]/","",$item->fields['name']);
//
//                  if ($gdata['operator'] == 'and'
//                          OR $gdata['operator'] == 'or'
//                          OR strstr($gdata['operator'], ' of:')) {
//
//                     $operator = '|';
//                     if ($gdata['operator'] == 'and') {
//                        $operator = '&';
//                     }
//                     if (!isset($a_group[$gdata['id']])) {
//                        $a_group[$gdata['id']] = '';
//                        if (strstr($gdata['operator'], ' of:')) {
//                           $a_group[$gdata['id']] = $gdata['operator'];
//                        }
//                        $a_group[$gdata['id']] .= $hostname.",".$pluginMonitoringService->fields['name']."-".$pluginMonitoringService->fields['id'];
//                     } else {
//                        $a_group[$gdata['id']] .= $operator.$hostname.",".$pluginMonitoringService->fields['name']."-".$pluginMonitoringService->fields['id'];
//                     }
//                  } else {
//                     $a_group[$gdata['id']] = $gdata['operator']." ".$hostname.",".$item->getName()."-".$item->fields['id'];
//                  }
//               }
//            }
//         }
//         foreach ($a_group as $key=>$value) {
//            if (!strstr($value, "&")
//                    AND !strstr($value, "|")) {
//               $a_group[$key] = trim($value);
//            } else {
//               $a_group[$key] = "(".trim($value).")";
//            }
//         }
//         $a_services[$i]['check_command'] = $command.implode("&", $a_group);
//         $a_services[$i]['notification_interval'] = '30';
//         $a_services[$i]['notification_period'] = '24x7';
//         $a_services[$i]['check_period'] = '24x7';
//         $a_services[$i]['notification_options'] = 'w,c,r';
//         $a_services[$i]['active_checks_enabled'] = '1';
//         $a_services[$i]['process_perf_data'] = '1';
//         $a_services[$i]['active_checks_enabled'] = '1';
//         $a_services[$i]['passive_checks_enabled'] = '1';
//         $a_services[$i]['parallelize_check'] = '1';
//         $a_services[$i]['obsess_over_service'] = '1';
//         $a_services[$i]['check_freshness'] = '1';
//         $a_services[$i]['freshness_threshold'] = '1';
//         $a_services[$i]['notifications_enabled'] = '1';
//         $a_services[$i]['event_handler_enabled'] = '0';
//         $a_services[$i]['event_handler'] = 'super_event_kill_everyone!DIE';
//         $a_services[$i]['flap_detection_enabled'] = '1';
//         $a_services[$i]['failure_prediction_enabled'] = '1';
//         $a_services[$i]['retain_status_information'] = '1';
//         $a_services[$i]['retain_nonstatus_information'] = '1';
//         $a_services[$i]['is_volatile'] = '0';
//         $a_services[$i]['_httpstink'] = 'NO';
//         $a_services[$i]['contacts'] = 'ddurieux';
//         $i++;
//      }
      
      if ($file == "1") {
         $config = "# Generated by plugin monitoring for GLPI\n# on ".date("Y-m-d H:i:s")."\n\n";

         foreach ($a_services as $data) {
            $config .= $this->constructFile("service", $data);
         }
         return array('services.cfg', $config);

      } else {
         return $a_services;
      }
   }

   
   


   function generateContactsCfg($file=0) {
      global $DB;
      
      $a_contacts = array();
      $i=0;

      $query = "SELECT * FROM `glpi_plugin_monitoring_contacts_items`";
      $result = $DB->query($query);
      $a_users_used = array();
      while ($data=$DB->fetch_array($result)) {
         if ($data['users_id'] > 0) {
            if ((!isset($a_users_used[$data['users_id']]))) {
               $a_contacts = $this->_addContactUser($a_contacts, $data['users_id'], $i);
               $i++;  
               $a_users_used[$data['users_id']] = 1;
            }
         } else if ($data['groups_id'] > 0) {
            $queryg = "SELECT * FROM `glpi_groups_users`
               WHERE `groups_id`='".$data['groups_id']."'";
            $resultg = $DB->query($queryg);
            while ($datag=$DB->fetch_array($resultg)) {
               if ((!isset($a_users_used[$datag['users_id']]))) {
                  $a_contacts = $this->_addContactUser($a_contacts, $datag['users_id'], $i);
                  $i++;
                  $a_users_used[$data['users_id']] = 1;
               }
            }
         }        
      
      }

      if ($file == "1") {
         $config = "# Generated by plugin monitoring for GLPI\n# on ".date("Y-m-d H:i:s")."\n\n";

         foreach ($a_contacts as $data) {
            $config .= $this->constructFile("contact", $data);
         }
         return array('contacts.cfg', $config);

      } else {
         return $a_contacts;
      }
   }
   
   
   
   function _addContactUser($a_contacts, $users_id, $i) {
      global $DB; 
      
      $pluginMonitoringContact             = new PluginMonitoringContact();
      $pluginMonitoringNotificationcommand = new PluginMonitoringNotificationcommand();
      $pmContacttemplate = new PluginMonitoringContacttemplate();
      $user     = new User();
      $calendar = new Calendar();
      
      $user->getFromDB($users_id);
      
      // Get template
      $a_pmcontact = current($pluginMonitoringContact->find("`users_id`='".$users_id."'", "", 1));
      if (empty($a_pmcontact)) {
         $a_pmcontact = current($pmContacttemplate->find("`is_default`='1'", "", 1));
      }      
      
      $a_contacts[$i]['contact_name'] = $user->fields['name'];
      $a_contacts[$i]['alias'] = $user->getName();
      $a_contacts[$i]['host_notifications_enabled'] = $a_pmcontact['host_notifications_enabled'];
      $a_contacts[$i]['service_notifications_enabled'] = $a_pmcontact['service_notifications_enabled'];
         $calendar->getFromDB($a_pmcontact['service_notification_period']);
      $a_contacts[$i]['service_notification_period'] = $calendar->fields['name'];
         $calendar->getFromDB($a_pmcontact['host_notification_period']);
      $a_contacts[$i]['host_notification_period'] = $calendar->fields['name'];
         $a_servicenotif = array();
         if ($a_pmcontact['service_notification_options_w'] == '1')
            $a_servicenotif[] = "w";
         if ($a_pmcontact['service_notification_options_u'] == '1')
            $a_servicenotif[] = "u";
         if ($a_pmcontact['service_notification_options_c'] == '1')
            $a_servicenotif[] = "c";
         if ($a_pmcontact['service_notification_options_r'] == '1')
            $a_servicenotif[] = "r";
         if ($a_pmcontact['service_notification_options_f'] == '1')
            $a_servicenotif[] = "f";
         if ($a_pmcontact['service_notification_options_n'] == '1')
            $a_servicenotif = array("n");
         if (count($a_servicenotif) == "0")
            $a_servicenotif = array("n");
      $a_contacts[$i]['service_notification_options'] = implode(",", $a_servicenotif);
         $a_hostnotif = array();
         if ($a_pmcontact['host_notification_options_d'] == '1')
            $a_hostnotif[] = "d";
         if ($a_pmcontact['host_notification_options_u'] == '1')
            $a_hostnotif[] = "u";
         if ($a_pmcontact['host_notification_options_r'] == '1')
            $a_hostnotif[] = "r";
         if ($a_pmcontact['host_notification_options_f'] == '1')
            $a_hostnotif[] = "f";
         if ($a_pmcontact['host_notification_options_s'] == '1')
            $a_hostnotif[] = "s";
         if ($a_pmcontact['host_notification_options_n'] == '1')
            $a_hostnotif = array("n");
         if (count($a_hostnotif) == "0")
            $a_hostnotif = array("n");
      $a_contacts[$i]['host_notification_options'] = implode(",", $a_hostnotif);
         $pluginMonitoringNotificationcommand->getFromDB($a_pmcontact['service_notification_commands']);
      $a_contacts[$i]['service_notification_commands'] = $pluginMonitoringNotificationcommand->fields['command_name'];
         $pluginMonitoringNotificationcommand->getFromDB($a_pmcontact['host_notification_commands']);
      $a_contacts[$i]['host_notification_commands'] = $pluginMonitoringNotificationcommand->fields['command_name'];
      $a_contacts[$i]['email'] = $user->fields['email'];
      $a_contacts[$i]['pager'] = $user->fields['phone'];
      return $a_contacts;
   }



   function generateTimeperiodsCfg($file=0) {

      $calendar = new Calendar();
      $calendarSegment = new CalendarSegment();

      $a_timeperiods = array();
      $i=0;
      
      $a_listcalendar = $calendar->find();
      foreach ($a_listcalendar as $datacalendar) {
         $a_timeperiods[$i]['timeperiod_name'] = $datacalendar['name'];
         $a_timeperiods[$i]['alias'] = $datacalendar['name'];
         $a_listsegment = $calendarSegment->find("`calendars_id`='".$datacalendar['id']."'");
         foreach ($a_listsegment as $datasegment) {
            $begin = preg_replace("/:00$/", "", $datasegment['begin']);
            $end = preg_replace("/:00$/", "", $datasegment['end']);
            switch ($datasegment['day']) {

               case "0":
                  $day = "sunday";
                  break;

               case "1":
                  $day = "monday";
                  break;

               case "2":
                  $day = "tuesday";
                  break;

               case "3":
                  $day = "wednesday";
                  break;

               case "4":
                  $day = "thursday";
                  break;

               case "5":
                  $day = "friday";
                  break;

               case "6":
                  $day = "saturday";
                  break;

            }
            $a_timeperiods[$i][$day] = $begin."-".$end;
         }
         $i++;
      }

      if ($file == "1") {
         $config = "# Generated by plugin monitoring for GLPI\n# on ".date("Y-m-d H:i:s")."\n\n";

         foreach ($a_timeperiods as $data) {
            $config .= $this->constructFile("timeperiod", $data);
         }
         return array('timeperiods.cfg', $config);

      } else {
         return $a_timeperiods;
      }
   }


}

?>