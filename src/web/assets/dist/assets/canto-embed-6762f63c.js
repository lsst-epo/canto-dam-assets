let p="",B="",u="",d={},m={},g="",f=[],L=50,S=0,r=!1,v="";function I(e){p=e.accessToken,u=e.tenant?e.tenant:"rubin.canto.com/",B=e.tokenType?e.tokenType:"bearer",m={Authorization:B+" "+p,"Content-Type":"application/x-www-form-urlencoded"},v=e.formatDistrict}d.loadTree=function(e){var a="https://"+u+"/api/v1/tree?sortBy=name&sortDirection=ascending&layer=1";$.ajax({headers:m,type:"GET",url:a,async:!0,error:function(n){alert("load tree error")},success:function(n){e(n.results)}})};d.loadSubTree=function(e,a){let n=`https://${u}/api/v1/tree/${e}`;$.ajax({headers:m,type:"GET",url:n,async:!0,error:function(t){alert("load tree error")},success:function(t){a(t.results)}})};d.getListByAlbum=function(e,a){if(r)return;let n=x(),t=`https://${u}/api/v1/album/${e}?${n}`;$.ajax({type:"GET",headers:m,url:t,async:!0,error:function(o){alert("load list error")},success:function(o){f.push.apply(f,o.results),o.start||(o.start=0),o.found-o.limit<=o.start?r=!0:r=!1,S=o.start+o.limit+1,$("#loadingMore").delay(1500).fadeOut("slow"),a(o.results)}})};d.getRedirectURL=function(e,a){if(!(e&&a))return;let n=e+"URI";$.ajax({type:"GET",headers:m,url:n,error:function(t){console.error(t.getError())},success:function(t){$("img#"+a).attr("src",t)}})};d.getHugeRedirectURL=function(e,a){if(!(e&&a))return;let n=`${e}URI/2000`;$.ajax({type:"GET",headers:m,url:n,error:function(t){console.error(t.getError())},success:function(t){$("#cantoViewBody").find("#imageBox").find("img").attr("src",t)}})};d.getListByScheme=function(e,a){if(e=="allfile"){let n={scheme:"allfile",keywords:""};d.getFilterList(n,a)}else{if(r)return;let n=x(),t=`https://${u}/api/v1/${e}?${n}`;$.ajax({type:"GET",headers:m,url:t,async:!1,error:function(o){alert("load list error")},success:function(o){f.push.apply(f,o.results),o.start||(o.start=0),o.found-o.limit<=o.start?r=!0:r=!1,S=o.start+o.limit+1,$("#loadingMore").delay(1500).fadeOut("slow"),a(o.results)}})}};d.getDetail=function(e,a,n){let t=`https://${u}/api/v1/${a}/${e}`;$.ajax({type:"GET",headers:m,url:t,async:!0,error:function(o){alert("load detail error")},success:function(o){n(o)}})};d.getFilterList=function(e,a){if(r)return;let n=x(),t=`https://${u}/api/v1/search?${n}`;t+=`&keyword=${e.keywords}`,e.scheme&&e.scheme=="allfile"?t+=`&scheme=${encodeURIComponent("image|presentation|document|audio|video|other")}`:e.scheme&&(t+=`&scheme=${e.scheme}`),$.ajax({type:"GET",headers:m,url:t,async:!1,error:function(o){alert("load List error")},success:function(o){f.push.apply(f,o.results),o.start||(o.start=0),o.found-o.limit<=o.start?r=!0:r=!1,S=o.start+o.limit+1,$("#loadingMore").delay(1500).fadeOut("slow"),a(o.results)}})};d.logout=function(){let e=parent,a={};a.type="cantoLogout",e.postMessage(a,"*")};d.insertImage=function(e){if(!(e&&e.length))return;let a=`https://${u}/api_binary/v1/batch/directuri`;fetch(a,{method:"post",headers:{Authorization:`${B} ${p}`,"Content-Type":"application/json; charset=utf-8"},body:JSON.stringify(e)}).then(n=>n.json()).then(n=>{let t=0;n.length===1&&(t=n[0].id);let o=`https://${u}/api/v1/batch/content`;fetch(o,{method:"post",headers:{Authorization:`${B} ${p}`,"Content-Type":"application/json; charset=utf-8"},body:JSON.stringify(e)}).then(l=>l.json()).then(l=>{const s=[];for(let C=0;C<n.length;C++)s.push({...l.docResult[C],...n[C]});let c=$("#treeviewSection").find("li.selected");const h=c.data("id");let w=c.find("span").text(),_={type:"closeModal",cantoId:t,cantoAlbumId:h,cantoAssetData:s,cantoAlbumData:{id:h,name:w}};parent.postMessage(_,"*")})})};$(document).ready(function(){O(),j(),z(),window.addEventListener("message",e=>{let a=e.data;a&&a.accessToken&&a.accessToken.length>0?I(a):I({accessToken:parent.document.querySelector(".canto-uc-subiframe").dataset.access}),H();let n=$("#cantoViewBody").find(".type-font.current").data("type");$("#cantoViewBody").find("#globalSearch input").val(""),b(n)})});function z(){let e=parent,a={};a.type="getTokenInfo",e.postMessage(a,"*")}function O(){let e=document.getElementsByClassName("canto-uc-subiframe")[0];e&&e.contentDocument}function j(){document.addEventListener("sendTokenInfo",function(n){let t=n.data;p=t.accessToken,t.refreshToken,B=t.tokenType}),$(document).off("click").on("click","#treeviewSwitch",function(n){$("#treeviewSection").hasClass("expanded")?($("#treeviewSection").stop().animate({left:"-20%"}),$("#cantoImageBody").stop().animate({width:"100%",left:"0"},V),$("#treeviewSection").removeClass("expanded"),$("#loadingMore").addClass("no-treeview"),$("#noItem").addClass("no-treeview"),$(".max-select-tips").addClass("no-treeview")):($("#treeviewSection").stop().animate({left:"0px"}),$("#cantoImageBody").stop().animate({width:"80%",left:"20%"},V),$("#treeviewSection").addClass("expanded"),$("#loadingMore").removeClass("no-treeview"),$("#noItem").removeClass("no-treeview"),$(".max-select-tips").removeClass("no-treeview"))}).on("click",".type-font",function(n){g="byScheme",$(".type-font").removeClass("current"),$(this).addClass("current"),$("#cantoViewBody").find("#globalSearch input").val(""),$("#cantoViewBody").find("#treeviewSection ul li").removeClass("selected");let t={};t.scheme=$("#cantoViewBody").find(".type-font.current").data("type"),t.keywords="",$("#cantoViewBody").find("#imagesContent").html(""),$("#cantoViewBody").find("#imagesContent").scrollTop(0),r=!1,f=[],d.getFilterList(t,y)}).on("click","#selectAllBtn",function(n){$("#cantoViewBody").find(".single-image .select-box").removeClass("icon-s-Ok2_32"),$("#cantoViewBody").find(".single-image").removeClass("selected"),T()}).on("click","#insertAssetsBtn",function(n){$("#cantoViewBody").find(".loading-icon").removeClass("hidden");let t=[],o=$("#cantoViewBody").find(".single-image .icon-s-Ok2_32").closest(".single-image");for(let l=0;l<o.length;l++){let s={};s.id=$(o[l]).data("id"),s.scheme=$(o[l]).data("scheme"),s.size=$(o[l]).data("size"),t.push(s)}d.insertImage(t)}).on("click","#insertAlbumBtn",function(n){$("#cantoViewBody").find(".loading-icon").removeClass("hidden");let t=[],o=$("#cantoViewBody").find(".single-image[data-scheme='image']").closest(".single-image");for(let l=0;l<o.length;l++){let s={};s.id=$(o[l]).data("id"),s.scheme=$(o[l]).data("scheme"),s.size=$(o[l]).data("size"),t.push(s)}t.length?d.insertImage(t):$("#cantoViewBody").find(".loading-icon").addClass("hidden")}).on("click",".icon-s-Fullscreen",function(n){n.cancelBubble=!0,n.stopPropagation(),n.preventDefault(),$("#cantoViewBody").find(".loading-icon").removeClass("hidden");let o=$(n.currentTarget).closest(".single-image").data("xurl")+"?Authorization="+p;R(o)}).on("click",".single-image",function(n){$("#cantoViewBody").find(".loading-icon").removeClass("hidden");let t=$(n.currentTarget).closest(".single-image").data("xurl"),o=$(n.currentTarget).closest(".single-image").data("id");d.getHugeRedirectURL(t,o);let l=$(this).data("id"),s=$(this).data("scheme");d.getDetail(l,s,E)}).on("click","#logoutBtn",function(n){$(".loading-icon").removeClass("hidden"),d.logout()}).on("click","#treeviewSection ul li",function(n){n.cancelBubble=!0,n.stopPropagation(),n.preventDefault();let t=$(n.currentTarget).children("ul");if($(n.currentTarget)[0].id=="treeviewContent")$("#cantoViewBody").find("#globalSearch input").val(""),$("#cantoViewBody").find("#treeviewSection ul li").removeClass("selected"),$("#cantoViewBody").find(".type-font").removeClass("current"),$("#cantoViewBody").find("#imagesContent").html(""),$("#cantoViewBody").find("#imagesContent").scrollTop(0),f=[],g="",r=!1,console.log("line 499"),b("allfile");else if(t&&t.length)t.animate({height:"toggle"});else if($(n.currentTarget).hasClass("has-sub-folder")){let o=$(n.currentTarget).data("id");$(n.currentTarget).addClass("current-tree-node"),$(n.currentTarget).find(".folder-loading").removeClass("hidden"),$(n.currentTarget).find(".icon-s-Folder_open-20px").addClass("hidden"),d.loadSubTree(o,q)}else{$("#treeviewSection ul li").removeClass("selected"),$("#cantoViewBody").find(".type-font").removeClass("current"),$("#insertAlbumWrapper").removeClass("hidden"),$(n.currentTarget).addClass("selected"),$("#cantoViewBody").find("#globalSearch input").val(""),$("#cantoViewBody").find("#imagesContent").html(""),$("#cantoViewBody").find("#imagesContent").scrollTop(0),f=[],r=!1,g="bytree";let o=$(n.currentTarget).data("id");d.getListByAlbum(o,y)}}).on("click","#globalSearchBtn",function(n){let t=$("#cantoViewBody").find("#globalSearch input").val();if(!t){$("#cantoViewBody").find("#treeviewSection ul li").removeClass("selected");let s=$("#cantoViewBody").find(".type-font.current").data("type");$("#cantoViewBody").find("#globalSearch input").val(""),$("#cantoViewBody").find("#imagesContent").html(""),$("#cantoViewBody").find("#imagesContent").scrollTop(0),f=[],g="",r=!1,console.log("line 492"),b(s)}g="bySearch",r=!1,$("#cantoViewBody").find("#treeviewSection ul li").removeClass("selected"),$("#cantoViewBody").find(".type-font").removeClass("current");let o=$("#cantoViewBody").find(".type-font.current").data("type"),l={};l.scheme=o,l.keywords=t,$("#cantoViewBody").find("#imagesContent").html(""),$("#cantoViewBody").find("#imagesContent").scrollTop(0),f=[],d.getFilterList(l,y)}),$("#cantoViewBody").find("#cantoImageBody").on("scroll",function(){N()&&!r&&M()});let e=$("#cantoViewBody").find("#globalSearch input");$(e).bind("keyup",function(n){n.keyCode=="13"&&$("#cantoViewBody").find("#globalSearchBtn").click()});let a=$("#cantoViewBody").find("#cantoImageBody");$(a).resize(function(){V()})}function b(e){d.getListByScheme(e,y)}function y(e){if(!(e&&e.length>0))return;let a=[];v&&v.length>1&&(a=v.split(";"));for(let s=0;s<e.length;s++){let c=e[s],h=c.name.substring(c.name.lastIndexOf(".")+1);if(a.length&&!a.includes(h))continue;let w="",k=c.name;c.name.length>150&&(k=c.name.substr(0,142)+"..."+c.name.substr(-5)),w+=`<div class="single-image" data-id="${c.id}" data-scheme="${c.scheme}" data-xurl="${c.url.preview}" data-name="${c.name}" data-size="${c.size}" >
                    <img id="${c.id}" src="https://s3-us-west-2.amazonaws.com/static.dmc/universal/icon/back.png" alt="${c.scheme}">
                    <div class="mask-layer"></div>
                    <div class="single-image-name">${k}</div>
                    <span class="select-box icon-s-UnselectedCheck_32  "></span><span class="select-icon-background"></span>
                </div>`,$("#cantoViewBody").find("#imagesContent").append(w),d.getRedirectURL(c.url.preview,c.id)}$("#cantoViewBody").find(".single-image").length==0?$("#cantoViewBody").find("#noItem").removeClass("hidden"):$("#cantoViewBody").find("#noItem").addClass("hidden");let t=[];$("#cantoViewBody").find(".single-image").hover(function(){let s=$(this).height()-$(this).find(".single-image-name").height()-20;$(this).find(".single-image-name").stop().animate({top:s})},function(){$(this).find(".single-image-name").stop().animate({top:"100%"})}),$("#cantoViewBody").find(".single-image .select-box").off("click").on("click",function(s){if(s.cancelBubble=!0,s.stopPropagation(),s.preventDefault(),t.push($(".single-image").index($(this).closest(".single-image"))),s.shiftKey){let c=Math.min(t[t.length-2],t[t.length-1]),h=Math.max(t[t.length-2],t[t.length-1]);for(i=c;i<=h;i++){if($("#cantoViewBody").find(".single-image .icon-s-Ok2_32").length>=20){$(".max-select-tips").fadeIn("normal").delay(2e3).fadeOut(1e3);return}$(".single-image:eq("+i+") .select-box").addClass("icon-s-Ok2_32"),$(".single-image:eq("+i+")").addClass("selected")}}else if($("#cantoViewBody").find(".single-image .icon-s-Ok2_32").length>=20){$(this).hasClass("icon-s-Ok2_32")||$(".max-select-tips").fadeIn("normal").delay(2e3).fadeOut(1e3),$(this).removeClass("icon-s-Ok2_32"),$(this).closest(".single-image").removeClass("selected");return}else $(this).toggleClass("icon-s-Ok2_32"),$(this).closest(".single-image").toggleClass("selected");T()}),V(),T();let o=$("#cantoImageBody").height();$("#imagesContent").height()<o&&!r&&M()}let T=function(){let e=$("#cantoViewBody").find(".single-image .icon-s-Ok2_32").length;$("#cantoViewBody").find("#selected-count").html(e),e?($("#cantoViewBody").find("#globalSearch").addClass("hidden"),$("#cantoViewBody").find("#filterSection").addClass("hidden"),$("#cantoViewBody").find("#selectedCountSection").removeClass("hidden"),$("#cantoViewBody").find("#selectedActionSection").removeClass("hidden"),$("#insertAlbumWrapper").addClass("hidden")):($("#cantoViewBody").find("#globalSearch").removeClass("hidden"),$("#cantoViewBody").find("#filterSection").removeClass("hidden"),$("#cantoViewBody").find("#selectedCountSection").addClass("hidden"),$("#cantoViewBody").find("#selectedActionSection").addClass("hidden"),$("#insertAlbumWrapper").removeClass("hidden")),$("#cantoViewBody").find("#selectAllBtn").addClass("all-selected"),$("#cantoViewBody").find("#selectAllBtn").attr("title","Deselect All")};function R(e){let a=$("#cantoViewBody").find("#viewImageModal"),n=$("#cantoViewBody").find("#pageMask");a.find("img").attr("src",e),$("#cantoViewBody").find(".loading-icon").addClass("hidden"),a.removeClass("hidden"),n.removeClass("hidden"),$("#cantoViewBody").find(".view-image-modal .close-btn").off("click").on("click",function(){a.addClass("hidden"),n.addClass("hidden")})}function E(e){let a=function(t,o,l){if(t)$(o).closest(".detail-item").removeClass("hidden");else return $(o).closest(".detail-item").addClass("hidden"),"Null";return l||(l=150),t.length>l?($(o).removeClass("hidden"),t.slice(0,l)+"..."):($(o).addClass("hidden"),t)};if(e){$("#cantoViewBody").find("#imagebox_name").html(e.name),$("#cantoViewBody").find("#imagebox_size").html(Math.round(e.size/1024)+"KB"),$("#cantoViewBody").find("#imagebox_created").html(e.metadata&&e.metadata["Create Date"]?e.metadata["Create Date"]:" "),$("#cantoViewBody").find("#imagebox_uploaded").html(F(e.lastUploaded)),$("#cantoViewBody").find("#imagebox_status").html(e.approvalStatus);let t=$("#imagebox_copyright").closest(".detail-item").find(".more");$("#cantoViewBody").find("#imagebox_copyright").html(a(e.copyright,t,177)),$("#cantoViewBody").find("#imagebox_copyright").data("field",e.copyright);let o=$("#imagebox_tac").closest(".detail-item").find(".more");$("#cantoViewBody").find("#imagebox_tac").html(a(e.termsAndConditions,o,160)),$("#cantoViewBody").find("#imagebox_tac").data("field",e.termsAndConditions),$("#cantoViewBody").find("#insertBtn").data("id",e.id),$("#cantoViewBody").find("#insertBtn").data("scheme",e.scheme)}let n=$("#cantoViewBody").find("#imagePreviewModal");$("#cantoViewBody").find(".loading-icon").addClass("hidden"),n.removeClass("hidden"),$("#cantoViewBody").find("#imagePreviewModal .close-btn").off("click").on("click",function(){n.addClass("hidden")}),$("#cantoViewBody").find("#imagePreviewModal #cancelBtn").off("click").on("click",function(){n.addClass("hidden")}),$("#cantoViewBody").find("#imagePreviewModal .detail-item .more").off("click").on("click",function(){let t=$(this).closest(".detail-item").find(".content").data("field");$(this).closest(".detail-item").find(".content").html(t),$(this).addClass("hidden")}),$("#cantoViewBody").find("#imagePreviewModal #insertBtn").off("click").on("click",function(){$("#cantoViewBody").find(".loading-icon").removeClass("hidden");let t=[{id:e.id,scheme:e.scheme,size:e.size,name:e.name}];d.insertImage(t)})}function F(e){return e.substr(0,4)+"-"+e.substr(4,2)+"-"+e.substr(6,2)+" "+e.substr(8,2)+":"+e.substr(10,2)}function H(){d.loadTree(P)}let P=function(e){let a="";a=W(e),$("#cantoViewBody").find("#treeviewContent").append(a),$("#cantoViewBody").find("#treeviewContent > ul").animate({height:"toggle"})},W=function(e){let a="<ul style='display: none;'>";return $.each(e,function(n,t){let o=" ";t.size==0?o="no-child":t.scheme=="folder"&&(o="has-sub-folder"),a+=`<li data-id="${t.id}"  class="${o}">`;let l="icon-s-Folder_open-20px";t.scheme=="album"&&(l="icon-s-Album-20px"),a+=`<i class="${l}"></i>
                    <img src="https://s3-us-west-2.amazonaws.com/static.dmc/universal/icon/cantoloading.gif" class="folder-loading hidden" alt="Loading">
                    <span>${t.name}</span>
                </li>`}),a+="</ul>",a},q=function(e){let a=A(e);$("#cantoViewBody").find(".current-tree-node").append(a),$("#cantoViewBody").find(".current-tree-node > ul").animate({height:"toggle"}),$("#cantoViewBody").find(".current-tree-node").find(".folder-loading").addClass("hidden"),$("#cantoViewBody").find(".current-tree-node").find(".icon-s-Folder_open-20px").removeClass("hidden"),$("#cantoViewBody").find(".current-tree-node").removeClass("current-tree-node")},A=function(e){let a="<ul style='display: none;'>";return $.each(e,function(n,t){let o=" ";t.size==0&&(o="no-child"),a+=`<li data-id="${t.id}"  class="${o}">`;let l="icon-s-Folder_open-20px";t.scheme=="album"&&(l="icon-s-Album-20px"),a+=`<i class="${l}"></i>
                    <span>${t.name}</span>`,t.children&&t.children.length&&(a+=A(t.children)),a+="</li>"}),a+="</ul>",a};function V(){let e=8,a=Number($("#cantoViewBody").find("#imagesContent")[0].offsetWidth),n=0,t=function(l){if(n=Number((a-8)/l-2),n>=160&&n<=210)return n;n<160?(l--,t(l)):n>210&&(l++,t(l))},o=t(e);$("#cantoViewBody").find(".single-image").css("width",o)}function N(){let e=$("#cantoImageBody").height(),a=$("#imagesContent").height(),n=$("#cantoImageBody").scrollTop(),t=a-e-n<0,o=$(".single-image").length==0;return t&&!o}function x(){let e=f.length==0?0:S,a="sortBy=time&sortDirection=descending&limit="+L+"&start="+e;return $(".single-image").length!==0?$("#loadingMore").fadeIn("slow"):$("#cantoViewBody").find("#imagesContent").html(""),a}function M(){if(g=="bySearch"){let e=$("#cantoViewBody").find("#globalSearch input").val();if(!e)return;let a=$("#cantoViewBody").find(".type-font.current").data("type"),n={};n.scheme=a,n.keywords=e,d.getFilterList(n,y)}else if(g=="bytree"){let e=$("#cantoViewBody").find("#treeviewSection ul li").find(".selected").data("id");d.getListByAlbum(e,y)}else{let e=$("#cantoViewBody").find(".type-font.current").data("type");b(e)}}parent.document.querySelector(".modal-status-bar").style.display="none";
//# sourceMappingURL=canto-embed-6762f63c.js.map