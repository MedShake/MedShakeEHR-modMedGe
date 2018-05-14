/*
 * This file is part of MedShakeEHR.
 *
 * Copyright (c) 2017
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
 * Js pour le formulaire 6 Synthèse grossesse en cours
 *
 * @author Bertrand Boutillier <b.boutillier@gmail.com>
 */

$(document).ready(function() {

  // Ajutement de hauteur pour le textarea principal
  $("#formName_gynObsSyntheseObs textarea").each(function(index) {
    $(this).css("overflow", "hidden");
    auto_grow(this);
  });
  $("#formName_gynObsSyntheseObs textarea").on("keyup", function() {
    $(this).css("overflow", "auto");
  });

  // Si on change la DDR
  $("#before_DDR").on("dp.change", function(e) {
    calcAndDisplayTdj();
    calcAndDisplayDdgt();
    calcAndDisplayT9m();
    if (typeof(dicomAutoSendPatient2Echo) != "undefined") {
      if (dicomAutoSendPatient2Echo == true) {
        prepareEcho('nopopup');
      }
    }
  });


  // Si on change la DDG retenue
  $("#before_ddgReel").on("dp.change", function(e) {
    calcAndDisplayTdj();
    calcAndDisplayT9m();
    if (typeof(dicomAutoSendPatient2Echo) != "undefined") {
      if (dicomAutoSendPatient2Echo == true) {
        prepareEcho('nopopup');
      }
    }
  });

  //close grossesseEnCours
  $('body').on("click", "#closeGro", function(e) {
    if (confirm("Voulez-vous fermer définitivement ce suivi de grossesse ?")) {
      if (confirm("Confirmez-vous réellement ?")) {

      } else {
        e.preventDefault();
      }
    } else {
      e.preventDefault();
    }
  });

  calcAndDisplayDdgt();
  calcAndDisplayTdj();
  calcAndDisplayT9m()
});

// calculer et afficher terme du jour
function calcAndDisplayTdj() {
  tdj = tdjCalc($('#id_DDR_id').val(), $('#id_ddgReel_id').val());
  if (tdj['status'] == 'ok') {
    $('#id_termeDuJour_id').val(tdj['human']);
    $('#id_termeDuJour_id').attr('data-tdj4math', tdj['math']);
  } else {
    $('#id_termeDuJour_id').val('');
    $('#id_termeDuJour_id').attr('data-tdj4math', '');
  }
}

// calculer et afficher terme 9 mois
function calcAndDisplayT9m() {
  t9 = terme9mCalc($('#id_DDR_id').val(), $('#id_ddgReel_id').val());
  if (t9['status'] == 'ok') {
    $('#id_terme9mois_id').val(t9['human']);
  } else {
    $('#id_terme9mois_id').val('');
  }
}


//calculer et afficher DDG théo
function calcAndDisplayDdgt() {
  ddgt = ddgtCalc($('#id_DDR_id').val());
  $('#id_ddg_id').val(ddgt);
}
