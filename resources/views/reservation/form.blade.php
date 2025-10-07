<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Complete Your Reservation</title>
  <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="{{ asset('css/reservation-portal.css') }}">
  <style>
    body { background:#0b0e14; font-family:'Montserrat',sans-serif; margin:0; color:#f5f7fb; }
    .reservation-wrapper { max-width:1180px; margin: clamp(60px,6vh,120px) auto 60px; padding: clamp(16px,2.2vw,38px); display:grid; grid-template-columns: 1.1fr .9fr; gap:40px; }
  h1 { margin:0 0 10px; font-size: clamp(1.65rem,1.2rem + 1.2vw,2.3rem); background:linear-gradient(135deg,#2a6aff,#6fb8ff); -webkit-background-clip:text; background-clip:text; color:transparent; }
    .subtext { font-size:.85rem; opacity:.75; margin-bottom:26px; }
    .panel { background:#121823; border:1px solid #1e2a3d; border-radius:18px; padding:26px 26px 30px; position:relative; overflow:hidden; }
    .panel::before { content:""; position:absolute; inset:0; background:radial-gradient(circle at 85% 15%,rgba(52,112,255,0.15),transparent 60%); pointer-events:none; }
    .reserved-list-header { display:flex; justify-content:space-between; align-items:center; margin-bottom:12px; }
    .reserved-items { display:flex; flex-direction:column; gap:14px; max-height:460px; overflow-y:auto; padding-right:4px; }
    .res-item { display:flex; gap:16px; background:#192232; border:1px solid #253246; border-radius:14px; padding:14px 16px; align-items:stretch; }
    .res-thumb { width:72px; height:72px; border-radius:10px; background:#0d121b; display:flex; align-items:center; justify-content:center; font-size:.65rem; color:#6d7a8c; overflow:hidden; }
    .res-thumb img { width:100%; height:100%; object-fit:cover; display:block; }
    .res-info { flex:1; display:flex; flex-direction:column; justify-content:space-between; }
    .res-title { font-size:.9rem; font-weight:600; letter-spacing:.4px; margin-bottom:4px; }
    .res-meta { font-size:.65rem; text-transform:uppercase; letter-spacing:.8px; opacity:.7; margin-bottom:6px; }
    .res-price { font-size:.8rem; font-weight:600; color:#8ab6ff; }
    .res-qty { font-size:.65rem; opacity:.75; }
    .summary-total { margin-top:18px; display:flex; justify-content:flex-end; font-weight:600; font-size:.95rem; gap:8px; align-items:center; }
    .summary-total span:last-child { font-size:1.05rem; color:#ffffff; }

    .form-section { display:flex; flex-direction:column; gap:22px; }
    .field-group { display:flex; flex-direction:column; gap:6px; }
    .field-group label { font-size:.7rem; font-weight:600; letter-spacing:.8px; text-transform:uppercase; color:#9fb4d4; }
    .field-group input { background:#161f2c; border:1px solid #253246; border-radius:10px; padding:12px 14px; font-size:.85rem; color:#e6edf4; font-family:inherit; transition:border .25s, background .25s; }
    .field-group input:focus { outline:none; border-color:#2a6aff; background:#1d2735; }
    .two-cols { display:grid; grid-template-columns:1fr 1fr; gap:16px; }
    .inline-date-time { display:grid; grid-template-columns: 1fr 1fr; gap:16px; }

    .agreements { margin-top:12px; background:#161f28; border:1px solid #263246; border-radius:14px; padding:16px 18px; font-size:.65rem; line-height:1.4; position:relative; }
    .agreements h3 { margin:0 0 10px; font-size:.72rem; letter-spacing:.7px; font-weight:600; text-transform:uppercase; color:#9fb4d4; }
    .check-row { display:flex; gap:10px; align-items:flex-start; margin-bottom:8px; }
    .check-row input { margin-top:2px; }
    .check-all-row { display:flex; gap:8px; align-items:center; padding:10px 12px; border-top:1px solid #253246; margin-top:10px; }

    .actions { margin-top:32px; display:flex; justify-content:space-between; gap:18px; flex-wrap:wrap; }
    .btn { cursor:pointer; border:none; border-radius:12px; font-family:inherit; font-weight:600; letter-spacing:.5px; font-size:.8rem; padding:14px 22px; display:inline-flex; align-items:center; gap:8px; position:relative; }
    .btn-primary { background:linear-gradient(135deg,#2a6aff,#4aa3ff); color:#fff; box-shadow:0 8px 24px -6px rgba(42,106,255,.45); }
    .btn-primary:disabled { opacity:.4; cursor:not-allowed; box-shadow:none; }
    .btn-outline { background:#141c27; color:#d2deee; border:1px solid #2d3a4d; }
    .btn-outline:hover { border-color:#4aa3ff; color:#ffffff; }
    .btn-primary:not(:disabled):hover { filter:brightness(1.08); }

    .empty-note { font-size:.75rem; opacity:.7; }

    /* Receipt Modal */
    .receipt-modal { position:fixed; inset:0; display:none; align-items:center; justify-content:center; background:rgba(0,0,0,0.65); backdrop-filter:blur(4px); z-index:1000; padding:30px 18px; }
    .receipt-modal.active { display:flex; }
    .receipt-panel { background:#ffffff; color:#1a2230; width:100%; max-width:560px; border-radius:20px; padding:34px 38px 42px; position:relative; box-shadow:0 18px 48px -12px rgba(0,0,0,.55); font-family:'Montserrat',sans-serif; }
    .receipt-panel h2 { margin:0 0 6px; font-size:1.05rem; letter-spacing:.5px; font-weight:700; color:#163156; }
    .receipt-meta { font-size:.62rem; letter-spacing:.7px; text-transform:uppercase; opacity:.65; margin-bottom:18px; display:flex; flex-wrap:wrap; gap:10px; }
    .receipt-store { font-weight:600; font-size:.7rem; letter-spacing:.8px; margin-bottom:2px; }
    .receipt-separator { border:none; border-top:1px dashed #b6c4d6; margin:14px 0; }
    .receipt-items { width:100%; border-collapse:collapse; font-size:.7rem; }
    .receipt-items thead th { text-align:left; font-weight:600; padding:6px 0; border-bottom:1px solid #d5dde6; font-size:.65rem; letter-spacing:.6px; }
    .receipt-items tbody td { padding:6px 0 4px; vertical-align:top; }
    .ri-name { width:56%; font-weight:600; }
    .ri-qty { width:8%; text-align:center; }
    .ri-price, .ri-sub { width:18%; text-align:right; }
    .receipt-total-row td { padding-top:10px; border-top:1px solid #d5dde6; font-weight:600; }
    .receipt-summary { margin-top:12px; font-size:.62rem; line-height:1.35; background:#f1f5f9; padding:10px 12px 12px; border-radius:12px; color:#2c3d55; }
    .receipt-summary strong { color:#14283e; }
    .receipt-actions { margin-top:26px; display:flex; gap:12px; flex-wrap:wrap; }
    .receipt-actions button { flex:1 1 0; cursor:pointer; border:none; border-radius:12px; font-weight:600; font-size:.7rem; letter-spacing:.5px; padding:12px 16px; }
    .btn-download { background:linear-gradient(135deg,#2a6aff,#4aa3ff); color:#fff; }
    .btn-close { background:#dde6f1; color:#1b2735; }
    .btn-close:hover { filter:brightness(1.05); }
    .btn-download:hover { filter:brightness(1.08); }
    .receipt-footer-note { margin-top:18px; font-size:.55rem; text-align:center; letter-spacing:.5px; text-transform:uppercase; opacity:.6; }
    .receipt-badge { position:absolute; top:-14px; right:18px; background:linear-gradient(135deg,#2a6aff,#6fb8ff); color:#fff; padding:6px 14px 8px; font-size:.55rem; font-weight:600; border-radius:0 0 12px 12px; letter-spacing:.9px; box-shadow:0 6px 16px -6px rgba(42,106,255,.55); }
    .receipt-logo { font-size:1rem; font-weight:700; letter-spacing:.5px; margin-bottom:2px; background:linear-gradient(135deg,#2a6aff,#6fb8ff); -webkit-background-clip:text; background-clip:text; color:transparent; }
    .receipt-id { font-size:.62rem; letter-spacing:.6px; opacity:.75; margin-bottom:10px; }
    @media (max-width:620px){
      .receipt-panel { padding:28px 24px 36px; }
      .receipt-items thead { display:none; }
      .receipt-items tbody tr { display:grid; grid-template-columns:1fr auto; padding:6px 0 4px; border-bottom:1px solid #e2e8f0; }
      .receipt-items tbody tr:last-child { border-bottom:none; }
      .ri-name { grid-column:1 / -1; }
      .ri-qty { order:2; }
      .ri-price { display:none; }
      .ri-sub { text-align:right; }
      .receipt-total-row td { grid-column:1 / -1; }
    }
    /* Canvas export ensuring crisp result */
    .receipt-export-target { background:#ffffff; }

    @media (max-width:960px){ .reservation-wrapper { grid-template-columns:1fr; } .actions { justify-content:flex-end; } }
    @media (max-width:560px){
      .reservation-wrapper { padding:20px 16px 80px; }
      .agreements { font-size:.62rem; }
      .res-item { gap:12px; padding:12px 12px 14px; }
      .res-thumb { width:62px; height:62px; }
      .actions { justify-content:center; gap:12px; flex-wrap:nowrap; }
      .actions .btn { flex:1 1 0; min-width:0; text-align:center; justify-content:center; padding:12px 10px; }
      .btn-primary { font-size:.75rem; }
      .btn-outline { font-size:.75rem; }
    }
  </style>
</head>
<body>
  <div class="reservation-wrapper" id="reservationApp">
    <div class="panel">
      <h1>Complete Your Reservation</h1>
      <div class="subtext">Review your reserved items and confirm your pick-up details below.</div>
      <div class="reserved-list-header">
        <strong style="font-size:.75rem; letter-spacing:.8px; opacity:.65; text-transform:uppercase;">Items</strong>
        <button class="btn btn-outline" id="editCartBtn" type="button">Return to Catalog</button>
      </div>
      <div class="reserved-items" id="reservedItems"></div>
      <div class="summary-total" id="summaryTotal" style="display:none;">
        <span>Total</span><span id="summaryTotalValue">₱ 0.00</span>
      </div>
    </div>
    <div class="panel">
      <h2 style="margin:0 0 18px; font-size:1.05rem; font-weight:600; letter-spacing:.6px;">Your Details</h2>
      <form id="reservationForm" novalidate>
        <div class="field-group">
          <label for="fullName">Full Name</label>
          <input id="fullName" name="fullName" required placeholder="Juan Dela Cruz" />
        </div>
        <div class="field-group">
          <label for="email">Email Address</label>
          <input id="email" name="email" type="email" required placeholder="you@example.com" />
        </div>
        <div class="field-group">
          <label for="phone">Phone Number</label>
          <input id="phone" name="phone" type="tel" required placeholder="09XX XXX XXXX" pattern="^(\+?63|0)9\d{9}$" />
        </div>
        <div class="inline-date-time">
          <div class="field-group">
            <label for="pickupDate">Pick-Up Date</label>
            <input id="pickupDate" name="pickupDate" type="date" required />
          </div>
          <div class="field-group">
            <label for="pickupTime">Pick-Up Time</label>
            <input id="pickupTime" name="pickupTime" type="time" required />
          </div>
        </div>
        <div class="agreements">
          <h3>Agreements</h3>
          <div class="check-row"><input type="checkbox" id="agreeTerms"> <label for="agreeTerms">I agree to the Terms and Conditions.</label></div>
          <div class="check-row"><input type="checkbox" id="agreePrivacy"> <label for="agreePrivacy">I have read and accept the Privacy Policy.</label></div>
          <div class="check-all-row"><input type="checkbox" id="agreeAll"> <label for="agreeAll"><strong>Select / Deselect All</strong></label></div>
        </div>
        <div class="actions">
          <button type="button" class="btn btn-outline" id="backCatalogBtn">Back to Catalog</button>
          <button type="submit" class="btn btn-primary" id="confirmReservationBtn" disabled>Confirm Reservation</button>
        </div>
      </form>
    </div>
  </div>

  <script>
    // Populate and manage reservation data from localStorage cart (sv_cart)
    const cartKey = 'sv_cart';
    const itemsEl = document.getElementById('reservedItems');
    const totalRow = document.getElementById('summaryTotal');
    const totalValue = document.getElementById('summaryTotalValue');
    const backCatalogBtn = document.getElementById('backCatalogBtn');
    const editCartBtn = document.getElementById('editCartBtn');

    function formatCurrency(n){ return '₱ ' + n.toLocaleString('en-PH',{minimumFractionDigits:2,maximumFractionDigits:2}); }

    function loadCart(){
      try { return JSON.parse(localStorage.getItem(cartKey)) || []; } catch(e){ return []; }
    }

    function renderItems(){
      const cart = loadCart();
      itemsEl.innerHTML = '';
      if(!cart.length){
        itemsEl.innerHTML = '<div class="empty-note">No items reserved yet. Return to the catalog to add products.</div>';
        totalRow.style.display = 'none';
        return;
      }
      let sum = 0;
      cart.forEach(item => {
        sum += item.priceNumber * item.qty;
        const div = document.createElement('div');
        div.className = 'res-item';
        div.innerHTML = `
          <div class="res-thumb">${ item.image ? `<img src="${item.image}" alt="${item.name}">` : 'NO IMG' }</div>
          <div class="res-info">
            <div>
              <div class="res-title">${item.name}</div>
              <div class="res-meta">${item.brand} • Size ${item.size} • ${item.color}</div>
            </div>
            <div style="display:flex; justify-content:space-between; align-items:flex-end; gap:8px;">
              <div class="res-price">${item.price}</div>
              <div class="res-qty">Qty: ${item.qty}</div>
            </div>
          </div>`;
        itemsEl.appendChild(div);
      });
      totalValue.textContent = formatCurrency(sum);
      totalRow.style.display = 'flex';
    }

    // Agreement logic
    const agreeTerms = document.getElementById('agreeTerms');
    const agreePrivacy = document.getElementById('agreePrivacy');
    const agreeAll = document.getElementById('agreeAll');
    const confirmBtn = document.getElementById('confirmReservationBtn');
    function updateConfirmState(){
      confirmBtn.disabled = !(agreeTerms.checked && agreePrivacy.checked);
      agreeAll.checked = agreeTerms.checked && agreePrivacy.checked;
    }
    agreeTerms.addEventListener('change', updateConfirmState);
    agreePrivacy.addEventListener('change', updateConfirmState);
    agreeAll.addEventListener('change', () => {
      const v = agreeAll.checked; agreeTerms.checked = v; agreePrivacy.checked = v; updateConfirmState();
    });

    // Form submit (placeholder for backend)
    document.getElementById('reservationForm').addEventListener('submit', (e) => {
      e.preventDefault();
      if(confirmBtn.disabled) return;
      const data = new FormData(e.target);
      const cart = loadCart();
      const payload = {
        customer: Object.fromEntries(data.entries()),
        items: cart.map(({key, ...rest}) => rest),
        total: cart.reduce((s,i)=> s + i.priceNumber * i.qty, 0)
      };
      console.log('Reservation submission payload (POST /api/reservations):', payload);
      alert('Reservation submitted! (Check console for payload)');
      // Potential future: redirect to success page or clear cart
    });

    // Navigation buttons
    backCatalogBtn.addEventListener('click', () => { window.location.href = "{{ route('reservation.portal') }}"; });
    editCartBtn.addEventListener('click', () => { window.location.href = "{{ route('reservation.portal') }}#cart"; });

    // Initialize
    renderItems();
    updateConfirmState();

    // Enforce future date/time minimal (today for date, next hour for time)
    const dateInput = document.getElementById('pickupDate');
    const timeInput = document.getElementById('pickupTime');
    const now = new Date();
    const yyyy = now.getFullYear();
    const mm = String(now.getMonth()+1).padStart(2,'0');
    const dd = String(now.getDate()).padStart(2,'0');
    dateInput.min = `${yyyy}-${mm}-${dd}`;
  </script>
  
  <!-- Receipt Modal -->
  <div class="receipt-modal" id="receiptModal" aria-hidden="true">
    <div class="receipt-panel receipt-export-target" id="receiptPanel">
      <div class="receipt-badge">E-RECEIPT</div>
      <div class="receipt-logo">Shoe Vault Batangas</div>
      <div class="receipt-id" id="receiptId">Receipt #TEMP</div>
      <h2>Reservation Confirmation</h2>
      <div class="receipt-meta" id="receiptMeta"></div>
      <table class="receipt-items" id="receiptItemsTable">
        <thead>
          <tr><th>Item</th><th>Qty</th><th>Price</th><th>Subtotal</th></tr>
        </thead>
        <tbody></tbody>
        <tfoot>
          <tr class="receipt-total-row"><td colspan="3" style="text-align:right;">Total</td><td id="receiptTotal">₱ 0.00</td></tr>
        </tfoot>
      </table>
      <div class="receipt-summary" id="receiptSummary"></div>
      <div class="receipt-actions">
        <button class="btn-download" id="downloadReceiptBtn" type="button">Download PNG</button>
        <button class="btn-close" id="closeReceiptBtn" type="button">Close</button>
      </div>
      <div class="receipt-footer-note">Thank you for reserving with us!</div>
    </div>
  </div>
  <script src="https://cdn.jsdelivr.net/npm/html2canvas@1.4.1/dist/html2canvas.min.js" integrity="sha256-eRhlJ74VwHFVg5K9Wf5puhhH4d0vCPQvapu8YQ+7aoM=" crossorigin="anonymous"></script>
  <script>
    // Receipt generation & export
    const receiptModal = document.getElementById('receiptModal');
    const receiptPanel = document.getElementById('receiptPanel');
    const receiptIdEl = document.getElementById('receiptId');
    const receiptMetaEl = document.getElementById('receiptMeta');
    const receiptItemsTbody = document.querySelector('#receiptItemsTable tbody');
    const receiptTotalEl = document.getElementById('receiptTotal');
    const receiptSummaryEl = document.getElementById('receiptSummary');
    const downloadBtn = document.getElementById('downloadReceiptBtn');
    const closeReceiptBtn = document.getElementById('closeReceiptBtn');

    function generateReceiptNumber(){
      const now = new Date();
      return 'SV-' + now.getFullYear().toString().slice(-2) + (now.getMonth()+1).toString().padStart(2,'0') + now.getDate().toString().padStart(2,'0') + '-' + Math.random().toString(36).substring(2,8).toUpperCase();
    }

    function openReceipt(payload){
      // payload: { customer, items, total }
      receiptIdEl.textContent = 'Receipt #' + generateReceiptNumber();
      const now = new Date();
      const dateStr = now.toLocaleDateString('en-PH',{year:'numeric',month:'short',day:'2-digit'});
      const timeStr = now.toLocaleTimeString('en-PH',{hour:'2-digit',minute:'2-digit'});
      receiptMetaEl.innerHTML = `<span>${dateStr}</span><span>${timeStr}</span><span>${payload.customer.fullName||''}</span><span>${payload.customer.phone||''}</span>`;

      // Items
      receiptItemsTbody.innerHTML = '';
      payload.items.forEach(it => {
        const tr = document.createElement('tr');
        const subtotal = it.priceNumber * it.qty;
        tr.innerHTML = `
          <td class="ri-name">${it.name}<div style="font-weight:400; opacity:.55; font-size:.58rem; letter-spacing:.5px;">${it.brand || ''} • Size ${it.size} • ${it.color}</div></td>
          <td class="ri-qty">${it.qty}</td>
          <td class="ri-price">${it.price}</td>
          <td class="ri-sub">₱ ${(subtotal).toLocaleString('en-PH',{minimumFractionDigits:2,maximumFractionDigits:2})}</td>`;
        receiptItemsTbody.appendChild(tr);
      });
      receiptTotalEl.textContent = formatCurrency(payload.total);
      receiptSummaryEl.innerHTML = `<strong>Pickup:</strong> ${payload.customer.pickupDate || ''} @ ${payload.customer.pickupTime || ''}<br><strong>Email:</strong> ${payload.customer.email || ''}`;
      receiptModal.classList.add('active');
      receiptModal.setAttribute('aria-hidden','false');
    }

    function closeReceipt(){
      receiptModal.classList.remove('active');
      receiptModal.setAttribute('aria-hidden','true');
    }

    downloadBtn.addEventListener('click', async () => {
      downloadBtn.disabled = true; downloadBtn.textContent = 'Rendering...';
      try {
        const canvas = await html2canvas(receiptPanel,{backgroundColor:'#ffffff',scale:2});
        const link = document.createElement('a');
        link.download = (receiptIdEl.textContent.replace(/[^A-Z0-9-]/gi,'_')) + '.png';
        link.href = canvas.toDataURL('image/png');
        link.click();
      } catch(err){
        console.error('Receipt export failed', err);
        alert('Failed to generate image. Please try again.');
      } finally {
        downloadBtn.disabled = false; downloadBtn.textContent = 'Download PNG';
      }
    });

    closeReceiptBtn.addEventListener('click', closeReceipt);
    receiptModal.addEventListener('click', (e)=>{ if(e.target === receiptModal) closeReceipt(); });

    // Intercept existing reservation submit logic to show receipt
    const originalForm = document.getElementById('reservationForm');
    originalForm.addEventListener('submit', (e) => {
      // previous handler already prevented default & alerted; we extend logic afterwards.
      // We rely on earlier added listener; to avoid double submission you could refactor into single handler if needed.
      setTimeout(()=>{
        const cart = loadCart();
        const formData = new FormData(originalForm);
        const customer = Object.fromEntries(formData.entries());
        const payload = {
          customer,
          items: cart.map(({key, ...rest}) => rest),
          total: cart.reduce((s,i)=> s + i.priceNumber * i.qty, 0)
        };
        openReceipt(payload);
      },50);
    });
  </script>
</body>
</html>
