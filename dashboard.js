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
 window.location.href = 'delete_account.php';
}

}


// PHOTO UPLOAD

const upload = document.getElementById("photoUpload");
const photo = document.getElementById("profilePhoto");
const topPhoto = document.getElementById("topProfilePhoto");
const initials = document.getElementById("profileInitials");
const topInitials = document.getElementById("topInitials");

upload.addEventListener("change", function(){

const file = this.files[0];

if(file){

const reader = new FileReader();

reader.onload = function(e){

photo.src = e.target.result;
photo.style.display = "block";
initials.style.display = "none";

/* SYNC TOP PROFILE PHOTO */

if(topPhoto){
topPhoto.src = e.target.result;
topPhoto.style.display = "block";
topInitials.style.display = "none";
}

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


// PROFILE DROPDOWN (TOP RIGHT)

function toggleProfileMenu(){

let menu = document.getElementById("profileMenu");

if(menu.style.display === "block"){
menu.style.display = "none";
}
else{
menu.style.display = "block";
}

}


// SEARCH FUNCTION (OPEN SIDEBAR IF MATCH)

function triggerSearch(){

let value = document.getElementById("searchInput").value.toLowerCase();

let sidebar = document.getElementById("sidebar");
let overlay = document.getElementById("overlay");

let keywords = [
"user info",
"expense",
"expense history",
"monthly",
"monthly report",
"settings",
"reset password",
"delete account",
"logout"
];

let found = keywords.some(function(word){
return value.includes(word);
});

if(found){

sidebar.classList.add("open");
overlay.style.display = "block";

}
else{

alert("No result found");

}

}


// ENTER KEY SUPPORT

document.getElementById("searchInput").addEventListener("keypress", function(e){

if(e.key === "Enter"){
triggerSearch();
}

});


// PIE CHART

new Chart(document.getElementById('pieChart'), {
    type: 'pie',
    data: {
        labels: pieLabels,
        datasets: [{
            data: pieData,
            backgroundColor: [
                '#FF6384', '#36A2EB', '#FFCE56', '#8A2BE2', 
                '#00FF7F', '#FFA500', '#00CED1', '#FF69B4'
            ]
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                position: 'right',      // moves legend to right side
                align: 'start',         // aligns legend vertically at top
                labels: {
                    boxWidth: 20,       // size of color box
                    padding: 10         // space between items
                }
            },
            tooltip: {
                enabled: true           // keeps tooltips on hover
            }
        }
    }
});


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

if(!sessionStorage.getItem('monthlyPromptShown')){
    if(confirm("Your monthly report is going to be generated. Do you want to edit something now?")){
        window.location.href = 'userInfo.php';
    } else {
        window.location.href = 'monthlyReport.php';
    }
    sessionStorage.setItem('monthlyPromptShown', 'true');
}

if(!sessionStorage.getItem('welcomeBackShown')){
    alert("👋 Welcome back! Do you want to edit something more?");
    sessionStorage.setItem('welcomeBackShown', 'true');
}

const savingsPercent = <?php echo round($percent); ?>;
if(savingsPercent >= 100){
    alert("🎉 Congratulations! You've reached your savings goal!");
    window.location.href = 'thank_you.php';
}