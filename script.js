/**
 * Script global de la plateforme
 * 1) Fermeture auto des alertes flash
 * 2) Validation JavaScript générique de tous les formulaires .js-validate
 *    (en complément de la validation serveur PHP, obligatoire au cahier des charges)
 */

document.addEventListener('DOMContentLoaded', function () {

  // --- 1. Fermeture automatique des alertes après 5 secondes ---
  document.querySelectorAll('.alert').forEach(function (alerte) {
    setTimeout(function () {
      alerte.style.transition = 'opacity 0.5s';
      alerte.style.opacity = '0';
      setTimeout(function () { alerte.remove(); }, 500);
    }, 5000);
  });

  // --- 2. Validation JavaScript des formulaires ---
  document.querySelectorAll('form.js-validate').forEach(function (form) {
    form.addEventListener('submit', function (evenement) {
      var erreurs = [];
      var champsInvalides = [];

      // Champs obligatoires vides
      form.querySelectorAll('[required]').forEach(function (champ) {
        var valeur = (champ.value || '').trim();
        if (valeur === '') {
          erreurs.push(libelleChamp(champ) + ' est obligatoire.');
          champsInvalides.push(champ);
        }
      });

      // Champs numériques négatifs (montants, quantités, parts...)
      form.querySelectorAll('input[type="number"]').forEach(function (champ) {
        if (champ.value !== '' && parseFloat(champ.value) < 0) {
          erreurs.push(libelleChamp(champ) + ' ne peut pas être négatif.');
          champsInvalides.push(champ);
        }
      });

      // Emails
      form.querySelectorAll('input[type="email"]').forEach(function (champ) {
        if (champ.value && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(champ.value)) {
          erreurs.push(libelleChamp(champ) + ' doit être une adresse email valide.');
          champsInvalides.push(champ);
        }
      });

      // Cohérence chronologique : date de fin/échéance postérieure à la date de début/signature
      var paires = [
        ['date_debut_mandat', 'date_fin_mandat'],
        ['date_signature', 'date_echeance'],
        ['date_entree', 'date_sortie'],
        ['date_echeance', 'date_realisation']
      ];
      paires.forEach(function (paire) {
        var debut = form.querySelector('[name="' + paire[0] + '"]');
        var fin = form.querySelector('[name="' + paire[1] + '"]');
        if (debut && fin && debut.value && fin.value && fin.value < debut.value) {
          erreurs.push('La date "' + paire[1].replace(/_/g, ' ') + '" ne peut pas être antérieure à "' + paire[0].replace(/_/g, ' ') + '".');
          champsInvalides.push(fin);
        }
      });

      // Réinitialise puis marque visuellement les champs en erreur
      form.querySelectorAll('.is-invalid').forEach(function (c) { c.classList.remove('is-invalid'); });
      champsInvalides.forEach(function (c) { c.classList.add('is-invalid'); });

      if (erreurs.length > 0) {
        evenement.preventDefault();
        afficherErreursFormulaire(form, erreurs);
      }
    });
  });

  function libelleChamp(champ) {
    var label = form_labelPour(champ);
    return label || champ.name || 'Ce champ';
  }

  function form_labelPour(champ) {
    // 1) Association explicite label[for=id]
    if (champ.id) {
      var labelExplicite = document.querySelector('label[for="' + champ.id + '"]');
      if (labelExplicite) return labelExplicite.textContent.replace('*', '').trim();
    }
    // 2) Label visuel le plus proche dans le même bloc de colonne (structure Bootstrap habituelle)
    var conteneur = champ.closest('.col-12, .col-md-2, .col-md-3, .col-md-4, .col-md-6, .col-md-8, .col-md-12');
    if (conteneur) {
      var labelProche = conteneur.querySelector('label.form-label');
      if (labelProche) return labelProche.textContent.replace('*', '').trim();
    }
    return null;
  }

  function afficherErreursFormulaire(form, erreurs) {
    var conteneur = form.querySelector('.js-validate-errors');
    if (!conteneur) {
      conteneur = document.createElement('div');
      conteneur.className = 'alert alert-danger js-validate-errors col-12';
      form.prepend(conteneur);
    }
    conteneur.innerHTML = '<strong><i class="fa-solid fa-triangle-exclamation me-1"></i>Merci de corriger les points suivants :</strong><ul class="mb-0 mt-1">' +
      erreurs.map(function (e) { return '<li>' + e + '</li>'; }).join('') + '</ul>';
    if (typeof conteneur.scrollIntoView === 'function') {
      conteneur.scrollIntoView({ behavior: 'smooth', block: 'center' });
    }
  }
});
