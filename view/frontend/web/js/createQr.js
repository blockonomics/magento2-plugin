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
var btcAmount = btcAmountDiv.dataset.amount;

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

		if(timeLeft < 0 && altcoin_waiting != true) {
			redirectToURL('blockonomics/payment/timeout?addr=' + btcAddress);
		}
	}

	// Build url and redirect to it
	function redirectToURL(urlToDir) {
		var urlToRedir = url.build(urlToDir);
		window.location.href = urlToRedir;
	}
});

var altcoin_waiting = false;
function pay_altcoins() {
	document.getElementById("altcoin-waiting").style.display = "block";
	document.getElementById("paywrapper").style.display = "none";
	altcoin_waiting = true;
	send_email = true;
	var systemUrlDiv = document.getElementById("system-url");
	var orderId = systemUrlDiv.dataset.orderid;
	var selected_altcoin = document.getElementById("altcoin_select");
   var flyp = new Object();
   flyp.from_currency = selected_altcoin.value;
   flyp.to_currency  = "BTC";
   flyp.ordered_amount = btcAmount;
   flyp.destination = btcAddress;
   var flypOrder = new Object();
   flypOrder.order = flyp;
   var flypOrderString= JSON.stringify(flypOrder);
	var new_order = new XMLHttpRequest();
	new_order.onreadystatechange = function() {
		if (this.readyState == 4 && this.status == 200) {
			var response = JSON.parse(this.responseText);
			var uuid = response['order']['uuid'];
			if(uuid){
				var flypObj = new Object();
			    flypObj.uuid = uuid;
			    var flypObjUUID= JSON.stringify(flypObj);
				var accept_order = new XMLHttpRequest();
				accept_order.onreadystatechange = function() {
					if (this.readyState == 4 && this.status == 200) {
						var response = JSON.parse(this.responseText);
						document.getElementById("alt-address").value = response['deposit_address'];
						document.getElementById("alt-amount").innerHTML = response['order']['invoiced_amount'];
						document.getElementById('bnomics-refund-input').placeholder = response['order']['from_currency']  + ' ' + 'Address';
						set_alt_status('waiting');
						interval_check = setInterval(function(response) {
						  checkOrder(uuid);
						}, 10000);
						var alt_qr_code = selected_altcoin[selected_altcoin.selectedIndex].id+":"+ response['deposit_address'] +"?amount="+ response['order']['invoiced_amount'] +"&value="+ response['order']['invoiced_amount'];
						document.getElementById("alt-qrcode").href = alt_qr_code;
						new QRCode(document.getElementById("alt-qrcode"), {
							text: alt_qr_code,
							width: 128,
							height: 128,
							correctLevel : QRCode.CorrectLevel.M
						});
						var altMinutesLeft = document.getElementById("alt-time-left-minutes");
						var altTotalProgress = 100;
						var altTotalTime = 10 * 60; //10m
						var altCurrentTime = 10 * 60; //10m
						var altCurrentProgress = 100;
						var altTimeDiv = document.getElementById("alt-time-left");
						interval = setInterval( function() { 
							altCurrentTime = altCurrentTime - 1;
							altCurrentProgress = Math.floor(altCurrentTime*altTotalProgress/altTotalTime);
							altTimeDiv.style.width = "" + altCurrentProgress + "%";

							var result = new Date(altCurrentTime * 1000).toISOString().substr(14, 5);
							altMinutesLeft.innerHTML = result;

							if (altCurrentTime <= 0) {
								document.getElementById("alt-time-wrapper").style.display = "none";
								document.getElementById("alt-time-left-minutes").style.display = "none";
							}
						}, 1000);
					}
				};
				accept_order.open("POST", "https://flyp.me/api/v1/order/accept", true);
				accept_order.setRequestHeader("Content-type", "application/json");
				accept_order.send(flypObjUUID);
			}
		}
	};
	new_order.open("POST", "https://flyp.me/api/v1/order/new", true);
	new_order.setRequestHeader("Content-type", "application/json");
	new_order.send(flypOrderString);

	var limits = new XMLHttpRequest();
	limits.onreadystatechange = function() {
		if (this.readyState == 4 && this.status == 200) {
			var response = JSON.parse(this.responseText);
			console.log(response);
			console.log(btcAmount);
			var alt_minimum = response['min'];
			var alt_maximum = response['max'];
			if(btcAmount <= alt_minimum || btcAmount >= alt_maximum){
				set_alt_status('low_high');
				clearInterval(interval_check);
			}
		}
	};
	limits.open("GET", "https://flyp.me/api/v1/order/limits/"+selected_altcoin.value+"/BTC", true);
	limits.send();
}

function disableAltcoin() {
	document.getElementById("altcoin-waiting").style.display = "none";
	document.getElementById("paywrapper").style.display = "block";
	document.getElementById("alt-qrcode").innerHTML = "";
	document.getElementById("alt-amount").innerHTML = "";
	document.getElementById("alt-address").value = "";
	altcoin_waiting = false;
	clearInterval(interval);
	clearInterval(interval_check);
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
var interval;
var interval_check;
var send_email = false;
function checkOrder(uuid){
	var flypObj = new Object();
    flypObj.uuid = uuid;
    var flypObjUUID = JSON.stringify(flypObj);
	var check = new XMLHttpRequest();
	check.onreadystatechange = function() {
		if (this.readyState == 4 && this.status == 200) {
			var response = JSON.parse(this.responseText);
			console.log(response);
			if(response['payment_status'] == "PAYMENT_RECEIVED" || response['payment_status'] == "PAYMENT_CONFIRMED"){
	          set_alt_status('received');
	          clearInterval(interval_check);
	        }else if(response['payment_status'] == "OVERPAY_RECEIVED" || response['payment_status'] == "UNDERPAY_RECEIVED" || response['payment_status'] == "OVERPAY_CONFIRMED" || response['payment_status'] == "UNDERPAY_CONFIRMED"){
	        	if(response['status'] == "EXPIRED"){
	        		set_alt_status('refunded');
	                clearInterval(interval_check);
	        	}else if(response['status'] == "REFUNDED"){
	        		if(response['txid']){
	        			set_alt_status('refunded-txid');
	          			clearInterval(interval_check);
	          			document.getElementById("alt-refund-txid").innerHTML = response['txid'];
	          			document.getElementById("alt-refund-url").innerHTML = response['txurl'];
	        		}else{
	        			set_alt_status('refunded');
	        		}
	        	}else{
                	if(send_email == true){
                		//Send the Email
                		console.log('Sending email');
                  		send_email = false;
                  	}
                  	if(address_present == true){
                  		set_alt_status('refunded');
	                }else{
		                set_alt_status('add_refund');
	                	clearInterval(interval_check);
	                }
                }
	        }else if(response['status'] == "WAITING_FOR_DEPOSIT"){
              set_alt_status('waiting');
            }else if(response['status'] == "EXPIRED"){
              set_alt_status('expired');
	          clearInterval(interval_check);
            }
		}
	};
	check.open("POST", "https://flyp.me/api/v1/order/check", true);
	check.setRequestHeader("Content-type", "application/json");
	check.send(flypObjUUID);
}

function set_alt_status(status) {
	var all_status = document.getElementsByClassName("altcoin-status");
	for(var i = 0; i < all_status.length; i++){
   		if (all_status.item(i).id == status){
   			all_status.item(i).style.display = "block";
   		}else{
			all_status.item(i).style.display = "none";
		}
	}
}

function btc_copy_click() {
    var copyText = document.getElementById("address");
    copyText.select();
    document.execCommand("copy");
    document.getElementById("btc-copy-text").style.display = "block";
    setTimeout(function() {
        document.getElementById("btc-copy-text").style.display = "none";
    }, 2000); 
}

function alt_copy_click() {
	var copyText = document.getElementById("alt-address");
    copyText.select();
    document.execCommand("copy");
    document.getElementById("alt-copy-text").style.display = "block";
    setTimeout(function() {
        document.getElementById("alt-copy-text").style.display = "none";
    }, 2000);
}

function altcoin_select() {
	var element = document.getElementById("alt_selected");
	var selected_altcoin = document.getElementById("altcoin_select");
	element.innerHTML = selected_altcoin.value;
	var element_pay = document.getElementById("alt_selected_pay");
	element_pay.innerHTML = selected_altcoin.value;
	var element_name = document.getElementById("alt_name_pay");
	element_name.innerHTML = selected_altcoin.value;
	var pay_with_icon = document.getElementById("pay_with_icon");
	if(selected_altcoin.value == 'ETH'){
		pay_with_icon.classList.add('cf-eth');
		pay_with_icon.classList.remove('cf-ltc');
	}
	else if(selected_altcoin.value == 'LTC'){
		pay_with_icon.classList.add('cf-ltc');
		pay_with_icon.classList.remove('cf-eth');
	}
}