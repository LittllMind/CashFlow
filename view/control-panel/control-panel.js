function changeUserFonction() {
    var patientName = '';
    var user_fonction = document.getElementById('user_fonction').value;
    var aftercare_type = 'none';

    // Créer une instance de l'objet XMLHttpRequest
  var xhr = new XMLHttpRequest();

  // Définir la fonction de rappel appelée lorsque la réponse est reçue
  xhr.onreadystatechange = function() {
    if (xhr.readyState === 4) {
      if (xhr.status === 200) {
        // Recharger la page après la soumission du formulaire
        location.reload();
      } else {
        console.log('Erreur de soumission du formulaire');
      }
    }
  };

  // Ouvrir une nouvelle requête POST
  xhr.open("POST", "index.php?action=controlPanel", true);

  // Définir les en-têtes HTTP nécessaires pour envoyer des données de formulaire
  xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");

  // Envoyer les données du formulaire en tant que chaîne de requête
  xhr.send("patient_name=" + patientName + "&user_fonction=" + encodeURIComponent(user_fonction) + "&aftercare_type=" + aftercare_type);
}

