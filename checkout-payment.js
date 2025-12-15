(function(){
  function qs(s, root){ return (root||document).querySelector(s); }
  function qsa(s, root){ return Array.prototype.slice.call((root||document).querySelectorAll(s)); }
  function on(el, ev, fn){ if(el) el.addEventListener(ev, fn); }

  var form = qs('form[action="place_order.php"]');
  if(!form) return;

  var radios = qsa('input[name="payment_method"]', form);
  var sections = {
    COD: qs('#section_cod', form) || null,
    CARD: qs('#section_card', form),
    NETBANKING: qs('#section_netbank', form),
    WALLET: qs('#section_wallet', form)
  };
  var btn = qs('#btn_place_order', form);
  var btnShowQr = qs('#btn_show_qr', form);
  var qrImg = qs('#wallet_qr', form);
  var qrWrap = qs('#wallet_qr_wrap', form);
  var totalInput = qs('input[name="order_total"]', form);

  function currentMethod(){
    var r = radios.find(function(x){ return x.checked; });
    return r ? r.value : 'COD';
  }

  function updateUI(){
    var m = currentMethod();
    Object.keys(sections).forEach(function(k){
      if(!sections[k]) return;
      sections[k].style.display = (k === m) ? '' : 'none';
    });
    if(btn){ btn.textContent = 'Place Order (' + (m === 'COD' ? 'COD' : (m === 'CARD' ? 'Card' : (m === 'NETBANKING' ? 'Net Banking' : 'Wallet'))) + ')'; }
  }

  radios.forEach(function(r){ on(r, 'change', updateUI); });
  updateUI();

  on(btnShowQr, 'click', function(){
    var amt = (totalInput && totalInput.value) ? totalInput.value : '0.00';
    var upiId = (qs('input[name="upi_id"]', form) || {}).value || 'mandaliatanvi1504@okaxis';
    var payeeName = 'Tanvi Mandaliya1504';
    var upi = 'upi://pay?pa=' + encodeURIComponent(upiId) + '&pn=' + encodeURIComponent(payeeName) + '&am=' + encodeURIComponent(amt) + '&cu=INR';
    var url = 'https://api.qrserver.com/v1/create-qr-code/?size=180x180&data=' + encodeURIComponent(upi);
    if(qrImg){ qrImg.src = url; }
    if(qrWrap){ qrWrap.style.display = ''; }
  });
})();
