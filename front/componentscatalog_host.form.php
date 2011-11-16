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


define('GLPI_ROOT', '../../..');
include (GLPI_ROOT . "/inc/includes.php");


$pmComponentscatalog_Host = new PluginMonitoringComponentscatalog_Host();

if (isset ($_POST["add"])) {
   $componentscatalogs_hosts_id = $pmComponentscatalog_Host->add($_POST);
   $pmComponentscatalog_Host->linkComponentsToItem($_POST['plugin_monitoring_componentscalalog_id'], 
                                                   $componentscatalogs_hosts_id);
   glpi_header($_SERVER['HTTP_REFERER']);
} else if (isset($_POST["deleteitem"])) {
   foreach ($_POST["item"] as $id=>$num) {
      $pmComponentscatalog_Host->delete(array('id'=>$id));
      $pmComponentscatalog_Host->unlinkComponentsToItem($id);
   }
   glpi_header($_SERVER['HTTP_REFERER']);
}

commonFooter();

?>