// SIDEBAR TOGGLE

function toggleMenu(){

let sidebar = document.getElementById("sidebar");
let overlay = document.getElementById("overlay");

if(sidebar.classList.contains("open")){

sidebar.classList.remove("open");
overlay.style.display = "none";

}
else{

sidebar.classList.add("open");
overlay.style.display = "block";

}

}

// SETTINGS SUBMENU

function toggleSettings(){

let menu = document.getElementById("settingsMenu");

if(menu.style.display === "block"){
menu.style.display = "none";
}
else{
menu.style.display = "block";
}

}

// PAGE NAVIGATION

function openPage(page){
window.location.href = page;
}

// LOGOUT

function logout(){
window.location.href = "index.html";
}

// DELETE ACCOUNT

function deleteAccount(){

let confirmDelete = confirm("Are you sure you want to delete your account?");

if(confirmDelete){
alert("Account will be deleted later with PHP.");
}

}

// PHOTO UPLOAD

const upload = document.getElementById("photoUpload");
const photo = document.getElementById("profilePhoto");

upload.addEventListener("change", function(){

const file = this.files[0];

if(file){

const reader = new FileReader();

reader.onload = function(e){
photo.src = e.target.result;
}

reader.readAsDataURL(file);

setTimeout(function(){
upload.style.display = "none";
},2000);

}

});

photo.addEventListener("click", function(){
upload.style.display = "block";
});

// PIE CHART

new Chart(
document.getElementById("pieChart"),
{
type:"pie",
data:{
labels:["Food","Travel","Shopping","Bills"],
datasets:[{
data:[5000,3000,2000,4000]

}]
},
options:{
responsive:true,
maintainAspectRatio:false
}
}
);

// HORIZONTAL BAR CHART

new Chart(
document.getElementById("barChart"),
{
type:"bar",
data:{
labels:["Income","Expense"],
datasets:[{
label:"Amount",
data:[50000,30000]

}]
},
options:{
indexAxis:"y",
responsive:true,
maintainAspectRatio:false
}
}
);