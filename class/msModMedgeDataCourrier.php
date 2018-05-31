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
 *
 * Données et calcules complémentaires :
 * - liés à la présence de typeID particuliers dans le tableau de tags
 * passé au modèle de courrier
 * - appelés en fonction du modèle (modeleID) du courrier
 * - appelés par défaut si existe par les methodes de la class msCourrier
 *
 * Module médecine générale
 *
 *
 * @author Bertrand Boutillier <b.boutillier@gmail.com>
 */

class msModMedgeDataCourrier
{

  /**
   * Extractions complémentaires générales pour getCrData() de msCourrier
   * @param  array $d         tableau de tags
   * @return void
   */
    public static function getCrDataCompleteModule(&$d) {

      //atcd du patient (data du formulaire latéral)
      $atcd = new msCourrier();
      $atcd = $atcd->getExamenData($d['patientID'], 'medGeATCD', 0);
      if(is_array($atcd)) {
        foreach($atcd as $k=>$v) {
          if(!in_array($k, array_keys($d))) $d[$k]=$v;
        }
      }
      // résoudre le problème de l'IMC
      unset($d['imc']);
      if(isset($d['poids'],$d['taillePatient'])) $d['imc']=msModMedgeCalcMed::imc($d['poids'],$d['taillePatient']);

    }

}
