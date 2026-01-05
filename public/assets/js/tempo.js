document.addEventListener("DOMContentLoaded", () => {
  const toggleBtns = [...document.getElementsByClassName("theme-toggle")];

  const body = document.body;
  const icons = toggleBtns ? toggleBtns.map(btn=> btn.querySelector("i")) : null;

  if (localStorage.getItem("theme") === "dark") {
    body.classList.add("dark-mode");
    if (icons) {
      icons.forEach(icon => {

        icon.classList.remove("fa-moon");
        icon.classList.add("fa-sun");
      })
    }
  }

  // 2. Toggle logic
  if (toggleBtns) {
    toggleBtns.forEach(btn => {
      btn.addEventListener("click", () => {
      body.classList.toggle("dark-mode");

      if (body.classList.contains("dark-mode")) {
        localStorage.setItem("theme", "dark");
        if (icons) {
          icons.forEach(icon => {
              icon.classList.remove("fa-moon");
              icon.classList.add("fa-sun");
          })
        }
      } else {
        localStorage.setItem("theme", "light");
        if (icons) {
          icons.forEach(icon=>{
            icon.classList.remove("fa-sun");
            icon.classList.add("fa-moon");
          })
        }
      }
    });
      
    });
  }
});
