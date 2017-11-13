define([
  "jquery",
  "Blockonomics_Merchant/js/qrcode.min",
  'Blockonomics_Merchant/js/reconnecting-websocket.min'
], 
function($, qrcode, ReconnectingWebSocket) {
  "use strict";

  var btcHrefDiv = document.getElementById("btc-href");
  var btcHref = btcHrefDiv.dataset.href;
  new QRCode(document.getElementById("qrcode"), {
  	text: btcHref,
  	width: 128,
  	height: 128,
  	correctLevel : QRCode.CorrectLevel.M
  });

  var btcAddressDiv = document.getElementById("btc-address");
  var btcAddress = btcAddressDiv.dataset.address;

  // Seconds now from epoch
	var d = new Date();
	var seconds = Math.round(d.getTime() / 1000);

	//Websocket
	var ws = new ReconnectingWebSocket("wss://www.blockonomics.co/payment/" + btcAddress + "?timestamp=" + seconds);
	ws.onmessage = function (evt) {
		ws.close();
		//$interval(function(){
			//Redirect to order received page
			window.location = "/magento2/checkout/onepage/success";
			//Wait for 2 seconds for order status
			//to update on server
		//}, 2000, 1);
	}

  //window.setInterval(tick, 1000);
});

/*
 * This will be functional in Ver 0.2
var timeLeftElem = document.getElementById("time-left");
var totalTime = 600;
var timeLeft = totalTime;

function tick() {
	timeLeft--;
	var timeLeftPercentage = timeLeft / totalTime * 100;
	timeLeftElem.style.width = timeLeftPercentage + "%";
}
*/