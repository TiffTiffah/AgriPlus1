//nav bar
document.addEventListener("DOMContentLoaded", function () {
    const navbar = document.getElementById("navbar");

    window.addEventListener("scroll", function () {
        if (window.scrollY > 50) {
            navbar.classList.add("blur");
        } else {
            navbar.classList.remove("blur");
        }
    });
});



