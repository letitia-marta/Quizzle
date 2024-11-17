let navbar = document.querySelector('.header .navbar');
let accountBox = document.querySelector('.header .account-box');

document.querySelector('#menu-btn').onclick = () =>{
   navbar.classList.toggle('active');
   accountBox.classList.remove('active');
}

document.querySelector('#user-btn').onclick = () =>{
   accountBox.classList.toggle('active');
   navbar.classList.remove('active');
}

window.onscroll = () =>{
   navbar.classList.remove('active');
   accountBox.classList.remove('active');
}

document.querySelector('#close-update').onclick = () =>{
    document.querySelector('.edit-product-form').style.display = 'none';
    window.location.href = 'admin_products.php';

 }

 // JavaScript pentru deschiderea meniului lateral
const menuBtn = document.getElementById("menu-btn");
const sideMenu = document.getElementById("side-menu");

// Când se apasă butonul, se activează meniul
menuBtn.addEventListener("click", () => {
    sideMenu.classList.toggle("active"); // Adaugă/înlătură clasa 'active' care arată meniul
});
