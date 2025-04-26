document.addEventListener("DOMContentLoaded", function() {
    const header = document.querySelector("header");
    const main = document.querySelector("main");
    
    const headerHeight = header.offsetHeight;
    main.style.paddingTop = `${headerHeight + 20}px`; // +20px para respiración
});
