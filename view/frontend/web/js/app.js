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
if(btcAddressDiv !== null){
	var btcAddress = btcAddressDiv.dataset.address;
}

var btcAmountDiv = document.getElementById("btc-amount");
if(btcAmountDiv !== null){
	var btcAmount = btcAmountDiv.dataset.amount;
}

var timestampDiv = document.getElementById("order-timestamp");
if(timestampDiv !== null){
	var orderTimestamp = timestampDiv.dataset.timestamp;
}

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
  if(btcHrefDiv !== null){
	var btcHref = btcHrefDiv.dataset.href;
  }
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
		if(timeLeftElem !== null){
			timeLeftElem.style.width = timeLeftPercentage + "%";
		}

		if(timeLeftDiv !== null){
			timeLeftDiv.innerHTML = new Date(timeLeft * 1000).toISOString().substr(14, 5);
		}

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
	var selected_altcoin = document.getElementById("altcoin_select");
	altcoin_waiting = true;
	//Only send email if create order
	send_email = true;
	var flyp = new Object();
	flyp.from_currency = selected_altcoin.value;
	flyp.to_currency = "BTC";
	flyp.ordered_amount = btcAmount;
	flyp.destination = btcAddress;
	var flypOrder = new Object();
	flypOrder.order = flyp;
	var flypOrderString = JSON.stringify(flypOrder);

	//Create the altcoin order
	(function(promises) {
		return new Promise((resolve, reject) => {
			//Wait for both the altcoin limits and new altcoin order uuid
			Promise.all(promises)
				.then(values => {
					var alt_minimum = values[0]['min'];
					var alt_maximum = values[0]['max'];
					//Compare the min/max limits for altcoin payments with the order amount
					if (btcAmount <= alt_minimum || btcAmount >= alt_maximum) {
						//Order amount too low for altcoin payment
						update_altcoin_status('low_high');
						stop_interval();
					} else {
						//Display altcoin order info
						document.getElementById("alt-address").value = values[1]['deposit_address'];
						document.getElementById("alt-amount").innerHTML = values[1]['order']['invoiced_amount'];
						document.getElementById('bnomics-refund-input').placeholder = values[1]['order']['from_currency'] + ' ' + 'Address';
						var alt_qr_code = selected_altcoin[selected_altcoin.selectedIndex].id + ":" + values[1]['deposit_address'] + "?amount=" + values[1]['order']['invoiced_amount'] + "&value=" + values[1]['order']['invoiced_amount'];
						document.getElementById("alt-qrcode").href = alt_qr_code;
						new QRCode(document.getElementById("alt-qrcode"), {
							text: alt_qr_code,
							width: 128,
							height: 128,
							correctLevel: QRCode.CorrectLevel.M
						});
						var altMinutesLeft = document.getElementById("alt-time-left-minutes");
						var altTotalProgress = 100;
						var altTotalTime = 10 * 60; //10m
						var altCurrentTime = 10 * 60; //10m
						var altCurrentProgress = 100;
						var altTimeDiv = document.getElementById("alt-time-left");
						interval = setInterval(function() {
							altCurrentTime = altCurrentTime - 1;
							altCurrentProgress = Math.floor(altCurrentTime * altTotalProgress / altTotalTime);
							altTimeDiv.style.width = "" + altCurrentProgress + "%";

							var result = new Date(altCurrentTime * 1000).toISOString().substr(14, 5);
							altMinutesLeft.innerHTML = result;

							if (altCurrentTime <= 0) {
								var time_wrapper = document.getElementById("alt-time-wrapper");
								if(time_wrapper !== null){
									time_wrapper.style.display = "none";
								}
								var time_left = document.getElementById("alt-time-wrapper");
								if(time_left !== null){
									time_left.style.display = "none";
								}
							}
						}, 1000);
						//Update altcoin status to waiting
						update_altcoin_status('waiting');
						//Start checking the order status
						start_check_order();
					}
					resolve(values);
				})
				.catch(err => {
					console.dir(err);
					throw err;
				});
		});
	})([
		new Promise((resolve, reject) => {
			//Fetch altcoin min/max limits
			var limits = new XMLHttpRequest();
			limits.onreadystatechange = function() {
				if (this.readyState == 4 && this.status == 200) {
					var response = JSON.parse(this.responseText);
					resolve(response);
				}
			};
			limits.open("GET", "https://flyp.me/api/v1/order/limits/" + selected_altcoin.value + "/BTC", true);
			limits.send();
		}),
		new Promise((resolve, reject) => {
			//Create the new altcoin order
			var new_order = new XMLHttpRequest();
			new_order.onreadystatechange = function() {
				if (this.readyState == 4 && this.status == 200) {
					var response = JSON.parse(this.responseText);
					var uuid = response['order']['uuid'];
					flyp_uuid = uuid;
					document.getElementById("alt-uuid").innerHTML = uuid;
					if (uuid) {
						//Accept the altcoin order using the uuid
						var flypObj = new Object();
						flypObj.uuid = uuid;
						var flypObjUUID = JSON.stringify(flypObj);
						var accept_order = new XMLHttpRequest();
						accept_order.onreadystatechange = function() {
							if (this.readyState == 4 && this.status == 200) {
								var response = JSON.parse(this.responseText);
								resolve(response);
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
		})
	]);
}

//Send altcoin refund email 
function send_refund_email() {
	//Send the Email
	var systemUrlDiv = document.getElementById("system-url");
	var orderId = systemUrlDiv.dataset.orderid;
	send_email = false;
	var params = "id="+orderId+"&uuid="+flyp_uuid;

	require([
	    "jquery"
	],function($) 
	{
        $.ajax({
            url: "/blockonomics/sendmail/sendmail",
            data: params,
            type: 'POST',
            dataType: 'json',
            beforeSend: function() {
                // show some loading icon
            },
            success: function(data, status, xhr) {
                console.log(this.data);
            },
            error: function (xhr, status, errorThrown) {
                console.log(errorThrown);
            }
        });
    });
}

function get_uuid() {
    return flyp_uuid;
}

function wait_for_refund() {
    //Make sure only one interval is running
    stop_interval();
    uuid = get_uuid();
    check_interval = setInterval(function(response) {
        info_order(uuid);
    }, 30000);
}

//Start checking the altcoin payment status every 10 sec
function start_check_order() {
    //Make sure only one interval is running
    stop_interval();
    uuid = get_uuid();
	check_interval = setInterval(function(response) {
	  check_order(uuid);
	}, 10000);
}

//Stop checking the altcoin payment status every 10 sec
function stop_interval() {
    clearInterval(check_interval);
}

//Process altcoin response
function process_alt_response(data) {
    switch (data.payment_status) {
        case "PAYMENT_RECEIVED":
        case "PAYMENT_CONFIRMED":
            update_altcoin_status('received');
            stop_interval();
            break;
        case "OVERPAY_RECEIVED":
        case "UNDERPAY_RECEIVED":
        case "OVERPAY_CONFIRMED":
        case "UNDERPAY_CONFIRMED":
            if ('refund_address' in data) {
                if ('txid'in data) {
                    //Refund has been sent
                    update_altcoin_status('refunded-txid');
                    stop_interval();
	          		document.getElementById("alt-refund-txid").innerHTML = response['txid'];
	          		document.getElementById("alt-refund-url").innerHTML = response['txurl'];
                    break;
                } else {
                    //Refund is being processed
                    wait_for_refund();
                    $scope.altuuid = uuid;
                    update_altcoin_status('refunded');
                    break;
                }
            }
            //Refund address has not been added
            update_altcoin_status('add_refund');
            stop_interval();
            //Send email if not sent
            if (send_email) {
                send_refund_email();
                send_email = false;
            }
            break;
        default:
            switch (data.status) {
                case "WAITING_FOR_DEPOSIT":
                    update_altcoin_status('waiting');
                    start_check_order();
                    break;
                case "EXPIRED":
                    update_altcoin_status('expired');
                    stop_interval();
                    break;
            }
    }
}

var flyp_uuid;
//Add a refund address to altcoin order
function add_refund() {
	var refund_address = document.getElementById("bnomics-refund-input").value;

	var flypObj = new Object();
    flypObj.uuid = flyp_uuid;
    flypObj.address = refund_address;
    var flypObjString= JSON.stringify(flypObj);
    var refund = new XMLHttpRequest();
	refund.onreadystatechange = function() {
		if (this.readyState == 4 && this.status == 200) {
			address_present = true
			update_altcoin_status('refunded');
			info_order(flyp_uuid);
		}
	};
	refund.open("POST", "https://flyp.me/api/v1/order/addrefund", true);
	refund.setRequestHeader("Content-type", "application/json");
	refund.send(flypObjString);
}

function disableAltcoin() {
	document.getElementById("altcoin-waiting").style.display = "none";
	document.getElementById("paywrapper").style.display = "block";
	document.getElementById("alt-qrcode").innerHTML = "";
	document.getElementById("alt-amount").innerHTML = "";
	document.getElementById("alt-address").value = "";
	altcoin_waiting = false;
	clearInterval(interval);
	stop_interval();
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
var check_interval;
var send_email = false;
//Check the altcoin payment status
function check_order(uuid){
	var flypObj = new Object();
    flypObj.uuid = uuid;
    var systemUrlDiv = document.getElementById("system-url");
    var flypObjUUID = JSON.stringify(flypObj);
	var check = new XMLHttpRequest();
	check.onreadystatechange = function() {
		if (this.readyState == 4 && this.status == 200) {
			var response = JSON.parse(this.responseText);
			process_alt_response(response);
		}
	};
	check.open("POST", "https://flyp.me/api/v1/order/check", true);
	check.setRequestHeader("Content-type", "application/json");
	check.send(flypObjUUID);
}

var address_present = false;
//Check the full altcoin payment info
function info_order(uuid){
	//Fetch the altcoin info using uuid
	var flypObj = new Object();
    flypObj.uuid = uuid;
    document.getElementById("alt-uuid").innerHTML = uuid;
    var flypObjUUID = JSON.stringify(flypObj);
	var check = new XMLHttpRequest();
	check.onreadystatechange = function() {
		if (this.readyState == 4 && this.status == 200) {
			var response = JSON.parse(this.responseText);
			process_alt_response(response);
		}
	};
	check.open("POST", "https://flyp.me/api/v1/order/info", true);
	check.setRequestHeader("Content-type", "application/json");
	check.send(flypObjUUID);
}

function update_altcoin_status(status) {
	var all_status = document.getElementsByClassName("altcoin-status");
	for(var i = 0; i < all_status.length; i++){
   		if (all_status.item(i).id == status){
   			all_status.item(i).style.display = "block";
   			var altcoinWaiting = document.getElementById("altcoin-waiting");
   			altcoinWaiting.style.display = "block";
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

document.addEventListener('DOMContentLoaded', function() {
	if(document.getElementById('flyp-uuid') !== null){
		var flypDiv = document.getElementById("flyp-uuid");
		flyp_uuid = flypDiv.dataset.uuid;
		info_order(flyp_uuid);
	}
});
