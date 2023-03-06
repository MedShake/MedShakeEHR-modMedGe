<?php
/*
 * This file is part of MedShakeEHR.
 *
 * Copyright (c) 2018
 * Bertrand Boutillier <b.boutillier@gmail.com>
 * http://www.medshake.net
 *
 * MedShakeEHR is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * any later version.
 *
 * MedShakeEHR is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with MedShakeEHR.  If not, see <http://www.gnu.org/licenses/>.
 */

/**
 * Patient : les actions avec reload de page
 * Module Gynéco Obstétrique
 *
 * @author Bertrand Boutillier <b.boutillier@gmail.com>
 */


//$debug='';
$m = $match['params']['m'];

$acceptedModes = array(
	'installNewGro', // déclencher un nouveau suivi de grossesse
	'closeGro' //fermer suivi de grossesse
);

if (!in_array($m, $acceptedModes)) {
	die;
}


// Installer une nnouvelle grossesse
if ($m == 'installNewGro' and $match['params']['patientID'] > 0) {
	include('inc-action-installNewGro.php');
}
// Fermer un suivi de grossesse
elseif ($m == 'closeGro') {
	include('inc-action-closeGro.php');
}
