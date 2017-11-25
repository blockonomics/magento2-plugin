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

  var secretDiv = document.getElementById("secret");
  var secret = secretDiv.dataset.secret;

  // Seconds now from epoch
	var d = new Date();
	var seconds = Math.round(d.getTime() / 1000);

	//Websocket
	var ws = new ReconnectingWebSocket("wss://www.blockonomics.co/payment/" + btcAddress + "?timestamp=" + seconds);
	
	ws.onmessage = function (evt) {
		ws.close();
		redirectToURL('checkout/onepage/success');
	}

  window.setInterval(tick, 1000);

	var timeLeftElem = document.getElementById("time-left");
	var totalTime = 12;
	var timeLeft = totalTime;

	// On every tick, update progressbar width to be percentage of total time divided by time left
	function tick() {
		timeLeft--;
		
		var timeLeftPercentage = timeLeft / totalTime * 100;
		timeLeftElem.style.width = timeLeftPercentage + "%";

		if(timeLeft <= 0) {
			redirectToURL('blockonomics/payment/timeout?secret=' + secret + '&addr=' + btcAddress);
		}
	}

	// Build url and redirect to it
	function redirectToURL(urlToDir) {
		var urlToRedir = url.build(urlToDir);
		window.location.href = urlToRedir;
	}
});