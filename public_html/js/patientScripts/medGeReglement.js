/**
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
 * Js complémentaire pour le module règlement du dossier patient du module médecine générale
 *
 * @author Bertrand Boutillier <b.boutillier@gmail.com>
 *
 */

$(document).ready(function() {

  //observer le changement sur dépassement
  $('#newReglement').on("change", "#mcContexte, #mcAge, #mcSituation, #mcPeriode, #mcActesECG, #mcActesFrottis, #mcIK", function(e) {
    e.preventDefault();
    calculerConsultation();
  });

  // reset au changement d'onglet
  $('#newReglement').on("click",'#menuReglementMedGe a' , function(e) {
    factureActuelle = [];
    resetModesReglement();
    $('#detFacturation').hide();
    $('#detFacturation tbody').html('');
    $('input[name="acteID"]').val('');
    $(".regleTarifCejour").attr('data-tarifdefaut', 0);
    $(".regleDepaCejour").attr('data-tarifdefaut', 0);
    setDefautTarifEtDepa();
    calcResteDu();
  });

  //onget consultations
  $('#consultation-tab').on('shown.bs.tab', function (e) {
      $( "#mcAge" ).trigger( "change" );
  });

});

// calculer / ajuster pour le Cabinet
function calculerConsultation() {
  $.ajax({
    url: urlBase + '/module/ajax/medGeCalcHonoraires/',
    type: 'post',
    data: {
      patientID: $('#identitePatient').attr("data-patientID"),
      mcContexte: $('#mcContexte option:selected').val(),
      mcAge: $('#mcAge option:selected').val(),
      mcSituation: $('#mcSituation option:selected').val(),
      mcPeriode: $('#mcPeriode option:selected').val(),
      mcActesECG: ($('#mcActesECG').is(':checked'))?true:false,
      mcActesFrottis: ($('#mcActesFrottis').is(':checked'))?true:false,
      mcIK:  $('#mcIK').val()
    },
    dataType: "json",
    success: function(data) {
      $('#mcContexte').html(data.mcContexte);
      $('#mcAge').html(data.mcAge);
      $('#mcSituation').html(data.mcSituation);
      $('#mcPeriode').html(data.mcPeriode);
      $('#ikHelpText').html(data.ikHelpText);

      if($('#mcContexte option:selected').val() == 'visite') {
        $('#mcIKblock').addClass('d-flex').removeClass('d-none');
      } else {
        $('#mcIKblock').addClass('d-none').removeClass('d-flex');
      }
      $(".regleTarifCejour").attr('data-tarifdefaut', data['tarif']);
      $(".regleDepaCejour").attr('data-tarifdefaut', 0);
      construireTableauActes(data)
      setDefautTarifEtDepa();
      calcResteDu();
      $('#detFacturation').show();
    },
    error: function() {
      alert_popup("danger", 'Problème, rechargez la page !');
    }
  });
}
