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

  //observer le changement pour consultation
  $('#newReglement').on("change", "#consultation .mcContexte, #consultation .mcAge, #consultation .mcSituation, #consultation .mcPeriode, #mcActesECG, #mcActesFrottis, #consultation .mcIK", function(e) {
    e.preventDefault();
    calculerConsultation();
  });

  //observer le changement pour calc Sutures
  $('#newReglement').on("change", "#sutures input[type='checkbox'], #sutures select, #sutures input[type='number']", function(e) {
    e.preventDefault();
    calculerSuturesCCAM();
  });
  $('#newReglement').on("keyup", "#sutures input[type='text']", function(e) {
    e.preventDefault();
    calculerSuturesCCAM();
  });

  // reset au changement d'onglet
  $('#newReglement').on("click", '#menuReglementMedGe a', function(e) {
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

  //onglet consultations
  $('#consultation-tab').on('shown.bs.tab', function(e) {
    $("#consultation .mcAge").trigger("change");
  });

  //onglet sutures
  $('#sutures-tab').on('shown.bs.tab', function(e) {
    $("#sutures .mcAge").trigger("change");
  });

});

// calculer / ajuster pour le Cabinet
function calculerConsultation() {
  $.ajax({
    url: urlBase + '/module/ajax/medGeCalcHonoraires/',
    type: 'post',
    data: {
      mode: 'calcCV',
      patientID: $('#identitePatient').attr("data-patientID"),
      mcContexte: $('#consultation .mcContexte option:selected').val(),
      mcAge: $('#consultation .mcAge option:selected').val(),
      mcSituation: $('#consultation .mcSituation option:selected').val(),
      mcPeriode: $('#consultation .mcPeriode option:selected').val(),
      mcActesECG: ($('#mcActesECG').is(':checked')) ? true : false,
      mcActesFrottis: ($('#mcActesFrottis').is(':checked')) ? true : false,
      mcIK: $('#consultation .mcIK').val(),
      regleSecteurGeoTarifaire : $("#newReglement input[name='regleSecteurGeoTarifaire']").val(),
      regleSecteurHonoraires : $("#newReglement input[name='regleSecteurHonoraires']").val(),
      regleSecteurHonorairesNgap : $("#newReglement input[name='regleSecteurHonorairesNgap']").val(),
    },
    dataType: "json",
    success: function(data) {
      $('#consultation .mcContexte').html(data.mcContexte);
      $('#consultation .mcAge').html(data.mcAge);
      $('#consultation .mcSituation').html(data.mcSituation);
      $('#consultation .mcPeriode').html(data.mcPeriode);
      $('#consultation .ikHelpText').html(data.ikHelpText);

      if ($('#consultation .mcContexte option:selected').val() == 'visite') {
        $('#consultation .mcIKblock').addClass('d-flex').removeClass('d-none');
      } else {
        $('#consultation .mcIKblock').addClass('d-none').removeClass('d-flex');
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


function calculerSuturesCCAM() {
  actesFaits = {
    pSourcil: ($('#pSourcil').is(':checked')) ? true : false,
    pNez: ($('#pNez').is(':checked')) ? true : false,
    pLevre: ($('#pLevre').is(':checked')) ? true : false,
    pLevreTrans: $('#pLevreTrans').val(),
    pAuricule: ($('#pAuricule').is(':checked')) ? true : false,
    pFaceSuper: $('#pFaceSuper').val(),
    pFacePro: $('#pFacePro').val(),
    pPulpoUngu: ($('#pPulpoUngu').is(':checked')) ? true : false,
    pPulpoUnguNb: $('#pPulpoUnguNb').val(),
    pMainSuper: $('#pMainSuper').val(),
    pMainPro: ($('#pMainPro').is(':checked')) ? true : false,
    pAutreSuper: $('#pAutreSuper').val(),
    pAutrePro: $('#pAutrePro').val(),
    mcLesions: $('#sutures .mcLesions option:selected').val(),
  };

  $.ajax({
    url: urlBase + '/module/ajax/medGeCalcHonoraires/',
    type: 'post',
    data: {
      mode: 'calcSutures',
      patientID: $('#identitePatient').attr("data-patientID"),
      mcAge: $('#consultation .mcAge option:selected').val(),
      mcContexte: $('#sutures .mcContexte option:selected').val(),
      mcSituation: $('#sutures .mcSituation option:selected').val(),
      mcPeriode: $('#sutures .mcPeriode option:selected').val(),
      mcIK: $('#sutures .mcIK').val(),
      actesFaits: actesFaits,
      regleSecteurGeoTarifaire : $("#newReglement input[name='regleSecteurGeoTarifaire']").val(),
      regleSecteurHonoraires : $("#newReglement input[name='regleSecteurHonoraires']").val(),
      regleSecteurHonorairesNgap : $("#newReglement input[name='regleSecteurHonorairesNgap']").val(),
    },
    dataType: "json",
    success: function(data) {

      $('#sutures .mcContexte').html(data.mcContexte);
      $('#sutures .mcSituation').html(data.mcSituation);
      $('#sutures .mcPeriode').html(data.mcPeriode);
      $('#sutures .ikHelpText').html(data.ikHelpText);

      if(data.messagesInfos.length > 0) {
        $('#messagesInfos').removeClass('d-none');
        $('#messagesInfosListe').html(data.messagesInfos);
      } else {
        $('#messagesInfos').addClass('d-none');
        $('#messagesInfosListe').html('');
      }

      if ($('#sutures .mcContexte option:selected').val() == 'visite') {
        $('#sutures .mcIKblock').addClass('d-flex').removeClass('d-none');
      } else {
        $('#sutures .mcIKblock').addClass('d-none').removeClass('d-flex');
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
