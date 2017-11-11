define([
  "jquery",
  "Blockonomics_Merchant/js/qrcode",
  'Blockonomics_Merchant/js/reconnecting-websocket.min'
], 
function($, qrcode, reconnectingWebsocket) {
  "use strict";

  var btcArddressDiv = document.getElementById("btc-address");
  var btcAddress = btcArddressDiv.dataset.address;
  new QRCode(document.getElementById("qrcode"), btcAddress);

  

  //window.setInterval(tick, 1000);
});

/*
 * This will be functionla in Ver 0.2
var timeLeftElem = document.getElementById("time-left");
var totalTime = 600;
var timeLeft = totalTime;

function tick() {
	timeLeft--;
	var timeLeftPercentage = timeLeft / totalTime * 100;
	timeLeftElem.style.width = timeLeftPercentage + "%";
}
*/