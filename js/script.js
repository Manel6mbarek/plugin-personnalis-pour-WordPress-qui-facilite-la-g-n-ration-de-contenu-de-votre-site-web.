let currentFieldType = "";

function openAddFieldModal(type) {
  currentFieldType = type;
  document.getElementById("addFieldModal").style.display = "block";
  document.getElementById("selectOptions").style.display =
    type === "select" ? "block" : "none";
}

function closeModal() {
  document.getElementById("addFieldModal").style.display = "none";
}

window.onclick = function (event) {
  if (event.target == document.getElementById("addFieldModal")) {
    closeModal();
  }
};

function addFieldToTextarea() {
  const label = document.getElementById("fieldLabel").value;
  const placeholder = document.getElementById("fieldPlaceholder").value;
  const isRequired = document.getElementById("fieldRequired").checked;
  const options =
    currentFieldType === "select"
      ? document.getElementById("selectOptionsList").value.split(",")
      : null;

  let fieldHtml = "";

  switch (currentFieldType) {
    case "text":
    case "email":
    case "number":
      fieldHtml = `<div class="form-group">
                    <label>${label}:</label>
                    <input type="${currentFieldType}" placeholder="${placeholder}" ${
        isRequired ? "required" : ""
      }>
                </div>\n`;
      break;
    case "checkbox":
      fieldHtml = `<div class="form-group">
                    <label>
                        <input type="checkbox" ${isRequired ? "required" : ""}>
                        ${label}
                    </label>
                </div>\n`;
      break;
    case "radio":
      fieldHtml = `<div class="form-group">
                    <label>
                        <input type="radio" ${isRequired ? "required" : ""}>
                        ${label}
                    </label>
                </div>\n`;
      break;
    case "select":
      fieldHtml = `<div class="form-group">
                    <label>${label}:</label>
                    <select ${isRequired ? "required" : ""}>
                        ${options
                          .map(
                            (option) =>
                              `<option value="${option.trim()}">${option.trim()}</option>`
                          )
                          .join("")}
                    </select>
                </div>\n`;
      break;
    case "textarea":
      fieldHtml = `<div class="form-group">
                    <label>${label}:</label>
                    <textarea placeholder="${placeholder}" ${
        isRequired ? "required" : ""
      }></textarea>
                </div>\n`;
      break;
    default:
      break;
  }

  document.getElementById("htmlCode").value += fieldHtml;
  closeModal();
  document.getElementById("addFieldForm").reset();
}
$(document).ready(function () {
  $.ajax({
    url: "get_templates.php",
    type: "GET",
    dataType: "json",
    success: function (data) {
      var container = $("#dynamic-templates");
      data.forEach(function (template) {
        var button = $("<button>")
          .addClass("template-button")
          .text(template.nom_fichier)
          .on("click", function () {
            window.location.href = "edit_template.php?id=" + template.id;
          });
        container.append(button);
      });
    },
    error: function (xhr, status, error) {
      console.error("Erreur lors du chargement des templates:", error);
    },
  });
});
