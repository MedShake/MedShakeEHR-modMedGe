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
 * Js pour le formulaire issue de grossesse module médecine générale
 *
 * @author Bertrand Boutillier <b.boutillier@gmail.com>
 */

$(document).ready(function() {

  //echos IG : observation nombre foetus
  $('body').on("keyup, change", '#id_igNbFoetus_id', function() {
    afficherFxNbFoetus();
  });

  //issue grossesse : calcul terme à l'acc
  $('body').on("focusout", '#id_igDate_id', function() {
    terme = termeAccCalc($('#id_igDate_id').val(), $('#id_DDR_id').val(), $('#id_ddgReel_id').val());
    if (terme != null) {
      $('#id_igTermeFA_id, #id_igTermeFB_id, #id_igTermeFC_id').val(terme);
    }
  });

});
