let y="",B="",m="",s={},u={},g="",f=[],_=50,v=0,r=!1,w="";function x(e){y=e.accessToken,m=e.tenant?e.tenant:"rubin.canto.com/",B=e.tokenType?e.tokenType:"bearer",u={Authorization:B+" "+y,"Content-Type":"application/x-www-form-urlencoded"},w=e.formatDistrict}s.loadTree=function(e){var l="https://"+m+"/api/v1/tree?sortBy=name&sortDirection=ascending&layer=1";$.ajax({headers:u,type:"GET",url:l,async:!0,error:function(n){alert("load tree error")},success:function(n){e(n.results)}})};s.loadSubTree=function(e,l){let n=`https://${m}/api/v1/tree/${e}`;$.ajax({headers:u,type:"GET",url:n,async:!0,error:function(t){alert("load tree error")},success:function(t){l(t.results)}})};s.getListByAlbum=function(e,l){if(r)return;let n=k(),t=`https://${m}/api/v1/album/${e}?${n}`;$.ajax({type:"GET",headers:u,url:t,async:!0,error:function(o){alert("load list error")},success:function(o){f.push.apply(f,o.results),o.start||(o.start=0),o.found-o.limit<=o.start?r=!0:r=!1,v=o.start+o.limit+1,$("#loadingMore").delay(1500).fadeOut("slow"),l(o.results)}})};s.getRedirectURL=function(e,l){if(!(e&&l))return;let n=e+"URI";$.ajax({type:"GET",headers:u,url:n,error:function(t){console.error(t.getError())},success:function(t){$("img#"+l).attr("src",t)}})};s.getHugeRedirectURL=function(e,l){if(!(e&&l))return;let n=`${e}URI/2000`;$.ajax({type:"GET",headers:u,url:n,error:function(t){console.error(t.getError())},success:function(t){$("#cantoViewBody").find("#imageBox").find("img").attr("src",t)}})};s.getListByScheme=function(e,l){if(e=="allfile"){let n={scheme:"allfile",keywords:""};s.getFilterList(n,l)}else{if(r)return;let n=k(),t=`https://${m}/api/v1/${e}?${n}`;$.ajax({type:"GET",headers:u,url:t,async:!1,error:function(o){alert("load list error")},success:function(o){f.push.apply(f,o.results),o.start||(o.start=0),o.found-o.limit<=o.start?r=!0:r=!1,v=o.start+o.limit+1,$("#loadingMore").delay(1500).fadeOut("slow"),l(o.results)}})}};s.getDetail=function(e,l,n){let t=`https://${m}/api/v1/${l}/${e}`;$.ajax({type:"GET",headers:u,url:t,async:!0,error:function(o){alert("load detail error")},success:function(o){n(o)}})};s.getFilterList=function(e,l){if(r)return;let n=k(),t=`https://${m}/api/v1/search?${n}`;t+=`&keyword=${e.keywords}`,e.scheme&&e.scheme=="allfile"?t+=`&scheme=${encodeURIComponent("image|presentation|document|audio|video|other")}`:e.scheme&&(t+=`&scheme=${e.scheme}`),$.ajax({type:"GET",headers:u,url:t,async:!1,error:function(o){alert("load List error")},success:function(o){f.push.apply(f,o.results),o.start||(o.start=0),o.found-o.limit<=o.start?r=!0:r=!1,v=o.start+o.limit+1,$("#loadingMore").delay(1500).fadeOut("slow"),l(o.results)}})};s.logout=function(){let e=parent,l={};l.type="cantoLogout",e.postMessage(l,"*")};s.insertImage=function(e){if(!(e&&e.length))return;let l=`https://${m}/api_binary/v1/batch/directuri`;fetch(l,{method:"post",headers:{Authorization:`${B} ${y}`,"Content-Type":"application/json; charset=utf-8"},body:JSON.stringify(e)}).then(n=>n.json()).then(n=>{for(let d=0;d<n.length;d++)for(let c=0;c<e.length;c++)n[d].id==e[c].id&&(n[d].size=e[c].size);let t=0;n.length===1&&(t=n[0].id);let o={type:"closeModal",cantoId:t,cantoAssetData:n};parent.postMessage(o,"*")})};$(document).ready(function(){A(),z(),L(),window.onmessage=function(e){let l=e.data;l&&l.accessToken&&l.accessToken.length>0?x(l):x({accessToken:parent.document.querySelector(".canto-uc-subiframe").dataset.access}),E();let n=$("#cantoViewBody").find(".type-font.current").data("type");$("#cantoViewBody").find("#globalSearch input").val(""),C(n)}});function L(){let e=parent,l={};l.type="getTokenInfo",e.postMessage(l,"*")}function A(){let e=document.getElementsByClassName("canto-uc-subiframe")[0];e&&e.contentDocument}function z(){document.addEventListener("sendTokenInfo",function(n){let t=n.data;y=t.accessToken,t.refreshToken,B=t.tokenType}),$(document).off("click").on("click","#treeviewSwitch",function(n){$("#treeviewSection").hasClass("expanded")?($("#treeviewSection").stop().animate({left:"-20%"}),$("#cantoImageBody").stop().animate({width:"100%",left:"0"},p),$("#treeviewSection").removeClass("expanded"),$("#loadingMore").addClass("no-treeview"),$("#noItem").addClass("no-treeview"),$(".max-select-tips").addClass("no-treeview")):($("#treeviewSection").stop().animate({left:"0px"}),$("#cantoImageBody").stop().animate({width:"80%",left:"20%"},p),$("#treeviewSection").addClass("expanded"),$("#loadingMore").removeClass("no-treeview"),$("#noItem").removeClass("no-treeview"),$(".max-select-tips").removeClass("no-treeview"))}).on("click",".type-font",function(n){g="byScheme",$(".type-font").removeClass("current"),$(this).addClass("current"),$("#cantoViewBody").find("#globalSearch input").val(""),$("#cantoViewBody").find("#treeviewSection ul li").removeClass("selected");let t={};t.scheme=$("#cantoViewBody").find(".type-font.current").data("type"),t.keywords="",$("#cantoViewBody").find("#imagesContent").html(""),$("#cantoViewBody").find("#imagesContent").scrollTop(0),r=!1,f=[],s.getFilterList(t,h)}).on("click","#selectAllBtn",function(n){$("#cantoViewBody").find(".single-image .select-box").removeClass("icon-s-Ok2_32"),$("#cantoViewBody").find(".single-image").removeClass("selected"),S()}).on("click","#insertAssetsBtn",function(n){$("#cantoViewBody").find(".loading-icon").removeClass("hidden");let t=[],o=$("#cantoViewBody").find(".single-image .icon-s-Ok2_32").closest(".single-image");for(let a=0;a<o.length;a++){let d={};d.id=$(o[a]).data("id"),d.scheme=$(o[a]).data("scheme"),d.size=$(o[a]).data("size"),t.push(d)}s.insertImage(t)}).on("click",".icon-s-Fullscreen",function(n){n.cancelBubble=!0,n.stopPropagation(),n.preventDefault(),$("#cantoViewBody").find(".loading-icon").removeClass("hidden");let o=$(n.currentTarget).closest(".single-image").data("xurl")+"?Authorization="+y;O(o)}).on("click",".single-image",function(n){$("#cantoViewBody").find(".loading-icon").removeClass("hidden");let t=$(n.currentTarget).closest(".single-image").data("xurl"),o=$(n.currentTarget).closest(".single-image").data("id");s.getHugeRedirectURL(t,o);let a=$(this).data("id"),d=$(this).data("scheme");s.getDetail(a,d,R)}).on("click","#logoutBtn",function(n){$(".loading-icon").removeClass("hidden"),s.logout()}).on("click","#treeviewSection ul li",function(n){n.cancelBubble=!0,n.stopPropagation(),n.preventDefault();let t=$(n.currentTarget).children("ul");if($(n.currentTarget)[0].id=="treeviewContent")$("#cantoViewBody").find("#globalSearch input").val(""),$("#cantoViewBody").find("#treeviewSection ul li").removeClass("selected"),$("#cantoViewBody").find(".type-font").removeClass("current"),$("#cantoViewBody").find("#imagesContent").html(""),$("#cantoViewBody").find("#imagesContent").scrollTop(0),f=[],g="",r=!1,console.log("line 499"),C("allfile");else if(t&&t.length)t.animate({height:"toggle"});else if($(n.currentTarget).hasClass("has-sub-folder")){let o=$(n.currentTarget).data("id");$(n.currentTarget).addClass("current-tree-node"),$(n.currentTarget).find(".folder-loading").removeClass("hidden"),$(n.currentTarget).find(".icon-s-Folder_open-20px").addClass("hidden"),s.loadSubTree(o,P)}else{$("#treeviewSection ul li").removeClass("selected"),$("#cantoViewBody").find(".type-font").removeClass("current"),$(n.currentTarget).addClass("selected"),$("#cantoViewBody").find("#globalSearch input").val(""),$("#cantoViewBody").find("#imagesContent").html(""),$("#cantoViewBody").find("#imagesContent").scrollTop(0),f=[],r=!1,g="bytree";let o=$(n.currentTarget).data("id");s.getListByAlbum(o,h)}}).on("click","#globalSearchBtn",function(n){let t=$("#cantoViewBody").find("#globalSearch input").val();if(!t){$("#cantoViewBody").find("#treeviewSection ul li").removeClass("selected");let d=$("#cantoViewBody").find(".type-font.current").data("type");$("#cantoViewBody").find("#globalSearch input").val(""),$("#cantoViewBody").find("#imagesContent").html(""),$("#cantoViewBody").find("#imagesContent").scrollTop(0),f=[],g="",r=!1,console.log("line 492"),C(d)}g="bySearch",r=!1,$("#cantoViewBody").find("#treeviewSection ul li").removeClass("selected"),$("#cantoViewBody").find(".type-font").removeClass("current");let o=$("#cantoViewBody").find(".type-font.current").data("type"),a={};a.scheme=o,a.keywords=t,$("#cantoViewBody").find("#imagesContent").html(""),$("#cantoViewBody").find("#imagesContent").scrollTop(0),f=[],s.getFilterList(a,h)}),$("#cantoViewBody").find("#cantoImageBody").on("scroll",function(){U()&&!r&&M()});let e=$("#cantoViewBody").find("#globalSearch input");$(e).bind("keyup",function(n){n.keyCode=="13"&&$("#cantoViewBody").find("#globalSearchBtn").click()});let l=$("#cantoViewBody").find("#cantoImageBody");$(l).resize(function(){p()})}function C(e){s.getListByScheme(e,h)}function h(e){if(!(e&&e.length>0))return;let l=[];w&&w.length>1&&(l=w.split(";"));for(let d=0;d<e.length;d++){let c=e[d],V=c.name.substring(c.name.lastIndexOf(".")+1);if(l.length&&!l.includes(V))continue;let b="",T=c.name;c.name.length>150&&(T=c.name.substr(0,142)+"..."+c.name.substr(-5)),b+=`<div class="single-image" data-id="${c.id}" data-scheme="${c.scheme}" data-xurl="${c.url.preview}" data-name="${c.name}" data-size="${c.size}" >
                    <img id="${c.id}" src="https://s3-us-west-2.amazonaws.com/static.dmc/universal/icon/back.png" alt="${c.scheme}">
                    <div class="mask-layer"></div>
                    <div class="single-image-name">${T}</div>
                    <span class="select-box icon-s-UnselectedCheck_32  "></span><span class="select-icon-background"></span>
                </div>`,$("#cantoViewBody").find("#imagesContent").append(b),s.getRedirectURL(c.url.preview,c.id)}$("#cantoViewBody").find(".single-image").length==0?$("#cantoViewBody").find("#noItem").removeClass("hidden"):$("#cantoViewBody").find("#noItem").addClass("hidden");let t=[];$("#cantoViewBody").find(".single-image").hover(function(){let d=$(this).height()-$(this).find(".single-image-name").height()-20;$(this).find(".single-image-name").stop().animate({top:d})},function(){$(this).find(".single-image-name").stop().animate({top:"100%"})}),$("#cantoViewBody").find(".single-image .select-box").off("click").on("click",function(d){if(d.cancelBubble=!0,d.stopPropagation(),d.preventDefault(),t.push($(".single-image").index($(this).closest(".single-image"))),d.shiftKey){let c=Math.min(t[t.length-2],t[t.length-1]),V=Math.max(t[t.length-2],t[t.length-1]);for(i=c;i<=V;i++){if($("#cantoViewBody").find(".single-image .icon-s-Ok2_32").length>=20){$(".max-select-tips").fadeIn("normal").delay(2e3).fadeOut(1e3);return}$(".single-image:eq("+i+") .select-box").addClass("icon-s-Ok2_32"),$(".single-image:eq("+i+")").addClass("selected")}}else if($("#cantoViewBody").find(".single-image .icon-s-Ok2_32").length>=20){$(this).hasClass("icon-s-Ok2_32")||$(".max-select-tips").fadeIn("normal").delay(2e3).fadeOut(1e3),$(this).removeClass("icon-s-Ok2_32"),$(this).closest(".single-image").removeClass("selected");return}else $(this).toggleClass("icon-s-Ok2_32"),$(this).closest(".single-image").toggleClass("selected");S()}),p(),S();let o=$("#cantoImageBody").height();$("#imagesContent").height()<o&&!r&&M()}let S=function(){let e=$("#cantoViewBody").find(".single-image .icon-s-Ok2_32").length;$("#cantoViewBody").find("#selected-count").html(e),e?($("#cantoViewBody").find("#globalSearch").addClass("hidden"),$("#cantoViewBody").find("#filterSection").addClass("hidden"),$("#cantoViewBody").find("#selectedCountSection").removeClass("hidden"),$("#cantoViewBody").find("#selectedActionSection").removeClass("hidden")):($("#cantoViewBody").find("#globalSearch").removeClass("hidden"),$("#cantoViewBody").find("#filterSection").removeClass("hidden"),$("#cantoViewBody").find("#selectedCountSection").addClass("hidden"),$("#cantoViewBody").find("#selectedActionSection").addClass("hidden")),$("#cantoViewBody").find("#selectAllBtn").addClass("all-selected"),$("#cantoViewBody").find("#selectAllBtn").attr("title","Deselect All")};function O(e){let l=$("#cantoViewBody").find("#viewImageModal"),n=$("#cantoViewBody").find("#pageMask");l.find("img").attr("src",e),$("#cantoViewBody").find(".loading-icon").addClass("hidden"),l.removeClass("hidden"),n.removeClass("hidden"),$("#cantoViewBody").find(".view-image-modal .close-btn").off("click").on("click",function(){l.addClass("hidden"),n.addClass("hidden")})}function R(e){let l=function(t,o,a){if(t)$(o).closest(".detail-item").removeClass("hidden");else return $(o).closest(".detail-item").addClass("hidden"),"Null";return a||(a=150),t.length>a?($(o).removeClass("hidden"),t.slice(0,a)+"..."):($(o).addClass("hidden"),t)};if(e){$("#cantoViewBody").find("#imagebox_name").html(e.name),$("#cantoViewBody").find("#imagebox_size").html(Math.round(e.size/1024)+"KB"),$("#cantoViewBody").find("#imagebox_created").html(e.metadata&&e.metadata["Create Date"]?e.metadata["Create Date"]:" "),$("#cantoViewBody").find("#imagebox_uploaded").html(j(e.lastUploaded)),$("#cantoViewBody").find("#imagebox_status").html(e.approvalStatus);let t=$("#imagebox_copyright").closest(".detail-item").find(".more");$("#cantoViewBody").find("#imagebox_copyright").html(l(e.copyright,t,177)),$("#cantoViewBody").find("#imagebox_copyright").data("field",e.copyright);let o=$("#imagebox_tac").closest(".detail-item").find(".more");$("#cantoViewBody").find("#imagebox_tac").html(l(e.termsAndConditions,o,160)),$("#cantoViewBody").find("#imagebox_tac").data("field",e.termsAndConditions),$("#cantoViewBody").find("#insertBtn").data("id",e.id),$("#cantoViewBody").find("#insertBtn").data("scheme",e.scheme)}let n=$("#cantoViewBody").find("#imagePreviewModal");$("#cantoViewBody").find(".loading-icon").addClass("hidden"),n.removeClass("hidden"),$("#cantoViewBody").find("#imagePreviewModal .close-btn").off("click").on("click",function(){n.addClass("hidden")}),$("#cantoViewBody").find("#imagePreviewModal #cancelBtn").off("click").on("click",function(){n.addClass("hidden")}),$("#cantoViewBody").find("#imagePreviewModal .detail-item .more").off("click").on("click",function(){let t=$(this).closest(".detail-item").find(".content").data("field");$(this).closest(".detail-item").find(".content").html(t),$(this).addClass("hidden")}),$("#cantoViewBody").find("#imagePreviewModal #insertBtn").off("click").on("click",function(){$("#cantoViewBody").find(".loading-icon").removeClass("hidden");let t=[{id:e.id,scheme:e.scheme,size:e.size,name:e.name}];s.insertImage(t)})}function j(e){return e.substr(0,4)+"-"+e.substr(4,2)+"-"+e.substr(6,2)+" "+e.substr(8,2)+":"+e.substr(10,2)}function E(){s.loadTree(F)}let F=function(e){let l="";l=H(e),$("#cantoViewBody").find("#treeviewContent").append(l),$("#cantoViewBody").find("#treeviewContent > ul").animate({height:"toggle"})},H=function(e){let l="<ul style='display: none;'>";return $.each(e,function(n,t){let o=" ";t.size==0?o="no-child":t.scheme=="folder"&&(o="has-sub-folder"),l+=`<li data-id="${t.id}"  class="${o}">`;let a="icon-s-Folder_open-20px";t.scheme=="album"&&(a="icon-s-Album-20px"),l+=`<i class="${a}"></i>
                    <img src="https://s3-us-west-2.amazonaws.com/static.dmc/universal/icon/cantoloading.gif" class="folder-loading hidden" alt="Loading">
                    <span>${t.name}</span>
                </li>`}),l+="</ul>",l},P=function(e){let l=I(e);$("#cantoViewBody").find(".current-tree-node").append(l),$("#cantoViewBody").find(".current-tree-node > ul").animate({height:"toggle"}),$("#cantoViewBody").find(".current-tree-node").find(".folder-loading").addClass("hidden"),$("#cantoViewBody").find(".current-tree-node").find(".icon-s-Folder_open-20px").removeClass("hidden"),$("#cantoViewBody").find(".current-tree-node").removeClass("current-tree-node")},I=function(e){let l="<ul style='display: none;'>";return $.each(e,function(n,t){let o=" ";t.size==0&&(o="no-child"),l+=`<li data-id="${t.id}"  class="${o}">`;let a="icon-s-Folder_open-20px";t.scheme=="album"&&(a="icon-s-Album-20px"),l+=`<i class="${a}"></i>
                    <span>${t.name}</span>`,t.children&&t.children.length&&(l+=I(t.children)),l+="</li>"}),l+="</ul>",l};function p(){let e=8,l=Number($("#cantoViewBody").find("#imagesContent")[0].offsetWidth),n=0,t=function(a){if(n=Number((l-8)/a-2),n>=160&&n<=210)return n;n<160?(a--,t(a)):n>210&&(a++,t(a))},o=t(e);$("#cantoViewBody").find(".single-image").css("width",o)}function U(){let e=$("#cantoImageBody").height(),l=$("#imagesContent").height(),n=$("#cantoImageBody").scrollTop(),t=l-e-n<0,o=$(".single-image").length==0;return t&&!o}function k(){let e=f.length==0?0:v,l="sortBy=time&sortDirection=descending&limit="+_+"&start="+e;return $(".single-image").length!==0?$("#loadingMore").fadeIn("slow"):$("#cantoViewBody").find("#imagesContent").html(""),l}function M(){if(g=="bySearch"){let e=$("#cantoViewBody").find("#globalSearch input").val();if(!e)return;let l=$("#cantoViewBody").find(".type-font.current").data("type"),n={};n.scheme=l,n.keywords=e,s.getFilterList(n,h)}else if(g=="bytree"){let e=$("#cantoViewBody").find("#treeviewSection ul li").find(".selected").data("id");s.getListByAlbum(e,h)}else{let e=$("#cantoViewBody").find(".type-font.current").data("type");C(e)}}parent.document.querySelector(".modal-status-bar").style.display="none";
//# sourceMappingURL=canto-embed-cddfa706.js.map