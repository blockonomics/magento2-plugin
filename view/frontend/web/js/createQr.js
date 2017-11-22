/**
 * Blockonomics qr creation and display methods
 *
 * @category    Blockonomics
 * @package     Blockonomics_Merchant
 * @author      Blockonomics
 * @copyright   Blockonomics (https://blockonomics.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
define([
  "jquery",
  "Blockonomics_Merchant/js/qrcode.min",
  'Blockonomics_Merchant/js/reconnecting-websocket.min',
	'mage/url'
], 
function($, qrcode, ReconnectingWebSocket, url) {
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
			var urlToRedir = url.build('checkout/onepage/success');
			window.location.href = urlToRedir;
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