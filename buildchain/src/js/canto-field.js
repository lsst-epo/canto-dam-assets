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

        const fieldNamespaceIdSelector = (fieldName) => `#fields-` + Craft.namespaceId(fieldName, this.options.id);

        $("#fields-remove-dam-asset").click((e) => {
          // Hide the preview, and change the button name
          $("#fields-rosas-clicker").html("Add a DAM Asset");
          $("#fields-dam-asset-preview").hide();
          $(fieldNamespaceIdSelector('cantoId')).val(null);
          $(fieldNamespaceIdSelector('cantoAssetData')).val([]);
        });

        // Beginning of Canto's Universal Connector code:
        window.onmessage = (event) => {
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
            let cantoAsset = data.cantoAssetData[0];
            $("#fields-dam-asset-preview").prepend(`<img id="fields-dam-preview-image" style="max-height:200px; max-width:200px;" src=${cantoAsset.directUri}>`);
            // Save the cantoId & cantoAssetData into the hidden field data
            $(fieldNamespaceIdSelector('cantoId')).val(data.cantoId);
            $(fieldNamespaceIdSelector('cantoAssetData')).val(JSON.stringify(data.cantoAssetData));
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
  $("#fields-dam-asset-preview").prepend(`<img id="fields-dam-preview-image" style="max-height:200px; max-width:200px;" src=${url}>`);
}

/*--------------------------load iframe content---------------------------------------*/
function loadIframeContent(fieldId, elementId, type, accessToken) {
//  let timeStamp = new Date().getTime();
  let tokenInfo = {accessToken: accessToken};
  let cantoLoginPage = "https://oauth.canto.com/oauth/api/oauth2/universal2/authorize?response_type=code&app_id=" + "52ff8ed9d6874d48a3bef9621bc1af26" + "&redirect_uri=http://localhost:8080&state=abcd" + "&code_challenge=" + "1649285048042" + "&code_challenge_method=plain";

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

$("#fields-rosas-clicker").click(function (e) {
  $modal.show();
  let fieldId = e.target.dataset.field;
  let elementId = e.target.dataset.element;
  let type = e.target.dataset.type;
  let accessToken = e.target.dataset.access;
  loadIframeContent(fieldId, elementId, type, accessToken);
});
