<?php
include("includes/head.html");
include("includes/menu.php");
?>
  <div class="container">
    <h3 id="titol-categoria">PRODUCTES DE LA CATEGORIA - ...</h3>
    <div id="llistat-productes" class="llistat-productes"></div>
  </div>

  <script>
  function getParam(param) {
    const urlParams = new URLSearchParams(window.location.search);
    return urlParams.get(param);
  }

  // Si no hay parámetro o es 'totes', forzamos 'totes'
  let categoria = getParam('categoria') || 'totes';

  // Siempre llamamos con ?category=
  const url = "api/productes.php?category=" + encodeURIComponent(categoria);

  console.log("Cridant URL de l'API:", url);

  // Actualiza el título
  document.getElementById("titol-categoria").innerText =
    "PRODUCTES DE LA CATEGORIA - " +
    categoria.charAt(0).toUpperCase() + categoria.slice(1);

  fetch(url)
    .then(response => response.json())
    .then(data => {
      const container = document.getElementById("llistat-productes");
      container.innerHTML = '';  // limpia contenido previo

      if (Array.isArray(data) && data.length > 0) {
        data.forEach(product => {
          const div = document.createElement("div");
          div.className = "producte-mini";
          div.innerHTML = `
            <img src="${product.image}" alt="Imatge del producte">
            <div><a href="veureProducte.php?id=${product.id}">${product.title}</a></div>
            <div class="preu">$${product.price}</div>
            <div class="rating">${product.rating.rate} (${product.rating.count})</div>
          `;
          container.appendChild(div);
        });
      } else if (Array.isArray(data)) {
        container.innerHTML = "<p>No hi ha productes en aquesta categoria.</p>";
      } else {
        container.innerHTML = "<p>Error al obtenir les dades de l'API.</p>";
      }
    })
    .catch(error => {
      console.error(error);
      document.getElementById("llistat-productes").innerHTML =
        "<p>Error de connexió a l'API.</p>";
    });

</script>


<?php
include("includes/foot.html");
?>