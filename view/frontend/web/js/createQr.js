define([
  "jquery",
  "Blockonomics_Merchant/js/qrcode"
], 
function($, qrcode) {
  "use strict";

  var btcArddressDiv = document.getElementById("btc-address");
  var btcAddress = btcArddressDiv.dataset.address;
  
  new QRCode(document.getElementById("qrcode"), btcAddress);

});