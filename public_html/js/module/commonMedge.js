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
 * Actions communes aux formulaires médicaux et calculs médicaux
 * nécessaires au module
 *
 * @author Bertrand Boutillier <b.boutillier@gmail.com>
 */

 /**
  * Actions communes aux formulaires médicaux
  *
  */

$(document).ready(function() {
  //observer #nouvelleCs et ajuster le nombre de foetus quand cela est nécessaire
  var observer = new MutationObserver(function() {
    afficherFxNbFoetus();
  });
  observer.observe(document.getElementById('nouvelleCs'), { childList: true });

});

//function afficher masque en fonction nombre foetus
function afficherFxNbFoetus() {
  if ($('#id_nbFoetusEcho12_id').val()) nombreFoetus = $('#id_nbFoetusEcho12_id').val();
  else if ($('#id_e11nbembryons_id').val()) nombreFoetus = $('#id_e11nbembryons_id').val();
  else if ($('#id_igNbFoetus_id').val()) nombreFoetus = $('#id_igNbFoetus_id').val();
  else if ($('#id_nbFoetusEcho22_id').val()) nombreFoetus = $('#id_nbFoetusEcho22_id').val();
  else if ($('#id_nbFoetusEcho32_id').val()) nombreFoetus = $('#id_nbFoetusEcho32_id').val();
  else return true;

  if (nombreFoetus == 1) {
    $('.foetusA').removeClass('d-none').addClass('d-flex');
    $('.foetusB, .foetusC').removeClass('d-flex').addClass('d-none');
    disabledForm('.foetusB');
    disabledForm('.foetusC');
  } else if (nombreFoetus == 2) {
    $('.foetusA, .foetusB').removeClass('d-none').addClass('d-flex');
    $('.foetusC').removeClass('d-flex').addClass('d-none');
    enabledForm('.foetusB');
    disabledForm('.foetusC');
  } else if (nombreFoetus == 3) {
    $('.foetusA, .foetusB, .foetusC').removeClass('d-none').addClass('d-flex');
    enabledForm('.foetusB');
    enabledForm('.foetusC');
  }

}

//disable
function disabledForm(classToDisabled) {
  $(classToDisabled + ' input').attr('disabled', 'disabled');
  $(classToDisabled + ' select').attr('disabled', 'disabled');
  $(classToDisabled + ' textarea').attr('disabled', 'disabled');
}

//enabled
function enabledForm(classToEnabled) {
  $(classToEnabled + ' input').removeAttr('disabled');
  $(classToEnabled + ' select').removeAttr('disabled', 'disabled');
  $(classToEnabled + ' textarea').removeAttr('disabled', 'disabled');
}

/**
 * Fonctions JS pour les calcules médicaux
 *
 */


// arrondir
function arrondir(nombre) {
  return Math.round(nombre * 1) / 1
}

function arrondir10(nombre) {
  return Math.round(nombre * 10) / 10
}

function arrondir100(nombre) {
  return Math.round(nombre * 100) / 100
}


// calcul IMC
function imcCalc(poids, taille) {

  taille = taille.replace(",", ".") / 100;
  poids = poids.replace(",", ".");

  if (taille > 0 && poids > 0) {
    imc = Math.round(poids / (taille * taille) * 10) / 10;
    if (imc >= 5 && imc < 90) {
      imc = imc;
    } else {
      imc = '';
    }
    return imc;
  }
}

// calcul DDG théo
function ddgtCalc(ddr) {
  if (moment(ddr, 'DD-MM-YYYY').isValid()) {
    ddg = moment(ddr, "DD-MM-YYYY").add(14, 'days').format('DD/MM/YYYY');
    return ddg;
  } else {
    return '';
  }
}

// calcul terme du jour
function tdjCalc(ddr, ddg) {
  var data = new Array();
  data["status"] = 'ko';

  if (ddr.length || ddg.length) {

    //terme du jour
    var tdjm = moment().startOf('day');
    var ddrm = moment(ddr, "DD-MM-YYYY");
    var ddgcm = moment(ddg, "DD-MM-YYYY");

    if (ddgcm.isValid()) {
      debut = ddgcm.subtract(14, 'days');
    } else if (ddrm.isValid()) {
      debut = ddrm;
    }

    if (debut.isValid()) {
      var diffmonths = tdjm.diff(debut, 'months');
      var diffweeks = tdjm.diff(debut, 'weeks');
      var diffweeksnotrounded = tdjm.diff(debut, 'weeks', true);
      var diffdays = tdjm.diff(debut, 'days');
      var plusdays = diffdays - (7 * diffweeks);
      var resultat = diffweeks + 'SA';
      if (plusdays > 0) resultat += ' + ' + plusdays + 'J';
      if (diffmonths <= 10) {
        data["status"] = 'ok';
        data["human"] = resultat;
        data["math"] = diffweeksnotrounded;
      } else {
        data["status"] = 'ko';
      }
    } else {
      data["status"] = 'ko';
    }
  }

  return data;
}

// calcul terme (9 mois)
function terme9mCalc(ddr, ddg) {
  var data = new Array();
  data["status"] = 'ko';

  if (ddr.length || ddg.length) {

    //terme du jour
    var tdjm = moment().startOf('day');
    var ddrm = moment(ddr, "DD-MM-YYYY");
    var ddgcm = moment(ddg, "DD-MM-YYYY");

    if (ddgcm.isValid()) {
      debut = ddgcm;
    } else if (ddrm.isValid()) {
      debut = ddrm.add(14, 'days');
    }

    if (debut.isValid()) {
      t9 = debut.add(9, 'months').format('DD/MM/YYYY');
      data["status"] = 'ok';
      data["human"] = t9;
    } else {
      data["status"] = 'ko';
    }
  }

  return data;
}

// function calcul terme jour accouchement
function termeAccCalc(tdj, ddr, ddg) {

  if (ddr.length || ddg.length) {

    //terme du jour
    var tdjm = moment(tdj, "DD-MM-YYYY");
    var ddrm = moment(ddr, "DD-MM-YYYY");
    var ddgcm = moment(ddg, "DD-MM-YYYY");

    if (ddgcm.isValid()) {
      debut = ddgcm.subtract(14, 'days');
    } else if (ddrm.isValid()) {
      debut = ddrm;
    }

    if (debut.isValid()) {
      var diffmonths = tdjm.diff(debut, 'months');
      var diffweeks = tdjm.diff(debut, 'weeks');
      var diffdays = tdjm.diff(debut, 'days');
      var plusdays = diffdays - (7 * diffweeks);
      var resultat = diffweeks + 'SA';
      if (plusdays > 0) resultat += ' + ' + plusdays + 'J';
      return resultat;
    } else {
      return null;
    }
  }
}
