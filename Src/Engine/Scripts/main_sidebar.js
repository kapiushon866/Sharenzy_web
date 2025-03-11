const menuIcon = document.getElementById("menu-icon");
const sidebar = document.getElementById("sidebar");

menuIcon.addEventListener("click", () => {
    
    sidebar.classList.toggle("expanded");
    menuIcon.classList.toggle("active");
});
