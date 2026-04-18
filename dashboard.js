sessionStorage.clear();

/* ---------------- SIDEBAR TOGGLE ---------------- */

function toggleMenu(){

let sidebar = document.getElementById("sidebar");
let overlay = document.getElementById("overlay");

if(sidebar.classList.contains("open")){

sidebar.classList.remove("open");
overlay.style.display="none";

}
else{

sidebar.classList.add("open");
overlay.style.display="block";

}

}


/* ---------------- SETTINGS SUBMENU ---------------- */

function toggleSettings(){

let menu = document.getElementById("settingsMenu");

if(menu.style.display==="block"){
menu.style.display="none";
}
else{
menu.style.display="block";
}

}


/* ---------------- PAGE NAVIGATION ---------------- */

function openPage(page){
window.location.href = page;
}

function deleteAccount(){

let confirmDelete = confirm("Are you sure you want to delete your account? This action cannot be undone.");

if(confirmDelete){
window.location.href = "delete_account.php";
}

} 
/* ---------------- PROFILE MENU ---------------- */

function toggleProfileMenu(){

let menu=document.getElementById("profileMenu");

if(menu.style.display==="block"){
menu.style.display="none";
}
else{
menu.style.display="block";
}

}


/* ---------------- SEARCH FUNCTION ---------------- */

/* SEARCH FUNCTION - OPEN SIDEBAR ONLY */

function triggerSearch(){

let value = document.getElementById("searchInput").value.toLowerCase();

let sidebar = document.getElementById("sidebar");
let overlay = document.getElementById("overlay");

/* keywords that exist in sidebar */

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


/* ENTER KEY SUPPORT */

document.getElementById("searchInput").addEventListener("keypress", function(e){

if(e.key === "Enter"){
triggerSearch();
}

});



/* ---------------- PROFILE PHOTO UPLOAD ---------------- */

const upload=document.getElementById("photoUpload");
const photo=document.getElementById("profilePhoto");
const topPhoto=document.getElementById("topProfilePhoto");
const initials=document.getElementById("profileInitials");
const topInitials=document.getElementById("topInitials");

if(upload){

upload.addEventListener("change",function(){

const file=this.files[0];

if(file){

const reader=new FileReader();

reader.onload=function(e){

photo.src=e.target.result;
photo.style.display="block";
initials.style.display="none";

topPhoto.src=e.target.result;
topPhoto.style.display="block";
topInitials.style.display="none";

}

reader.readAsDataURL(file);

}

});

}


/* ---------------- PIE CHART ---------------- */

new Chart(document.getElementById("pieChart"),{

type:"pie",

data:{
labels:pieLabels,
datasets:[{
data:pieData,
backgroundColor:[
'#FF6384','#36A2EB','#FFCE56','#8A2BE2',
'#00FF7F','#FFA500','#00CED1','#FF69B4'
]
}]
},

options:{
responsive:true,
maintainAspectRatio:false
}

});


/* ---------------- BAR CHART ---------------- */

new Chart(document.getElementById("barChart"),{

type:"bar",

data:{
labels:["Income","Expense"],
datasets:[{
label:"Amount",
data:[totalIncome,totalExpense],
backgroundColor:["#36A2EB","#FF6384"]
}]
},

options:{
indexAxis:"y",
responsive:true,
maintainAspectRatio:false
}

});


/* ---------------- THANK YOU MESSAGE ---------------- */

if(savingsPercent >= 100){
    alert("🎉 Congratulations! You reached your savings goal!");
    window.location.href = "thank_you.php";

} else{

    /* ---------------- EDIT PROMPT ---------------- */

    setTimeout(function(){
        let edit = confirm("Do you want to edit something?");
        if(edit){
            
            toggleMenu();
        }
    }, 1500);


    /* ---------------- MONTH END REPORT ---------------- */

    let today = new Date();
    let lastDay = new Date(today.getFullYear(), today.getMonth() + 1, 0).getDate();

    if(today.getDate() === lastDay){

        setTimeout(function(){
            let report = confirm("Your monthly report is ready! Do you want to view it?");
            if(report){
               
            toggleMenu();
            }
        }, 3000);

    }

}