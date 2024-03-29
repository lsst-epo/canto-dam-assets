/**
 * =====================================================================================================================
 * Refactored plugin code
 * =====================================================================================================================
 **/

(function ($) {
  let tokenInfo = {},
    env,
    appId,
    tenantHostName,
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
        // this.options gives us access to the $jsonVars that our FieldType passed down to us
        settings(this.options);

        // Macro for getting a namespaced field selector
        const fieldNamespaceIdSelector = (fieldName) => '#' + this.options.prefix + '-' + Craft.namespaceId(fieldName, this.options.id);

        // Display the image preview on init
        const damAssetPreview = fieldNamespaceIdSelector('damAssetPreview');

        /**
         * Displays the image preview for the chosen Canto asset(s)
         */
        function displayImagePreview() {
          const damAssetPreviewWrapper = fieldNamespaceIdSelector('damAssetPreviewWrapper');
          $(damAssetPreviewWrapper).remove();
          if ($(damAssetPreview).attr("data-thumbnailurl") == null ||
            $(damAssetPreview).attr("data-thumbnailurl") == "none") {
            $(damAssetPreview).hide();
          } else {
            const url = $(damAssetPreview).attr("data-thumbnailurl");
            const albumName = $(damAssetPreview).attr("data-albumName");
            let name = $(damAssetPreview).attr("data-thumbnailName");
            const assetCount = $(damAssetPreview).attr("data-assetCount");
            const className = assetCount == 1 ? "" : "canto-asset-preview-stack";
            name = assetCount == 1 ? name : `${assetCount} images`;
            if (albumName.length) {
              name += ' / ' + albumName;
            }
            $(fieldNamespaceIdSelector('chooseAsset')).html("Choose a Different DAM Asset");
            $(damAssetPreview).prepend(`
<div id="${damAssetPreviewWrapper.slice(1)}">
<img class="${className}" style="max-height:200px; max-width:200px;" src=${url}>
<span>${name}</span><br />
</div>
`);
          }
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

        // Handle adding or changing an asset
        $(fieldNamespaceIdSelector('chooseAsset')).click((e) => {
          $modal.show();
          let fieldId = e.target.dataset.field;
          let elementId = e.target.dataset.element;
          let type = e.target.dataset.type;
          let accessToken = e.target.dataset.access;
          loadIframeContent(fieldId, elementId, type, accessToken, this.options.bodyClass);
        });

        // Handle clicks to remove the asset
        $(fieldNamespaceIdSelector('removeDamAsset')).click((e) => {
          // Hide the preview, and change the button name
          $(fieldNamespaceIdSelector('chooseAsset')).html("Add a DAM Asset");
          $(damAssetPreview).hide();
          $(fieldNamespaceIdSelector('cantoId')).val(null);
          $(fieldNamespaceIdSelector('cantoAlbumId')).val(null);
          $(fieldNamespaceIdSelector('cantoAssetData')).val([]);
          $(fieldNamespaceIdSelector('cantoAlbumData')).val([]);
        });

        // Beginning of Canto's Universal Connector code:
        window.addEventListener("message", (event) => {
          const data = event.data;
          // Onlu listen in if we are the target for this fieldId
          if ($(cantoUCFrame).attr("data-field") != this.options.fieldId) {
            return;
          }
          if (data && data.type == "getTokenInfo") {
            var receiver = document.getElementById(cantoUCFrame.slice(1)).contentWindow;
            tokenInfo.formatDistrict = formatDistrict;
            tokenInfo.tenant = tenantHostName;
            receiver.postMessage(tokenInfo, '*');
          } else if (data && data.type == "cantoLogout") {
            tokenInfo = {};
            $(".canto-uc-iframe-close-btn").trigger("click");

          } else if (data && data.type == "cantoInsertImage") {
            $(".canto-uc-iframe-close-btn").trigger("click");
            callback(currentCantoTagID, data.assetList);

          } else if (data && data.type == "closeModal") {
            let cantoAsset = data.cantoAssetData[0];
            const assetCount = data.cantoAssetData.length;
            $(damAssetPreview).attr("data-assetCount", assetCount);
            $(damAssetPreview).attr("data-thumbnailUrl", cantoAsset.url.directUrlPreview);
            $(damAssetPreview).attr("data-thumbnailName", cantoAsset.name);
            $(damAssetPreview).attr("data-albumName", data.cantoAlbumData.name);
            displayImagePreview();
            // Save the cantoId & cantoAssetData into the hidden field data
            $(fieldNamespaceIdSelector('cantoId')).val(data.cantoId);
            $(fieldNamespaceIdSelector('cantoAlbumId')).val(data.cantoAlbumId);
            $(fieldNamespaceIdSelector('cantoAssetData')).val(JSON.stringify(data.cantoAssetData));
            $(fieldNamespaceIdSelector('cantoAlbumData')).val(JSON.stringify(data.cantoAlbumData));
            $(damAssetPreview).show();
            $modal.hide();

          } else if (data) {
            let verifyCode = data;
            getTokenByVerifycode(verifyCode);

          }

        });
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
    appId = options.appId;
    tenantHostName = options.tenantHostName;
    formatDistrict = options.extensions;
  }

  // The modal for the Canto picker
  const cantoUCFrame = '#cantoDamAssetsUCFrame';
  let modalMarkup = $(`
                <div class="modal"> <!-- modal body -->
                    <div class="body modal-test" style="padding: 0;"> <!-- modal-content -->
                        <header class="header" style="padding: 48px 48px 24px; margin: -24px -24px 0px;">
                            <h2>Canto Assets</h2>
                        </header>
                        <iframe id="${cantoUCFrame.slice(1)}" class="canto-uc-subiframe" src=""></iframe>
                        <div class="modal-status-bar">Uploading Image...</div>
                    </div>
                </div>
                `);
  let $modal = new Garnish.Modal(modalMarkup, {'autoShow': false});

  /*--------------------------load iframe content---------------------------------------*/
  function loadIframeContent(fieldId, elementId, type, accessToken, bodyClass) {
//  let timeStamp = new Date().getTime();
    let tokenInfo = {
      accessToken: accessToken,
      tenant: tenantHostName,
    };
    let cantoLoginPage = "https://oauth.canto.com/oauth/api/oauth2/universal2/authorize?response_type=code&app_id=" + "52ff8ed9d6874d48a3bef9621bc1af26" + "&redirect_uri=http://localhost:8080&state=abcd" + "&code_challenge=" + "1649285048042" + "&code_challenge_method=plain";

    var cantoContentPage = "/admin/_canto-dam-assets/canto-embed.twig";
    if (tokenInfo.accessToken) {
      $(cantoUCFrame).attr("data-element", elementId);
      $(cantoUCFrame).attr("data-field", fieldId);
      $(cantoUCFrame).attr("data-type", type);
      $(cantoUCFrame).attr("data-access", tokenInfo.accessToken);
      $(cantoUCFrame).attr("data-tenant", tokenInfo.tenant);
      $(cantoUCFrame).attr("src", cantoContentPage);
    } else {
      $(cantoUCFrame).attr("data-element", elementId);
      $(cantoUCFrame).attr("data-field", fieldId);
      $(cantoUCFrame).attr("data-type", type);
      $(cantoUCFrame).attr("src", cantoLoginPage);
    }

    // Apply the feature classes for the iFrame immediately, and also after the iFrame as loaded
    function applyIFrameClasses() {
      const $iFrameBody = $(cantoUCFrame).contents().find("body");
      $iFrameBody.removeClass('can-select-single can-select-multiple can-select-album');
      $iFrameBody.addClass(bodyClass);
    }

    $(cantoUCFrame).on("load", applyIFrameClasses);
    applyIFrameClasses();
  }

  function getTenant(tokenInfo) {
    $.ajax({
      type: "GET",
      url: "https://oauth." + env + ":443/oauth/api/oauth2/tenant/" + tokenInfo.refreshToken,
      success: function (data) {
        tokenInfo.tenant = data;
        $(cantoUCFrame).attr("src", "/admin/_canto-dam-assets/canto-embed.twig");
      },
      error: function () {
        alert("Get tenant error");
      }
    });
  }

})(jQuery, window, document);

// Accept HMR as per: https://vitejs.dev/guide/api-hmr.html
if (import.meta.hot) {
  import.meta.hot.accept(() => {
    console.log("HMR")
  });
}

// Re-broadcast the custom `vite-script-loaded` event so that we know that this module has loaded
// Needed because when <script> tags are appended to the DOM, the `onload` handlers
// are not executed, which happens in the field Settings page, and in slideouts
// Do this after the document is ready to ensure proper execution order
$(document).ready(function () {
  const e = new CustomEvent('vite-script-loaded', {detail: {path: 'src/js/canto-field.js'}});
  document.dispatchEvent(e);
});
