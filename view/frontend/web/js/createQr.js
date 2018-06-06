/**
 * Blockonomics qr creation and display methods
 *
 * @category    Blockonomics
 * @package     Blockonomics_Merchant
 * @author      Blockonomics
 * @copyright   Blockonomics (https://blockonomics.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

var btcAddressDiv = document.getElementById("btc-address");
var btcAddress = btcAddressDiv.dataset.address;

var btcAmountDiv = document.getElementById("btc-amount");
var btcAmount = btcAmountDiv.dataset.address;

var timestampDiv = document.getElementById("order-timestamp");
var orderTimestamp = timestampDiv.dataset.timestamp;

var timeLeftDiv = document.getElementById("time-left-minutes");

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
	var totalTime = 600;
	var timeLeft = totalTime;

	// On every tick, update progressbar width to be percentage of total time divided by time left
	function tick() {

		var timeUsed = new Date() / 1000 - orderTimestamp;
		timeLeft = Math.round(totalTime - timeUsed);

		var timeLeftPercentage = timeLeft / totalTime * 100;
		timeLeftElem.style.width = timeLeftPercentage + "%";

		timeLeftDiv.innerHTML = new Date(timeLeft * 1000).toISOString().substr(14, 5);

		if(timeLeft < 0) {
			redirectToURL('blockonomics/payment/timeout?addr=' + btcAddress);
		}
	}

	// Build url and redirect to it
	function redirectToURL(urlToDir) {
		var urlToRedir = url.build(urlToDir);
		window.location.href = urlToRedir;
	}
});

function pay_altcoins() {
	document.getElementById("altcoin-waiting").style.display = "block";
	document.getElementById("paywrapper").style.display = "none";
	var altcoin_waiting = true;
	url = "https://shapeshift.io/shifty.html?destination=" + btcAddress + "&amount=" + btcAmount + "&output=BTC";
	window.open(url, '1418115287605','width=700,height=500,toolbar=0,menubar=0,location=0,status=1,scrollbars=1,resizable=0,left=0,top=0');
}

function disableAltcoin() {
	document.getElementById("altcoin-waiting").style.display = "none";
	document.getElementById("paywrapper").style.display = "block";
}

function toggleCoin(coin) {

	var btcBtn = document.getElementById('btc');
	var altcoinBtn = document.getElementById('altcoin');

	var btcDiv = document.getElementById('bnomics-btc-pane');
	var altcoinDiv = document.getElementById('bnomics-altcoin-pane');

	if(coin === 'btc') {
		btcBtn.classList.add('active');
		altcoinBtn.classList.remove('active');
		btcDiv.style.display = "block";
		altcoinDiv.style.display = "none";
	}

	if(coin === 'altcoin') {
		btcBtn.classList.remove('active');
		altcoinBtn.classList.add('active');
		btcDiv.style.display = "none";
		altcoinDiv.style.display = "block";
	}
}