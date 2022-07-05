$(".nav a").filter(function(){
    return this.href == location.href.replace(/#.*/, "");
}).addClass("active");