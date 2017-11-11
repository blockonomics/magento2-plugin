define([
  "jquery",
  "Blockonomics_Merchant/js/qrcode",
  'Blockonomics_Merchant/js/reconnecting-websocket.min'
], 
function($, qrcode, reconnectingWebsocket) {
  "use strict";

  var btcHrefDiv = document.getElementById("btc-href");
  var btcHref = btcHrefDiv.dataset.href;
  new QRCode(document.getElementById("qrcode"), btcHref);

  var btcAddressDiv = document.getElementById("btc-address");
  var btcAddress = btcAddressDiv.dataset.address;

  // Seconds now from epoch
	var d = new Date();
	var seconds = Math.round(d.getTime() / 1000);

	//Websocket
	var ws = new ReconnectingWebSocket("wss://www.blockonomics.co/payment/" + btcAddress + "?timestamp=" + seconds);
	ws.onmessage = function (evt) {
		ws.close();
		$interval(function(){
			//Redirect to order received page
			window.location = "#";
			//Wait for 2 seconds for order status
			//to update on server
		}, 2000, 1);
	}

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