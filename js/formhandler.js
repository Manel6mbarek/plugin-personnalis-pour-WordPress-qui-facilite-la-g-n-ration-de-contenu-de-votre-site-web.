document.addEventListener("DOMContentLoaded", function () {
  const form = document.getElementById("contentForm");

  // Gestion des boutons pour les styles d'écriture
  const writingStyleButtons = document.querySelectorAll(
    "#writing-styles .toggle-button"
  );
  writingStyleButtons.forEach((button) => {
    button.addEventListener("click", function () {
      // Désélectionner tous les boutons
      writingStyleButtons.forEach((btn) => btn.classList.remove("selected"));
      // Sélectionner le bouton cliqué
      button.classList.add("selected");
      // Mettre à jour le champ caché
      document.getElementById("writing-style-input").value =
        button.getAttribute("data-value");
    });
  });

  // Gestion des boutons pour les tons
  const toneButtons = document.querySelectorAll("#tone .toggle-button");
  toneButtons.forEach((button) => {
    button.addEventListener("click", function () {
      // Désélectionner tous les boutons
      toneButtons.forEach((btn) => btn.classList.remove("selected"));
      // Sélectionner le bouton cliqué
      button.classList.add("selected");
      // Mettre à jour le champ caché
      document.getElementById("tone-input").value =
        button.getAttribute("data-value");
    });
  });

  // Gestion de la soumission du formulaire
  form.addEventListener("submit", function (event) {
    event.preventDefault(); // Empêcher le comportement de soumission par défaut

    const formData = new FormData(form);

    fetch("submit.php", {
      method: "POST",
      body: formData,
    })
      .then((response) => response.text())
      .then((result) => {
        // Afficher le résultat ou gérer la réponse du serveur
        console.log(result);
        alert("Les données ont été envoyées avec succès.");
      })
      .catch((error) => {
        console.error("Erreur :", error);
        alert("Une erreur est survenue.");
      });
  });
});
