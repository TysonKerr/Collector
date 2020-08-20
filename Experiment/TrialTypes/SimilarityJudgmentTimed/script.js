var resp1    = null;
var resp1_rt = null;
var resp2    = null;
var resp2_rt = null;
var start    = null;
var min_time_before_response = 0;

var give_warning_for_rushing = function() {
    document.querySelector(".image_container").classList.add("hidden");
    document.querySelector(".status_bar_container").classList.add("hidden");
    document.querySelector(".rushing_warning").classList.remove("hidden");
    document.querySelector("input[name='Gave_Warning']").value = "yes";
    COLLECTOR.timer(3, function() {
        let btn = document.querySelector(".next_button");
        btn.classList.remove("disabled");
        btn.disabled = false;
        btn.addEventListener("click", submit_form);
    });
};

var record_unselected_answers = function() {
    let i = 1;
    let ans1 = resp1.value;
    let ans2 = resp2.value;
    
    document.querySelectorAll(".image_container img").forEach(img_node => {
        if (img_node.classList.contains("comparison_source")) return;
        
        let ans = img_node.dataset['answer'];
        
        if (ans === ans1 || ans === ans2) return;
        
        document.querySelector(`input[name='Option_Not_Selected_${i++}']`).value = ans;
    });
};

var submit_form = function() {
    $("#FormSubmitButton").click();
};

var click_img = function(img_node) {
    if (img_node.classList.contains("comparison_source")) return;
    if (img_node.classList.contains("selected")) return;
    
    let answer = img_node.dataset['answer'];
    let rt = Date.now() - start;
    
    if (!resp1.value) {
        resp1.value = answer;
        resp1_rt.value = rt;
        img_node.classList.add("selected");
    } else {
        resp2.value = answer;
        resp2_rt.value = rt;
        record_unselected_answers();
        
        if (COLLECTOR.getRT() < min_time_before_response) {
            give_warning_for_rushing();
        } else {
            submit_form();
        }
        
    }
};

window.addEventListener("load", function() {
    document.querySelectorAll("img").forEach(
        node => node.addEventListener("click", e => click_img(e.target))
    );

    document.addEventListener("contextmenu", e => {
        e.preventDefault();
    });
    
    start    = Date.now();
    resp1    = document.querySelector("input[name='Response1']");
    resp1_rt = document.querySelector("input[name='Response1_RT']");
    resp2    = document.querySelector("input[name='Response2']");
    resp2_rt = document.querySelector("input[name='Response2_RT']");
});
