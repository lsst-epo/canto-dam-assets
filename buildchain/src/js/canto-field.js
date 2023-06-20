/**
 * =====================================================================================================================
 * Refactored plugin code
 * =====================================================================================================================
 **/

(function ($) {
  let tokenInfo = {},
    env = "canto.com",
    appId = "52ff8ed9d6874d48a3bef9621bc1af26",
    currentCantoTagID,
    formatDistrict;

  const pluginName = "CantoDamConnector",
    defaults = {
      env: "canto.com",
    };

  // Plugin constructor
  function Plugin(element, options) {
    this.element = element;

    this.options = $.extend({}, defaults, options);

    this._defaults = defaults;
    this._name = pluginName;

    this.init();
  }

  Plugin.prototype = {

    init: function () {

      $(() => {
        /* -- this.options gives us access to the $jsonVars that our FieldType passed down to us */
        settings(this.options);
        console.log(this.options);

        // Beginning of Canto's Universal Connector code:
        window.onmessage = function (event) {
          var data = event.data;
          if (data && data.type == "getTokenInfo") {
            var receiver = document.getElementById('cantoUCFrame').contentWindow;
            tokenInfo.formatDistrict = formatDistrict;
            receiver.postMessage(tokenInfo, '*');
          } else if (data && data.type == "cantoLogout") {
            tokenInfo = {};
            $(".canto-uc-iframe-close-btn").trigger("click");

          } else if (data && data.type == "cantoInsertImage") {
            $(".canto-uc-iframe-close-btn").trigger("click");
            callback(currentCantoTagID, data.assetList);

          } else if (data && data.type == "closeModal") {
            $("#fields-dam-preview-image").remove();
            $("#fields-rosas-clicker").html("Choose a Different DAM Asset");
            $("#fields-dam-asset-preview").prepend(`<img id="fields-dam-preview-image" style="max-height:200px; max-width:200px;" src=${data.thumbnailUrl}/>`);
            $("#fields-dam-asset-preview").show();
            $modal.hide();

          } else if (data) {
            let verifyCode = data;
            getTokenByVerifycode(verifyCode);

          }

        };
      });
    }

  };

  // A really lightweight plugin wrapper around the constructor,
  // preventing against multiple instantiations
  $.fn[pluginName] = function (options) {
    return this.each(function () {
      if (!$.data(this, "plugin_" + pluginName)) {
        $.data(this, "plugin_" + pluginName,
          new Plugin(this, options));
      }
    });
  };

  function settings(options) {
    env = options.env;
    formatDistrict = options.extensions;
  }

  function getTokenByVerifycode(verifyCode) {
    $.ajax({
      type: "POST",
      url: "https://oauth.canto.com/oauth/api/oauth2/universal2/token",
      dataType: "json",
      data: {
        "app_id": appId,
        "grant_type": "authorization_code",
        "redirect_uri": "http://localhost:8080",
        "code": verifyCode,
        "code_verifier": "1649285048042"
      },
      success: function (data) {
        tokenInfo = data;
        getTenant(tokenInfo);

      },
      error: function () {
        alert("Get token errorz");
      }
    });
  }

  function getTenant(tokenInfo) {
    $.ajax({
      type: "GET",
      url: "https://oauth." + env + ":443/oauth/api/oauth2/tenant/" + tokenInfo.refreshToken,
      success: function (data) {
        tokenInfo.tenant = data;
        console.log("in test.js loading UC!");
        $("#cantoUCFrame").attr("src", "/admin/_canto-dam-assets/canto-embed.twig");
      },
      error: function () {
        alert("Get tenant error");
      }
    });
  }
})(jQuery, window, document);

/**
 * =====================================================================================================================
 * Event handlers originally from script.js
 * =====================================================================================================================
 **/

if ($("#fields-dam-asset-preview").attr("data-thumbnailurl") == null ||
  $("#fields-dam-asset-preview").attr("data-thumbnailurl") == "none") {
  $("#fields-dam-asset-preview").hide();
} else {
  let url = $("#fields-dam-asset-preview").attr("data-thumbnailurl");
  $("#fields-rosas-clicker").html("Choose a Different DAM Asset");
  $("#fields-dam-asset-preview").prepend(`<img id="fields-dam-preview-image" style="max-height:200px; max-width:200px;" src=${url}/>`);
}

/*--------------------------load iframe content---------------------------------------*/
function loadIframeContent(fieldId, elementId, type, accessToken) {
//  let timeStamp = new Date().getTime();
  let tokenInfo = {accessToken: accessToken};
  let cantoLoginPage = "https://oauth.canto.com/oauth/api/oauth2/universal2/authorize?response_type=code&app_id=" + "52ff8ed9d6874d48a3bef9621bc1af26" + "&redirect_uri=http://localhost:8080&state=abcd" + "&code_challenge=" + "1649285048042" + "&code_challenge_method=plain";

  console.log("Inside of script.js about to load UC!");
  var cantoContentPage = "/admin/_canto-dam-assets/canto-embed.twig";
  if (tokenInfo.accessToken) {
    // $("#cantoUCFrame").attr("data-test", val);
    $("#cantoUCFrame").attr("data-element", elementId);
    $("#cantoUCFrame").attr("data-field", fieldId);
    $("#cantoUCFrame").attr("data-type", type);
    $("#cantoUCFrame").attr("data-access", tokenInfo.accessToken);
    $("#cantoUCFrame").attr("src", cantoContentPage);
  } else {
    $("#cantoUCFrame").attr("data-element", elementId);
    $("#cantoUCFrame").attr("data-field", fieldId);
    $("#cantoUCFrame").attr("data-type", type);
    $("#cantoUCFrame").attr("src", cantoLoginPage);
  }
}

let modalMarkup = $(`
                <div id="rosas-modal" class="modal"> <!-- modal body -->
                    <div id="modal-test" class="body" style="padding: 0;"> <!-- modal-content -->
                        <header class="header" style="padding: 48px 48px 24px; margin: -24px -24px 0px;">
                            <h2>Canto Assets</h2>
                        </header>
                        <iframe id="cantoUCFrame" class="canto-uc-subiframe" src=""></iframe>
                        <div id="modal-status-bar">Uploading Image...</div>
                    </div>
                </div>
                `);
let $modal = new Garnish.Modal(modalMarkup, {'autoShow': false});

$("#fields-remove-dam-asset").click(function (e) {
  let fieldId = e.target.dataset.field;
  let elementId = e.target.dataset.element;
//  let assetId = e.target.dataset.asset;
  $.ajax({
    type: "POST",
    url: "/canto-dam-integrator/dam-asset-removal",
    dataType: "json",
    data: {
      "elementId": elementId,
      "fieldId": fieldId
    },
    success: function (data) {
      let res = JSON.parse(data);
      if (res.status == "success") {
        $("#fields-rosas-clicker").html("Add a DAM Asset");
        $("#fields-dam-asset-preview").hide();
      } else {
        console.log("logging data!!!");
        console.log(data);
        alert("An error occurred while attempting to remove the image, please try again later.");
      }
    },
    error: function (request) {
      console.log("logging request!!!");
      console.log(request);
      alert("An error occurred while attempting to remove the image, please try again later.");
    }
  });
});

$("#fields-rosas-clicker").click(function (e) {
  $modal.show();
  let fieldId = e.target.dataset.field;
  let elementId = e.target.dataset.element;
  let type = e.target.dataset.type;
  let accessToken = e.target.dataset.access;
  loadIframeContent(fieldId, elementId, type, accessToken);
});

/**
 * =====================================================================================================================
 * jQuery plugin and other helper functions originally from test.js
 * =====================================================================================================================
 **/

function calcImageSize(num) {
  var size = Math.round(Number(num) / 1024);
  return size < 1024 ? size + "KB" : Math.round(size / 1024) + "MB";
}

// $("#uploadBtn").change(e => {
//     console.log("uploaded!");
//     console.log(e);
// });

function replaceCantoTagByImage(id, assetArray) {
  var body = $("body");
  var cantoTag = body.find("canto" + "#" + id);
  var imageHtml = "";
  for (var i = 0; i < assetArray.length; i++) {
    imageHtml += '<div class="canto-block">';
    imageHtml += '<img class="canto-preview-img" src="' + assetArray[i].previewUri + '">';
    imageHtml += '<div class="canto-preview-name">Name: ' + assetArray[i].displayName + '</div>';
    imageHtml += '<div class="canto-preview-size">Size: ' + calcImageSize(assetArray[i].size) + '</div>';
    imageHtml += '<a class="canto-preview-size" href="' + assetArray[i].directUri + '">Download</a>';
    imageHtml += '</div>';
  }
  cantoTag.replaceWith(imageHtml);
}
